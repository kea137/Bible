<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { usePage } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const props = defineProps<{
    open: boolean;
    verseId: number;
    verseText: string;
    verseReference: string;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    saved: [];
}>();

const page = usePage();
const noteTitle = ref('');
const noteContent = ref('');
const saving = ref(false);

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            // Reset form when dialog opens
            noteTitle.value = '';
            noteContent.value = '';
        }
    },
);

async function saveNote() {
    if (!noteContent.value.trim()) {
        alert(t('Please enter a note'));
        return;
    }

    saving.value = true;

    try {
        let csrfToken = document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content');
        if (!csrfToken && page.props.csrf_token) {
            csrfToken = String(page.props.csrf_token);
        }
        if (!csrfToken) {
            alert(
                'CSRF token not found. Refreshing page to fix authentication...',
            );
            window.location.reload();
            return;
        }

        const response = await fetch('/api/notes', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                Accept: 'application/json',
            },
            body: JSON.stringify({
                verse_id: props.verseId,
                title: noteTitle.value,
                content: noteContent.value,
            }),
        });

        const result = await response.json();

        if (response.ok && result?.success) {
            emit('saved');
            emit('update:open', false);
        } else {
            alert(result?.message || 'Failed to save note.');
        }
    } catch (error) {
        alert('Failed to save note.');
        console.error(error);
    } finally {
        saving.value = false;
    }
}

function closeDialog() {
    emit('update:open', false);
}
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent class="sm:max-w-[525px]">
            <DialogHeader>
                <DialogTitle>{{ t('Add Note to Verse') }}</DialogTitle>
                <DialogDescription>
                    {{ verseReference }}
                </DialogDescription>
            </DialogHeader>
            <div class="grid gap-4 py-4">
                <div class="rounded-lg border bg-muted/50 p-3">
                    <p class="text-sm italic">"{{ verseText }}"</p>
                </div>
                <div class="grid gap-2">
                    <Label for="note-title"> {{t('Title (Optional)')}} </Label>
                    <Input
                        id="note-title"
                        v-model="noteTitle"
                        :placeholder="t('Enter a title for your note...')"
                    />
                </div>
                <div class="grid gap-2">
                    <Label for="note-content">
                        {{t('Note')}} <span class="text-destructive">*</span>
                    </Label>
                    <Textarea
                        id="note-content"
                        v-model="noteContent"
                        :placeholder="t('Write your thoughts, insights, or reflections...')"
                        rows="6"
                    />
                </div>
            </div>
            <DialogFooter>
                <Button
                    variant="outline"
                    @click="closeDialog"
                    :disabled="saving"
                >
                    {{t('Cancel')}}
                </Button>
                <Button @click="saveNote" :disabled="saving">
                    <LoaderCircle
                        v-if="saving"
                        class="mr-2 h-4 w-4 animate-spin"
                    />
                    {{t('Save Note')}}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
