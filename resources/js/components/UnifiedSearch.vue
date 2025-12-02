<template>
    <div class="unified-search">
        <div class="search-header mb-6">
            <div class="relative">
                <Input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Search verses, notes, and lessons..."
                    class="w-full pl-10"
                    @keyup.enter="performSearch"
                />
                <Icon
                    name="search"
                    class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                />
            </div>
        </div>

        <div class="search-filters mb-4">
            <Button
                variant="outline"
                size="sm"
                @click="showFilters = !showFilters"
                class="flex items-center gap-2"
            >
                <Icon name="filter" class="h-4 w-4" />
                Filters
                <Icon
                    :name="showFilters ? 'chevron-up' : 'chevron-down'"
                    class="h-4 w-4"
                />
            </Button>

            <div
                v-if="showFilters"
                class="mt-4 space-y-4 rounded-lg border p-4"
            >
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <!-- Search Types -->
                    <div>
                        <label class="mb-2 block text-sm font-medium"
                            >Search In</label
                        >
                        <div class="space-y-2">
                            <div class="flex items-center gap-2">
                                <Checkbox
                                    id="type-verses"
                                    v-model="filters.types"
                                    value="verses"
                                />
                                <label for="type-verses" class="text-sm"
                                    >Verses</label
                                >
                            </div>
                            <div class="flex items-center gap-2">
                                <Checkbox
                                    id="type-notes"
                                    v-model="filters.types"
                                    value="notes"
                                />
                                <label for="type-notes" class="text-sm"
                                    >Notes</label
                                >
                            </div>
                            <div class="flex items-center gap-2">
                                <Checkbox
                                    id="type-lessons"
                                    v-model="filters.types"
                                    value="lessons"
                                />
                                <label for="type-lessons" class="text-sm"
                                    >Lessons</label
                                >
                            </div>
                        </div>
                    </div>

                    <!-- Bible Version Filter -->
                    <div v-if="filters.types.includes('verses')">
                        <label class="mb-2 block text-sm font-medium"
                            >Bible Version</label
                        >
                        <Select v-model="filters.version">
                            <SelectTrigger>
                                <SelectValue placeholder="All Versions" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="">All Versions</SelectItem>
                                <SelectItem
                                    v-for="bible in filterOptions.bibles"
                                    :key="bible.id"
                                    :value="bible.abbreviation"
                                >
                                    {{ bible.name }} ({{ bible.abbreviation }})
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <!-- Book Filter -->
                    <div v-if="filters.types.includes('verses')">
                        <label class="mb-2 block text-sm font-medium"
                            >Book</label
                        >
                        <Select v-model="filters.book_id">
                            <SelectTrigger>
                                <SelectValue placeholder="All Books" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="">All Books</SelectItem>
                                <SelectItem
                                    v-for="book in filterOptions.books"
                                    :key="book.id"
                                    :value="book.id"
                                >
                                    {{ book.title }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <!-- Language Filter -->
                    <div>
                        <label class="mb-2 block text-sm font-medium"
                            >Language</label
                        >
                        <Select v-model="filters.language">
                            <SelectTrigger>
                                <SelectValue placeholder="All Languages" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="">All Languages</SelectItem>
                                <SelectItem
                                    v-for="lang in filterOptions.languages"
                                    :key="lang"
                                    :value="lang"
                                >
                                    {{ lang }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>

                <div class="flex gap-2">
                    <Button @click="performSearch" size="sm">
                        Apply Filters
                    </Button>
                    <Button @click="resetFilters" variant="outline" size="sm">
                        Reset
                    </Button>
                </div>
            </div>
        </div>

        <div v-if="loading" class="py-8 text-center">
            <div
                class="mx-auto h-8 w-8 animate-spin rounded-full border-b-2 border-primary"
            ></div>
            <p class="mt-2 text-sm text-muted-foreground">Searching...</p>
        </div>

        <div v-else-if="hasResults" class="search-results space-y-6">
            <!-- Verses Results -->
            <div v-if="results.verses?.data?.length" class="results-section">
                <h3 class="mb-3 text-lg font-semibold">
                    Verses ({{ results.verses.total }})
                </h3>
                <div class="space-y-2">
                    <div
                        v-for="verse in results.verses.data"
                        :key="'verse-' + verse.id"
                        class="rounded-lg border p-4 transition-colors hover:bg-accent"
                    >
                        <p
                            class="mb-1 text-sm font-medium text-muted-foreground"
                        >
                            {{ verse.book }} {{ verse.chapter }}:{{
                                verse.verse_number
                            }}
                            <span class="ml-2 text-xs"
                                >({{ verse.version }})</span
                            >
                        </p>
                        <p class="text-base">{{ verse.text }}</p>
                    </div>
                </div>
            </div>

            <!-- Notes Results -->
            <div v-if="results.notes?.data?.length" class="results-section">
                <h3 class="mb-3 text-lg font-semibold">
                    Notes ({{ results.notes.total }})
                </h3>
                <div class="space-y-2">
                    <div
                        v-for="note in results.notes.data"
                        :key="'note-' + note.id"
                        class="rounded-lg border p-4 transition-colors hover:bg-accent"
                    >
                        <p class="mb-1 font-medium">{{ note.title }}</p>
                        <p class="text-sm text-muted-foreground">
                            {{ note.content }}
                        </p>
                        <p class="mt-2 text-xs text-muted-foreground">
                            {{ formatDate(note.created_at) }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Lessons Results -->
            <div v-if="results.lessons?.data?.length" class="results-section">
                <h3 class="mb-3 text-lg font-semibold">
                    Lessons ({{ results.lessons.total }})
                </h3>
                <div class="space-y-2">
                    <div
                        v-for="lesson in results.lessons.data"
                        :key="'lesson-' + lesson.id"
                        class="rounded-lg border p-4 transition-colors hover:bg-accent"
                    >
                        <p class="mb-1 font-medium">{{ lesson.title }}</p>
                        <p class="text-sm text-muted-foreground">
                            {{ lesson.description }}
                        </p>
                        <div class="mt-2 flex gap-2">
                            <span
                                class="rounded bg-secondary px-2 py-1 text-xs"
                            >
                                {{ lesson.language }}
                            </span>
                            <span
                                v-if="lesson.episode_number"
                                class="rounded bg-secondary px-2 py-1 text-xs"
                            >
                                Episode {{ lesson.episode_number }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div
            v-else-if="searchPerformed && !loading"
            class="py-8 text-center text-muted-foreground"
        >
            No results found for "{{ searchQuery }}"
        </div>
    </div>
</template>

<script setup lang="ts">
import Icon from '@/components/Icon.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { trackSearch } from '@/composables/useAnalytics';
import { computed, onMounted, ref } from 'vue';

const searchQuery = ref('');
const loading = ref(false);
const showFilters = ref(false);
const searchPerformed = ref(false);
const results = ref({});
const filterOptions = ref({
    bibles: [],
    books: [],
    series: [],
    languages: [],
});

const filters = ref({
    types: ['verses', 'notes', 'lessons'],
    version: '',
    book_id: '',
    language: '',
    series_id: '',
    date_from: '',
    date_to: '',
});

const hasResults = computed(() => {
    return (
        results.value.verses?.data?.length ||
        results.value.notes?.data?.length ||
        results.value.lessons?.data?.length
    );
});

const performSearch = async () => {
    if (!searchQuery.value.trim()) {
        return;
    }

    loading.value = true;
    searchPerformed.value = true;

    try {
        const params = new URLSearchParams({
            query: searchQuery.value,
            types: JSON.stringify(filters.value.types),
            filters: JSON.stringify({
                version: filters.value.version,
                book_id: filters.value.book_id,
                language: filters.value.language,
                series_id: filters.value.series_id,
                date_from: filters.value.date_from,
                date_to: filters.value.date_to,
            }),
        });

        const response = await fetch(`/api/search?${params}`);
        const data = await response.json();
        results.value = data;

        // Track search with results count
        const totalResults =
            (data.verses?.data?.length || 0) +
            (data.notes?.data?.length || 0) +
            (data.lessons?.data?.length || 0);
        trackSearch(searchQuery.value, totalResults);
    } catch (error) {
        console.error('Search error:', error);
        trackSearch(searchQuery.value, 0);
    } finally {
        loading.value = false;
    }
};

const resetFilters = () => {
    filters.value = {
        types: ['verses', 'notes', 'lessons'],
        version: '',
        book_id: '',
        language: '',
        series_id: '',
        date_from: '',
        date_to: '',
    };
    performSearch();
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString();
};

const loadFilterOptions = async () => {
    try {
        const response = await fetch('/api/search/filters');
        filterOptions.value = await response.json();
    } catch (error) {
        console.error('Error loading filter options:', error);
    }
};

onMounted(() => {
    loadFilterOptions();
});
</script>
