<script setup lang="ts">
import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import CardDescription from '@/components/ui/card/CardDescription.vue';
import CardHeader from '@/components/ui/card/CardHeader.vue';
import CardTitle from '@/components/ui/card/CardTitle.vue';
import { Progress } from '@/components/ui/progress';
import AppLayout from '@/layouts/AppLayout.vue';
import { reading_plan } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import {
    BookOpen,
    Calendar,
    Target,
    TrendingUp,
    CheckCircle2,
    BookMarked,
} from 'lucide-vue-next';

const props = defineProps<{
    totalChapters: number;
    completedChapters: number;
    progressPercentage: number;
    chaptersReadToday: number;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Reading Plan',
        href: reading_plan().url,
    },
];

// Calculate estimated days to complete based on different reading paces
const remainingChapters = props.totalChapters - props.completedChapters;
const readingPlans = [
    {
        name: 'Intensive Plan',
        chaptersPerDay: 10,
        days: Math.ceil(remainingChapters / 10),
        description: 'Complete the Bible in about 4 months',
    },
    {
        name: 'Standard Plan',
        chaptersPerDay: 4,
        days: Math.ceil(remainingChapters / 4),
        description: 'Complete the Bible in about 10 months',
    },
    {
        name: 'Leisurely Plan',
        chaptersPerDay: 2,
        days: Math.ceil(remainingChapters / 2),
        description: 'Complete the Bible in about 20 months',
    },
    {
        name: 'Year Plan',
        chaptersPerDay: 3,
        days: Math.ceil(remainingChapters / 3),
        description: 'Complete the Bible in about one year',
    },
];
</script>

<template>
    <Head title="Reading Plan" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <!-- Welcome Message -->
            <div class="mb-2">
                <h1 class="text-2xl font-bold text-foreground">
                    Your Bible Reading Journey
                </h1>
                <p class="text-muted-foreground">
                    Track your progress and stay motivated as you read through the Bible
                </p>
            </div>

            <!-- Progress Overview -->
            <div class="grid gap-4 md:grid-cols-4">
                <Card class="md:col-span-4">
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div>
                                <CardTitle>Overall Progress</CardTitle>
                                <CardDescription>
                                    {{ completedChapters }} of {{ totalChapters }} chapters completed
                                </CardDescription>
                            </div>
                            <TrendingUp class="h-8 w-8 text-primary" />
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-2">
                            <Progress :model-value="progressPercentage" class="h-3" />
                            <p class="text-center text-2xl font-bold text-primary">
                                {{ progressPercentage }}%
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <CardTitle class="text-lg">Today</CardTitle>
                            <Calendar class="h-5 w-5 text-muted-foreground" />
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold">
                            {{ chaptersReadToday }}
                        </div>
                        <p class="mt-1 text-xs text-muted-foreground">
                            Chapters read
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <CardTitle class="text-lg">Completed</CardTitle>
                            <CheckCircle2 class="h-5 w-5 text-muted-foreground" />
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold">
                            {{ completedChapters }}
                        </div>
                        <p class="mt-1 text-xs text-muted-foreground">
                            Chapters done
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <CardTitle class="text-lg">Remaining</CardTitle>
                            <BookMarked class="h-5 w-5 text-muted-foreground" />
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold">
                            {{ remainingChapters }}
                        </div>
                        <p class="mt-1 text-xs text-muted-foreground">
                            Chapters left
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <CardTitle class="text-lg">Total</CardTitle>
                            <BookOpen class="h-5 w-5 text-muted-foreground" />
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold">
                            {{ totalChapters }}
                        </div>
                        <p class="mt-1 text-xs text-muted-foreground">
                            Total chapters
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Reading Plans -->
            <Card>
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <Target class="h-5 w-5 text-primary" />
                        <CardTitle>Suggested Reading Plans</CardTitle>
                    </div>
                    <CardDescription>
                        Choose a pace that works for you
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div
                            v-for="plan in readingPlans"
                            :key="plan.name"
                            class="rounded-lg border p-4 transition-colors hover:bg-accent"
                        >
                            <h3 class="font-semibold">{{ plan.name }}</h3>
                            <p class="mt-1 text-sm text-muted-foreground">
                                {{ plan.description }}
                            </p>
                            <div class="mt-3 flex items-center gap-4 text-sm">
                                <div>
                                    <span class="font-medium">{{ plan.chaptersPerDay }}</span>
                                    <span class="text-muted-foreground"> chapters/day</span>
                                </div>
                                <div>
                                    <span class="font-medium">~{{ plan.days }}</span>
                                    <span class="text-muted-foreground"> days</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Reading Guidelines -->
            <Card>
                <CardHeader>
                    <CardTitle>How to Use Reading Progress Tracking</CardTitle>
                    <CardDescription>
                        Simple steps to track your Bible reading journey
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="space-y-4">
                        <div class="flex gap-3">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-primary text-sm font-bold text-primary-foreground">
                                1
                            </div>
                            <div>
                                <h4 class="font-medium">Open Any Bible Chapter</h4>
                                <p class="text-sm text-muted-foreground">
                                    Navigate to the Bible you want to read and select a book and chapter.
                                </p>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-primary text-sm font-bold text-primary-foreground">
                                2
                            </div>
                            <div>
                                <h4 class="font-medium">Read Through the Chapter</h4>
                                <p class="text-sm text-muted-foreground">
                                    Take your time to read and understand the chapter. Highlight important verses and add notes.
                                </p>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-primary text-sm font-bold text-primary-foreground">
                                3
                            </div>
                            <div>
                                <h4 class="font-medium">Mark as Read</h4>
                                <p class="text-sm text-muted-foreground">
                                    Click the "Mark as Read" button between the navigation arrows to track your progress.
                                </p>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-primary text-sm font-bold text-primary-foreground">
                                4
                            </div>
                            <div>
                                <h4 class="font-medium">Track Your Progress</h4>
                                <p class="text-sm text-muted-foreground">
                                    View your progress on this page and the dashboard. The system will track chapters completed and update your statistics.
                                </p>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Tips for Consistent Reading -->
            <Card class="border-primary/20 bg-gradient-to-r from-primary/5 to-primary/10">
                <CardHeader>
                    <CardTitle>Tips for Consistent Bible Reading</CardTitle>
                </CardHeader>
                <CardContent>
                    <ul class="space-y-2 text-sm">
                        <li class="flex gap-2">
                            <span class="text-primary">•</span>
                            <span>Set a specific time each day for reading (morning or evening works best)</span>
                        </li>
                        <li class="flex gap-2">
                            <span class="text-primary">•</span>
                            <span>Start with smaller goals and gradually increase your reading pace</span>
                        </li>
                        <li class="flex gap-2">
                            <span class="text-primary">•</span>
                            <span>Use the highlight feature to mark verses that speak to you</span>
                        </li>
                        <li class="flex gap-2">
                            <span class="text-primary">•</span>
                            <span>Add notes to capture insights and reflections</span>
                        </li>
                        <li class="flex gap-2">
                            <span class="text-primary">•</span>
                            <span>Don't worry about perfection - consistency is more important than speed</span>
                        </li>
                        <li class="flex gap-2">
                            <span class="text-primary">•</span>
                            <span>Study cross-references to deepen your understanding</span>
                        </li>
                    </ul>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
