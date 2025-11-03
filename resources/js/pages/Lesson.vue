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
import { Head, usePage } from '@inertiajs/vue3';
import {
    BookOpen,
    CheckCircle,
} from 'lucide-vue-next';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const props = defineProps<{
    lesson: {
        id: number;
        name: string;
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
        title: props.lesson.name,
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

function formatParagraphText(paragraph: any): { text: string; hasReferences: boolean } {
    let text = paragraph.text;
    let hasReferences = false;
    
    // Replace full verse references with their text
    if (paragraph.references) {
        paragraph.references.forEach((ref: any) => {
            if (ref.type === 'full' && ref.text) {
                text = text.replace(ref.original, `"${ref.text}"`);
            }
        });
        hasReferences = paragraph.references.filter((r: any) => r.type === 'short').length > 0;
    }
    
    return { text, hasReferences };
}

function getShortReferences(paragraph: any): any[] {
    if (!paragraph.references) return [];
    return paragraph.references.filter((r: any) => r.type === 'short');
}

function handleReferenceHover(reference: any) {
    hoveredVerseReferences.value = [reference];
    selectedReferenceVerse.value = reference;
}

</script>

<template>
    <Head :title="lesson.name" />

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
                        <div
                            class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between"
                        >
                            <div class="flex-shrink-0">
                                <CardTitle
                                    class="flex items-center gap-2 text-base sm:text-lg"
                                >
                                    <BookOpen class="h-4 w-4 sm:h-5 sm:w-5" />
                                    {{ lesson.name }}
                                </CardTitle>
                                <CardDescription class="text-xs sm:text-sm"
                                    >{{ lesson.language }}
                                </CardDescription>
                            </div>
                            <div
                                class="flex flex-col gap-2 sm:flex-row sm:gap-3"
                            >
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div
                            class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between"
                        >
                            <div v-if="lesson.series" class="text-sm text-muted-foreground">
                                <span class="font-semibold">{{ t('Series') }}:</span> {{ lesson.series.title }}
                                <span v-if="lesson.episode_number" class="ml-2">
                                    {{ t('Episode') }} {{ lesson.episode_number }}
                                </span>
                            </div>
                            <Button
                                v-if="page.props.auth?.user"
                                :variant="
                                    chapterCompleted ? 'default' : 'outline'
                                "
                                size="sm"
                                @click="toggleChapterCompletion"
                                class="w-full sm:w-auto"
                            >
                                <CheckCircle class="mr-1 h-4 w-4" />
                                {{
                                    chapterCompleted
                                        ? t('Completed')
                                        : t('Mark as Read')
                                }}
                            </Button>
                        </div>
                        <ScrollArea
                            class="mx-0 h-118 max-w-4xl space-y-2 text-justify text-base leading-relaxed sm:mx-30 sm:text-lg"
                        >
                            <h3
                                class="mb-4 text-center text-lg font-semibold sm:text-xl"
                            >
                                {{ lesson.description }}
                            </h3>
                            <p
                                v-for="paragraph in lesson.paragraphs"
                                :key="paragraph.id"
                                class="mb-2 rounded px-2 py-1 transition-colors"
                            >
                                <span class="font-semibold text-primary">{{ paragraph.id }}.</span>
                                {{ formatParagraphText(paragraph).text }}
                                
                                <!-- Display scripture references as tag-like elements -->
                                <template v-if="getShortReferences(paragraph).length > 0">
                                    <span
                                        v-for="(ref, idx) in getShortReferences(paragraph)"
                                        :key="idx"
                                        class="ml-1"
                                    >
                                        <HoverCard @update:open="(open) => open && handleReferenceHover(ref)">
                                            <HoverCardTrigger>
                                                <span
                                                    class="inline-flex cursor-pointer items-center rounded-md bg-primary/10 px-2 py-0.5 text-xs font-medium text-primary ring-1 ring-inset ring-primary/20 transition-colors hover:bg-primary/20 hover:ring-primary/30"
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
                        </ScrollArea>
                    </CardContent>
                </Card>
            </div>

            <!-- References sidebar (1/3) -->
            <div class="flex flex-[1] flex-col gap-3 lg:h-160 lg:gap-4">
                <!-- Scripture reference card showing verse on hover/click -->
                <Card class="flex-1 overflow-hidden">
                    <CardHeader class="pb-3">
                        <CardTitle class="text-sm sm:text-base"
                            >{{ t('Scripture Reference') }}</CardTitle
                        >
                        <CardDescription class="text-xs"
                            >{{ t('Hover or click on reference tags to view verses') }}</CardDescription
                        >
                    </CardHeader>
                    <CardContent
                        class="max-h-[35vh] overflow-y-auto lg:max-h-[45vh]"
                    >
                        <div v-if="selectedReferenceVerse" class="space-y-3">
                            <div class="rounded-lg border border-primary/20 bg-primary/5 p-3">
                                <p class="mb-2 text-sm font-semibold text-primary">
                                    {{ selectedReferenceVerse.reference }}
                                </p>
                                <p class="text-sm leading-relaxed">
                                    {{ selectedReferenceVerse.text }}
                                </p>
                            </div>
                            
                            <!-- Related references separator and list -->
                            <div v-if="hoveredVerseReferences.length > 0" class="space-y-2">
                                <div class="flex items-center gap-2">
                                    <div class="h-px flex-1 bg-border"></div>
                                    <span class="text-xs font-medium text-muted-foreground">{{ t('Related References') }}</span>
                                    <div class="h-px flex-1 bg-border"></div>
                                </div>
                                
                                <div class="space-y-2">
                                    <div
                                        v-for="ref in hoveredVerseReferences"
                                        :key="ref.reference"
                                        class="cursor-pointer rounded-md border p-2 transition-all hover:border-primary/30 hover:bg-accent"
                                        :class="{ 'border-primary/30 bg-accent': ref.reference === selectedReferenceVerse?.reference }"
                                        @click="handleReferenceClick(ref)"
                                    >
                                        <p class="text-xs font-semibold text-primary">
                                            {{ ref.reference }}
                                        </p>
                                        <p class="mt-1 text-xs text-muted-foreground line-clamp-2">
                                            {{ ref.text }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p v-else class="text-sm text-muted-foreground italic">
                            {{ t('Hover or click on a scripture reference tag to view the verse') }}
                        </p>
                    </CardContent>
                </Card>

                <!-- Additional selected reference details -->
                <Card class="flex-1 overflow-hidden">
                    <CardHeader class="pb-3">
                        <CardTitle class="text-sm sm:text-base"
                            >{{ t('Selected Verse Details') }}</CardTitle
                        >
                        <CardDescription class="text-xs"
                            >{{ t('Full verse text and context') }}</CardDescription
                        >
                    </CardHeader>
                    <CardContent
                        class="max-h-[25vh] overflow-y-auto lg:max-h-[35vh]"
                    >
                        <div v-if="selectedReferenceVerse" class="space-y-2">
                            <div class="rounded-lg bg-muted p-3">
                                <p class="mb-1 text-xs font-medium text-muted-foreground">
                                    {{ selectedReferenceVerse.book_title }} {{ selectedReferenceVerse.chapter_number }}:{{ selectedReferenceVerse.verse_number }}
                                </p>
                                <p class="text-sm leading-relaxed">
                                    {{ selectedReferenceVerse.text }}
                                </p>
                            </div>
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
