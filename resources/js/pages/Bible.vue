<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import AlertUser from '@/components/AlertUser.vue';
import { bibles } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import Card from '@/components/ui/card/Card.vue';
import CardHeader from '@/components/ui/card/CardHeader.vue';
import CardTitle from '@/components/ui/card/CardTitle.vue';
import { BookOpen } from 'lucide-vue-next';
import CardDescription from '@/components/ui/card/CardDescription.vue';
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
    bible: {
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
    },
    initialChapter: {
        id:number;
        chapter_number:number;
        book: {
            id:number;
            title:string;
        };
        verses: {
            id:number;
            verse_number:number;
            text:string;
        }[]
    }
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

function loadChapter(chapterId: number) {
    fetch(`/api/bibles/books/chapters/${chapterId}`)
        .then(res => res.json())
        .then(data => {
            loadedChapter.value = data;
            selectedChapterId.value = chapterId;
        });
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
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <div>
                            <CardTitle class="flex items-center gap-2">
                                <BookOpen class="h-5 w-5" />
                                {{ bible.name }}
                            </CardTitle>
                            <CardDescription>{{ bible.language }} • {{ bible.version }}</CardDescription>
                        </div>
                        <div class="flex flex-row gap-4">
                            <Select v-model="selectedBookId">
                                <SelectTrigger class="w-48">
                                    <SelectValue placeholder="Select a book" />
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
                                            v-for="chapter in bible.books.find(book => book.id === Number(selectedBookId))?.chapters || []"
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
                    <div class="text-lg leading-relaxed space-y-2 max-w-4xl mx-auto">
                        <h3 class="text-xl font-semibold mb-4">
                            {{ loadedChapter.book?.title }} {{ loadedChapter.chapter_number }}
                        </h3>
                        <p v-for="verse in loadedChapter.verses" :key="verse.id" class="mb-2">
                            <span class="font-semibold text-primary">{{ verse.verse_number }}.</span>
                            {{ verse.text }}
                        </p>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
