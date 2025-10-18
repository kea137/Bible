<script setup lang="ts">
import AlertUser from '@/components/AlertUser.vue';
import NotesDialog from '@/components/NotesDialog.vue';
import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import CardHeader from '@/components/ui/card/CardHeader.vue';
import CardTitle from '@/components/ui/card/CardTitle.vue';
import Button from '@/components/ui/button/Button.vue';
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
import AppLayout from '@/layouts/AppLayout.vue';
import { bibles_parallel } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import { BookOpen, ChevronLeft, ChevronRight } from 'lucide-vue-next';

import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { computed, ref, watch } from 'vue';
import ScrollArea from '@/components/ui/scroll-area/ScrollArea.vue';

const props = defineProps<{
    biblesOther: {
        id: number;
        name: string;
        abbreviation: string;
        description: string;
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
        description: string;
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
        title: 'Parallel Bibles',
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
const verseHighlights1 = ref<Record<number, any>>({});
const verseHighlights2 = ref<Record<number, any>>({});
const notesDialogOpen = ref(false);
const selectedVerseForNote = ref<any>(null);
const selectedVerseChapterInfo = ref<any>(null);

const page = usePage();

// Computed properties for Bible 1 chapter navigation
const currentBook1 = computed(() => 
    props.biblesList.find(b => b.id === Number(selectedBible1.value))?.books.find(book => book.id === Number(selectedBook1.value))
);

const currentChapterIndex1 = computed(() => 
    currentBook1.value?.chapters.findIndex(ch => ch.id === Number(selectedChapter1.value)) ?? -1
);

const hasPreviousChapter1 = computed(() => {
    if (!currentBook1.value) return false;
    return currentChapterIndex1.value > 0;
});

const hasNextChapter1 = computed(() => {
    if (!currentBook1.value) return false;
    return currentChapterIndex1.value < (currentBook1.value.chapters.length - 1);
});

// Computed properties for Bible 2 chapter navigation
const currentBook2 = computed(() => 
    props.biblesOther.find(b => b.id === Number(selectedBible2.value))?.books.find(book => book.id === Number(selectedBook2.value))
);

const currentChapterIndex2 = computed(() => 
    currentBook2.value?.chapters.findIndex(ch => ch.id === Number(selectedChapter2.value)) ?? -1
);

const hasPreviousChapter2 = computed(() => {
    if (!currentBook2.value) return false;
    return currentChapterIndex2.value > 0;
});

const hasNextChapter2 = computed(() => {
    if (!currentBook2.value) return false;
    return currentChapterIndex2.value < (currentBook2.value.chapters.length - 1);
});

function goToPreviousChapter1() {
    if (!hasPreviousChapter1.value || !currentBook1.value) return;
    const prevChapter = currentBook1.value.chapters[currentChapterIndex1.value - 1];
    selectedChapter1.value = prevChapter.id;
}

function goToNextChapter1() {
    if (!hasNextChapter1.value || !currentBook1.value) return;
    const nextChapter = currentBook1.value.chapters[currentChapterIndex1.value + 1];
    selectedChapter1.value = nextChapter.id;
}

function goToPreviousChapter2() {
    if (!hasPreviousChapter2.value || !currentBook2.value) return;
    const prevChapter = currentBook2.value.chapters[currentChapterIndex2.value - 1];
    selectedChapter2.value = prevChapter.id;
}

function goToNextChapter2() {
    if (!hasNextChapter2.value || !currentBook2.value) return;
    const nextChapter = currentBook2.value.chapters[currentChapterIndex2.value + 1];
    selectedChapter2.value = nextChapter.id;
}

async function loadChapterHighlights(chapterId: number, side: 'left' | 'right') {
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

function loadChapter(chapterId: number, side: 'left' | 'right') {
    fetch(`/api/bibles/books/chapters/${chapterId}`)
        .then((res) => res.json())
        .then((data) => {
            if (side === 'left') {
                loadedChapter1.value = data;
                loadChapterHighlights(chapterId, 'left');
            } else {
                loadedChapter2.value = data;
                loadChapterHighlights(chapterId, 'right');
            }
            hoveredVerseReferences.value = [];
            selectedReferenceVerse.value = null;
        });
}

async function highlightVerse(verseId: number, color: string) {
    if (!page.props.auth?.user) {
        alert('Please log in to highlight verses');
        return;
    }

    try {
        let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (!csrfToken && page.props.csrf_token) {
            csrfToken = String(page.props.csrf_token);
        }
        if (!csrfToken) {
            alert('CSRF token not found. Refreshing page to fix authentication...');
            window.location.reload();
            return;
        }

        const response = await fetch('/api/verse-highlights', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
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
        let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (!csrfToken && page.props.csrf_token) {
            csrfToken = String(page.props.csrf_token);
        }
        if (!csrfToken) {
            alert('CSRF token not found. Refreshing page to fix authentication...');
            window.location.reload();
            return;
        }

        const response = await fetch('/api/verse-highlights/' + verseId, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
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

function getVerseHighlightClass(verseId: number, side: 'left' | 'right'): string {
    const highlights = side === 'left' ? verseHighlights1.value : verseHighlights2.value;
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

watch(selectedChapter1, (newChapterId) => {
    if (newChapterId) {
        loadChapter(newChapterId, 'left');
    }
});

watch(selectedChapter2, (newChapterId) => {
    if (newChapterId) {
        loadChapter(newChapterId, 'right');
    }
});

const success = page.props.success;
const error = page.props.error;
const info = page.props.info;

const alertSuccess = ref(false);
const alertError = ref(false);
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
</script>

<template>
    <Head title="Parallel Bibles" />

    <AlertUser
        v-if="alertSuccess"
        :open="true"
        title="Success"
        :confirmButtonText="'OK'"
        :message="success"
        variant="success"
        @update:open="
            () => {
                alertSuccess = false;
            }
        "
    />
    <AlertUser
        v-if="alertError"
        :open="true"
        title="Error"
        :confirmButtonText="'OK'"
        :message="error"
        variant="error"
        @update:open="
            () => {
                alertError = false;
            }
        "
    />
    <AlertUser
        v-if="alertInfo"
        :open="true"
        title="Information"
        :confirmButtonText="'OK'"
        :message="info"
        variant="info"
        @update:open="
            () => {
                alertInfo = false;
            }
        "
    />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <!-- Left Bible -->
                <Card>
                    <CardHeader>
                        <div class="space-y-4">
                            <CardTitle class="flex items-center gap-2">
                                <BookOpen class="h-5 w-5" />
                                Bible 1
                            </CardTitle>
                            <Select v-model="selectedBible1">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select a Bible" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectGroup>
                                        <SelectLabel>Bibles</SelectLabel>
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
                            <div v-if="selectedBible1" class="flex gap-2">
                                <Select v-model="selectedBook1">
                                    <SelectTrigger class="flex-1">
                                        <SelectValue placeholder="Book" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectGroup>
                                            <SelectLabel>Books</SelectLabel>
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
                                    <SelectTrigger class="w-32">
                                        <SelectValue placeholder="Chapter" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectGroup>
                                            <SelectLabel>Chapters</SelectLabel>
                                            <SelectItem
                                                v-for="chapter in biblesList
                                                    .find(
                                                        (b) =>
                                                            b.id ===
                                                            Number(
                                                                selectedBible1,
                                                            ),
                                                    )
                                                    ?.books.find(
                                                        (book) =>
                                                            book.id ===
                                                            Number(
                                                                selectedBook1,
                                                            ),
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
                        <div class="mb-4 flex items-center justify-between">
                            <Button
                                variant="outline"
                                size="sm"
                                @click="goToPreviousChapter1"
                                :disabled="!hasPreviousChapter1"
                            >
                                <ChevronLeft class="h-4 w-4 mr-1" />
                                Previous
                            </Button>
                            <Button
                                variant="outline"
                                size="sm"
                                @click="goToNextChapter1"
                                :disabled="!hasNextChapter1"
                            >
                                Next
                                <ChevronRight class="h-4 w-4 ml-1" />
                            </Button>
                        </div>
                        <ScrollArea class="space-y-2 text-base leading-relaxed h-110">
                            <h3 class="mb-4 text-lg font-semibold">
                                {{ loadedChapter1.book?.title }}
                                {{ loadedChapter1.chapter_number }}
                            </h3>
                            <p
                                v-for="verse in loadedChapter1.verses"
                                :key="verse.id"
                                class="mb-2 rounded px-2 py-1 transition-colors"
                                :class="getVerseHighlightClass(verse.id, 'left')"
                            >
                                <DropdownMenu>
                                    <DropdownMenuTrigger class="w-full cursor-default text-left">
                                        <HoverCard
                                            @update:open="
                                                (open) =>
                                                    open &&
                                                    handleVerseHover(verse.verse_number)
                                            "
                                        >
                                            <HoverCardTrigger>
                                                <span
                                                    class="cursor-pointer font-semibold text-primary hover:underline"
                                                >{{ verse.verse_number }}.</span>
                                            </HoverCardTrigger>
                                            <HoverCardContent class="w-80">
                                                <div
                                                    v-if="hoveredVerseReferences.length > 0"
                                                    class="space-y-2"
                                                >
                                                    <p class="text-sm font-semibold">
                                                        Cross References:
                                                    </p>
                                                    <div class="space-y-1 text-sm">
                                                        <p
                                                            v-for="ref in hoveredVerseReferences.slice(0, 3)"
                                                            :key="ref.id"
                                                            class="text-muted-foreground"
                                                        >
                                                            {{ ref.reference }}:
                                                            {{ ref.verse?.text?.substring(0, 80) }}...
                                                        </p>
                                                        <p
                                                            v-if="hoveredVerseReferences.length > 3"
                                                            class="text-xs text-muted-foreground italic"
                                                        >
                                                            +{{ hoveredVerseReferences.length - 3 }}
                                                            more references
                                                        </p>
                                                    </div>
                                                </div>
                                                <p v-else class="text-sm text-muted-foreground">
                                                    No cross-references available
                                                </p>
                                            </HoverCardContent>
                                        </HoverCard>
                                        {{ verse.text }}
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent>
                                        <DropdownMenuLabel>Highlight</DropdownMenuLabel>
                                        <DropdownMenuSeparator />
                                        <DropdownMenuItem
                                            @click="highlightVerse(verse.id, 'yellow')"
                                        >
                                            <span class="flex items-center gap-2">
                                                <span class="h-4 w-4 rounded bg-yellow-300"></span>
                                                Highlight - Yellow
                                            </span>
                                        </DropdownMenuItem>
                                        <DropdownMenuItem
                                            @click="highlightVerse(verse.id, 'green')"
                                        >
                                            <span class="flex items-center gap-2">
                                                <span class="h-4 w-4 rounded bg-green-300"></span>
                                                Highlight - Green
                                            </span>
                                        </DropdownMenuItem>
                                        <DropdownMenuItem
                                            @click="removeHighlight(verse.id)"
                                        >
                                            Remove Highlight
                                        </DropdownMenuItem>
                                        <DropdownMenuLabel>Learn More</DropdownMenuLabel>
                                        <DropdownMenuSeparator />
                                        <DropdownMenuItem
                                            @click="studyVerse(verse.id)"
                                        >
                                            Study this Verse
                                        </DropdownMenuItem>
                                        <DropdownMenuItem
                                            @click="openNotesDialog(verse, loadedChapter1)"
                                        >
                                            Put Note on this Verse
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </p>
                        </ScrollArea>
                    </CardContent>
                    <CardContent
                        v-else
                        class="py-8 text-center text-muted-foreground"
                    >
                        <p>Select a Bible to start</p>
                    </CardContent>
                </Card>

                <!-- Right Bible -->
                <Card>
                    <CardHeader>
                        <div class="space-y-4">
                            <CardTitle class="flex items-center gap-2">
                                <BookOpen class="h-5 w-5" />
                                Bible 2
                            </CardTitle>
                            <Select v-model="selectedBible2">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select a Bible" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectGroup>
                                        <SelectLabel>Bibles</SelectLabel>
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
                            <div v-if="selectedBible2" class="flex gap-2">
                                <Select v-model="selectedBook2">
                                    <SelectTrigger class="flex-1">
                                        <SelectValue placeholder="Book" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectGroup>
                                            <SelectLabel>Books</SelectLabel>
                                            <SelectItem
                                                v-for="book in biblesOther.find(
                                                    (b) =>
                                                        b.id ===
                                                        Number(selectedBible2),
                                                )?.books || []"
                                                :key="book.id"
                                                :value="book.id.toString()"
                                            >
                                                {{ book.title }}
                                            </SelectItem>
                                        </SelectGroup>
                                    </SelectContent>
                                </Select>
                                <Select v-model="selectedChapter2">
                                    <SelectTrigger class="w-32">
                                        <SelectValue placeholder="Chapter" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectGroup>
                                            <SelectLabel>Chapters</SelectLabel>
                                            <SelectItem
                                                v-for="chapter in biblesOther
                                                    .find(
                                                        (b) =>
                                                            b.id ===
                                                            Number(
                                                                selectedBible2,
                                                            ),
                                                    )
                                                    ?.books.find(
                                                        (book) =>
                                                            book.id ===
                                                            Number(
                                                                selectedBook2,
                                                            ),
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
                    <CardContent v-if="loadedChapter2">
                        <div class="mb-4 flex items-center justify-between">
                            <Button
                                variant="outline"
                                size="sm"
                                @click="goToPreviousChapter2"
                                :disabled="!hasPreviousChapter2"
                            >
                                <ChevronLeft class="h-4 w-4 mr-1" />
                                Previous
                            </Button>
                            <Button
                                variant="outline"
                                size="sm"
                                @click="goToNextChapter2"
                                :disabled="!hasNextChapter2"
                            >
                                Next
                                <ChevronRight class="h-4 w-4 ml-1" />
                            </Button>
                        </div>
                        <ScrollArea class="space-y-2 text-base leading-relaxed h-110">
                            <h3 class="mb-4 text-lg font-semibold">
                                {{ loadedChapter2.book?.title }}
                                {{ loadedChapter2.chapter_number }}
                            </h3>
                            <p
                                v-for="verse in loadedChapter2.verses"
                                :key="verse.id"
                                class="mb-2 rounded px-2 py-1 transition-colors"
                                :class="getVerseHighlightClass(verse.id, 'right')"
                            >
                                <DropdownMenu>
                                    <DropdownMenuTrigger class="w-full cursor-default text-left">
                                        <HoverCard
                                            @update:open="
                                                (open) =>
                                                    open &&
                                                    handleVerseHover(verse.verse_number)
                                            "
                                        >
                                            <HoverCardTrigger>
                                                <span
                                                    class="cursor-pointer font-semibold text-primary hover:underline"
                                                >{{ verse.verse_number }}.</span>
                                            </HoverCardTrigger>
                                            <HoverCardContent class="w-80">
                                                <div
                                                    v-if="hoveredVerseReferences.length > 0"
                                                    class="space-y-2"
                                                >
                                                    <p class="text-sm font-semibold">
                                                        Cross References:
                                                    </p>
                                                    <div class="space-y-1 text-sm">
                                                        <p
                                                            v-for="ref in hoveredVerseReferences.slice(0, 3)"
                                                            :key="ref.id"
                                                            class="text-muted-foreground"
                                                        >
                                                            {{ ref.reference }}:
                                                            {{ ref.verse?.text?.substring(0, 80) }}...
                                                        </p>
                                                        <p
                                                            v-if="hoveredVerseReferences.length > 3"
                                                            class="text-xs text-muted-foreground italic"
                                                        >
                                                            +{{ hoveredVerseReferences.length - 3 }}
                                                            more references
                                                        </p>
                                                    </div>
                                                </div>
                                                <p v-else class="text-sm text-muted-foreground">
                                                    No cross-references available
                                                </p>
                                            </HoverCardContent>
                                        </HoverCard>
                                        {{ verse.text }}
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent>
                                        <DropdownMenuLabel>Highlight</DropdownMenuLabel>
                                        <DropdownMenuSeparator />
                                        <DropdownMenuItem
                                            @click="highlightVerse(verse.id, 'yellow')"
                                        >
                                            <span class="flex items-center gap-2">
                                                <span class="h-4 w-4 rounded bg-yellow-300"></span>
                                                Highlight - Yellow
                                            </span>
                                        </DropdownMenuItem>
                                        <DropdownMenuItem
                                            @click="highlightVerse(verse.id, 'green')"
                                        >
                                            <span class="flex items-center gap-2">
                                                <span class="h-4 w-4 rounded bg-green-300"></span>
                                                Highlight - Green
                                            </span>
                                        </DropdownMenuItem>
                                        <DropdownMenuItem
                                            @click="removeHighlight(verse.id)"
                                        >
                                            Remove Highlight
                                        </DropdownMenuItem>
                                        <DropdownMenuLabel>Learn More</DropdownMenuLabel>
                                        <DropdownMenuSeparator />
                                        <DropdownMenuItem
                                            @click="studyVerse(verse.id)"
                                        >
                                            Study this Verse
                                        </DropdownMenuItem>
                                        <DropdownMenuItem
                                            @click="openNotesDialog(verse, loadedChapter2)"
                                        >
                                            Put Note on this Verse
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </p>
                        </ScrollArea>
                    </CardContent>
                    <CardContent
                        v-else
                        class="py-8 text-center text-muted-foreground"
                    >
                        <p>Select a Bible to start</p>
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
            :verse-reference="`${selectedVerseChapterInfo.book?.title} ${selectedVerseChapterInfo.chapter_number}:${selectedVerseForNote.verse_number}`"
            @saved="handleNoteSaved"
        />
    </AppLayout>
</template>
