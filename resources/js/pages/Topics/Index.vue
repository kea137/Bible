<script setup lang="ts">
import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import CardDescription from '@/components/ui/card/CardDescription.vue';
import CardHeader from '@/components/ui/card/CardHeader.vue';
import CardTitle from '@/components/ui/card/CardTitle.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { BookOpenIcon, ChevronRight } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: t('Topics'),
        href: '/topics',
    },
];

interface Topic {
    id: number;
    title: string;
    description: string;
    category: string;
    verses_count: number;
}

defineProps<{
    topics: Record<string, Topic[]>;
}>();

function viewTopic(topicId: number) {
    router.visit(`/topics/${topicId}`);
}
</script>

<template>
    <Head :title="t('Bible Topics')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                        {{ t('Bible Topics') }}
                    </h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">
                        {{ t('Explore curated topics and related verses') }}
                    </p>
                </div>

                <!-- Topics grouped by category -->
                <div class="space-y-8">
                    <div
                        v-for="(categoryTopics, category) in topics"
                        :key="category"
                        class="space-y-4"
                    >
                        <h2
                            class="text-2xl font-semibold text-gray-800 dark:text-gray-200"
                        >
                            {{ category || t('General Topics') }}
                        </h2>

                        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                            <Card
                                v-for="topic in categoryTopics"
                                :key="topic.id"
                                class="cursor-pointer transition-all hover:shadow-lg"
                                @click="viewTopic(topic.id)"
                            >
                                <CardHeader>
                                    <div class="flex items-start justify-between">
                                        <div class="flex items-center gap-2">
                                            <BookOpenIcon class="h-5 w-5 text-blue-600" />
                                            <CardTitle class="text-lg">
                                                {{ topic.title }}
                                            </CardTitle>
                                        </div>
                                        <ChevronRight class="h-5 w-5 text-gray-400" />
                                    </div>
                                    <CardDescription v-if="topic.description">
                                        {{ topic.description }}
                                    </CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <div class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ topic.verses_count }}
                                        {{ t(topic.verses_count === 1 ? 'verse' : 'verses') }}
                                    </div>
                                </CardContent>
                            </Card>
                        </div>
                    </div>

                    <!-- Empty state -->
                    <div
                        v-if="Object.keys(topics).length === 0"
                        class="flex flex-col items-center justify-center py-16"
                    >
                        <BookOpenIcon class="mb-4 h-16 w-16 text-gray-400" />
                        <h3 class="mb-2 text-lg font-semibold text-gray-900 dark:text-gray-100">
                            {{ t('No topics yet') }}
                        </h3>
                        <p class="text-gray-600 dark:text-gray-400">
                            {{ t('Topics will appear here once they are created') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
