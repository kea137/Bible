<script setup lang="ts">
import AlertUser from '@/components/AlertUser.vue';
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
import Pagination from '@/components/ui/pagination/Pagination.vue';
import PaginationContent from '@/components/ui/pagination/PaginationContent.vue';
import PaginationEllipsis from '@/components/ui/pagination/PaginationEllipsis.vue';
import PaginationItem from '@/components/ui/pagination/PaginationItem.vue';
import PaginationNext from '@/components/ui/pagination/PaginationNext.vue';
import PaginationPrevious from '@/components/ui/pagination/PaginationPrevious.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { bibles } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { LibraryBigIcon, PenTool, Search } from 'lucide-vue-next';
import { computed, ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
// import { MeiliSearch } from 'meilisearch';
import { Link } from '@inertiajs/vue3';

const { t } = useI18n();
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: t('Bibles'),
        href: bibles().url,
    },
];

const props = defineProps<{
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

const pageSize = 5;
const currentPage = ref(1);

const paginatedBibles = computed(() => {
    const start = (currentPage.value - 1) * pageSize;
    return props.biblesList.slice(start, start + pageSize);
});

function handlePageChange(page: number) {
    currentPage.value = page;
}

const searchOpen = ref(false);

const filteredBibles = computed(() => {
    if (!searchQuery.value) {
        return props.biblesList;
    }
    const query = searchQuery.value.toLowerCase();
    return props.biblesList.filter(
        (bible) =>
            bible.name.toLowerCase().includes(query) ||
            bible.language.toLowerCase().includes(query) ||
            bible.version.toLowerCase().includes(query) ||
            bible.abbreviation.toLowerCase().includes(query),
    );
});

function viewBible(bibleId: number) {
    router.visit(`/bibles/${bibleId}`);
    searchOpen.value = false;
}

const page = usePage();
const successMessage = computed(() => page.props.success as string);
const errorMessage = computed(() => page.props.error as string);
const alertSuccess = ref(!!successMessage.value);
const alertError = ref(!!errorMessage.value);
const alertErrorMessage = ref('');


onMounted(() => {
    searchVerses();
});

// Stub for highlights
const highlights = ref<any[]>([]);

// Stub for availableBibles
const availableBibles = ref<any[]>([]);

// Method to handle highlight selection
function viewHighlight(verseId: number) {
    router.visit(`/verses/${verseId}/study`);
    searchOpen.value = false;
}

// Method to generate verse study link
function verse_study(verseId: number) {
    return `/verses/${verseId}/study`;
}

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

const searchQuery = ref('');
// const client = new MeiliSearch({
//   host: 'http://127.0.0.1:7700', // Replace with your Meilisearch host
//   apiKey: 'Bzp5QuuYWH9xAS6uFH4EHGUb0MbopWJ4JiyTtUu6iaU', // Replace with your Meilisearch API key
// });

// const index = client.index('verses'); // Replace with your Meilisearch index name

const searchVerses = async () => {
    if (searchQuery.value.trim() === '') {
        // Fetch all highlights and bibles if search query is empty
        await loadHighlights();
        await loadBibles();
    } else {
        // Search verses from Meilisearch
        // const response = await index.search(searchQuery.value, {
        //     limit: 10,
        // });
        // Map Meilisearch hits to a format similar to highlights
        // const verseResults = response.hits.map((hit: any) => ({
        //     verse: {
        //         id: hit.id,
        //         text: hit.text,
        //         verse_number: hit.verse_number,
        //     },
        // }));
        // Merge highlights and verse search results
        // highlights.value = verseResults;
        // console.log('Search Results:', verseResults);
        // Optionally, search bibles by name/language/version
        const bibleResponse = await fetch(`/api/bibles?search=${encodeURIComponent(searchQuery.value)}`);
        if (bibleResponse.ok) {
            availableBibles.value = await bibleResponse.json();
        }
    }
};

</script>

<template>
    <Head title="Bibles" />

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
            <Card>
                <CardHeader class="pb-3">
                    <div
                        class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div>
                            <CardTitle
                                class="flex items-center gap-2 text-base sm:text-lg"
                            >
                                <LibraryBigIcon class="h-4 w-4 sm:h-5 sm:w-5" />
                                {{t('Bibles')}}
                            </CardTitle>
                            <CardDescription class="text-xs sm:text-sm"
                                >{{t('Available Bibles')}}</CardDescription
                            >
                        </div>
                        <Button
                            @click="searchOpen = true"
                            variant="outline"
                            class="w-full sm:w-auto"
                        >
                            <Search class="mr-2 h-4 w-4" />
                            {{t('Search Bibles')}}
                        </Button>
                    </div>
                </CardHeader>
                <CardContent>
                    <div
                        v-if="paginatedBibles.length > 0"
                        class="space-y-2 sm:space-y-3"
                    >
                        <div
                            v-for="bible in paginatedBibles"
                            :key="bible.id"
                            class="flex cursor-pointer items-center justify-between rounded-lg border border-border p-2 transition-colors hover:bg-accent/50 sm:p-3"
                            @click="viewBible(bible.id)"
                        >
                            <div class="flex-1">
                                <p class="text-sm font-medium sm:text-base">
                                    {{ bible.name }}
                                </p>
                                <p
                                    class="text-xs text-muted-foreground sm:text-sm"
                                >
                                    {{ bible.language }} • {{ bible.version }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div
                        v-else
                        class="py-6 text-center text-sm text-muted-foreground sm:py-8 sm:text-base"
                    >
                        <p>{{t('No Bibles Available')}}</p>
                    </div>
                </CardContent>
            </Card>
            <div class="mt-8 w-full">
                <Pagination :items-per-page="pageSize" :total="biblesList.length" :default-page="1" @update:page="handlePageChange">
                    <PaginationContent v-slot="{ items }">
                        <PaginationPrevious />

                        <template v-for="(item, index) in items" :key="index">
                            <PaginationItem
                                v-if="item.type === 'page'"
                                :value="item.value"
                                :is-active="item.value === currentPage"
                                @click="handlePageChange(item.value)"
                            >
                                {{ item.value }}
                            </PaginationItem>
                        </template>

                        <PaginationEllipsis v-if="items.some((i: { type: string }) => i.type === 'ellipsis')" />

                        <PaginationNext />
                    </PaginationContent>
                </Pagination>
            </div>

            <!-- Search Dialog -->
            <CommandDialog
                :open="searchOpen"
                @update:open="searchOpen = $event"
            >
                <Command>
                    <CommandInput
                        v-model="searchQuery"
                        @input="searchVerses()"
                        placeholder="Search bibles by name, language, or version..."
                    />
                    <CommandList>
                        <CommandEmpty>{{t('No bibles found.')}}</CommandEmpty>
                        <CommandGroup heading="Bibles">
                            <CommandItem
                                v-for="bible in filteredBibles"
                                :key="bible.id"
                                :value="bible.name"
                                @select="viewBible(bible.id)"
                                class="cursor-pointer"
                            >
                                <div class="flex flex-col">
                                    <span class="font-medium">{{
                                        bible.name
                                    }}</span>
                                    <span class="text-xs text-muted-foreground">
                                        {{ bible.language }} •
                                        {{ bible.version }}
                                    </span>
                                </div>
                            </CommandItem>
                        </CommandGroup>
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
        </div>
    </AppLayout>
</template>
