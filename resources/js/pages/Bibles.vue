<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import AlertUser from '@/components/AlertUser.vue';
import { bibles } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import Card from '@/components/ui/card/Card.vue';
import CardHeader from '@/components/ui/card/CardHeader.vue';
import CardTitle from '@/components/ui/card/CardTitle.vue';
import { GraduationCap } from 'lucide-vue-next';
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
import { reactive, ref } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Bibles',
        href: bibles().url,
    },
];

const props = defineProps<{
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

// Track selected book for each bible
const selectedBookIds = reactive<{ [bibleId: number]: number }>({});
const loadedChapter = ref('');

function chapter_loaded( chapterId: number) {
    // Example: send data to backend via Inertia or fetch/axios
    // Replace with your actual backend call
    // For demonstration, using Inertia.visit (if using Inertia.js)
    // import { Inertia } from '@inertiajs/inertia'
    // Inertia.visit(`/bibles/${bibleId}/books/${bookId}/chapters/${chapterId}`)

    // Or use fetch/axios:
    fetch(`/api/bibles/books/chapters/${chapterId}`)
        .then(res => res.json())
        .then(data => {
            loadedChapter.value = data.verses
            .map((verse: any) => `${verse.verse_number}. ${verse.text}`)
            .join('\n');
        });
}

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
    <Head title="Bibles" />

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
                                <GraduationCap class="h-5 w-5" />
                                Available Bibles
                            </CardTitle>
                            <CardDescription>Loaded Bibles</CardDescription>
                        </div>
                    </div>
                </CardHeader>
                <CardContent>
                    <div v-if="biblesList.length > 0" class="space-y-3">
                        <div
                            v-for="bible in biblesList.slice(0, 5)"
                            :key="bible.id"
                            class="flex items-center justify-between rounded-lg border border-border p-3 transition-colors hover:bg-accent/50"
                        >
                            <div class="flex-1">
                                <p class="font-medium">{{ bible.name }}</p>
                                <p class="text-sm text-muted-foreground">{{ bible.language }} • {{ bible.version }}</p>
                            </div>
                            <div class="flex flex-row gap-4">
                                <Select class="w-24 ml-12">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select a book" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectGroup>
                                            <SelectLabel>Books</SelectLabel>
                                            <SelectItem
                                                v-for="book in bible.books"
                                                :key="book.id"
                                                :value="book.id.toString()"
                                                @click="selectedBookIds[bible.id] = book.id"
                                                :selected="selectedBookIds[bible.id] === book.id"
                                            >
                                                {{ book.title }}
                                            </SelectItem>
                                        </SelectGroup>
                                    </SelectContent>
                                </Select>
                                <Select class="w-24 mr-12">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select a Chapter" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectGroup>
                                            <SelectLabel>Chapters</SelectLabel>
                                            <SelectItem
                                                v-for="chapter in bible.books.find(book => book.id === selectedBookIds[bible.id])?.chapters || []"
                                                :key="chapter.id"
                                                :value="chapter.id.toString()"
                                                @click="chapter_loaded(chapter.id)"
                                            >
                                                {{ chapter.chapter_number }}
                                            </SelectItem>
                                        </SelectGroup>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>
                    </div>
                    <div v-else class="py-8 text-center text-muted-foreground">
                        <p>No Bibles Available</p>
                    </div>
                </CardContent>
                <CardContent>
                    <CardDescription>
                        <div class="text-2xl text-center my-8 mx-auto max-w-3xl whitespace-pre-line">
                            {{ loadedChapter }}
                        </div>
                    </CardDescription>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
