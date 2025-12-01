<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import CardHeader from '@/components/ui/card/CardHeader.vue';
import CardTitle from '@/components/ui/card/CardTitle.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { BookOpenIcon, ExternalLinkIcon } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

interface Verse {
    id: number;
    verse_number: number;
    text: string;
    book: {
        title: string;
    };
    chapter: {
        chapter_number: number;
    };
    bible: {
        abbreviation: string;
        language: string;
    };
    pivot: {
        order: number;
        note: string | null;
    };
}

interface Topic {
    id: number;
    title: string;
    description: string;
    category: string;
    verses: Verse[];
}

const props = defineProps<{
    topic: Topic;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: t('Topics'),
        href: '/topics',
    },
    {
        title: props.topic.title,
        href: `/topics/${props.topic.id}`,
    },
];

function viewVerse(verse: Verse) {
    const book = verse.book.title;
    const chapter = verse.chapter.chapter_number;
    const verseNumber = verse.verse_number;
    router.visit(`/verses/${verse.id}/study`);
}

function formatVerseReference(verse: Verse): string {
    return `${verse.book.title} ${verse.chapter.chapter_number}:${verse.verse_number}`;
}
</script>

<template>
    <Head :title="topic.title" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-12">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <!-- Topic Header -->
                <div class="mb-8">
                    <div class="mb-4 flex items-center gap-2">
                        <BookOpenIcon class="h-8 w-8 text-blue-600" />
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                            {{ topic.title }}
                        </h1>
                    </div>
                    <p
                        v-if="topic.description"
                        class="text-lg text-gray-600 dark:text-gray-400"
                    >
                        {{ topic.description }}
                    </p>
                    <div class="mt-4">
                        <span
                            v-if="topic.category"
                            class="inline-flex items-center rounded-full bg-blue-100 px-3 py-1 text-sm font-medium text-blue-800 dark:bg-blue-900 dark:text-blue-200"
                        >
                            {{ topic.category }}
                        </span>
                    </div>
                </div>

                <!-- Verse Chain -->
                <div class="space-y-4">
                    <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-200">
                        {{ t('Verse Chain') }}
                    </h2>

                    <div v-if="topic.verses.length > 0" class="space-y-4">
                        <Card
                            v-for="(verse, index) in topic.verses"
                            :key="verse.id"
                            class="cursor-pointer transition-all hover:shadow-md"
                            @click="viewVerse(verse)"
                        >
                            <CardHeader>
                                <div class="flex items-start justify-between">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 text-sm font-semibold text-blue-800 dark:bg-blue-900 dark:text-blue-200"
                                        >
                                            {{ index + 1 }}
                                        </div>
                                        <CardTitle class="text-base">
                                            {{ formatVerseReference(verse) }}
                                        </CardTitle>
                                    </div>
                                    <ExternalLinkIcon class="h-4 w-4 text-gray-400" />
                                </div>
                            </CardHeader>
                            <CardContent>
                                <p class="mb-4 text-gray-700 dark:text-gray-300">
                                    {{ verse.text }}
                                </p>
                                <div
                                    v-if="verse.pivot.note"
                                    class="rounded-lg bg-amber-50 p-3 dark:bg-amber-900/20"
                                >
                                    <p
                                        class="text-sm italic text-amber-900 dark:text-amber-100"
                                    >
                                        {{ verse.pivot.note }}
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <!-- Empty state -->
                    <Card v-else>
                        <CardContent class="py-16">
                            <div class="flex flex-col items-center justify-center">
                                <BookOpenIcon class="mb-4 h-12 w-12 text-gray-400" />
                                <h3
                                    class="mb-2 text-lg font-semibold text-gray-900 dark:text-gray-100"
                                >
                                    {{ t('No verses yet') }}
                                </h3>
                                <p class="text-gray-600 dark:text-gray-400">
                                    {{ t('This topic doesn\'t have any verses yet') }}
                                </p>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
