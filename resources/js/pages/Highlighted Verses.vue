<script setup lang="ts">
import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import CardDescription from '@/components/ui/card/CardDescription.vue';
import CardHeader from '@/components/ui/card/CardHeader.vue';
import CardTitle from '@/components/ui/card/CardTitle.vue';
import ScrollArea from '@/components/ui/scroll-area/ScrollArea.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { highlighted_verses_page } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import {
    BookOpen,
    Highlighter,
} from 'lucide-vue-next';
import { onMounted, ref } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Highlighted Verses',
        href: highlighted_verses_page().url,
    },
];

const highlights = ref<any[]>([]);

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

onMounted(() => {
    loadHighlights();
});

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
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <!-- Reading Reminder -->
            <Card
                class="border-primary/20 bg-gradient-to-r from-primary/5 to-primary/10"
            >
                <CardContent class="pt-2">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="mb-1 font-semibold">
                                Make Reading a Habit
                            </h3>
                            <p class="text-sm text-muted-foreground">
                                Set aside time each day to read and reflect on
                                the Word
                            </p>
                        </div>
                        <BookOpen class="h-8 w-8 text-primary/40" />
                    </div>
                </CardContent>
            </Card>

            <!-- Highlighted Verses -->
            <Card v-if="highlights.length > 0">
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <Highlighter class="h-5 w-5 text-primary" />
                        <CardTitle>Your Highlighted Verses</CardTitle>
                    </div>
                    <CardDescription
                        >Recent verses you've marked</CardDescription
                    >
                </CardHeader>
                <CardContent>
                    <ScrollArea class="space-y-3 h-100">
                        <div
                            v-for="highlight in highlights"
                            :key="highlight.id"
                            :class="[
                                'rounded-r border-l-4 py-2 pl-4 transition-colors',
                                getHighlightColorClass(highlight.color),
                            ]"
                        >
                            <p class="mb-2 text-sm">
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
                                Note: {{ highlight.note }}
                            </p>
                        </div>
                    </ScrollArea>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
