<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import AlertUser from '@/components/AlertUser.vue';
import { bibles_parallel } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import Card from '@/components/ui/card/Card.vue';
import CardHeader from '@/components/ui/card/CardHeader.vue';
import CardTitle from '@/components/ui/card/CardTitle.vue';
import { BookOpen } from 'lucide-vue-next';
import CardContent from '@/components/ui/card/CardContent.vue';

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

const props = defineProps<{
    biblesOther:       {
            id:number;
            name:string;
            abbreviation:string;
            description:string;
            language:string;
            version:string;
            books: {
                id:number;
                title:string;
                book_number:number;
                chapters: {
                    id:number;
                    chapter_number:number;
                }[]
            }[]
        }[],
    biblesList:
        {
            id:number;
            name:string;
            abbreviation:string;
            description:string;
            language:string;
            version:string;
            books: {
                id:number;
                title:string;
                book_number:number;
                chapters: {
                    id:number;
                    chapter_number:number;
                }[]
            }[]
        }[]
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

function loadChapter(chapterId: number, side: 'left' | 'right') {
    fetch(`/api/bibles/books/chapters/${chapterId}`)
        .then(res => res.json())
        .then(data => {
            if (side === 'left') {
                loadedChapter1.value = data;
            } else {
                loadedChapter2.value = data;
            }
        });
}

watch(selectedBible1, (newBibleId) => {
    if (newBibleId) {
        const bible = props.biblesList.find(b => b.id === newBibleId);
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
        const bible = props.biblesOther.find(b => b.id === newBibleId);
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
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                                            {{ bible.name }} ({{ bible.language }})
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
                                                v-for="book in biblesList.find(b => b.id === Number(selectedBible1))?.books || []"
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
                                                v-for="chapter in biblesList.find(b => b.id === Number(selectedBible1))?.books.find(book => book.id === Number(selectedBook1))?.chapters || []"
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
                        <div class="text-base leading-relaxed space-y-2">
                            <h3 class="text-lg font-semibold mb-4">
                                {{ loadedChapter1.book?.title }} {{ loadedChapter1.chapter_number }}
                            </h3>
                            <p v-for="verse in loadedChapter1.verses" :key="verse.id" class="mb-2">
                                <span class="font-semibold text-primary">{{ verse.verse_number }}.</span>
                                {{ verse.text }}
                            </p>
                        </div>
                    </CardContent>
                    <CardContent v-else class="py-8 text-center text-muted-foreground">
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
                                            {{ bible.name }} ({{ bible.language }})
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
                                                v-for="book in biblesOther.find(b => b.id === Number(selectedBible2))?.books || []"
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
                                                v-for="chapter in biblesOther.find(b => b.id === Number(selectedBible2))?.books.find(book => book.id === Number(selectedBook2))?.chapters || []"
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
                        <div class="text-base leading-relaxed space-y-2">
                            <h3 class="text-lg font-semibold mb-4">
                                {{ loadedChapter2.book?.title }} {{ loadedChapter2.chapter_number }}
                            </h3>
                            <p v-for="verse in loadedChapter2.verses" :key="verse.id" class="mb-2">
                                <span class="font-semibold text-primary">{{ verse.verse_number }}.</span>
                                {{ verse.text }}
                            </p>
                        </div>
                    </CardContent>
                    <CardContent v-else class="py-8 text-center text-muted-foreground">
                        <p>Select a Bible to start</p>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
