<script setup lang="ts">
import AlertUser from '@/components/AlertUser.vue';
import Button from '@/components/ui/button/Button.vue';
import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import CardDescription from '@/components/ui/card/CardDescription.vue';
import CardHeader from '@/components/ui/card/CardHeader.vue';
import CardTitle from '@/components/ui/card/CardTitle.vue';
import {
    DropdownMenu,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    HoverCard,
    HoverCardContent,
    HoverCardTrigger,
} from '@/components/ui/hover-card';
import { ScrollArea } from '@/components/ui/scroll-area';
import AppLayout from '@/layouts/AppLayout.vue';
import { lessons } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import {
    BookOpen,
    CheckCircle,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
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
const auth = computed(() => page.props.auth);

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

function translateReference(ref: string): string {
    // translate EXO 12 12 to other lannguage e.g KUT 12:12
    const parts = ref.split(' ');
    if (parts.length !== 3) {
        return ref; // return original if format is unexpected
    }
    const bookCode = parts[0];
    const chapter = parts[1];
    const verse = parts[2];
    return `${t(`${bookCode}`)} ${chapter}:${verse}`;
}

function handleVerseHover(paragraphId: number){
    const paragraph = props.lesson.paragraphs.find(p => p.id === paragraphId);
    if (paragraph && paragraph.references) {
        hoveredVerseReferences.value = paragraph.references.filter(ref => ref.type === 'short');
    }
}

function handleVerseClick(paragraphId: number){
    const paragraph = props.lesson.paragraphs.find(p => p.id === paragraphId);
    if (paragraph && paragraph.references) {
        hoveredVerseReferences.value = paragraph.references.filter(ref => ref.type === 'short');
    }
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
    
    // Remove short reference markers for display
    text = text.replace(/'([A-Z0-9]{3})\s+(\d+):(\d+)'/g, '$1 $2:$3');
    
    return text;
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
                                <DropdownMenu>
                                    <DropdownMenuTrigger
                                        class="w-full cursor-default"
                                    >
                                        <!-- Mobile: Click for references, Desktop: Hover for references -->
                                        <HoverCard
                                            @update:open="
                                                (open) =>
                                                    open &&
                                                    handleVerseHover(paragraph.id)
                                            "
                                        >
                                            <HoverCardTrigger>
                                                <span
                                                    class="cursor-pointer font-semibold text-primary hover:underline"
                                                    @click.stop="
                                                        handleVerseClick(
                                                            paragraph.id,
                                                        )
                                                    "
                                                    >{{
                                                        paragraph.id
                                                    }}.</span
                                                >
                                            </HoverCardTrigger>
                                            <HoverCardContent class="w-80">
                                                <div
                                                    v-if="
                                                        paragraph.references && paragraph.references.filter((r: any) => r.type === 'short').length > 0
                                                    "
                                                    class="space-y-2"
                                                >
                                                    <p
                                                        class="text-sm font-semibold"
                                                    >
                                                        {{ t('Scripture References') }}:
                                                    </p>
                                                    <div
                                                        class="space-y-1 text-sm"
                                                    >
                                                        <p
                                                            v-for="ref in paragraph.references.filter((r: any) => r.type === 'short').slice(0, 3)"
                                                            :key="ref.reference"
                                                            class="text-muted-foreground"
                                                        >
                                                            {{ ref.reference }}:
                                                            {{
                                                                ref.text?.substring(
                                                                    0,
                                                                    80,
                                                                )
                                                            }}...
                                                        </p>
                                                        <p
                                                            v-if="
                                                                paragraph.references.filter((r: any) => r.type === 'short').length > 3
                                                            "
                                                            class="text-xs text-muted-foreground italic"
                                                        >
                                                            +{{
                                                                paragraph.references.filter((r: any) => r.type === 'short').length - 3
                                                            }}
                                                            {{ t('more references') }}
                                                        </p>
                                                    </div>
                                                </div>
                                                <p
                                                    v-else
                                                    class="text-sm text-muted-foreground"
                                                >
                                                    {{ t('No scripture references') }}
                                                    {{ t('available') }}
                                                </p>
                                            </HoverCardContent>
                                        </HoverCard>
                                        {{ formatParagraphText(paragraph) }}
                                    </DropdownMenuTrigger>
                                </DropdownMenu>
                            </p>
                        </ScrollArea>
                    </CardContent>
                </Card>
            </div>

            <!-- References sidebar (1/3) -->
            <div class="flex flex-[1] flex-col gap-3 lg:h-160 lg:gap-4">
                <!-- Top half - Hovered verse references -->
                <Card class="flex-1 overflow-hidden">
                    <CardHeader class="pb-3">
                        <CardTitle class="text-sm sm:text-base"
                            >{{ t('Scripture References') }}</CardTitle
                        >
                        <CardDescription class="text-xs"
                            ><span class="hidden sm:inline"
                                >{{ t('Hover over paragraph numbers to see') }}
                                {{ t('references') }}</span
                            ><span class="sm:hidden"
                                >{{ t('Tap paragraph numbers to see references') }}</span
                            ></CardDescription
                        >
                    </CardHeader>
                    <CardContent
                        class="max-h-[30vh] overflow-y-auto lg:max-h-[40vh]"
                    >
                        <ScrollArea
                            v-if="hoveredVerseReferences.length > 0"
                            class="h-full"
                        >
                            <div class="space-y-3">
                                <div
                                    v-for="ref in hoveredVerseReferences"
                                    :key="ref.reference"
                                    class="cursor-pointer rounded border p-2 transition-colors hover:bg-accent"
                                    @click="handleReferenceClick(ref)"
                                >
                                    <p
                                        class="text-sm font-semibold text-primary"
                                    >
                                        {{ ref.reference }}
                                    </p>
                                    <p
                                        class="mt-1 text-xs text-muted-foreground"
                                    >
                                        {{
                                            ref.text?.substring(0, 100)
                                        }}...
                                    </p>
                                </div>
                            </div>
                        </ScrollArea>
                        <p v-else class="text-sm text-muted-foreground italic">
                            <span class="hidden sm:inline"
                                >{{ t('Hover over a paragraph number to see its') }}
                                {{ t('scripture references') }}</span
                            >
                            <span class="sm:hidden"
                                >{{ t('Tap a paragraph number to see its') }}
                                {{ t('scripture references') }}</span
                            >
                        </p>
                    </CardContent>
                </Card>

                <!-- Bottom half - Selected reference verse -->
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
                    <CardContent
                        class="max-h-[30vh] overflow-y-auto lg:max-h-[40vh]"
                    >
                        <div v-if="selectedReferenceVerse" class="space-y-2">
                            <p class="text-sm font-semibold">
                                {{ selectedReferenceVerse.reference }}
                            </p>
                            <p class="text-sm">
                                {{ selectedReferenceVerse.text }}
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
