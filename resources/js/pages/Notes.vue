<script setup lang="ts">
import AlertUser from '@/components/AlertUser.vue';
import Button from '@/components/ui/button/Button.vue';
import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import CardDescription from '@/components/ui/card/CardDescription.vue';
import CardHeader from '@/components/ui/card/CardHeader.vue';
import CardTitle from '@/components/ui/card/CardTitle.vue';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { FileText, LoaderCircle, Pencil, Save, Trash2, X } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Notes',
        href: '/notes',
    },
];

// page props for notes data
defineProps<{
    notes: any[];
    csrf_token?: string;
    success?: string;
    error?: string;
}>();

const page = usePage();
const selectedNote = ref<any>(null);
const editMode = ref(false);
const editTitle = ref('');
const editContent = ref('');
const saving = ref(false);
const deleting = ref(false);
const showDeleteDialog = ref(false);

const successMessage = computed(() => page.props.success as string);
const errorMessage = computed(() => page.props.error as string);
const alertSuccess = ref(!!successMessage.value);
const alertError = ref(!!errorMessage.value);
const alertErrorMessage = ref('');

function selectNote(note: any) {
    selectedNote.value = note;
    editMode.value = false;
    editTitle.value = note.title || '';
    editContent.value = note.content || '';
}

function startEdit() {
    if (!selectedNote.value) return;
    editMode.value = true;
    editTitle.value = selectedNote.value.title || '';
    editContent.value = selectedNote.value.content || '';
}

function cancelEdit() {
    editMode.value = false;
    if (selectedNote.value) {
        editTitle.value = selectedNote.value.title || '';
        editContent.value = selectedNote.value.content || '';
    }
}

async function saveNote() {
    if (!selectedNote.value) return;

    saving.value = true;

    try {
        let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (!csrfToken && page.props.csrf_token) {
            csrfToken = String(page.props.csrf_token);
        }

        const response = await fetch(`/api/notes/${selectedNote.value.id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken || '',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                title: editTitle.value,
                content: editContent.value,
            }),
        });

        const result = await response.json();

        if (response.ok && result?.success) {
            // Update the selected note with new values
            selectedNote.value.title = editTitle.value;
            selectedNote.value.content = editContent.value;
            editMode.value = false;
            alertSuccess.value = true;
            // Reload the page to refresh notes list
            router.reload({ only: ['notes'] });
        } else {
            alertErrorMessage.value = result?.message || 'Failed to save note.';
            alertError.value = true;
        }
    } catch (error) {
        alertErrorMessage.value = 'Failed to save note.';
        alertError.value = true;
        console.error(error);
    } finally {
        saving.value = false;
    }
}

function confirmDelete() {
    showDeleteDialog.value = true;
}

async function deleteNote() {
    if (!selectedNote.value) return;

    showDeleteDialog.value = false;
    deleting.value = true;

    try {
        let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (!csrfToken && page.props.csrf_token) {
            csrfToken = String(page.props.csrf_token);
        }

        const response = await fetch(`/api/notes/${selectedNote.value.id}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken || '',
                'Accept': 'application/json',
            },
        });

        const result = await response.json();

        if (response.ok && result?.success) {
            selectedNote.value = null;
            editMode.value = false;
            alertSuccess.value = true;
            // Reload the page to refresh notes list
            router.reload({ only: ['notes'] });
        } else {
            alertErrorMessage.value = result?.message || 'Failed to delete note.';
            alertError.value = true;
        }
    } catch (error) {
        alertErrorMessage.value = 'Failed to delete note.';
        alertError.value = true;
        console.error(error);
    } finally {
        deleting.value = false;
    }
}

</script>

<template>
    <Head title="Notes" />

    <AlertUser
        v-if="alertSuccess"
        :open="true"
        title="Success"
        :confirmButtonText="'OK'"
        :message="successMessage || 'Note saved successfully'"
        variant="success"
        @update:open="() => (alertSuccess = false)"
    />
    <AlertUser
        v-if="alertError"
        :open="true"
        title="Error"
        :confirmButtonText="'OK'"
        :message="errorMessage || alertErrorMessage"
        variant="error"
        @update:open="() => (alertError = false)"
    />

    <!-- Delete Confirmation Dialog -->
    <AlertDialog v-model:open="showDeleteDialog">
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>Delete Note</AlertDialogTitle>
                <AlertDialogDescription>
                    Are you sure you want to delete this note? This action cannot be undone.
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel>Cancel</AlertDialogCancel>
                <AlertDialogAction @click="deleteNote" class="bg-destructive text-destructive-foreground hover:bg-destructive/90">
                    Delete
                </AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-row gap-4 overflow-x-auto rounded-xl p-4"
        >
            <!-- Notes List (Left Side - 1/3) -->
            <div class="flex-[1]">
                <Card class="h-full">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <FileText class="h-5 w-5" />
                            My Notes
                        </CardTitle>
                        <CardDescription>{{ notes.length }} notes</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <ScrollArea v-if="notes.length > 0" class="h-[calc(100vh-16rem)]">
                            <div class="space-y-2">
                                <div
                                    v-for="note in notes"
                                    :key="note.id"
                                    class="cursor-pointer rounded-lg border p-3 transition-colors hover:bg-accent/50"
                                    :class="{ 'bg-accent': selectedNote?.id === note.id }"
                                    @click="selectNote(note)"
                                >
                                    <div class="mb-2">
                                        <p class="text-sm font-semibold text-primary">
                                            {{ note.verse.book?.title }}
                                            {{ note.verse.chapter?.chapter_number }}:{{ note.verse.verse_number }}
                                        </p>
                                    </div>
                                    <p v-if="note.title" class="mb-1 text-sm font-medium">
                                        {{ note.title }}
                                    </p>
                                    <p class="line-clamp-2 text-xs text-muted-foreground">
                                        {{ note.content }}
                                    </p>
                                    <p class="mt-2 text-xs text-muted-foreground italic">
                                        "{{ note.verse.text.substring(0, 60) }}..."
                                    </p>
                                </div>
                            </div>
                        </ScrollArea>
                        <div v-else class="py-8 text-center text-muted-foreground">
                            <p class="mb-4">No notes yet</p>
                            <p class="text-sm">
                                Add notes to verses while reading to see them here
                            </p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Note Details (Right Side - 2/3) -->
            <div class="flex-[2]">
                <Card class="h-full">
                    <CardHeader v-if="selectedNote">
                        <div class="flex items-center justify-between">
                            <div class="flex-1 mr-4">
                                <CardTitle>
                                    {{ selectedNote.verse.book?.title }}
                                    {{ selectedNote.verse.chapter?.chapter_number }}:{{ selectedNote.verse.verse_number }}
                                </CardTitle>
                                <CardDescription class="mt-2">
                                    "{{ selectedNote.verse.text }}"
                                </CardDescription>
                            </div>
                            <div class="flex gap-2">
                                <Button
                                    v-if="!editMode"
                                    variant="outline"
                                    class=" cursor-pointer"
                                    size="sm"
                                    @click="startEdit"
                                >
                                    <Pencil class="h-4 w-4 mr-1" />
                                    Edit
                                </Button>
                                <Button
                                    v-if="!editMode"
                                    variant="destructive"
                                    size="sm"
                                    class=" cursor-pointer"
                                    @click="confirmDelete"
                                    :disabled="deleting"
                                >
                                    <LoaderCircle
                                        v-if="deleting"
                                        class="mr-1 h-4 w-4 animate-spin"
                                    />
                                    <Trash2 v-else class="h-4 w-4 mr-1" />
                                    Delete
                                </Button>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent v-if="selectedNote" class="space-y-4">
                        <div v-if="editMode" class="space-y-4">
                            <div class="space-y-2">
                                <Label for="edit-title">Title (Optional)</Label>
                                <Input
                                    id="edit-title"
                                    v-model="editTitle"
                                    placeholder="Note title..."
                                />
                            </div>
                            <div class="space-y-2">
                                <Label for="edit-content">Content</Label>
                                <Textarea
                                    id="edit-content"
                                    v-model="editContent"
                                    placeholder="Your thoughts, insights, or reflections..."
                                    rows="12"
                                />
                            </div>
                            <div class="flex justify-end gap-2">
                                <Button
                                    variant="outline"
                                    @click="cancelEdit"
                                    :disabled="saving"
                                >
                                    <X class="h-4 w-4 mr-1" />
                                    Cancel
                                </Button>
                                <Button
                                    @click="saveNote"
                                    :disabled="saving"
                                >
                                    <LoaderCircle
                                        v-if="saving"
                                        class="mr-2 h-4 w-4 animate-spin"
                                    />
                                    <Save v-else class="h-4 w-4 mr-1" />
                                    Save Changes
                                </Button>
                            </div>
                        </div>
                        <div v-else class="space-y-4">
                            <div v-if="selectedNote.title">
                                <h3 class="text-lg font-semibold">{{ selectedNote.title }}</h3>
                            </div>
                            <div class="prose prose-sm dark:prose-invert max-w-none">
                                <p class="whitespace-pre-wrap">{{ selectedNote.content }}</p>
                            </div>
                            <div class="text-xs text-muted-foreground">
                                Created: {{ new Date(selectedNote.created_at).toLocaleDateString() }}
                            </div>
                        </div>
                    </CardContent>
                    <CardContent v-else class="py-16 text-center text-muted-foreground">
                        <FileText class="mx-auto mb-4 h-12 w-12 opacity-20" />
                        <p>Select a note from the list to view its details</p>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
