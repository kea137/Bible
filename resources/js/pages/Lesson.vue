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
import { ScrollArea } from '@/components/ui/scroll-area';
import AppLayout from '@/layouts/AppLayout.vue';
import { lessons } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage, router } from '@inertiajs/vue3';
import {
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
        
        if (!response.ok) {
            throw new Error('Failed to toggle completion');
        }
        
        const data = await response.json();
        if (data.success) {
            chapterCompleted.value = data.progress.completed;
        }
    } catch (error) {
        console.error('Failed to toggle lesson completion:', error);
        alert('Failed to toggle lesson completion. Please try again.');
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

async function handleReferenceHover(reference: any) {
    // Fetch cross-references for this verse from the database
    try {
        // First set the selected verse
        selectedReferenceVerse.value = reference;
        
        // Then fetch cross-references if we have a verse with proper data
        if (reference.verse_id) {
            const response = await fetch(`/api/verses/${reference.verse_id}/references`);
            const data = await response.json();
            if (data && data.references) {
                hoveredVerseReferences.value = data.references;
            } else {
                hoveredVerseReferences.value = [];
            }
        } else {
            hoveredVerseReferences.value = [];
        }
    } catch {
        hoveredVerseReferences.value = [];
    }
}

function formatParagraphText(paragraph: any): any[] {
    let text = paragraph.text;
    const textParts: any[] = [];
    let lastIndex = 0;
    
    // Replace full verse references with their text
    if (paragraph.references) {
        paragraph.references.forEach((ref: any) => {
            if (ref.type === 'full' && ref.text) {
                text = text.replace(ref.original, `"${ref.text}"`);
            }
        });
        
        // Find all short reference positions and create text parts
        const shortRefs = paragraph.references.filter((r: any) => r.type === 'short');
        shortRefs.forEach((ref: any) => {
            const refPattern = new RegExp(ref.original.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'));
            const match = refPattern.exec(text.substring(lastIndex));
            
            if (match) {
                const matchIndex = lastIndex + match.index;
                
                // Add text before reference
                if (matchIndex > lastIndex) {
                    textParts.push({
                        type: 'text',
                        content: text.substring(lastIndex, matchIndex)
                    });
                }
                
                // Add reference as separate part
                textParts.push({
                    type: 'reference',
                    content: ref
                });
                
                lastIndex = matchIndex + ref.original.length;
            }
        });
    }
    
    // Add remaining text
    if (lastIndex < text.length) {
        textParts.push({
            type: 'text',
            content: text.substring(lastIndex)
        });
    }
    
    // If no parts, just return the full text
    if (textParts.length === 0) {
        textParts.push({
            type: 'text',
            content: text.trim()
        });
    }
    
    return textParts;
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
                                <span v-if="lesson.episode_number !== null && lesson.episode_number !== undefined" class="ml-2">
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
                                class="indent-6 text-justify"
                            >
                                <template v-for="(part, idx) in formatParagraphText(paragraph)" :key="idx">
                                    <template v-if="part.type === 'text'">{{ part.content }}</template>
                                    <HoverCard v-else-if="part.type === 'reference'" @update:open="(open) => open && handleReferenceHover(part.content)">
                                        <HoverCardTrigger>
                                            <span
                                                class="cursor-pointer font-semibold text-primary hover:underline"
                                                @click="handleReferenceClick(part.content)"
                                            >
                                                {{ part.content.reference }}
                                            </span>
                                        </HoverCardTrigger>
                                        <HoverCardContent class="w-80">
                                            <div class="space-y-2">
                                                <p class="text-sm font-semibold text-primary">
                                                    {{ part.content.reference }}
                                                </p>
                                                <p class="text-sm">
                                                    {{ part.content.text }}
                                                </p>
                                            </div>
                                        </HoverCardContent>
                                    </HoverCard>
                                </template>
                            </p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- References sidebar (1/3) - matching Bible cross-reference UI -->
            <div class="flex flex-[1] flex-col gap-3 lg:h-160 lg:gap-4">
                <!-- Top half - Cross References -->
                <Card class="flex-1 overflow-hidden">
                    <CardHeader class="pb-3">
                        <CardTitle class="text-sm sm:text-base"
                            >{{ t('Cross References') }}</CardTitle
                        >
                        <CardDescription class="text-xs"
                            ><span class="hidden sm:inline"
                                >{{ t('Hover over scripture references to see') }}
                                {{ t('references') }}</span
                            ><span class="sm:hidden"
                                >{{ t('Tap scripture references to see references') }}</span
                            ></CardDescription
                        >
                    </CardHeader>
                    <CardContent class="max-h-[30vh] overflow-y-auto lg:max-h-[40vh]">
                        <ScrollArea v-if="hoveredVerseReferences.length > 0" class="h-full">
                            <div class="space-y-3">
                                <div
                                    v-for="ref in hoveredVerseReferences"
                                    :key="ref.id"
                                    class="cursor-pointer rounded border p-2 transition-colors hover:bg-accent"
                                    @click="handleReferenceClick(ref)"
                                >
                                    <p class="text-sm font-semibold text-primary">
                                        {{ ref.reference }}
                                    </p>
                                    <p class="mt-1 text-xs text-muted-foreground">
                                        {{ ref.verse?.text?.substring(0, 100) }}...
                                    </p>
                                </div>
                            </div>
                        </ScrollArea>
                        <p v-else class="text-sm text-muted-foreground italic">
                            <span class="hidden sm:inline"
                                >{{ t('Hover over a scripture reference to see its') }}
                                {{ t('cross-references') }}</span
                            >
                            <span class="sm:hidden"
                                >{{ t('Tap a scripture reference to see its') }}
                                {{ t('cross-references') }}</span
                            >
                        </p>
                    </CardContent>
                </Card>

                <!-- Bottom half - Selected Reference Verse -->
                <Card class="flex-1 overflow-hidden">
                    <CardHeader class="pb-3">
                        <CardTitle class="text-sm sm:text-base"
                            >{{ t('Selected Reference') }}</CardTitle
                        >
                        <CardDescription class="text-xs"
                            >{{ t('Click a reference above to view full') }}
                            {{ t('verse') }}</CardDescription
                        >
                    </CardHeader>
                    <CardContent class="max-h-[30vh] overflow-y-auto lg:max-h-[40vh]">
                        <div v-if="selectedReferenceVerse" class="space-y-2">
                            <p class="text-sm font-semibold">
                                {{ selectedReferenceVerse.book_title || selectedReferenceVerse.verse?.book?.title }}
                                {{ selectedReferenceVerse.chapter_number || selectedReferenceVerse.verse?.chapter?.chapter_number }}:{{ selectedReferenceVerse.verse_number || selectedReferenceVerse.verse?.verse_number }}
                            </p>
                            <p class="text-sm">
                                {{ selectedReferenceVerse.text || selectedReferenceVerse.verse?.text }}
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
