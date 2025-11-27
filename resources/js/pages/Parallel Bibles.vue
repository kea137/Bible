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
import {
    HoverCard,
    HoverCardContent,
    HoverCardTrigger,
} from '@/components/ui/hover-card';
import ScrollArea from '@/components/ui/scroll-area/ScrollArea.vue';
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import { bibles_parallel } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import {
    BookOpen,
    CheckCircle,
    ChevronLeft,
    ChevronRight,
    Share2,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const props = defineProps<{
    biblesOther: {
        id: number;
        name: string;
        abbreviation: string;
        language: string;
        version: string;
        books: {
            id: number;
            title: string;
            book_number: number;
            chapters: {
                id: number;
                chapter_number: number;
            }[];
        }[];
    }[];
    biblesList: {
        id: number;
        name: string;
        abbreviation: string;
        language: string;
        version: string;
        books: {
            id: number;
            title: string;
            book_number: number;
            chapters: {
                id: number;
                chapter_number: number;
            }[];
        }[];
    }[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: t('Parallel Bibles'),
        href: bibles_parallel().url,
    },
];

// Bible 1 state
const selectedBible1 = ref<number | null>(null);
const selectedBook1 = ref<number | null>(null);
const selectedChapter1 = ref<number | null>(null);
const loadedChapter1 = ref<any>(null);

// Bible 2 state
const selectedBible2 = ref<number | null>(null);
const selectedBook2 = ref<number | null>(null);
const selectedChapter2 = ref<number | null>(null);
const loadedChapter2 = ref<any>(null);

const hoveredVerseReferences = ref<any[]>([]);
const selectedReferenceVerse = ref<any>(null);
const clickedVerseId = ref<number | null>(null);
const verseHighlights1 = ref<Record<number, any>>({});
const verseHighlights2 = ref<Record<number, any>>({});
const notesDialogOpen = ref(false);
const selectedVerseForNote = ref<any>(null);
const selectedVerseChapterInfo = ref<any>(null);
const chapterCompleted1 = ref(false);
const chapterCompleted2 = ref(false);

const page = usePage();

// Computed properties for Bible 1 chapter navigation
const currentBook1 = computed(() =>
    props.biblesList
        .find((b) => b.id === Number(selectedBible1.value))
        ?.books.find((book) => book.id === Number(selectedBook1.value)),
);

const currentChapterIndex1 = computed(
    () =>
        currentBook1.value?.chapters.findIndex(
            (ch) => ch.id === Number(selectedChapter1.value),
        ) ?? -1,
);

const hasPreviousChapter1 = computed(() => {
    if (!currentBook1.value) return false;
    return currentChapterIndex1.value > 0;
});

const hasNextChapter1 = computed(() => {
    if (!currentBook1.value) return false;
    return currentChapterIndex1.value < currentBook1.value.chapters.length - 1;
});

function goToPreviousChapter1() {
    if (!hasPreviousChapter1.value || !currentBook1.value) return;
    const prevChapter =
        currentBook1.value.chapters[currentChapterIndex1.value - 1];
    selectedChapter1.value = prevChapter.id;
}

function goToNextChapter1() {
    if (!hasNextChapter1.value || !currentBook1.value) return;
    const nextChapter =
        currentBook1.value.chapters[currentChapterIndex1.value + 1];
    selectedChapter1.value = nextChapter.id;
}

async function loadChapterHighlights(
    chapterId: number,
    side: 'left' | 'right',
) {
    if (!page.props.auth?.user) return;

    try {
        const response = await fetch(
            `/api/verse-highlights/chapter?chapter_id=${chapterId}`,
        );
        const data = await response.json();
        if (side === 'left') {
            verseHighlights1.value = data;
        } else {
            verseHighlights2.value = data;
        }
    } catch (error) {
        console.error('Failed to load highlights:', error);
    }
}

async function loadChapterProgress(
    chapterId: number,
    bibleId: number,
    side: 'left' | 'right',
) {
    if (!page.props.auth?.user) return;

    try {
        const response = await fetch(
            `/api/reading-progress/bible?bible_id=${bibleId}`,
        );
        const data = await response.json();
        if (side === 'left') {
            chapterCompleted1.value = !!data[chapterId];
        } else {
            chapterCompleted2.value = !!data[chapterId];
        }
    } catch (error) {
        console.error('Failed to load chapter progress:', error);
        if (side === 'left') {
            chapterCompleted1.value = false;
        } else {
            chapterCompleted2.value = false;
        }
    }
}

async function toggleChapterCompletion(
    chapterId: number,
    bibleId: number,
    side: 'left' | 'right',
) {
    if (!page.props.auth?.user) {
        alert('Please log in to track reading progress');
        return;
    }

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

        const response = await fetch('/api/reading-progress/toggle', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                Accept: 'application/json',
            },
            body: JSON.stringify({
                chapter_id: chapterId,
                bible_id: bibleId,
            }),
        });

        if (response.ok) {
            const result = await response.json();
            if (side === 'left') {
                chapterCompleted1.value = result.progress.completed;
            } else {
                chapterCompleted2.value = result.progress.completed;
            }
        }
    } catch (error) {
        console.error('Failed to toggle chapter completion:', error);
    }
}

function loadChapter(chapterId: number, side: 'left' | 'right') {
    fetch(`/api/bibles/books/chapters/${chapterId}`)
        .then((res) => res.json())
        .then((data) => {
            if (side === 'left') {
                loadedChapter1.value = data;
                loadChapterHighlights(chapterId, 'left');
                if (selectedBible1.value) {
                    loadChapterProgress(
                        chapterId,
                        selectedBible1.value,
                        'left',
                    );
                }
            } else {
                loadedChapter2.value = data;
                loadChapterHighlights(chapterId, 'right');
                if (selectedBible2.value) {
                    loadChapterProgress(
                        chapterId,
                        selectedBible2.value,
                        'right',
                    );
                }
            }
            hoveredVerseReferences.value = [];
        });
}

async function highlightVerse(verseId: number, color: string) {
    if (!page.props.auth?.user) {
        alert('Please log in to highlight verses');
        return;
    }

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

        const response = await fetch('/api/verse-highlights', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                Accept: 'application/json',
            },
            body: JSON.stringify({
                verse_id: verseId,
                color: color,
            }),
        });

        type HighlightResponse = {
            success?: boolean;
            message?: string;
        };
        let result: HighlightResponse = {};
        try {
            result = await response.json();
        } catch {
            alert('Unexpected server response. Please try again.');
            return;
        }

        if (response.ok && result?.success) {
            if (selectedChapter1.value) {
                await loadChapterHighlights(selectedChapter1.value, 'left');
            }
            if (selectedChapter2.value) {
                await loadChapterHighlights(selectedChapter2.value, 'right');
            }
        } else {
            alert(result?.message || 'Failed to highlight verse.');
        }
    } catch (error) {
        alert('Failed to highlight verse.');
        console.error(error);
    }
}

async function removeHighlight(verseId: number) {
    if (!page.props.auth?.user) {
        alert('Please log in to highlight verses');
        return;
    }

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

        const response = await fetch('/api/verse-highlights/' + verseId, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                Accept: 'application/json',
            },
            body: JSON.stringify({
                verse_id: verseId,
            }),
        });

        try {
            await response.json();
            if (selectedChapter1.value) {
                await loadChapterHighlights(selectedChapter1.value, 'left');
            }
            if (selectedChapter2.value) {
                await loadChapterHighlights(selectedChapter2.value, 'right');
            }
        } catch {
            alert('Unexpected server response. Please try again.');
            return;
        }
    } catch (error) {
        alert('Failed to Remove highlight.');
        console.error(error);
    }
}

function getVerseHighlightClass(
    verseId: number,
    side: 'left' | 'right',
): string {
    const highlights =
        side === 'left' ? verseHighlights1.value : verseHighlights2.value;
    const highlight = highlights[verseId];

    if (!highlight) return '';

    if (highlight.color === 'yellow') {
        return 'bg-yellow-300 dark:bg-yellow-300/30';
    } else if (highlight.color === 'green') {
        return 'bg-green-300 dark:bg-green-300/30';
    }
    return '';
}

async function handleVerseHover(verseId: number) {
    try {
        const response = await fetch(`/api/verses/${verseId}/references`);
        const data = await response.json();
        if (data && data.references) {
            hoveredVerseReferences.value = data.references;
        } else {
            hoveredVerseReferences.value = [];
        }
    } catch {
        hoveredVerseReferences.value = [];
    }
}

async function handleVerseClick(verseId: number) {
    // Toggle: if clicking the same verse, close it
    if (clickedVerseId.value === verseId) {
        clickedVerseId.value = null;
        hoveredVerseReferences.value = [];
    } else {
        clickedVerseId.value = verseId;
        await handleVerseHover(verseId);
    }
}

async function handleReferenceClick(reference: any) {
    selectedReferenceVerse.value = reference.verse;
}

function translateReference(ref: string): string {
    // translate EXO 12 12 to other language e.g KUT 12:12
    const parts = ref.split(' ');
    if (parts.length !== 3) {
        return ref; // return original if format is unexpected
    }
    const bookCode = parts[0];
    const chapter = parts[1];
    const verse = parts[2];
    return `${t(`${bookCode}`)} ${chapter}:${verse}`;
}

function studyVerse(verseId: number) {
    window.location.href = `/verses/${verseId}/study`;
}

function openNotesDialog(verse: any, chapterInfo: any) {
    selectedVerseForNote.value = verse;
    selectedVerseChapterInfo.value = chapterInfo;
    notesDialogOpen.value = true;
}

function handleNoteSaved() {
    // Show success message
    alertSuccess.value = true;
}

watch(selectedBible1, (newBibleId) => {
    if (newBibleId) {
        const bible = props.biblesList.find((b) => b.id === newBibleId);
        if (bible && bible.books.length > 0) {
            selectedBook1.value = bible.books[0].id;
            if (bible.books[0].chapters.length > 0) {
                selectedChapter1.value = bible.books[0].chapters[0].id;
            }
        }
    }
});

watch(selectedBible2, (newBibleId) => {
    if (newBibleId) {
        const bible = props.biblesOther.find((b) => b.id === newBibleId);
        if (bible && bible.books.length > 0) {
            selectedBook2.value = bible.books[0].id;
            if (bible.books[0].chapters.length > 0) {
                selectedChapter2.value = bible.books[0].chapters[0].id;
            }
        }
    }
});

// Synchronize book and chapter selections between Bible 1 and Bible 2
watch(selectedBook1, (newBookId) => {
    if (newBookId && selectedBible2.value) {
        // Try to find the corresponding book in Bible 2
        const book1 = props.biblesList
            .find((b) => b.id === Number(selectedBible1.value))
            ?.books.find((book) => book.id === Number(newBookId));

        if (book1) {
            const book2 = props.biblesOther
                .find((b) => b.id === Number(selectedBible2.value))
                ?.books.find((book) => book.book_number === book1.book_number);

            if (book2) {
                selectedBook2.value = book2.id;
            }
        }
    }
});

watch(selectedChapter1, (newChapterId) => {
    if (newChapterId) {
        loadChapter(newChapterId, 'left');

        // Synchronize chapter for Bible 2
        if (selectedBook2.value) {
            const chapter1 = props.biblesList
                .find((b) => b.id === Number(selectedBible1.value))
                ?.books.find((book) => book.id === Number(selectedBook1.value))
                ?.chapters.find((ch) => ch.id === Number(newChapterId));

            if (chapter1) {
                const chapter2 = props.biblesOther
                    .find((b) => b.id === Number(selectedBible2.value))
                    ?.books.find(
                        (book) => book.id === Number(selectedBook2.value),
                    )
                    ?.chapters.find(
                        (ch) => ch.chapter_number === chapter1.chapter_number,
                    );

                if (chapter2) {
                    selectedChapter2.value = chapter2.id;
                }
            }
        }
    }
});

// Helper function to get verse from Bible 2 by verse number
function getVerse2ByNumber(verseNumber: number) {
    if (!loadedChapter2.value || !loadedChapter2.value.verses) {
        return null;
    }
    return loadedChapter2.value.verses.find(
        (v: any) => v.verse_number === verseNumber,
    );
}

// Helper function to get Bible name by ID
function getBibleName(bibleId: number | null) {
    if (!bibleId) return '';
    const bible =
        props.biblesList.find((b) => b.id === bibleId) ||
        props.biblesOther.find((b) => b.id === bibleId);
    return bible ? bible.abbreviation || bible.name : '';
}

watch(selectedChapter2, (newChapterId) => {
    if (newChapterId) {
        loadChapter(newChapterId, 'right');
    }
});

const success = page.props.success;
const error = page.props.error;
const info = page.props.info;
const successMessage = computed(() => page.props.success as string);
const errorMessage = computed(() => page.props.error as string);
const alertSuccess = ref(!!successMessage.value);
const alertError = ref(!!errorMessage.value);
const alertInfo = ref(false);

if (success) {
    alertSuccess.value = true;
}

if (error) {
    alertError.value = true;
}

if (info) {
    alertInfo.value = true;
}

function shareVerse(verse: any, chapter: any) {
    const verseReference = `${chapter.book?.title} ${chapter.chapter_number}:${verse.verse_number}`;
    const verseText = verse.text;
    router.visit(
        `/share?reference=${encodeURIComponent(verseReference)}&text=${encodeURIComponent(verseText)}&verseId=${verse.id}`,
    );
}
</script>

<template>
    <Head :title="t('Parallel Bibles')" />

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
            class="flex h-full flex-1 flex-col gap-3 overflow-x-auto rounded-xl p-2 sm:p-4 lg:flex-row lg:gap-4"
        >
            <!-- Main content area (2/3) -->
            <div class="flex-[2]">
                <!-- Single Card for Merged View -->
                <Card>
                <CardHeader class="pb-3">
                    <div class="space-y-3 sm:space-y-4">
                        <CardTitle
                            class="flex items-center gap-2 text-base sm:text-lg"
                        >
                            <BookOpen class="h-4 w-4 sm:h-5 sm:w-5" />
                            {{ t('Parallel Bibles') }}
                        </CardTitle>

                        <!-- Translation Selection Row -->
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <!-- Bible 1 Selection -->
                            <div class="space-y-2">
                                <label class="text-sm font-medium">{{
                                    t('Translation 1')
                                }}</label>
                                <Select v-model="selectedBible1">
                                    <SelectTrigger>
                                        <SelectValue
                                            :placeholder="
                                                t('Select first Bible')
                                            "
                                        />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectGroup>
                                            <SelectLabel>{{
                                                t('Bibles')
                                            }}</SelectLabel>
                                            <SelectItem
                                                v-for="bible in biblesList"
                                                :key="bible.id"
                                                :value="bible.id.toString()"
                                            >
                                                {{ bible.name }} ({{
                                                    bible.language
                                                }})
                                            </SelectItem>
                                        </SelectGroup>
                                    </SelectContent>
                                </Select>
                            </div>

                            <!-- Bible 2 Selection -->
                            <div class="space-y-2">
                                <label class="text-sm font-medium">{{
                                    t('Translation 2')
                                }}</label>
                                <Select v-model="selectedBible2">
                                    <SelectTrigger>
                                        <SelectValue
                                            :placeholder="
                                                t('Select second Bible')
                                            "
                                        />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectGroup>
                                            <SelectLabel>{{
                                                t('Bibles')
                                            }}</SelectLabel>
                                            <SelectItem
                                                v-for="bible in biblesOther"
                                                :key="bible.id"
                                                :value="bible.id.toString()"
                                            >
                                                {{ bible.name }} ({{
                                                    bible.language
                                                }})
                                            </SelectItem>
                                        </SelectGroup>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        <!-- Book and Chapter Selection -->
                        <div
                            v-if="selectedBible1"
                            class="flex flex-col gap-2 sm:flex-row"
                        >
                            <Select v-model="selectedBook1">
                                <SelectTrigger class="w-full flex-1">
                                    <SelectValue :placeholder="t('Book')" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectGroup>
                                        <SelectLabel>{{
                                            t('Books')
                                        }}</SelectLabel>
                                        <SelectItem
                                            v-for="book in biblesList.find(
                                                (b) =>
                                                    b.id ===
                                                    Number(selectedBible1),
                                            )?.books || []"
                                            :key="book.id"
                                            :value="book.id.toString()"
                                        >
                                            {{ book.title }}
                                        </SelectItem>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                            <Select v-model="selectedChapter1">
                                <SelectTrigger class="w-full sm:w-32">
                                    <SelectValue :placeholder="t('Chapter')" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectGroup>
                                        <SelectLabel>{{
                                            t('Chapters')
                                        }}</SelectLabel>
                                        <SelectItem
                                            v-for="chapter in biblesList
                                                .find(
                                                    (b) =>
                                                        b.id ===
                                                        Number(selectedBible1),
                                                )
                                                ?.books.find(
                                                    (book) =>
                                                        book.id ===
                                                        Number(selectedBook1),
                                                )?.chapters || []"
                                            :key="chapter.id"
                                            :value="chapter.id.toString()"
                                        >
                                            {{ chapter.chapter_number }}
                                        </SelectItem>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                </CardHeader>

                <CardContent v-if="loadedChapter1">
                    <div
                        class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <Button
                            variant="outline"
                            size="sm"
                            @click="goToPreviousChapter1"
                            :disabled="!hasPreviousChapter1"
                            class="w-full sm:w-auto"
                        >
                            <ChevronLeft class="mr-1 h-4 w-4" />
                            {{ t('Previous') }}
                        </Button>
                        <Button
                            v-if="page.props.auth?.user && selectedChapter1"
                            :variant="chapterCompleted1 ? 'default' : 'outline'"
                            size="sm"
                            @click="
                                toggleChapterCompletion(
                                    selectedChapter1,
                                    Number(selectedBible1),
                                    'left',
                                )
                            "
                            class="w-full sm:w-auto"
                        >
                            <CheckCircle class="mr-1 h-4 w-4" />
                            {{
                                chapterCompleted1
                                    ? t('Completed')
                                    : t('Mark as Read')
                            }}
                        </Button>
                        <Button
                            variant="outline"
                            size="sm"
                            @click="goToNextChapter1"
                            :disabled="!hasNextChapter1"
                            class="w-full sm:w-auto"
                        >
                            {{ t('Next') }}
                            <ChevronRight class="ml-1 h-4 w-4" />
                        </Button>
                    </div>

                    <!-- Legend for font styles or message -->
                    <div
                        v-if="loadedChapter2"
                        class="mb-4 flex flex-wrap items-center gap-4 text-xs text-muted-foreground"
                    >
                        <div class="flex items-center gap-2">
                            <span class="font-normal">{{
                                getBibleName(Number(selectedBible1))
                            }}</span>
                            <span class="text-xs">({{ t('Regular') }})</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="font-normal italic">{{
                                getBibleName(Number(selectedBible2))
                            }}</span>
                            <span class="text-xs">({{ t('Italic') }})</span>
                        </div>
                    </div>
                    <div
                        v-else
                        class="mb-4 rounded bg-muted p-3 text-sm text-muted-foreground"
                    >
                        {{
                            t(
                                'Select a second translation to see parallel verses',
                            )
                        }}
                    </div>

                    <ScrollArea
                        class="h-98 space-y-2 text-sm leading-relaxed sm:text-base"
                    >
                        <h3 class="mb-4 text-base font-semibold sm:text-lg">
                            {{ loadedChapter1.book?.name }}
                            {{ loadedChapter1.chapter_number }}
                        </h3>

                        <!-- Merged Verses -->
                        <div
                            v-for="verse1 in loadedChapter1.verses"
                            :key="verse1.id"
                            class="mb-4 rounded px-2 py-1 transition-colors"
                            :class="getVerseHighlightClass(verse1.id, 'left')"
                        >
                            <DropdownMenu>
                                <DropdownMenuTrigger
                                    class="w-full cursor-default text-left"
                                >
                                    <div class="space-y-2">
                                        <!-- Verse Number -->
                                        <HoverCard
                                            @update:open="
                                                (open) =>
                                                    open &&
                                                    handleVerseHover(
                                                        verse1.id,
                                                    )
                                            "
                                        >
                                            <HoverCardTrigger>
                                                <span
                                                    class="cursor-pointer font-semibold text-primary hover:underline"
                                                    @click.stop="
                                                        handleVerseClick(
                                                            verse1.id,
                                                        )
                                                    "
                                                    >{{
                                                        verse1.verse_number
                                                    }}.</span
                                                >
                                            </HoverCardTrigger>
                                            <HoverCardContent class="w-80">
                                                <div
                                                    v-if="
                                                        hoveredVerseReferences.length >
                                                        0
                                                    "
                                                    class="space-y-2"
                                                >
                                                    <p
                                                        class="text-sm font-semibold"
                                                    >
                                                        {{
                                                            t(
                                                                'Cross References',
                                                            )
                                                        }}:
                                                    </p>
                                                    <div
                                                        class="space-y-1 text-sm"
                                                    >
                                                        <p
                                                            v-for="ref in hoveredVerseReferences.slice(
                                                                0,
                                                                3,
                                                            )"
                                                            :key="ref.id"
                                                            class="text-muted-foreground"
                                                        >
                                                            {{ translateReference(ref.reference) }}:
                                                            {{
                                                                ref.verse?.text?.substring(
                                                                    0,
                                                                    80,
                                                                )
                                                            }}...
                                                        </p>
                                                        <p
                                                            v-if="
                                                                hoveredVerseReferences.length >
                                                                3
                                                            "
                                                            class="text-xs text-muted-foreground italic"
                                                        >
                                                            +{{
                                                                hoveredVerseReferences.length -
                                                                3
                                                            }}
                                                            {{
                                                                t(
                                                                    'more references',
                                                                )
                                                            }}
                                                        </p>
                                                    </div>
                                                </div>
                                                <p
                                                    v-else
                                                    class="text-sm text-muted-foreground"
                                                >
                                                    {{
                                                        t('No cross-references')
                                                    }}
                                                    {{ t('available') }}
                                                </p>
                                            </HoverCardContent>
                                        </HoverCard>

                                        <!-- Translation 1 Text (Regular) -->
                                        <span class="font-normal">{{
                                            verse1.text
                                        }}</span>

                                        <!-- Translation 2 Text (Italic) -->
                                        <template
                                            v-if="
                                                loadedChapter2 &&
                                                getVerse2ByNumber(
                                                    verse1.verse_number,
                                                )
                                            "
                                        >
                                            <span
                                                class="ml-1 font-normal italic"
                                            >
                                                {{
                                                    getVerse2ByNumber(
                                                        verse1.verse_number,
                                                    ).text
                                                }}
                                            </span>
                                        </template>
                                    </div>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent>
                                    <!-- Cross References for Mobile -->
                                    <div
                                        v-if="
                                            clickedVerseId === verse1.id &&
                                            hoveredVerseReferences.length > 0
                                        "
                                        class="mb-2 sm:hidden"
                                    >
                                        <DropdownMenuLabel>{{
                                            t('Cross References')
                                        }}</DropdownMenuLabel>
                                        <DropdownMenuSeparator />
                                        <div
                                            class="max-h-40 overflow-y-auto px-2 py-1"
                                        >
                                            <div
                                                v-for="ref in hoveredVerseReferences.slice(
                                                    0,
                                                    3,
                                                )"
                                                :key="ref.id"
                                                class="mb-2 text-xs"
                                            >
                                                <p
                                                    class="font-semibold text-primary"
                                                >
                                                    {{
                                                        translateReference(
                                                            ref.reference,
                                                        )
                                                    }}
                                                </p>
                                                <p
                                                    class="text-muted-foreground"
                                                >
                                                    {{
                                                        ref.verse?.text?.substring(
                                                            0,
                                                            60,
                                                        )
                                                    }}...
                                                </p>
                                            </div>
                                            <p
                                                v-if="
                                                    hoveredVerseReferences.length >
                                                    3
                                                "
                                                class="text-xs text-muted-foreground italic"
                                            >
                                                +{{
                                                    hoveredVerseReferences.length -
                                                    3
                                                }}
                                                {{ t('more references') }}
                                            </p>
                                        </div>
                                        <DropdownMenuSeparator />
                                    </div>
                                    <DropdownMenuLabel>{{
                                        t('Highlight')
                                    }}</DropdownMenuLabel>
                                    <DropdownMenuSeparator />
                                    <DropdownMenuItem
                                        @click="
                                            highlightVerse(verse1.id, 'yellow')
                                        "
                                    >
                                        <span class="flex items-center gap-2">
                                            <span
                                                class="h-4 w-4 rounded bg-yellow-300"
                                            ></span>
                                            {{ t('Highlight - Yellow') }}
                                        </span>
                                    </DropdownMenuItem>
                                    <DropdownMenuItem
                                        @click="
                                            highlightVerse(verse1.id, 'green')
                                        "
                                    >
                                        <span class="flex items-center gap-2">
                                            <span
                                                class="h-4 w-4 rounded bg-green-300"
                                            ></span>
                                            {{ t('Highlight - Green') }}
                                        </span>
                                    </DropdownMenuItem>
                                    <DropdownMenuItem
                                        @click="removeHighlight(verse1.id)"
                                    >
                                        {{ t('Remove Highlight') }}
                                    </DropdownMenuItem>
                                    <DropdownMenuLabel>{{
                                        t('Learn More')
                                    }}</DropdownMenuLabel>
                                    <DropdownMenuSeparator />
                                    <DropdownMenuItem
                                        @click="studyVerse(verse1.id)"
                                    >
                                        {{ t('Study this Verse') }}
                                    </DropdownMenuItem>
                                    <DropdownMenuItem
                                        @click="
                                            openNotesDialog(
                                                verse1,
                                                loadedChapter1,
                                            )
                                        "
                                    >
                                        {{ t('Put Note on this Verse') }}
                                    </DropdownMenuItem>
                                    <DropdownMenuLabel>{{
                                        t('Share')
                                    }}</DropdownMenuLabel>
                                    <DropdownMenuSeparator />
                                    <DropdownMenuItem
                                        @click="
                                            shareVerse(verse1, loadedChapter1)
                                        "
                                    >
                                        <Share2 class="mr-2 h-4 w-4" />
                                        {{ t('Share this Verse') }}
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                    </ScrollArea>
                </CardContent>

                <CardContent
                    v-else
                    class="py-6 text-center text-sm text-muted-foreground sm:py-8 sm:text-base"
                >
                    <p>{{ t('Select a translation to start') }}</p>
                </CardContent>
            </Card>
            </div>

            <!-- References sidebar (1/3) -->
            <div class="flex flex-[1] flex-col gap-3 lg:h-160 lg:gap-4">
                <!-- Top half - Hovered verse references -->
                <Card class="flex-1 overflow-hidden">
                    <CardHeader class="pb-3">
                        <CardTitle class="text-sm sm:text-base">{{
                            t('Cross References')
                        }}</CardTitle>
                        <CardDescription class="text-xs"
                            ><span class="hidden sm:inline"
                                >{{ t('Hover over verse numbers to see') }}
                                {{ t('references') }}</span
                            ><span class="sm:hidden">{{
                                t('Tap verse numbers to see references')
                            }}</span></CardDescription
                        >
                    </CardHeader>
                    <CardContent
                        class="max-h-[30vh] overflow-y-auto lg:max-h-[40vh]"
                    >
                        <ScrollArea
                            v-if="hoveredVerseReferences.length > 0"
                            class="h-full"
                        >
                            <div class="space-y-3">
                                <div
                                    v-for="ref in hoveredVerseReferences"
                                    :key="ref.id"
                                    class="cursor-pointer rounded border p-2 transition-colors hover:bg-accent"
                                    @click="handleReferenceClick(ref)"
                                >
                                    <p
                                        class="text-sm font-semibold text-primary"
                                    >
                                        {{ translateReference(ref.reference) }}
                                    </p>
                                    <p
                                        class="mt-1 text-xs text-muted-foreground"
                                    >
                                        {{
                                            ref.verse?.text?.substring(0, 100)
                                        }}...
                                    </p>
                                </div>
                            </div>
                        </ScrollArea>
                        <p v-else class="text-sm text-muted-foreground italic">
                            <span class="hidden sm:inline"
                                >{{
                                    t('Hover over a verse number to see its')
                                }}
                                {{ t('cross-references') }}</span
                            >
                            <span class="sm:hidden"
                                >{{ t('Tap a verse number to see its') }}
                                {{ t('cross-references') }}</span
                            >
                        </p>
                    </CardContent>
                </Card>

                <!-- Bottom half - Selected reference verse -->
                <Card class="flex-1 overflow-hidden">
                    <CardHeader class="pb-3">
                        <CardTitle class="text-sm sm:text-base">{{
                            t('Selected Reference')
                        }}</CardTitle>
                        <CardDescription class="text-xs"
                            >{{ t('Click a reference above to view full') }}
                            {{ t('verse') }}</CardDescription
                        >
                    </CardHeader>
                    <CardContent
                        class="max-h-[30vh] overflow-y-auto lg:max-h-[40vh]"
                    >
                        <div v-if="selectedReferenceVerse" class="space-y-2">
                            <p class="text-sm font-semibold">
                                {{ selectedReferenceVerse.book?.title }}
                                {{
                                    selectedReferenceVerse.chapter
                                        ?.chapter_number
                                }}:{{ selectedReferenceVerse.verse_number }}
                            </p>
                            <p class="text-sm">
                                {{ selectedReferenceVerse.text }}
                            </p>
                        </div>
                        <p v-else class="text-sm text-muted-foreground italic">
                            {{
                                t('Click on a reference to view the full verse')
                            }}
                        </p>
                    </CardContent>
                </Card>
            </div>
        </div>

        <!-- Notes Dialog -->
        <NotesDialog
            v-if="selectedVerseForNote && selectedVerseChapterInfo"
            :open="notesDialogOpen"
            @update:open="notesDialogOpen = $event"
            :verse-id="selectedVerseForNote.id"
            :verse-text="selectedVerseForNote.text"
            :verse-reference="`${selectedVerseChapterInfo.book?.name} ${selectedVerseChapterInfo.chapter_number}:${selectedVerseForNote.verse_number}`"
            @saved="handleNoteSaved"
        />
    </AppLayout>
</template>
