<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import CardDescription from '@/components/ui/card/CardDescription.vue';
import CardHeader from '@/components/ui/card/CardHeader.vue';
import CardTitle from '@/components/ui/card/CardTitle.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { bibles, dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import {
    BookOpen,
    Highlighter,
    Library,
    Quote,
    TrendingUp,
} from 'lucide-vue-next';
import { onMounted, ref } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
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

function continueLast() {
    if (props.lastReading) {
        router.visit(`/bibles/${props.lastReading.bible_id}`);
    }
}

function exploreBibles() {
    router.visit(bibles().url);
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
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <!-- Welcome Message -->
            <div class="mb-2">
                <h1 class="text-2xl font-bold text-foreground">
                    Welcome back, {{ userName }}!
                </h1>
                <p class="text-muted-foreground">
                    Continue your spiritual journey
                </p>
            </div>

            <!-- Stats Cards -->
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <Card class="transition-shadow hover:shadow-md">
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <CardTitle class="text-lg"
                                >Bibles Available</CardTitle
                            >
                            <Library class="h-5 w-5 text-muted-foreground" />
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold">
                            {{ readingStats.total_bibles }}
                        </div>
                        <p class="mt-1 text-xs text-muted-foreground">
                            In your language
                        </p>
                    </CardContent>
                </Card>

                <Card class="transition-shadow hover:shadow-md">
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <CardTitle class="text-lg">Verses Today</CardTitle>
                            <BookOpen class="h-5 w-5 text-muted-foreground" />
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold">
                            {{ readingStats.verses_read_today }}
                        </div>
                        <p class="mt-1 text-xs text-muted-foreground">
                            Keep reading!
                        </p>
                    </CardContent>
                </Card>

                <Card class="transition-shadow hover:shadow-md">
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <CardTitle class="text-lg">Progress</CardTitle>
                            <TrendingUp class="h-5 w-5 text-muted-foreground" />
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold">
                            {{ readingStats.chapters_completed }}
                        </div>
                        <p class="mt-1 text-xs text-muted-foreground">
                            Chapters completed
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Main Content Area -->
            <div class="grid gap-4 md:grid-cols-2">
                <!-- Verse of the Day -->
                <Card class="md:col-span-2">
                    <CardHeader>
                        <div class="flex items-center gap-2">
                            <Quote class="h-5 w-5 text-primary" />
                            <CardTitle>Verse of the Day</CardTitle>
                        </div>
                    </CardHeader>
                    <CardContent v-if="verseOfTheDay">
                        <blockquote
                            class="border-l-4 border-primary pl-4 italic"
                        >
                            <p class="mb-4 text-lg leading-relaxed">
                                "{{ verseOfTheDay.text }}"
                            </p>
                            <footer
                                class="text-sm font-medium text-muted-foreground"
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
                        class="py-8 text-center text-muted-foreground"
                    >
                        <p>
                            No verse available. Start reading to discover
                            inspiring passages!
                        </p>
                    </CardContent>
                </Card>

                <!-- Last Reading / Continue Reading -->
                <Card v-if="lastReading">
                    <CardHeader>
                        <CardTitle>Continue Reading</CardTitle>
                        <CardDescription
                            >Pick up where you left off</CardDescription
                        >
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-3">
                            <div>
                                <p class="font-medium">
                                    {{ lastReading.bible_name }}
                                </p>
                                <p class="text-sm text-muted-foreground">
                                    {{ lastReading.book_title }} Chapter
                                    {{ lastReading.chapter_number }}
                                </p>
                            </div>
                            <Button @click="continueLast" class="w-full">
                                Continue Reading
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <!-- Quick Actions -->
                <Card :class="lastReading ? '' : 'md:col-span-2'">
                    <CardHeader>
                        <CardTitle>Quick Actions</CardTitle>
                        <CardDescription
                            >Start your study session</CardDescription
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
                                Browse Bibles
                            </Button>
                            <Button
                                @click="router.visit('/bibles/parallel')"
                                variant="outline"
                                class="w-full justify-start"
                            >
                                <Library class="mr-2 h-4 w-4" />
                                Compare Translations
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Reading Reminder -->
            <Card
                class="border-primary/20 bg-gradient-to-r from-primary/5 to-primary/10"
            >
                <CardContent class="pt-6">
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
                    <div class="space-y-3">
                        <div
                            v-for="highlight in highlights.slice(0, 5)"
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
                        <Button
                            v-if="highlights.length > 5"
                            variant="outline"
                            class="mt-4 w-full"
                        >
                            View All {{ highlights.length }} Highlights
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
