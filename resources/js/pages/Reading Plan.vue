<script setup lang="ts">
import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import CardDescription from '@/components/ui/card/CardDescription.vue';
import CardHeader from '@/components/ui/card/CardHeader.vue';
import CardTitle from '@/components/ui/card/CardTitle.vue';
import { Progress } from '@/components/ui/progress';
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import { reading_plan } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import {
    BookMarked,
    BookOpen,
    Calendar,
    CheckCircle2,
    Target,
    TrendingUp,
} from 'lucide-vue-next';
import { ref, watch } from 'vue';

const props = defineProps<{
    totalChapters: number;
    completedChapters: number;
    progressPercentage: number;
    chaptersReadToday: number;
    selectedBible: {
        id: number;
        name: string;
        language: string;
    };
    allBibles: Array<{
        id: number;
        name: string;
        language: string;
    }>;
}>();

const selectedBibleId = ref(props.selectedBible.id.toString());

watch(selectedBibleId, (newBibleId) => {
    router.visit(`/reading-plan?bible_id=${newBibleId}`, {
        preserveScroll: true,
    });
});

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
            class="flex h-full flex-1 flex-col gap-3 overflow-x-auto rounded-xl p-2 sm:gap-4 sm:p-4"
        >
            <!-- Welcome Message -->
            <div
                class="mb-2 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between"
            >
                <div>
                    <h1 class="text-xl font-bold text-foreground sm:text-2xl">
                        Your Bible Reading Journey
                    </h1>
                    <p class="text-sm text-muted-foreground sm:text-base">
                        Track your progress and stay motivated as you read
                        through the Bible
                    </p>
                </div>
                <div class="w-full sm:w-64">
                    <Select v-model="selectedBibleId">
                        <SelectTrigger>
                            <SelectValue placeholder="Select Bible" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectGroup>
                                <SelectLabel>Select Bible</SelectLabel>
                                <SelectItem
                                    v-for="bible in allBibles"
                                    :key="bible.id"
                                    :value="bible.id.toString()"
                                >
                                    {{ bible.name }} ({{ bible.language }})
                                </SelectItem>
                            </SelectGroup>
                        </SelectContent>
                    </Select>
                </div>
            </div>

            <!-- Progress Overview -->
            <div class="grid gap-3 sm:gap-4 lg:grid-cols-4">
                <Card class="lg:col-span-4">
                    <CardHeader class="pb-3">
                        <div
                            class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                        >
                            <div>
                                <CardTitle class="text-base sm:text-lg"
                                    >Overall Progress</CardTitle
                                >
                                <CardDescription class="text-xs sm:text-sm">
                                    {{ completedChapters }} of
                                    {{ totalChapters }} chapters completed
                                </CardDescription>
                            </div>
                            <TrendingUp
                                class="h-6 w-6 text-primary sm:h-8 sm:w-8"
                            />
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-2">
                            <Progress
                                :model-value="progressPercentage"
                                class="h-2 sm:h-3"
                            />
                            <p
                                class="text-center text-xl font-bold text-primary sm:text-2xl"
                            >
                                {{ progressPercentage }}%
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="pb-3">
                        <div class="flex items-center justify-between">
                            <CardTitle class="text-base sm:text-lg"
                                >Today</CardTitle
                            >
                            <Calendar
                                class="h-4 w-4 text-muted-foreground sm:h-5 sm:w-5"
                            />
                        </div>
                    </CardHeader>
                    <CardContent class="pb-4">
                        <div class="text-2xl font-bold sm:text-3xl">
                            {{ chaptersReadToday }}
                        </div>
                        <p class="mt-1 text-xs text-muted-foreground">
                            Chapters read
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="pb-3">
                        <div class="flex items-center justify-between">
                            <CardTitle class="text-base sm:text-lg"
                                >Completed</CardTitle
                            >
                            <CheckCircle2
                                class="h-4 w-4 text-muted-foreground sm:h-5 sm:w-5"
                            />
                        </div>
                    </CardHeader>
                    <CardContent class="pb-4">
                        <div class="text-2xl font-bold sm:text-3xl">
                            {{ completedChapters }}
                        </div>
                        <p class="mt-1 text-xs text-muted-foreground">
                            Chapters done
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="pb-3">
                        <div class="flex items-center justify-between">
                            <CardTitle class="text-base sm:text-lg"
                                >Remaining</CardTitle
                            >
                            <BookMarked
                                class="h-4 w-4 text-muted-foreground sm:h-5 sm:w-5"
                            />
                        </div>
                    </CardHeader>
                    <CardContent class="pb-4">
                        <div class="text-2xl font-bold sm:text-3xl">
                            {{ remainingChapters }}
                        </div>
                        <p class="mt-1 text-xs text-muted-foreground">
                            Chapters left
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="pb-3">
                        <div class="flex items-center justify-between">
                            <CardTitle class="text-base sm:text-lg"
                                >Total</CardTitle
                            >
                            <BookOpen
                                class="h-4 w-4 text-muted-foreground sm:h-5 sm:w-5"
                            />
                        </div>
                    </CardHeader>
                    <CardContent class="pb-4">
                        <div class="text-2xl font-bold sm:text-3xl">
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
                <CardHeader class="pb-3">
                    <div class="flex items-center gap-2">
                        <Target class="h-4 w-4 text-primary sm:h-5 sm:w-5" />
                        <CardTitle class="text-base sm:text-lg"
                            >Suggested Reading Plans</CardTitle
                        >
                    </div>
                    <CardDescription class="text-xs sm:text-sm">
                        Choose a pace that works for you
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-3 sm:gap-4 lg:grid-cols-2">
                        <div
                            v-for="plan in readingPlans"
                            :key="plan.name"
                            class="rounded-lg border p-3 transition-colors hover:bg-accent sm:p-4"
                        >
                            <h3 class="text-sm font-semibold sm:text-base">
                                {{ plan.name }}
                            </h3>
                            <p
                                class="mt-1 text-xs text-muted-foreground sm:text-sm"
                            >
                                {{ plan.description }}
                            </p>
                            <div class="mt-3 flex items-center gap-4 text-sm">
                                <div>
                                    <span class="font-medium">{{
                                        plan.chaptersPerDay
                                    }}</span>
                                    <span class="text-muted-foreground">
                                        chapters/day</span
                                    >
                                </div>
                                <div>
                                    <span class="font-medium"
                                        >~{{ plan.days }}</span
                                    >
                                    <span class="text-muted-foreground">
                                        days</span
                                    >
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
                            <div
                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-primary text-sm font-bold text-primary-foreground"
                            >
                                1
                            </div>
                            <div>
                                <h4 class="font-medium">
                                    Open Any Bible Chapter
                                </h4>
                                <p class="text-sm text-muted-foreground">
                                    Navigate to the Bible you want to read and
                                    select a book and chapter.
                                </p>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <div
                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-primary text-sm font-bold text-primary-foreground"
                            >
                                2
                            </div>
                            <div>
                                <h4 class="font-medium">
                                    Read Through the Chapter
                                </h4>
                                <p class="text-sm text-muted-foreground">
                                    Take your time to read and understand the
                                    chapter. Highlight important verses and add
                                    notes.
                                </p>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <div
                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-primary text-sm font-bold text-primary-foreground"
                            >
                                3
                            </div>
                            <div>
                                <h4 class="font-medium">Mark as Read</h4>
                                <p class="text-sm text-muted-foreground">
                                    Click the "Mark as Read" button between the
                                    navigation arrows to track your progress.
                                </p>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <div
                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-primary text-sm font-bold text-primary-foreground"
                            >
                                4
                            </div>
                            <div>
                                <h4 class="font-medium">Track Your Progress</h4>
                                <p class="text-sm text-muted-foreground">
                                    View your progress on this page and the
                                    dashboard. The system will track chapters
                                    completed and update your statistics.
                                </p>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Tips for Consistent Reading -->
            <Card
                class="border-primary/20 bg-gradient-to-r from-primary/5 to-primary/10"
            >
                <CardHeader>
                    <CardTitle>Tips for Consistent Bible Reading</CardTitle>
                </CardHeader>
                <CardContent>
                    <ul class="space-y-2 text-sm">
                        <li class="flex gap-2">
                            <span class="text-primary">•</span>
                            <span
                                >Set a specific time each day for reading
                                (morning or evening works best)</span
                            >
                        </li>
                        <li class="flex gap-2">
                            <span class="text-primary">•</span>
                            <span
                                >Start with smaller goals and gradually increase
                                your reading pace</span
                            >
                        </li>
                        <li class="flex gap-2">
                            <span class="text-primary">•</span>
                            <span
                                >Use the highlight feature to mark verses that
                                speak to you</span
                            >
                        </li>
                        <li class="flex gap-2">
                            <span class="text-primary">•</span>
                            <span
                                >Add notes to capture insights and
                                reflections</span
                            >
                        </li>
                        <li class="flex gap-2">
                            <span class="text-primary">•</span>
                            <span
                                >Don't worry about perfection - consistency is
                                more important than speed</span
                            >
                        </li>
                        <li class="flex gap-2">
                            <span class="text-primary">•</span>
                            <span
                                >Study cross-references to deepen your
                                understanding</span
                            >
                        </li>
                    </ul>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
