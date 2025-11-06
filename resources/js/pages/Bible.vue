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
    Command,
    CommandDialog,
    CommandEmpty,
    CommandGroup,
    CommandInput,
    CommandItem,
    CommandList,
} from '@/components/ui/command';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import DropdownMenuLabel from '@/components/ui/dropdown-menu/DropdownMenuLabel.vue';
import DropdownMenuSeparator from '@/components/ui/dropdown-menu/DropdownMenuSeparator.vue';
import {
    HoverCard,
    HoverCardContent,
    HoverCardTrigger,
} from '@/components/ui/hover-card';
import { ScrollArea } from '@/components/ui/scroll-area';
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import VerseDialog from '@/components/VerseDialog.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { bibles, verse_study } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { algoliasearch } from 'algoliasearch';
import {
    BookOpen,
    CheckCircle,
    ChevronLeft,
    ChevronRight,
    PenTool,
    Search,
    Share2,
} from 'lucide-vue-next';
import { computed, onMounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const props = defineProps<{
    bible: {
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
    };
    initialChapter: {
        id: number;
        chapter_number: number;
        book: {
            id: number;
            title: string;
        };
        verses: {
            id: number;
            verse_number: number;
            text: string;
        }[];
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: t('Bibles'),
        href: bibles().url,
    },
    {
        title: props.bible.name,
        href: `/bibles/${props.bible.id}`,
    },
];

const page = usePage();
const selectedBookId = ref(props.initialChapter.book.id);
const selectedChapterId = ref(props.initialChapter.id);
const loadedChapter = ref(props.initialChapter);
const hoveredVerseReferences = ref<any[]>([]);
const selectedReferenceVerse = ref<any>(null);
const verseHighlights = ref<Record<number, any>>({});
const notesDialogOpen = ref(false);
const verseDialogOpen = ref(false);
const selectedVerseForNote = ref<any>(null);
const selectedVerseForEdit = ref<any>(null);
const chapterCompleted = ref(false);
const clickedVerseId = ref<number | null>(null);
const auth = computed(() => page.props.auth);
const roleNumbers = computed(() => auth.value?.roleNumbers || []);

const currentBook = computed(() =>
    props.bible.books.find((book) => book.id === Number(selectedBookId.value)),
);

const currentChapterIndex = computed(
    () =>
        currentBook.value?.chapters.findIndex(
            (ch) => ch.id === Number(selectedChapterId.value),
        ) ?? -1,
);

const canUpdate = computed(() => {
    return roleNumbers.value.includes(1) || roleNumbers.value.includes(2);
});

const hasPreviousChapter = computed(() => {
    if (!currentBook.value) return false;
    return currentChapterIndex.value > 0;
});

const hasNextChapter = computed(() => {
    if (!currentBook.value) return false;
    return currentChapterIndex.value < currentBook.value.chapters.length - 1;
});

function goToPreviousChapter() {
    if (!hasPreviousChapter.value || !currentBook.value) return;
    const prevChapter =
        currentBook.value.chapters[currentChapterIndex.value - 1];
    selectedChapterId.value = prevChapter.id;
}

function goToNextChapter() {
    if (!hasNextChapter.value || !currentBook.value) return;
    const nextChapter =
        currentBook.value.chapters[currentChapterIndex.value + 1];
    selectedChapterId.value = nextChapter.id;
}

async function loadChapterHighlights(chapterId: number) {
    if (!page.props.auth?.user) return;

    try {
        const response = await fetch(
            `/api/verse-highlights/chapter?chapter_id=${chapterId}`,
        );
        const data = await response.json();
        verseHighlights.value = data;
    } catch (error) {
        console.error('Failed to load highlights:', error);
    }
}

async function loadChapterProgress(chapterId: number) {
    if (!page.props.auth?.user) return;

    try {
        const response = await fetch(
            `/api/reading-progress/bible?bible_id=${props.bible.id}`,
        );
        const data = await response.json();
        chapterCompleted.value = !!data[chapterId];
    } catch (error) {
        console.error('Failed to load chapter progress:', error);
        chapterCompleted.value = false;
    }
}

async function toggleChapterCompletion() {
    if (!page.props.auth?.user) {
        alert(t('Please log in to track reading progress'));
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
                t('CSRF token not found. Refreshing page to fix authentication...'),
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
                chapter_id: selectedChapterId.value,
                bible_id: props.bible.id,
            }),
        });

        if (response.ok) {
            const result = await response.json();
            chapterCompleted.value = result.progress.completed;
        }
    } catch (error) {
        console.error('Failed to toggle chapter completion:', error);
    }
}

function loadChapter(chapterId: number) {
    fetch(`/api/bibles/books/chapters/${chapterId}`)
        .then((res) => res.json())
        .then((data) => {
            loadedChapter.value = data;
            selectedChapterId.value = chapterId;
            hoveredVerseReferences.value = [];
            selectedReferenceVerse.value = null;
            loadChapterHighlights(chapterId);
            loadChapterProgress(chapterId);
        });
}

async function highlightVerse(verseId: number, color: string) {
    if (!page.props.auth?.user) {
        alert(t('Please log in to highlight verses'));
        return;
    }

    try {
        // Try to get CSRF token from meta tag first
        let csrfToken = document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content');
        // Fallback to Inertia page props if not found
        if (!csrfToken && page.props.csrf_token) {
            csrfToken = String(page.props.csrf_token);
        }
        // If still not found, try to reload the page to get a fresh token
        if (!csrfToken) {
            // Attempt to reload the page to refresh CSRF token
            alert(
                t('CSRF token not found. Refreshing page to fix authentication...'),
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
            // If response is not JSON, treat as error
            alert(t('Unexpected server response. Please try again.'));
            return;
        }

        if (response.ok && result?.success) {
            await loadChapterHighlights(selectedChapterId.value);
        } else {
            alert(result?.message || t('Failed to highlight verse.'));
        }
    } catch (error) {
        alert(t('Failed to highlight verse.'));
        console.error(error);
    }
}

async function removeHighlight(verseId: number) {
    if (!page.props.auth?.user) {
        alert(t('Please log in to highlight verses'));
        return;
    }

    try {
        // Try to get CSRF token from meta tag first
        let csrfToken = document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content');
        // Fallback to Inertia page props if not found
        if (!csrfToken && page.props.csrf_token) {
            csrfToken = String(page.props.csrf_token);
        }
        // If still not found, try to reload the page to get a fresh token
        if (!csrfToken) {
            // Attempt to reload the page to refresh CSRF token
            alert(
                t('CSRF token not found. Refreshing page to fix authentication...'),
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
            // Remove highlight color
            removeVerseHighlightClass(verseId);
        } catch {
            // If response is not JSON, treat as error
            alert(t('Unexpected server response. Please try again.'));
            return;
        }
    } catch (error) {
        alert(t('Failed to Remove highlight.'));
        console.error(error);
    }
}

function getVerseHighlightClass(verseId: number): string {
    const highlight = verseHighlights.value[verseId];

    if (!highlight) return '';

    if (highlight.color === 'yellow') {
        return 'bg-yellow-300 dark:bg-yellow-300/30';
    } else if (highlight.color === 'green') {
        return 'bg-green-300 dark:bg-green-300/30';
    }
    return '';
}

// function to remove class based on verseId
function removeVerseHighlightClass(verseId: number): void {
    const highlight = verseHighlights.value[verseId];
    if (highlight) {
        delete verseHighlights.value[verseId];
    }
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

function studyVerse(verseId: number) {
    window.location.href = `/verses/${verseId}/study`;
}

function openNotesDialog(verse: any) {
    selectedVerseForNote.value = verse;
    notesDialogOpen.value = true;
}

function openVerseDialog(verse: any) {
    selectedVerseForEdit.value = verse;
    verseDialogOpen.value = true;
}

function shareVerse(verse: any) {
    const verseReference = `${loadedChapter.value.book?.title} ${loadedChapter.value.chapter_number}:${verse.verse_number}`;
    const verseText = verse.text;
    router.visit(
        `/share?reference=${encodeURIComponent(verseReference)}&text=${encodeURIComponent(verseText)}&verseId=${verse.id}`,
    );
}

function handleNoteSaved() {
    // Optionally reload highlights or show a success message
    alertSuccess.value = true;
}

function handleVerseSaved() {
    // Optionally reload highlights or show a success message
    alertSuccess.value = true;
}

watch(selectedChapterId, (newChapterId) => {
    if (newChapterId) {
        loadChapter(newChapterId);
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

// Load initial highlights and progress
if (props.initialChapter?.id) {
    loadChapterHighlights(props.initialChapter.id);
    loadChapterProgress(props.initialChapter.id);
}

const searchOpen = ref(false);

onMounted(() => {
    searchVerses();
});

const highlights = ref<any[]>([]);
const searchQuery = ref('');
const client = algoliasearch(
    import.meta.env.VITE_ALGOLIA_APP_ID || 'ZRYCA9P53B',
    import.meta.env.VITE_ALGOLIA_API_KEY || '4bb73bb3c87b2a1005c2c06e9128dec4',
);

const searchVerses = async () => {
    if (searchQuery.value.trim() === '') {
        // Fetch all highlights and bibles if search query is empty
        await loadHighlights();
    } else {
        // Search verses from Algolia 
        try {
            const response = await client.searchSingleIndex({
                indexName: 'verses',
                searchParams: {
                    query: searchQuery.value,
                    hitsPerPage: 10,
                },
            });
            // Map Algolia hits to a format similar to highlights
            const verseResults = response.hits.map((hit: any) => ({
                verse: {
                    id: typeof hit.objectID === 'string' && hit.objectID.startsWith('App\\Models\\Verse::')
                        ? parseInt(hit.objectID.split('::')[1], 10)
                        : hit.objectID || hit.id,
                    text: hit.text,
                },
            }));

            highlights.value = verseResults.map(v => v.verse);
        } catch (error) {
            console.error('Algolia search error:', error);
        }
    }
};

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

function getVerse(h: any) {
    return h?.verse || h;
}

const filteredHighlights = computed(() => {
    if (!searchQuery.value) {
        return highlights.value;
    }
    const query = searchQuery.value.toLowerCase();
    return highlights.value.filter((h) => {
        const verse = getVerse(h);
        if (!verse || typeof verse.text !== 'string') return false;
        const textMatch = verse.text.toLowerCase().includes(query);
        const bookMatch = verse.book && typeof verse.book.title === 'string' && verse.book.title.toLowerCase().includes(query);
        return textMatch || bookMatch;
    });
});

function viewHighlight(verseId: number) {
    // Navigate to the verse's bible page
    const highlight = highlights.value.find((h) => h.verse.id === verseId);
    if (highlight && highlight.verse.chapter?.bible_id) {
        router.visit(`/bibles/${highlight.verse.chapter.bible_id}`);
        searchOpen.value = false;
    }
}

function translateReference(ref: string): string {
    // translate EXO 12 12 to other lannguage e.g KUT 12:12
    const parts = ref.split(' ');
    if (parts.length !== 3) {
        return ref; // return original if format is unexpected
    }
    const bookCode = parts[0];
    const chapter = parts[1];
    const verse = parts[2];
    return `${t(`${bookCode}`)} ${chapter}:${verse}`;
}
</script>

<template>
    <Head :title="bible.name" />

    <AlertUser
        v-if="alertSuccess"
        :open="true"
        :title="t('Success')"
        :confirmButtonText="'OK'"
        :message="t('Operation was successful!')"
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
        :title="t('Error')"
        :confirmButtonText="'OK'"
        :message="t('Operation failed! Please try again.')"
        variant="error"
        @update:open="
            () => {
                alertError = false;
            }
        "
    />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-3 overflow-x-auto rounded-xl p-2 sm:p-4 lg:flex-row lg:gap-4"
        >
            <!-- Main content area (2/3) -->
            <div class="flex-[2]">
                <Card>
                    <CardHeader class="pb-3">
                        <div
                            class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between"
                        >
                            <div class="flex-shrink-0">
                                <CardTitle
                                    class="flex items-center gap-2 text-base sm:text-lg"
                                >
                                    <BookOpen class="h-4 w-4 sm:h-5 sm:w-5" />
                                    {{ bible.name }}
                                </CardTitle>
                                <CardDescription class="text-xs sm:text-sm"
                                    >{{ bible.language }} •
                                    {{ bible.version }}</CardDescription
                                >
                            </div>
                            <div
                                class="flex flex-col gap-2 sm:flex-row sm:gap-3"
                            >
                                <Select v-model="selectedBookId">
                                    <SelectTrigger class="w-full sm:w-40">
                                        <SelectValue
                                            :placeholder="t('Select a book')"
                                        />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectGroup>
                                            <SelectLabel>{{
                                                t('Books')
                                            }}</SelectLabel>
                                            <SelectItem
                                                v-for="book in bible.books"
                                                :key="book.id"
                                                :value="book.id.toString()"
                                            >
                                                {{ book.title }}
                                            </SelectItem>
                                        </SelectGroup>
                                    </SelectContent>
                                </Select>
                                <Select v-model="selectedChapterId">
                                    <SelectTrigger class="w-full sm:w-28">
                                        <SelectValue
                                            :placeholder="t('Chapter')"
                                        />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectGroup>
                                            <SelectLabel>{{
                                                t('Chapters')
                                            }}</SelectLabel>
                                            <SelectItem
                                                v-for="chapter in bible.books.find(
                                                    (book) =>
                                                        book.id ===
                                                        Number(selectedBookId),
                                                )?.chapters || []"
                                                :key="chapter.id"
                                                :value="chapter.id.toString()"
                                            >
                                                {{ chapter.chapter_number }}
                                            </SelectItem>
                                        </SelectGroup>
                                    </SelectContent>
                                </Select>

                                <Button
                                    @click="searchOpen = true"
                                    variant="outline"
                                    class="w-full sm:w-auto"
                                >
                                    <Search class="mr-2 h-4 w-4" />
                                    {{ t('Search') }}
                                </Button>

                                <CommandDialog
                                    :open="searchOpen"
                                    @update:open="searchOpen = $event"
                                >
                                    <template #title>
                                        <span class="sr-only">{{ t('Search Verses') }}</span>
                                    </template>
                                    <template #description>
                                        <span class="sr-only">{{ t('Type to search for Bible verses or highlights.') }}</span>
                                    </template>
                                    <Command>
                                        <CommandInput
                                            v-model="searchQuery"
                                            @input="searchVerses()"
                                            :placeholder="t('Search verses...')"
                                        />
                                        <CommandList>
                                            <CommandEmpty>{{
                                                t('No Verses found.')
                                            }}</CommandEmpty>
                                            <CommandGroup
                                                v-if="filteredHighlights.length > 0"
                                                :heading="t('Highlighted Verses')"
                                            >
                                                <CommandItem
                                                    v-for="highlight in filteredHighlights.slice(0, 10)"
                                                    :key="highlight.id || (highlight.verse && highlight.verse.id)"
                                                    :value="getVerse(highlight).text"
                                                    @select="viewHighlight(getVerse(highlight).id)"
                                                >
                                                    <PenTool class="mr-2 h-4 w-4" />
                                                    <Link :href="verse_study(highlight.id).url" class="flex w-full items-center gap-2">
                                                        <div class="flex flex-col">
                                                            <span class="line-clamp-1 text-sm">
                                                                {{ getVerse(highlight).text }}
                                                            </span>
                                                            <span class="text-xs text-muted-foreground">
                                                                {{ getVerse(highlight).book?.title }}
                                                                {{ getVerse(highlight).chapter?.chapter_number }}:{{ getVerse(highlight).verse_number }}
                                                            </span>
                                                        </div>
                                                    </Link>
                                                </CommandItem>
                                            </CommandGroup>
                                        </CommandList>
                                    </Command>
                                </CommandDialog>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div
                            class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between"
                        >
                            <Button
                                variant="outline"
                                size="sm"
                                @click="goToPreviousChapter"
                                :disabled="!hasPreviousChapter"
                                class="w-full sm:w-auto"
                            >
                                <ChevronLeft class="mr-1 h-4 w-4" />
                                {{ t('Previous') }}
                            </Button>
                            <Button
                                v-if="page.props.auth?.user"
                                :variant="
                                    chapterCompleted ? 'default' : 'outline'
                                "
                                size="sm"
                                @click="toggleChapterCompletion"
                                class="w-full sm:w-auto"
                            >
                                <CheckCircle class="mr-1 h-4 w-4" />
                                {{
                                    chapterCompleted
                                        ? t('Completed')
                                        : t('Mark as Read')
                                }}
                            </Button>
                            <Button
                                variant="outline"
                                size="sm"
                                @click="goToNextChapter"
                                :disabled="!hasNextChapter"
                                class="w-full sm:w-auto"
                            >
                                {{ t('Next') }}
                                <ChevronRight class="ml-1 h-4 w-4" />
                            </Button>
                        </div>
                        <ScrollArea
                            class="mx-0 h-118 max-w-4xl space-y-2 text-justify text-base leading-relaxed sm:mx-30 sm:text-lg"
                        >
                            <h3
                                class="mb-4 text-center text-lg font-semibold sm:text-xl"
                            >
                                {{ loadedChapter.book?.title }}
                                {{ loadedChapter.chapter_number }}
                            </h3>
                            <p
                                v-for="verse in loadedChapter.verses"
                                :key="verse.id"
                                class="mb-2 rounded px-2 py-1 transition-colors"
                                :class="getVerseHighlightClass(verse.id)"
                            >
                                <DropdownMenu>
                                    <DropdownMenuTrigger
                                        class="w-full cursor-default"
                                    >
                                        <!-- Mobile: Click for references, Desktop: Hover for references -->
                                        <HoverCard
                                            @update:open="
                                                (open) =>
                                                    open &&
                                                    handleVerseHover(verse.id)
                                            "
                                        >
                                            <HoverCardTrigger>
                                                <span
                                                    class="cursor-pointer font-semibold text-primary hover:underline"
                                                    @click.stop="
                                                        handleVerseClick(
                                                            verse.id,
                                                        )
                                                    "
                                                    >{{
                                                        verse.verse_number
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
                                                            {{
                                                                translateReference(
                                                                    ref.reference,
                                                                )
                                                            }}:
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
                                        {{ verse.text }}
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent>
                                        <!-- Cross References for Mobile -->
                                        <div
                                            v-if="
                                                clickedVerseId === verse.id &&
                                                hoveredVerseReferences.length > 0
                                            "
                                            class="mb-2 sm:hidden"
                                        >
                                            <DropdownMenuLabel>{{
                                                t('Cross References')
                                            }}</DropdownMenuLabel>
                                            <DropdownMenuSeparator />
                                            <div class="max-h-40 overflow-y-auto px-2 py-1">
                                                <div
                                                    v-for="ref in hoveredVerseReferences.slice(
                                                        0,
                                                        3,
                                                    )"
                                                    :key="ref.id"
                                                    class="mb-2 text-xs"
                                                >
                                                    <p class="font-semibold text-primary">
                                                        {{ translateReference(ref.reference) }}
                                                    </p>
                                                    <p class="text-muted-foreground">
                                                        {{ ref.verse?.text?.substring(0, 60) }}...
                                                    </p>
                                                </div>
                                                <p
                                                    v-if="hoveredVerseReferences.length > 3"
                                                    class="text-xs text-muted-foreground italic"
                                                >
                                                    +{{ hoveredVerseReferences.length - 3 }} {{ t('more references') }}
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
                                                highlightVerse(
                                                    verse.id,
                                                    'yellow',
                                                )
                                            "
                                        >
                                            <span
                                                class="flex items-center gap-2"
                                            >
                                                <span
                                                    class="h-4 w-4 rounded bg-yellow-300"
                                                ></span>
                                                {{ t('Highlight - Yellow') }}
                                            </span>
                                        </DropdownMenuItem>
                                        <DropdownMenuItem
                                            @click="
                                                highlightVerse(
                                                    verse.id,
                                                    'green',
                                                )
                                            "
                                        >
                                            <span
                                                class="flex items-center gap-2"
                                            >
                                                <span
                                                    class="h-4 w-4 rounded bg-green-300"
                                                ></span>
                                                {{ t('Highlight - Green') }}
                                            </span>
                                        </DropdownMenuItem>
                                        <DropdownMenuItem
                                            @click="removeHighlight(verse.id)"
                                        >
                                            {{ t('Remove Highlight') }}
                                        </DropdownMenuItem>
                                        <DropdownMenuLabel>{{
                                            t('Learn More')
                                        }}</DropdownMenuLabel>
                                        <DropdownMenuSeparator />
                                        <DropdownMenuItem
                                            @click="studyVerse(verse.id)"
                                        >
                                            {{ t('Study this Verse') }}
                                        </DropdownMenuItem>
                                        <DropdownMenuItem
                                            @click="openNotesDialog(verse)"
                                        >
                                            {{ t('Put Note on this Verse') }}
                                        </DropdownMenuItem>
                                        <DropdownMenuItem
                                            v-if="canUpdate"
                                            @click="openVerseDialog(verse)"
                                        >
                                            {{ t('Edit Verse Text') }}
                                        </DropdownMenuItem>
                                        <DropdownMenuLabel>{{
                                            t('Share')
                                        }}</DropdownMenuLabel>
                                        <DropdownMenuSeparator />
                                        <DropdownMenuItem
                                            @click="shareVerse(verse)"
                                        >
                                            <Share2 class="mr-2 h-4 w-4" />
                                            {{ t('Share this Verse') }}
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </p>
                        </ScrollArea>
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
            v-if="selectedVerseForNote"
            :open="notesDialogOpen"
            @update:open="notesDialogOpen = $event"
            :verse-id="selectedVerseForNote.id"
            :verse-text="selectedVerseForNote.text"
            :verse-reference="`${loadedChapter.book?.title} ${loadedChapter.chapter_number}:${selectedVerseForNote.verse_number}`"
            @saved="handleNoteSaved"
        />

        <!-- Notes Dialog -->
        <VerseDialog
            v-if="selectedVerseForEdit && canUpdate"
            :open="verseDialogOpen"
            @update:open="verseDialogOpen = $event"
            :verse-id="selectedVerseForEdit.id"
            :verse-text="selectedVerseForEdit.text"
            :verse-reference="`${loadedChapter.book?.title} ${loadedChapter.chapter_number}:${selectedVerseForEdit.verse_number}`"
            @saved="handleVerseSaved"
        />
    </AppLayout>
</template>
