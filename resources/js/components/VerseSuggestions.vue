<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import CardDescription from '@/components/ui/card/CardDescription.vue';
import CardHeader from '@/components/ui/card/CardHeader.vue';
import CardTitle from '@/components/ui/card/CardTitle.vue';
import { router } from '@inertiajs/vue3';
import { Lightbulb, RefreshCw, X, ExternalLink, Info } from 'lucide-vue-next';
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

interface Reason {
    type: string;
    description: string;
    source_verse?: string;
    keyword?: string;
}

interface Verse {
    id: number;
    text: string;
    verse_number: number;
    book: {
        title: string;
    };
    chapter: {
        chapter_number: number;
    };
    bible: {
        abbreviation: string;
    };
}

interface Suggestion {
    id: number;
    verse_id: number;
    score: number;
    reasons: Reason[];
    verse: Verse;
}

const suggestions = ref<Suggestion[]>([]);
const loading = ref(false);
const showExplanations = ref<Record<number, boolean>>({});

onMounted(() => {
    loadSuggestions();
});

async function loadSuggestions() {
    loading.value = true;
    try {
        const response = await fetch('/api/verse-suggestions?limit=5');
        const data = await response.json();
        if (data.success) {
            suggestions.value = data.suggestions;
        }
    } catch (error) {
        console.error('Failed to load suggestions:', error);
    } finally {
        loading.value = false;
    }
}

async function generateNewSuggestions() {
    loading.value = true;
    try {
        const response = await fetch('/api/verse-suggestions/generate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document
                    .querySelector('meta[name="csrf-token"]')
                    ?.getAttribute('content') || '',
            },
        });
        const data = await response.json();
        if (data.success) {
            suggestions.value = data.suggestions;
        }
    } catch (error) {
        console.error('Failed to generate suggestions:', error);
    } finally {
        loading.value = false;
    }
}

async function dismissSuggestion(suggestionId: number) {
    try {
        await fetch(`/api/verse-suggestions/${suggestionId}/dismiss`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document
                    .querySelector('meta[name="csrf-token"]')
                    ?.getAttribute('content') || '',
            },
        });
        suggestions.value = suggestions.value.filter((s) => s.id !== suggestionId);
    } catch (error) {
        console.error('Failed to dismiss suggestion:', error);
    }
}

async function viewVerse(suggestion: Suggestion) {
    // Mark as clicked
    try {
        await fetch(`/api/verse-suggestions/${suggestion.id}/click`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document
                    .querySelector('meta[name="csrf-token"]')
                    ?.getAttribute('content') || '',
            },
        });
    } catch (error) {
        console.error('Failed to mark as clicked:', error);
    }

    // Navigate to verse study
    router.visit(`/verses/${suggestion.verse_id}/study`);
}

function toggleExplanation(suggestionId: number) {
    showExplanations.value[suggestionId] = !showExplanations.value[suggestionId];
}

function formatVerseReference(verse: Verse): string {
    return `${verse.book.title} ${verse.chapter.chapter_number}:${verse.verse_number}`;
}

function getReasonIcon(type: string): string {
    switch (type) {
        case 'cross_reference':
            return '🔗';
        case 'same_book':
            return '📖';
        case 'keyword_match':
            return '🔍';
        default:
            return '💡';
    }
}
</script>

<template>
    <Card class="w-full">
        <CardHeader>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <Lightbulb class="h-5 w-5 text-amber-500" />
                    <CardTitle>{{ t('Suggested Verses') }}</CardTitle>
                </div>
                <Button
                    variant="ghost"
                    size="sm"
                    :disabled="loading"
                    @click="generateNewSuggestions"
                >
                    <RefreshCw :class="{ 'animate-spin': loading }" class="h-4 w-4" />
                </Button>
            </div>
            <CardDescription>
                {{ t('Discover verses related to your highlights and reading history') }}
            </CardDescription>
        </CardHeader>
        <CardContent>
            <div v-if="loading && suggestions.length === 0" class="text-center py-8">
                <RefreshCw class="h-8 w-8 animate-spin mx-auto text-gray-400" />
                <p class="mt-2 text-sm text-gray-500">{{ t('Loading suggestions...') }}</p>
            </div>

            <div v-else-if="suggestions.length === 0" class="text-center py-8">
                <Lightbulb class="h-12 w-12 mx-auto text-gray-400" />
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    {{ t('No suggestions yet. Start highlighting verses to get personalized recommendations!') }}
                </p>
                <Button class="mt-4" size="sm" @click="generateNewSuggestions">
                    {{ t('Generate Suggestions') }}
                </Button>
            </div>

            <div v-else class="space-y-3">
                <div
                    v-for="suggestion in suggestions"
                    :key="suggestion.id"
                    class="group relative rounded-lg border border-gray-200 p-4 transition-all hover:border-blue-300 hover:shadow-md dark:border-gray-700 dark:hover:border-blue-600"
                >
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex-1 cursor-pointer" @click="viewVerse(suggestion)">
                            <div class="mb-2 flex items-center gap-2">
                                <span
                                    class="text-sm font-semibold text-blue-600 dark:text-blue-400"
                                >
                                    {{ formatVerseReference(suggestion.verse) }}
                                </span>
                                <ExternalLink class="h-3 w-3 text-gray-400 opacity-0 transition-opacity group-hover:opacity-100" />
                            </div>
                            <p class="mb-2 text-sm text-gray-700 dark:text-gray-300 line-clamp-2">
                                {{ suggestion.verse.text }}
                            </p>
                        </div>
                        <Button
                            variant="ghost"
                            size="sm"
                            class="h-6 w-6 p-0"
                            @click="dismissSuggestion(suggestion.id)"
                        >
                            <X class="h-4 w-4" />
                        </Button>
                    </div>

                    <!-- Reasons -->
                    <div class="mt-2">
                        <Button
                            variant="ghost"
                            size="sm"
                            class="h-auto p-0 text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                            @click="toggleExplanation(suggestion.id)"
                        >
                            <Info class="mr-1 h-3 w-3" />
                            {{ showExplanations[suggestion.id] ? t('Hide explanation') : t('Why this verse?') }}
                        </Button>

                        <div
                            v-if="showExplanations[suggestion.id]"
                            class="mt-2 space-y-1 rounded-lg bg-blue-50 p-2 dark:bg-blue-900/20"
                        >
                            <div
                                v-for="(reason, idx) in suggestion.reasons"
                                :key="idx"
                                class="text-xs text-blue-900 dark:text-blue-100"
                            >
                                <span class="mr-1">{{ getReasonIcon(reason.type) }}</span>
                                {{ reason.description }}
                                <span v-if="reason.source_verse" class="font-semibold">
                                    ({{ reason.source_verse }})
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </CardContent>
    </Card>
</template>
