<script setup lang="ts">
import AlertUser from '@/components/AlertUser.vue';
import Button from '@/components/ui/button/Button.vue';
import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import CardDescription from '@/components/ui/card/CardDescription.vue';
import CardHeader from '@/components/ui/card/CardHeader.vue';
import CardTitle from '@/components/ui/card/CardTitle.vue';
import {
    HoverCard,
    HoverCardContent,
    HoverCardTrigger,
} from '@/components/ui/hover-card';
import AppLayout from '@/layouts/AppLayout.vue';
import { lessons } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage, router } from '@inertiajs/vue3';
import {
    BookOpen,
    CheckCircle,
    ChevronLeft,
    ChevronRight,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const props = defineProps<{
    lesson: {
        id: number;
        title: string;
        description: string;
        language: string;
        series_id?: number;
        episode_number?: number;
        series?: {
            id: number;
            title: string;
        };
        paragraphs: {
            id: number;
            title: string;
            text: string;
            references: {
                type: 'short' | 'full';
                book_code: string;
                chapter: number;
                verse: number;
                original: string;
                text?: string;
                book_title?: string;
                reference?: string;
            }[];
        }[];
    };
    userProgress?: {
        id: number;
        completed: boolean;
        completed_at: string | null;
    } | null;
    seriesLessons?: {
        id: number;
        title: string;
        episode_number: number;
    }[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: t('Lessons'),
        href: lessons().url,
    },
    {
        title: props.lesson.title,
        href: `/lessons/${props.lesson.id}`,
    },
];

const page = usePage();
const hoveredVerseReferences = ref<any[]>([]);
const selectedReferenceVerse = ref<any>(null);
const chapterCompleted = ref(props.userProgress?.completed || false);

async function toggleChapterCompletion() {
    try {
        const response = await fetch(`/api/lessons/${props.lesson.id}/progress`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
        });
        
        const data = await response.json();
        if (data.success) {
            chapterCompleted.value = data.progress.completed;
        }
    } catch (error) {
        console.error('Failed to toggle lesson completion:', error);
    }
}

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

function handleReferenceClick(reference: any){
    selectedReferenceVerse.value = reference;
}

function formatParagraphText(paragraph: any): string {
    let text = paragraph.text;
    
    // Replace full verse references with their text
    if (paragraph.references) {
        paragraph.references.forEach((ref: any) => {
            if (ref.type === 'full' && ref.text) {
                text = text.replace(ref.original, `"${ref.text}"`);
            }
        });
    }
    
    // Remove short reference markers for inline display
    // text = text.replace(/'([A-Z0-9]{3})\s+(\d+):(\d+)'/g, '');
    
    return text.trim();
}

function getInlineReferences(paragraph: any): any[] {
    if (!paragraph.references) return [];
    return paragraph.references.filter((r: any) => r.type === 'short');
}

function handleReferenceHover(reference: any) {
    hoveredVerseReferences.value = [reference];
    selectedReferenceVerse.value = reference;
}

function navigateToNextLesson() {
    if (!props.seriesLessons || !props.lesson.episode_number) return;
    
    const currentIndex = props.seriesLessons.findIndex(l => l.id === props.lesson.id);
    if (currentIndex < props.seriesLessons.length - 1) {
        const nextLesson = props.seriesLessons[currentIndex + 1];
        router.visit(`/lessons/show/${nextLesson.id}`);
    }
}

function navigateToPreviousLesson() {
    if (!props.seriesLessons || !props.lesson.episode_number) return;
    
    const currentIndex = props.seriesLessons.findIndex(l => l.id === props.lesson.id);
    if (currentIndex > 0) {
        const previousLesson = props.seriesLessons[currentIndex - 1];
        router.visit(`/lessons/show/${previousLesson.id}`);
    }
}

const hasNextLesson = computed(() => {
    if (!props.seriesLessons || !props.lesson.episode_number) return false;
    const currentIndex = props.seriesLessons.findIndex(l => l.id === props.lesson.id);
    return currentIndex < props.seriesLessons.length - 1;
});

const hasPreviousLesson = computed(() => {
    if (!props.seriesLessons || !props.lesson.episode_number) return false;
    const currentIndex = props.seriesLessons.findIndex(l => l.id === props.lesson.id);
    return currentIndex > 0;
});

</script>

<template>
    <Head :title="lesson.title" />

    <AlertUser
        v-if="alertSuccess"
        :open="true"
        :title="t('Success')"
        :confirmButtonText="'OK'"
        :message="t('Operation was successful!')"
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
        :title="t('Error')"
        :confirmButtonText="'OK'"
        :message="t('Operation failed! Please try again.')"
        variant="error"
        @update:open="
            () => {
                alertError = false;
            }
        "
    />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-3 overflow-x-auto rounded-xl p-2 sm:p-4 lg:flex-row lg:gap-4"
        >
            <!-- Main content area (2/3) -->
            <div class="flex-[2]">
                <Card>
                    <CardHeader class="pb-3">
                        <!-- Title centered and bold at top -->
                        <CardTitle class="text-center text-xl font-bold sm:text-2xl">
                            {{ lesson.title }}
                        </CardTitle>
                        
                        <!-- Description styled like verse of the day -->
                        <div class="mt-4">
                            <blockquote class="border-l-4 border-primary pl-3 italic sm:pl-4">
                                <p class="text-base leading-relaxed sm:text-lg">
                                    "{{ lesson.description }}"
                                </p>
                            </blockquote>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <!-- Series info and navigation buttons -->
                        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div v-if="lesson.series" class="text-sm text-muted-foreground">
                                <span class="font-semibold">{{ t('Series') }}:</span> {{ lesson.series.title }}
                                <span v-if="lesson.episode_number" class="ml-2">
                                    {{ t('Episode') }} {{ lesson.episode_number }}
                                </span>
                            </div>
                            
                            <!-- Navigation and action buttons for series lessons -->
                            <div class="flex flex-wrap gap-2">
                                <Button
                                    v-if="lesson.series_id && hasPreviousLesson"
                                    variant="outline"
                                    size="sm"
                                    @click="navigateToPreviousLesson"
                                >
                                    <ChevronLeft class="h-4 w-4" />
                                    {{ t('Previous') }}
                                </Button>
                                
                                <Button
                                    v-if="page.props.auth?.user"
                                    :variant="chapterCompleted ? 'default' : 'outline'"
                                    size="sm"
                                    @click="toggleChapterCompletion"
                                >
                                    <CheckCircle class="mr-1 h-4 w-4" />
                                    {{ chapterCompleted ? t('Completed') : t('Mark as Read') }}
                                </Button>
                                
                                <Button
                                    v-if="lesson.series_id && hasNextLesson"
                                    variant="outline"
                                    size="sm"
                                    @click="navigateToNextLesson"
                                >
                                    {{ t('Next') }}
                                    <ChevronRight class="h-4 w-4" />
                                </Button>
                            </div>
                        </div>

                        <!-- Lesson content with indented paragraphs and inline references -->
                        <div class="space-y-4 text-justify text-base leading-relaxed sm:text-lg">
                            <p
                                v-for="paragraph in lesson.paragraphs"
                                :key="paragraph.id"
                                class="pl-6 text-justify"
                            >
                                {{ formatParagraphText(paragraph) }}
                                
                                <!-- Inline scripture reference buttons -->
                                <template v-if="getInlineReferences(paragraph).length > 0">
                                    <span
                                        v-for="(ref, idx) in getInlineReferences(paragraph)"
                                        :key="idx"
                                        class="ml-1"
                                    >
                                        <HoverCard @update:open="(open) => open && handleReferenceHover(ref)">
                                            <HoverCardTrigger>
                                                <span
                                                    class="cursor-pointer font-semibold text-primary hover:underline"
                                                    @click="handleReferenceClick(ref)"
                                                >
                                                    {{ ref.reference }}
                                                </span>
                                            </HoverCardTrigger>
                                            <HoverCardContent class="w-80">
                                                <div class="space-y-2">
                                                    <p class="text-sm font-semibold text-primary">
                                                        {{ ref.reference }}
                                                    </p>
                                                    <p class="text-sm">
                                                        {{ ref.text }}
                                                    </p>
                                                </div>
                                            </HoverCardContent>
                                        </HoverCard>
                                    </span>
                                </template>
                            </p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- References sidebar (1/3) - matching Bible cross-reference UI -->
            <div class="flex flex-[1] flex-col gap-3 lg:h-160 lg:gap-4">
                <!-- Related references section -->
                <Card class="flex-1 overflow-hidden">
                    <CardHeader class="pb-3">
                        <CardTitle class="text-sm sm:text-base"
                            >{{ t('Related References') }}</CardTitle
                        >
                        <CardDescription class="text-xs"
                            >{{ t('Hover or click scripture references in the lesson') }}</CardDescription
                        >
                    </CardHeader>
                    <CardContent class="max-h-[35vh] overflow-y-auto lg:max-h-[45vh]">
                        <div v-if="hoveredVerseReferences.length > 0" class="space-y-2">
                            <div
                                v-for="ref in hoveredVerseReferences"
                                :key="ref.reference"
                                class="cursor-pointer rounded border p-2 transition-colors hover:bg-accent"
                                :class="{ 'bg-accent': ref.reference === selectedReferenceVerse?.reference }"
                                @click="handleReferenceClick(ref)"
                            >
                                <p class="text-sm font-semibold text-primary">
                                    {{ ref.reference }}
                                </p>
                                <p class="mt-1 text-xs text-muted-foreground line-clamp-2">
                                    {{ ref.text }}
                                </p>
                            </div>
                        </div>
                        <p v-else class="text-sm text-muted-foreground italic">
                            {{ t('Hover or click on a scripture reference to view related verses') }}
                        </p>
                    </CardContent>
                </Card>

                <!-- Selected verse full text -->
                <Card class="flex-1 overflow-hidden">
                    <CardHeader class="pb-3">
                        <CardTitle class="text-sm sm:text-base"
                            >{{ t('Selected Verse') }}</CardTitle
                        >
                        <CardDescription class="text-xs"
                            >{{ t('Full verse text') }}</CardDescription
                        >
                    </CardHeader>
                    <CardContent class="max-h-[25vh] overflow-y-auto lg:max-h-[35vh]">
                        <div v-if="selectedReferenceVerse" class="space-y-2">
                            <p class="text-sm font-semibold text-primary">
                                {{ selectedReferenceVerse.reference }}
                            </p>
                            <p class="text-sm leading-relaxed">
                                {{ selectedReferenceVerse.text }}
                            </p>
                            <p v-if="selectedReferenceVerse.book_title" class="mt-2 text-xs text-muted-foreground">
                                {{ selectedReferenceVerse.book_title }} {{ selectedReferenceVerse.chapter_number }}:{{ selectedReferenceVerse.verse_number }}
                            </p>
                        </div>
                        <p v-else class="text-sm text-muted-foreground italic">
                            {{ t('Click on a reference to view the full verse') }}
                        </p>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
