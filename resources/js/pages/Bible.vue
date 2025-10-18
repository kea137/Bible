<script setup lang="ts">
import AlertUser from '@/components/AlertUser.vue';
import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import CardDescription from '@/components/ui/card/CardDescription.vue';
import CardHeader from '@/components/ui/card/CardHeader.vue';
import CardTitle from '@/components/ui/card/CardTitle.vue';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/Dropdown-menu';
import {
    HoverCard,
    HoverCardContent,
    HoverCardTrigger,
} from '@/components/ui/hover-card';
import AppLayout from '@/layouts/AppLayout.vue';
import { bibles } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import { BookOpen } from 'lucide-vue-next';
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { ref, watch } from 'vue';
import { ScrollArea } from '@/components/ui/scroll-area';
import DropdownMenuLabel from '@/components/ui/dropdown-menu/DropdownMenuLabel.vue';
import DropdownMenuSeparator from '@/components/ui/dropdown-menu/DropdownMenuSeparator.vue';

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
        title: 'Bibles',
        href: bibles().url,
    },
    {
        title: props.bible.name,
        href: `/bibles/${props.bible.id}`,
    },
];

const selectedBookId = ref(props.initialChapter.book.id);
const selectedChapterId = ref(props.initialChapter.id);
const loadedChapter = ref(props.initialChapter);
const hoveredVerseReferences = ref<any[]>([]);
const selectedReferenceVerse = ref<any>(null);
const verseHighlights = ref<Record<number, any>>({});

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

function loadChapter(chapterId: number) {
    fetch(`/api/bibles/books/chapters/${chapterId}`)
        .then((res) => res.json())
        .then((data) => {
            loadedChapter.value = data;
            selectedChapterId.value = chapterId;
            hoveredVerseReferences.value = [];
            selectedReferenceVerse.value = null;
            loadChapterHighlights(chapterId);
        });
}

async function highlightVerse(verseId: number, color: string) {
    if (!page.props.auth?.user) {
        alert('Please log in to highlight verses');
        return;
    }

    try {
        // Try to get CSRF token from meta tag first
        let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        // Fallback to Inertia page props if not found
        if (!csrfToken && page.props.csrf_token) {
            csrfToken = String(page.props.csrf_token);
        }
        // If still not found, try to reload the page to get a fresh token
        if (!csrfToken) {
            // Attempt to reload the page to refresh CSRF token
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
        } catch (jsonError) {
            // If response is not JSON, treat as error
            alert('Unexpected server response. Please try again.');
            return;
        }

        if (response.ok && result?.success) {
            await loadChapterHighlights(selectedChapterId.value);
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
        // Try to get CSRF token from meta tag first
        let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        // Fallback to Inertia page props if not found
        if (!csrfToken && page.props.csrf_token) {
            csrfToken = String(page.props.csrf_token);
        }
        // If still not found, try to reload the page to get a fresh token
        if (!csrfToken) {
            // Attempt to reload the page to refresh CSRF token
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

        type HighlightResponse = {
            success?: boolean;
            message?: string;
        };
        let result: HighlightResponse = {};
        try {
            result = await response.json();
            // Remove highlight color
            removeVerseHighlightClass(verseId);
        } catch (jsonError) {
            // If response is not JSON, treat as error
            alert('Unexpected server response. Please try again.');
            return;
        }

    } catch (error) {
        alert('Failed to Remove highlight.');
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
    } catch (error) {
        hoveredVerseReferences.value = [];
    }
}

async function handleReferenceClick(reference: any) {
    selectedReferenceVerse.value = reference.verse;
}

function studyVerse(verseId: number) {
    window.location.href = `/verses/${verseId}/study`;
}

watch(selectedChapterId, (newChapterId) => {
    if (newChapterId) {
        loadChapter(newChapterId);
    }
});

const page = usePage();
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

// Load initial highlights
if (props.initialChapter?.id) {
    loadChapterHighlights(props.initialChapter.id);
}
</script>

<template>
    <Head :title="bible.name" />

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
            class="flex h-full flex-1 flex-row gap-4 overflow-x-auto rounded-xl p-4"
        >
            <!-- Main content area (2/3) -->
            <div class="flex-[2]">
                <Card>
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div>
                                <CardTitle class="flex items-center gap-2">
                                    <BookOpen class="h-5 w-5" />
                                    {{ bible.name }}
                                </CardTitle>
                                <CardDescription
                                    >{{ bible.language }} •
                                    {{ bible.version }}</CardDescription
                                >
                            </div>
                            <div class="flex flex-row gap-4">
                                <Select v-model="selectedBookId">
                                    <SelectTrigger class="w-48">
                                        <SelectValue
                                            placeholder="Select a book"
                                        />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectGroup>
                                            <SelectLabel>Books</SelectLabel>
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
                                    <SelectTrigger class="w-32">
                                        <SelectValue placeholder="Chapter" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectGroup>
                                            <SelectLabel>Chapters</SelectLabel>
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
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <ScrollArea class="mx-30 max-w-4xl space-y-2 text-justify text-lg leading-relaxed h-130">
                            <h3 class="mb-4 text-center text-xl font-semibold">
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
                                    <DropdownMenuTrigger class="w-full cursor-default">
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
                                            @click="studyVerse(verse.id)"
                                        >
                                            Put Note on this Verse
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </p>
                        </ScrollArea>
                    </CardContent>
                </Card>
            </div>

            <!-- References sidebar (1/3) -->
            <div class="flex flex-[1] flex-col gap-4 h-160">
                <!-- Top half - Hovered verse references -->
                <Card class="flex-1 overflow-hidden">
                    <CardHeader>
                        <CardTitle class="text-base"
                            >Cross References</CardTitle
                        >
                        <CardDescription class="text-xs"
                            >Hover over verse numbers to see
                            references</CardDescription
                        >
                    </CardHeader>
                    <CardContent class="max-h-[40vh] overflow-y-auto">
                        <ScrollArea v-if="hoveredVerseReferences.length > 0" class="h-full">
                            <div
                                
                                class="space-y-3"
                            >
                                <div
                                    v-for="ref in hoveredVerseReferences"
                                    :key="ref.id"
                                    class="cursor-pointer rounded border p-2 transition-colors hover:bg-accent"
                                    @click="handleReferenceClick(ref)"
                                >
                                    <p class="text-sm font-semibold text-primary">
                                        {{ ref.reference }}
                                    </p>
                                    <p class="mt-1 text-xs text-muted-foreground">
                                        {{ ref.verse?.text?.substring(0, 100) }}...
                                    </p>
                                </div>
                            </div>
                        </ScrollArea>
                        <p v-else class="text-sm text-muted-foreground italic">
                            Hover over a verse number to see its
                            cross-references
                        </p>
                    </CardContent>
                </Card>

                <!-- Bottom half - Selected reference verse -->
                <Card class="flex-1 overflow-hidden">
                    <CardHeader>
                        <CardTitle class="text-base"
                            >Selected Reference</CardTitle
                        >
                        <CardDescription class="text-xs"
                            >Click a reference above to view full
                            verse</CardDescription
                        >
                    </CardHeader>
                    <CardContent class="max-h-[40vh] overflow-y-auto">
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
                            Click on a reference to view the full verse
                        </p>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
