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
        paragraphs: {
            id: number;
            title: string;
            text: number;
        }[];
    };
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
const chapterCompleted = ref(false);
const auth = computed(() => page.props.auth);

async function toggleChapterCompletion() {
    
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

function handleVerseHover(verse: number){

}

function handleVerseClick(verse: number){

}

function handleReferenceClick(verse: number){

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
                                
                            </h3>
                            <p
                                v-for="verse in lesson.paragraphs"
                                :key="verse.id"
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
                                                    handleVerseHover(verse.id)
                                            "
                                        >
                                            <HoverCardTrigger>
                                                <span
                                                    class="cursor-pointer font-semibold text-primary hover:underline"
                                                    @click.stop="
                                                        handleVerseClick(
                                                            verse.id,
                                                        )
                                                    "
                                                    >{{
                                                        verse.id
                                                    }}.</span
                                                >
                                            </HoverCardTrigger>
                                            <HoverCardContent class="w-80">
                                                <div
                                                    v-if="
                                                        hoveredVerseReferences.length >
                                                        0
                                                    "
                                                    class="space-y-2"
                                                >
                                                    <p
                                                        class="text-sm font-semibold"
                                                    >
                                                        {{ t('Cross References') }}:
                                                    </p>
                                                    <div
                                                        class="space-y-1 text-sm"
                                                    >
                                                        <p
                                                            v-for="ref in hoveredVerseReferences.slice(
                                                                0,
                                                                3,
                                                            )"
                                                            :key="ref.id"
                                                            class="text-muted-foreground"
                                                        >
                                                            {{ translateReference(ref.reference) }}:
                                                            {{
                                                                ref.verse?.text?.substring(
                                                                    0,
                                                                    80,
                                                                )
                                                            }}...
                                                        </p>
                                                        <p
                                                            v-if="
                                                                hoveredVerseReferences.length >
                                                                3
                                                            "
                                                            class="text-xs text-muted-foreground italic"
                                                        >
                                                            +{{
                                                                hoveredVerseReferences.length -
                                                                3
                                                            }}
                                                            {{ t('more references') }}
                                                        </p>
                                                    </div>
                                                </div>
                                                <p
                                                    v-else
                                                    class="text-sm text-muted-foreground"
                                                >
                                                    {{ t('No cross-references') }}
                                                    {{ t('available') }}
                                                </p>
                                            </HoverCardContent>
                                        </HoverCard>
                                        {{ verse.text }}
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
                            >{{ t('Cross References') }}</CardTitle
                        >
                        <CardDescription class="text-xs"
                            ><span class="hidden sm:inline"
                                >{{ t('Hover over verse numbers to see') }}
                                {{ t('references') }}</span
                            ><span class="sm:hidden"
                                >{{ t('Tap verse numbers to see references') }}</span
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
                                    :key="ref.id"
                                    class="cursor-pointer rounded border p-2 transition-colors hover:bg-accent"
                                    @click="handleReferenceClick(ref)"
                                >
                                    <p
                                        class="text-sm font-semibold text-primary"
                                    >
                                        {{ translateReference(ref.reference) }}
                                    </p>
                                    <p
                                        class="mt-1 text-xs text-muted-foreground"
                                    >
                                        {{
                                            ref.verse?.text?.substring(0, 100)
                                        }}...
                                    </p>
                                </div>
                            </div>
                        </ScrollArea>
                        <p v-else class="text-sm text-muted-foreground italic">
                            <span class="hidden sm:inline"
                                >{{ t('Hover over a verse number to see its') }}
                                {{ t('cross-references') }}</span
                            >
                            <span class="sm:hidden"
                                >{{ t('Tap a verse number to see its') }}
                                {{ t('cross-references') }}</span
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
                                {{ selectedReferenceVerse.book?.title }}
                                {{
                                    selectedReferenceVerse.chapter
                                        ?.chapter_number
                                }}:{{ selectedReferenceVerse.verse_number }}
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
