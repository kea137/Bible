<script setup lang="ts">
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
import { Head, router } from '@inertiajs/vue3';
import { BookOpen, ExternalLink, Languages } from 'lucide-vue-next';

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

interface Props {
    verse: Verse;
    references: Reference[];
    otherVersions: Verse[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Bibles',
        href: bibles().url,
    },
    {
        title: props.verse.bible.name,
        href: `/bibles/${props.verse.bible.id}`,
    },
    {
        title: 'Verse Study',
        href: `/verses/${props.verse.id}/study`,
    },
];

function navigateToVerse(verse: Verse) {
    router.visit(`/bibles/${verse.bible.id}`);
}
</script>

<template>
    <Head
        :title="`${verse.book.title} ${verse.chapter.chapter_number}:${verse.verse_number}`"
    />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <!-- Main Verse Card -->
            <Card>
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <BookOpen class="h-6 w-6 text-primary" />
                        <div>
                            <CardTitle class="text-2xl">
                                {{ verse.book.title }}
                                {{ verse.chapter.chapter_number }}:{{
                                    verse.verse_number
                                }}
                            </CardTitle>
                            <CardDescription
                                >{{ verse.bible.name }} ({{
                                    verse.bible.version
                                }})</CardDescription
                            >
                        </div>
                    </div>
                </CardHeader>
                <CardContent>
                    <blockquote
                        class="rounded-r-lg border-l-4 border-primary bg-muted/30 py-4 pl-6"
                    >
                        <p class="text-xl leading-relaxed italic">
                            "{{ verse.text }}"
                        </p>
                    </blockquote>
                </CardContent>
            </Card>

            <div class="grid gap-4 md:grid-cols-2">
                <!-- Cross References -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <CardTitle>Cross References</CardTitle>
                            <ExternalLink
                                class="h-5 w-5 text-muted-foreground"
                            />
                        </div>
                        <CardDescription
                            >Related verses from scripture</CardDescription
                        >
                    </CardHeader>
                    <CardContent>
                        <ScrollArea
                            v-if="references.length > 0"
                            class="h-60 space-y-4"
                        >
                            <div
                                v-for="ref in references"
                                :key="ref.id"
                                class="cursor-pointer rounded-lg border p-4 mt-2 transition-colors hover:bg-accent"
                                @click="navigateToVerse(ref.verse)"
                            >
                                <p
                                    class="mb-2 text-sm font-semibold text-primary"
                                >
                                    {{ ref.reference }}
                                </p>
                                <p class="text-sm">{{ ref.verse.text }}</p>
                            </div>
                        </ScrollArea>
                        <p v-else class="text-sm text-muted-foreground italic">
                            No cross-references available for this verse.
                        </p>
                    </CardContent>
                </Card>

                <!-- Other Bible Versions -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <CardTitle>Other Translations</CardTitle>
                            <Languages class="h-5 w-5 text-muted-foreground" />
                        </div>
                        <CardDescription
                            >Same verse in different Bibles</CardDescription
                        >
                    </CardHeader>
                    <CardContent>
                        <div
                            v-if="otherVersions.length > 0"
                            class="max-h-[60vh] space-y-4 overflow-y-auto"
                        >
                            <div
                                v-for="version in otherVersions"
                                :key="version.id"
                                class="rounded-lg border p-4 transition-colors hover:bg-accent"
                            >
                                <div
                                    class="mb-2 flex items-center justify-between"
                                >
                                    <p
                                        class="text-sm font-semibold text-primary"
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
                                <p class="mb-2 text-xs text-muted-foreground">
                                    {{ version.bible.language }} •
                                    {{ version.bible.version }}
                                </p>
                                <p class="text-sm">{{ version.text }}</p>
                            </div>
                        </div>
                        <p v-else class="text-sm text-muted-foreground italic">
                            No other translations available for this verse.
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Back Button -->
            <div class="mt-4 flex justify-center">
                <Button
                    variant="outline"
                    @click="router.visit(`/bibles/${verse.bible.id}`)"
                >
                    <BookOpen class="mr-2 h-4 w-4" />
                    Return to Bible
                </Button>
            </div>
        </div>
    </AppLayout>
</template>
