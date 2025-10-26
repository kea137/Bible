<script setup lang="ts">
import AlertUser from '@/components/AlertUser.vue';
import NotesDialog from '@/components/NotesDialog.vue';
import Button from '@/components/ui/button/Button.vue';
import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import CardDescription from '@/components/ui/card/CardDescription.vue';
import CardHeader from '@/components/ui/card/CardHeader.vue';
import CardTitle from '@/components/ui/card/CardTitle.vue';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import ScrollArea from '@/components/ui/scroll-area/ScrollArea.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { highlighted_verses_page } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { BookOpen, Highlighter, MoreVertical } from 'lucide-vue-next';
import { onMounted, ref, computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Highlighted Verses',
        href: highlighted_verses_page().url,
    },
];

const highlights = ref<any[]>([]);
const notesDialogOpen = ref(false);
const selectedVerseForNote = ref<any>(null);
const page = usePage();
const successMessage = computed(() => page.props.success as string);
const errorMessage = computed(() => page.props.error as string);
const alertSuccess = ref(!!successMessage.value);
const alertError = ref(!!errorMessage.value);
const alertErrorMessage = ref('');

async function loadHighlights() {
    try {
        const response = await fetch('/api/verse-highlights');
        if (response.ok) {
            highlights.value = await response.json();
        }
    } catch (error) {
        console.error('Failed to load highlights:', error);
    }
}

onMounted(() => {
    loadHighlights();
});

function getHighlightColorClass(color: string): string {
    if (color === 'yellow') {
        return 'border-l-yellow-400 bg-yellow-50 dark:bg-yellow-900/20';
    } else if (color === 'green') {
        return 'border-l-green-400 bg-green-50 dark:bg-green-900/20';
    }
    return '';
}

async function removeHighlight(highlight: any) {
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

        const response = await fetch(
            '/api/verse-highlights/' + highlight.verse.id,
            {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    Accept: 'application/json',
                },
            },
        );

        if (response.ok) {
            // Remove from local array
            highlights.value = highlights.value.filter(
                (h) => h.id !== highlight.id,
            );
            alertSuccess.value = true;
        } else {
            alertError.value = true;
        }
    } catch (error) {
        console.error('Failed to remove highlight:', error);
        alertError.value = true;
    }
}

function studyVerse(verseId: number) {
    router.visit(`/verses/${verseId}/study`);
}

function openNotesDialog(highlight: any) {
    selectedVerseForNote.value = highlight.verse;
    notesDialogOpen.value = true;
}

function handleNoteSaved() {
    alertSuccess.value = true;
}
</script>

<template>
    <Head title="Highlighted Verses" />

    <AlertUser
        v-if="alertSuccess"
        :open="true"
        :title="t('Success')"
        :confirmButtonText="'OK'"
        :message="t('Operation was successful!')"
        variant="success"
        @update:open="() => (alertSuccess = false)"
    />
    <AlertUser
        v-if="alertError"
        :open="true"
        :title="t('Error')"
        :confirmButtonText="'OK'"
        :message="t('Operation failed! Please try again.')"
        variant="error"
        @update:open="() => (alertError = false)"
    />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-3 overflow-x-auto rounded-xl p-2 sm:gap-4 sm:p-4"
        >
            <!-- Reading Reminder -->
            <Card
                class="border-primary/20 bg-gradient-to-r from-primary/5 to-primary/10"
            >
                <CardContent class="pt-2">
                    <div
                        class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div>
                            <h3 class="mb-1 text-sm font-semibold sm:text-base">
                                {{t('Make Reading a Habit')}}
                            </h3>
                            <p class="text-xs text-muted-foreground sm:text-sm">
                                {{t('Set aside time each day to read and reflect on')}}
                                the Word
                            </p>
                        </div>
                        <BookOpen
                            class="h-6 w-6 text-primary/40 sm:h-8 sm:w-8"
                        />
                    </div>
                </CardContent>
            </Card>

            <!-- Highlighted Verses -->
            <Card v-if="highlights.length > 0">
                <CardHeader class="pb-3">
                    <div class="flex items-center gap-2">
                        <Highlighter
                            class="h-4 w-4 text-primary sm:h-5 sm:w-5"
                        />
                        <CardTitle class="text-base sm:text-lg"
                            >{{t('Your Highlighted Verses')}}</CardTitle
                        >
                    </div>
                    <CardDescription class="text-xs sm:text-sm"
                        >{{t('Recent verses you\'ve marked')}}</CardDescription
                    >
                </CardHeader>
                <CardContent>
                    <ScrollArea class="h-100 space-y-2 sm:space-y-3">
                        <div
                            v-for="highlight in highlights"
                            :key="highlight.id"
                            :class="[
                                'group relative rounded-r border-l-4 py-2 pl-3 transition-colors sm:pl-4',
                                getHighlightColorClass(highlight.color),
                            ]"
                        >
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex-1">
                                    <p class="mb-2 text-xs sm:text-sm">
                                        {{ highlight.verse.text }}
                                    </p>
                                    <p
                                        class="text-xs font-medium text-muted-foreground"
                                    >
                                        {{ highlight.verse.book?.title }}
                                        {{
                                            highlight.verse.chapter
                                                ?.chapter_number
                                        }}:{{ highlight.verse.verse_number }}
                                    </p>
                                    <p
                                        v-if="highlight.note"
                                        class="mt-1 text-xs text-muted-foreground italic"
                                    >
                                        {{t('Note:')}} {{ highlight.note }}
                                    </p>
                                </div>
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            class="h-8 w-8 p-0"
                                        >
                                            <MoreVertical class="h-4 w-4" />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end">
                                        <DropdownMenuLabel
                                            >{{t('Actions')}}</DropdownMenuLabel
                                        >
                                        <DropdownMenuSeparator />
                                        <DropdownMenuItem
                                            @click="
                                                studyVerse(highlight.verse.id)
                                            "
                                        >
                                            {{t('Study this Verse')}}
                                        </DropdownMenuItem>
                                        <DropdownMenuItem
                                            @click="openNotesDialog(highlight)"
                                        >
                                            {{t('Add/Edit Note')}}
                                        </DropdownMenuItem>
                                        <DropdownMenuSeparator />
                                        <DropdownMenuItem
                                            @click="removeHighlight(highlight)"
                                            class="text-destructive"
                                        >
                                            {{t('Remove Highlight')}}
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </div>
                        </div>
                    </ScrollArea>
                </CardContent>
            </Card>

            <!-- Empty State -->
            <Card v-else>
                <CardContent class="py-8 text-center">
                    <Highlighter
                        class="mx-auto h-12 w-12 text-muted-foreground/50"
                    />
                    <h3 class="mt-4 text-lg font-semibold">
                        {{t('No Highlighted Verses')}}
                    </h3>
                    <p class="mt-2 text-sm text-muted-foreground">
                        {{t('Start highlighting verses as you read through the Bible')}}
                        {{t('to keep track of important passages.')}}
                    </p>
                </CardContent>
            </Card>
        </div>

        <!-- Notes Dialog -->
        <NotesDialog
            v-if="selectedVerseForNote"
            :open="notesDialogOpen"
            @update:open="notesDialogOpen = $event"
            :verse-id="selectedVerseForNote.id"
            :verse-text="selectedVerseForNote.text"
            :verse-reference="`${selectedVerseForNote.book?.title} ${selectedVerseForNote.chapter?.chapter_number}:${selectedVerseForNote.verse_number}`"
            @saved="handleNoteSaved"
        />
    </AppLayout>
</template>
