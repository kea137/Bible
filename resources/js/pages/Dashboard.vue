<script setup lang="ts">
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
    CommandSeparator,
} from '@/components/ui/command';
import AppLayout from '@/layouts/AppLayout.vue';
import { bibles, dashboard, highlighted_verses_page, verse_study } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    BookOpen,
    Highlighter,
    Library,
    PenTool,
    Quote,
    Search,
    TrendingUp,
} from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { algoliasearch } from 'algoliasearch';

onMounted(() => {
    searchVerses();
});

const searchQuery = ref('');
const client = algoliasearch(
    import.meta.env.VITE_ALGOLIA_APP_ID || 'VV2R5XG4FF',
    import.meta.env.VITE_ALGOLIA_API_KEY || '3a774edb6e30e191a2b70602ddfd65b0'
);

const searchVerses = async () => {
    if (searchQuery.value.trim() === '') {
        // Fetch all highlights and bibles if search query is empty
        await loadHighlights();
        await loadBibles();
    } else {
        // Search verses from Algolia
        try {
            const response = await client.searchSingleIndex({
                indexName: 'verses',
                searchParams: {
                    query: searchQuery.value,
                    hitsPerPage: 10,
                }
            });
            // Map Algolia hits to a format similar to highlights
            const verseResults = response.hits.map((hit: any) => ({
                verse: {
                    id: hit.objectID || hit.id,
                    text: hit.text,
                    verse_number: hit.verse_number,
                },
            }));
            // Merge highlights and verse search results
            highlights.value = verseResults;
            console.log('Search Results:', verseResults);
        } catch (error) {
            console.error('Algolia search error:', error);
        }
        // Optionally, search bibles by name/language/version
        const bibleResponse = await fetch(`/api/bibles?search=${encodeURIComponent(searchQuery.value)}`);
        if (bibleResponse.ok) {
            availableBibles.value = await bibleResponse.json();
        }
    }
};


const { t } = useI18n();
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: t('Dashboard'),
        href: dashboard().url,
    },
];

interface Props {
    verseOfTheDay?: {
        id: number;
        text: string;
        verse_number: number;
        bible: {
            name: string;
        };
        book: {
            title: string;
        };
        chapter: {
            chapter_number: number;
        };
    } | null;
    lastReading?: {
        bible_id: number;
        bible_name: string;
        book_title: string;
        chapter_number: number;
    } | null;
    readingStats: {
        total_bibles: number;
        verses_read_today: number;
        chapters_completed: number;
    };
    userName: string;
}

const props = defineProps<Props>();

const highlights = ref<any[]>([]);
const searchOpen = ref(false);
const availableBibles = ref<any[]>([]);

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

async function loadBibles() {
    try {
        const response = await fetch('/api/bibles');
        if (response.ok) {
            availableBibles.value = await response.json();
        }
    } catch (error) {
        console.error('Failed to load bibles:', error);
    }
}

const filteredHighlights = computed(() => {
    if (!searchQuery.value) {
        return highlights.value;
    }
    const query = searchQuery.value.toLowerCase();
    return highlights.value.filter(
        (h) =>
            h.verse.text.toLowerCase().includes(query) ||
            h.verse.book?.title.toLowerCase().includes(query),
    );
});

const filteredBibles = computed(() => {
    if (!searchQuery.value) {
        return availableBibles.value;
    }
    const query = searchQuery.value.toLowerCase();
    return availableBibles.value.filter(
        (bible) =>
            bible.name.toLowerCase().includes(query) ||
            bible.language.toLowerCase().includes(query) ||
            bible.version.toLowerCase().includes(query),
    );
});

onMounted(() => {
    loadHighlights();
    loadBibles();
});

function continueLast() {
    if (props.lastReading) {
        router.visit(`/bibles/${props.lastReading.bible_id}`);
    }
}

function exploreBibles() {
    router.visit(bibles().url);
}

function viewBible(bibleId: number) {
    router.visit(`/bibles/${bibleId}`);
    searchOpen.value = false;
}

function viewHighlight(verseId: number) {
    // Navigate to the verse's bible page
    const highlight = highlights.value.find((h) => h.verse.id === verseId);
    if (highlight && highlight.verse.chapter?.bible_id) {
        router.visit(`/bibles/${highlight.verse.chapter.bible_id}`);
        searchOpen.value = false;
    }
}

function getHighlightColorClass(color: string): string {
    if (color === 'yellow') {
        return 'border-l-yellow-400 bg-yellow-50 dark:bg-yellow-900/20';
    } else if (color === 'green') {
        return 'border-l-green-400 bg-green-50 dark:bg-green-900/20';
    }
    return '';
}
</script>

<template>
    <Head :title="t('Dashboard')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-2 sm:p-4"
        >
            <!-- Welcome Message -->
            <div
                class="mb-2 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <h1 class="text-xl font-bold text-foreground sm:text-2xl">
                        {{t('Welcome back,')}} {{ userName }}!
                    </h1>
                    <p class="text-sm text-muted-foreground sm:text-base">
                        {{t('Continue your spiritual journey')}}
                    </p>
                </div>
                <Button
                    @click="searchOpen = true"
                    variant="outline"
                    class="w-full sm:w-auto"
                >
                    <Search class="mr-2 h-4 w-4" />
                    {{t('Search')}}
                </Button>
            </div>

            <!-- Stats Cards -->
            <div
                class="grid auto-rows-min gap-3 sm:grid-cols-2 sm:gap-4 lg:grid-cols-3"
            >
                <Card class="transition-shadow hover:shadow-md">
                    <CardHeader class="pb-3">
                        <div class="flex items-center justify-between">
                            <CardTitle class="text-base sm:text-lg"
                                >{{t('Bibles Available')}}</CardTitle
                            >
                            <Library
                                class="h-4 w-4 text-muted-foreground sm:h-5 sm:w-5"
                            />
                        </div>
                    </CardHeader>
                    <CardContent class="pb-4">
                        <div class="text-2xl font-bold sm:text-3xl">
                            {{ readingStats.total_bibles }}
                        </div>
                        <p class="mt-1 text-xs text-muted-foreground">
                            {{t('In your language')}}
                        </p>
                    </CardContent>
                </Card>

                <Card class="transition-shadow hover:shadow-md">
                    <CardHeader class="pb-3">
                        <div class="flex items-center justify-between">
                            <CardTitle class="text-base sm:text-lg"
                                >{{t('Verses Today')}}</CardTitle
                            >
                            <BookOpen
                                class="h-4 w-4 text-muted-foreground sm:h-5 sm:w-5"
                            />
                        </div>
                    </CardHeader>
                    <CardContent class="pb-4">
                        <div class="text-2xl font-bold sm:text-3xl">
                            {{ readingStats.verses_read_today }}
                        </div>
                        <p class="mt-1 text-xs text-muted-foreground">
                            {{t('Keep reading!')}}
                        </p>
                    </CardContent>
                </Card>

                <Card class="transition-shadow hover:shadow-md">
                    <CardHeader class="pb-3">
                        <div class="flex items-center justify-between">
                            <CardTitle class="text-base sm:text-lg"
                                >{{t('Progress')}}</CardTitle
                            >
                            <TrendingUp
                                class="h-4 w-4 text-muted-foreground sm:h-5 sm:w-5"
                            />
                        </div>
                    </CardHeader>
                    <CardContent class="pb-4">
                        <div class="text-2xl font-bold sm:text-3xl">
                            {{ readingStats.chapters_completed }}
                        </div>
                        <p class="mt-1 text-xs text-muted-foreground">
                            {{t('Chapters completed')}}
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Main Content Area -->
            <div class="grid gap-3 sm:gap-4 lg:grid-cols-2">
                <!-- Verse of the Day -->
                <Card class="lg:col-span-2">
                    <CardHeader class="pb-3">
                        <div class="flex items-center gap-2">
                            <Quote class="h-4 w-4 text-primary sm:h-5 sm:w-5" />
                            <CardTitle class="text-base sm:text-lg"
                                >{{t('Verse of the Day')}}</CardTitle
                            >
                        </div>
                    </CardHeader>
                    <CardContent v-if="verseOfTheDay">
                        <blockquote
                            class="border-l-4 border-primary pl-3 italic sm:pl-4"
                        >
                            <p
                                class="mb-3 text-base leading-relaxed sm:mb-4 sm:text-lg"
                            >
                                "{{ verseOfTheDay.text }}"
                            </p>
                            <footer
                                class="text-xs font-medium text-muted-foreground sm:text-sm"
                            >
                                — {{ verseOfTheDay.book.title }}
                                {{ verseOfTheDay.chapter.chapter_number }}:{{
                                    verseOfTheDay.verse_number
                                }}
                                <span class="text-xs"
                                    >({{ verseOfTheDay.bible.name }})</span
                                >
                            </footer>
                        </blockquote>
                    </CardContent>
                    <CardContent
                        v-else
                        class="py-6 text-center text-sm text-muted-foreground sm:py-8 sm:text-base"
                    >
                        <p>
                            {{t('No verse available. Start reading to discover')}}
                            {{t('inspiring passages!')}}
                        </p>
                    </CardContent>
                </Card>

                <!-- Last Reading / Continue Reading -->
                <Card v-if="lastReading">
                    <CardHeader class="pb-3">
                        <CardTitle class="text-base sm:text-lg"
                            >{{t('Continue Reading')}}</CardTitle
                        >
                        <CardDescription class="text-xs sm:text-sm"
                            >{{t('Pick up where you left off')}}</CardDescription
                        >
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-3">
                            <div>
                                <p class="text-sm font-medium sm:text-base">
                                    {{ lastReading.bible_name }}
                                </p>
                                <p
                                    class="text-xs text-muted-foreground sm:text-sm"
                                >
                                    {{ lastReading.book_title }} Chapter
                                    {{ lastReading.chapter_number }}
                                </p>
                            </div>
                            <Button @click="continueLast" class="w-full">
                                {{t('Continue Reading')}}
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <!-- Quick Actions -->
                <Card :class="lastReading ? '' : 'lg:col-span-2'">
                    <CardHeader class="pb-3">
                        <CardTitle class="text-base sm:text-lg"
                            >{{t('Quick Actions')}}</CardTitle
                        >
                        <CardDescription class="text-xs sm:text-sm"
                            >{{t('Start your study session')}}</CardDescription
                        >
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-2">
                            <Button
                                @click="exploreBibles"
                                variant="outline"
                                class="w-full justify-start"
                            >
                                <BookOpen class="mr-2 h-4 w-4" />
                                {{t('Browse Bibles')}}
                            </Button>
                            <Button
                                @click="router.visit('/bibles/parallel')"
                                variant="outline"
                                class="w-full justify-start"
                            >
                                <Library class="mr-2 h-4 w-4" />
                                {{t('Compare Translations')}}
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>

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
                                {{t('the Word')}}
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
                    <div class="space-y-3">
                        <div
                            v-for="highlight in highlights.slice(0, 5)"
                            :key="highlight.id"
                            :class="[
                                'rounded-r border-l-4 py-2 pl-3 transition-colors sm:pl-4',
                                getHighlightColorClass(highlight.color),
                            ]"
                        >
                            <p class="mb-2 text-xs sm:text-sm">
                                {{ highlight.verse.text }}
                            </p>
                            <p
                                class="text-xs font-medium text-muted-foreground"
                            >
                                {{ highlight.verse.book?.title }}
                                {{ highlight.verse.chapter?.chapter_number }}:{{
                                    highlight.verse.verse_number
                                }}
                            </p>
                            <p
                                v-if="highlight.note"
                                class="mt-1 text-xs text-muted-foreground italic"
                            >
                                {{t('Note:')}} {{ highlight.note }}
                            </p>
                        </div>
                        <Link :href="highlighted_verses_page()">
                            <Button
                                v-if="highlights.length > 5"
                                variant="outline"
                                class="mt-4 w-full"
                            >
                                {{t('View All')}} {{ highlights.length }} {{t('Highlights')}}
                            </Button>
                        </Link>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Search Dialog -->
        <CommandDialog :open="searchOpen" @update:open="searchOpen = $event">
            <Command>
                <CommandInput
                    v-model="searchQuery"
                    @input="searchVerses()"
                    :placeholder="t('Search bibles, verses, or highlighted passages...')"
                />
                <CommandList>
                    <CommandEmpty>{{t('No results found.')}}</CommandEmpty>

                    <CommandGroup
                        v-if="filteredBibles.length > 0"
                        :heading="t('Bibles')"
                    >
                        <CommandItem
                            v-for="bible in filteredBibles.slice(0, 5)"
                            :key="bible.id"
                            :value="bible.name"
                            @select="viewBible(bible.id)"
                        >
                            <BookOpen class="mr-2 h-4 w-4" />
                            <div class="flex flex-col">
                                <span class="font-medium">{{
                                    bible.name
                                }}</span>
                                <span class="text-xs text-muted-foreground">
                                    {{ bible.language }} • {{ bible.version }}
                                </span>
                            </div>
                        </CommandItem>
                    </CommandGroup>

                    <CommandSeparator
                        v-if="
                            filteredBibles.length > 0 &&
                            filteredHighlights.length > 0
                        "
                    />

                    <CommandGroup
                        v-if="filteredHighlights.length > 0"
                        :heading="t('Highlighted Verses')"
                    >         
                        <CommandItem
                            v-for="highlight in filteredHighlights.slice(0, 10)"
                            :key="highlight.id"
                            :value="highlight.verse.text"
                            @select="viewHighlight(highlight.verse.id)"
                        >
                            <PenTool class="mr-2 h-4 w-4" />
                            <Link :href="verse_study(highlight.verse.id)">
                            <div class="flex flex-col">
                                <span class="line-clamp-1 text-sm">{{
                                    highlight.verse.text
                                }}</span>
                                <span class="text-xs text-muted-foreground">
                                    {{ highlight.verse.book?.title }}
                                    {{
                                        highlight.verse.chapter?.chapter_number
                                    }}:{{ highlight.verse.verse_number }}
                                </span>
                            </div>
                            </Link>
                        </CommandItem>
                    </CommandGroup>
                </CommandList>
            </Command>
        </CommandDialog>
    </AppLayout>
</template>
