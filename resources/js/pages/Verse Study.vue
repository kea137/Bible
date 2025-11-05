<script setup lang="ts">
import AlertUser from '@/components/AlertUser.vue';
import NotesDialog from '@/components/NotesDialog.vue';
import Button from '@/components/ui/button/Button.vue';
import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import CardDescription from '@/components/ui/card/CardDescription.vue';
import CardHeader from '@/components/ui/card/CardHeader.vue';
import CardTitle from '@/components/ui/card/CardTitle.vue';
import ScrollArea from '@/components/ui/scroll-area/ScrollArea.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { bibles } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import {
    BookOpen,
    ExternalLink,
    Languages,
    Share2,
    StickyNote,
} from 'lucide-vue-next';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
interface Verse {
    id: number;
    verse_number: number;
    text: string;
    book: {
        id: number;
        title: string;
        book_number: number;
    };
    chapter: {
        id: number;
        chapter_number: number;
    };
    bible: {
        id: number;
        name: string;
        language: string;
        version: string;
    };
}

interface Reference {
    id: string;
    reference: string;
    verse: Verse;
    parsed: {
        book: string;
        chapter: number;
        verse: number;
    };
}

function shareVerse(verse: any) {
    const verseReference = `${verse.book.title} ${verse.chapter.chapter_number}:${verse.verse_number}`;
    const verseText = verse.text;
    router.visit(
        `/share?reference=${encodeURIComponent(verseReference)}&text=${encodeURIComponent(verseText)}&verseId=${verse.id}`,
    );
}

interface Props {
    verse: Verse;
    references: Reference[];
    otherVersions: Verse[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: t('Bibles'),
        href: bibles().url,
    },
    {
        title: props.verse.bible.name,
        href: `/bibles/${props.verse.bible.id}`,
    },
    {
        title: t('Verse Study'),
        href: `/verses/${props.verse.id}/study`,
    },
];

const page = usePage();
const notesDialogOpen = ref(false);
const alertSuccess = ref(false);
const alertError = ref(false);

function navigateToVerse(verse: Verse) {
    router.visit(`/verses/${verse.id}/study`);
}

function openNotesDialog() {
    notesDialogOpen.value = true;
}

function handleNoteSaved() {
    alertSuccess.value = true;
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
</script>

<template>
    <Head
        :title="`${verse.book.title} ${verse.chapter.chapter_number}:${verse.verse_number}`"
    />

    <AlertUser
        v-if="alertSuccess"
        :open="true"
        :title="t('Success')"
        :confirmButtonText="'OK'"
        :message="t('Operation was successful')"
        variant="success"
        @update:open="alertSuccess = false"
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
            class="flex h-full flex-1 flex-col gap-3 overflow-x-auto rounded-xl p-2 sm:gap-4 sm:p-4"
        >
            <!-- Main Verse Card -->
            <Card>
                <CardHeader class="pb-3">
                    <div
                        class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between"
                    >
                        <div class="flex items-start gap-2">
                            <BookOpen
                                class="mt-1 h-5 w-5 flex-shrink-0 text-primary sm:h-6 sm:w-6"
                            />
                            <div>
                                <CardTitle class="text-lg sm:text-2xl">
                                    {{ verse.book.title }}
                                    {{ verse.chapter.chapter_number }}:{{
                                        verse.verse_number
                                    }}
                                </CardTitle>
                                <CardDescription class="text-xs sm:text-sm"
                                    >{{ verse.bible.name }} ({{
                                        verse.bible.version
                                    }})</CardDescription
                                >
                            </div>
                        </div>
                        <Button
                            @click="openNotesDialog"
                            variant="outline"
                            size="sm"
                            class="w-full flex-shrink-0 sm:w-auto"
                        >
                            <StickyNote class="mr-2 h-4 w-4" />
                            {{ t('Add Note') }}
                        </Button>
                        <Button
                            variant="secondary"
                            size="sm"
                            class="w-full flex-shrink-0 sm:w-auto"
                            @click="shareVerse(verse)"
                        >
                            <Share2 class="mr-2 h-4 w-4" />
                            {{ t('Share this Verse') }}
                        </Button>
                    </div>
                </CardHeader>
                <CardContent>
                    <blockquote
                        class="rounded-r-lg border-l-4 border-primary bg-muted/30 py-3 pl-4 sm:py-4 sm:pl-6"
                    >
                        <p class="text-base leading-relaxed italic sm:text-xl">
                            "{{ verse.text }}"
                        </p>
                    </blockquote>
                </CardContent>
            </Card>

            <div class="grid gap-3 sm:gap-4 lg:grid-cols-2">
                <!-- Cross References -->
                <Card>
                    <CardHeader class="pb-3">
                        <div class="flex items-center justify-between">
                            <CardTitle class="text-base sm:text-lg">{{
                                t('Cross References')
                            }}</CardTitle>
                            <ExternalLink
                                class="h-4 w-4 text-muted-foreground sm:h-5 sm:w-5"
                            />
                        </div>
                        <CardDescription class="text-xs sm:text-sm">{{
                            t('Related verses from scripture')
                        }}</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <ScrollArea
                            v-if="references.length > 0"
                            class="h-100 space-y-3 sm:space-y-4"
                        >
                            <div
                                v-for="ref in references"
                                :key="ref.id"
                                class="mt-2 cursor-pointer rounded-lg border p-3 transition-colors hover:bg-accent sm:p-4"
                                @click="navigateToVerse(ref.verse)"
                            >
                                <p
                                    class="mb-2 text-xs font-semibold text-primary sm:text-sm"
                                >
                                    {{ translateReference(ref.reference) }}
                                </p>
                                <p class="text-xs sm:text-sm">
                                    {{ ref.verse.text }}
                                </p>
                            </div>
                        </ScrollArea>
                        <p
                            v-else
                            class="text-xs text-muted-foreground italic sm:text-sm"
                        >
                            {{
                                t(
                                    'No cross-references available for this verse.',
                                )
                            }}
                        </p>
                    </CardContent>
                </Card>

                <!-- Other Bible Versions -->
                <Card>
                    <CardHeader class="pb-3">
                        <div class="flex items-center justify-between">
                            <CardTitle class="text-base sm:text-lg">{{
                                t('Other Translations')
                            }}</CardTitle>
                            <Languages
                                class="h-4 w-4 text-muted-foreground sm:h-5 sm:w-5"
                            />
                        </div>
                        <CardDescription class="text-xs sm:text-sm">{{
                            t('Same verse in different Bibles')
                        }}</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div
                            v-if="otherVersions.length > 0"
                            class="max-h-[60vh] space-y-3 overflow-y-auto sm:space-y-4"
                        >
                            <ScrollArea class="h-100">
                                <div
                                    v-for="version in otherVersions"
                                    :key="version.id"
                                    class="rounded-lg border p-3 transition-colors hover:bg-accent sm:p-4"
                                >
                                    <div
                                        class="mb-2 flex items-center justify-between gap-2"
                                    >
                                        <p
                                            class="text-xs font-semibold text-primary sm:text-sm"
                                        >
                                            {{ version.bible.name }}
                                        </p>
                                        <Button
                                            size="sm"
                                            variant="ghost"
                                            @click="navigateToVerse(version)"
                                        >
                                            <ExternalLink class="h-3 w-3" />
                                        </Button>
                                    </div>
                                    <p
                                        class="mb-2 text-xs text-muted-foreground"
                                    >
                                        {{ version.bible.language }} •
                                        {{ version.bible.version }}
                                    </p>
                                    <p class="text-xs sm:text-sm">
                                        {{ version.text }}
                                    </p>
                                </div>
                            </ScrollArea>
                        </div>
                        <p
                            v-else
                            class="text-xs text-muted-foreground italic sm:text-sm"
                        >
                            {{
                                t(
                                    'No other translations available for this verse.',
                                )
                            }}
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Back Button -->
            <div class="flex justify-center">
                <Button
                    variant="outline"
                    @click="router.visit(`/bibles/${verse.bible.id}`)"
                    class="w-full sm:w-auto"
                >
                    <BookOpen class="mr-2 h-4 w-4" />
                    {{ t('Return to Bible') }}
                </Button>
            </div>
        </div>

        <!-- Notes Dialog -->
        <NotesDialog
            :open="notesDialogOpen"
            @update:open="notesDialogOpen = $event"
            :verse-id="verse.id"
            :verse-text="verse.text"
            :verse-reference="`${verse.book.title} ${verse.chapter.chapter_number}:${verse.verse_number}`"
            @saved="handleNoteSaved"
        />
    </AppLayout>
</template>
