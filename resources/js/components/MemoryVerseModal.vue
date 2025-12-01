<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { ScrollArea } from '@/components/ui/scroll-area';
import { usePage } from '@inertiajs/vue3';
import { BookMarked, Brain, CheckCircle, LoaderCircle, X } from 'lucide-vue-next';
import { computed, onMounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const props = defineProps<{
    open: boolean;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
}>();

const page = usePage();
const loading = ref(false);
const dueVerses = ref<any[]>([]);
const reviewing = ref(false);
const currentReviewIndex = ref(0);
const showAnswer = ref(false);

const currentVerse = computed(() => {
    if (reviewing.value && dueVerses.value.length > 0) {
        return dueVerses.value[currentReviewIndex.value];
    }
    return null;
});

watch(
    () => props.open,
    async (isOpen) => {
        if (isOpen) {
            await loadDueVerses();
        } else {
            resetReview();
        }
    },
);

async function loadDueVerses() {
    loading.value = true;
    try {
        const response = await fetch('/api/memory-verses/due', {
            headers: {
                Accept: 'application/json',
            },
        });

        if (response.ok) {
            const data = await response.json();
            dueVerses.value = data.due_verses || [];
        }
    } catch (error) {
        console.error('Failed to load due verses:', error);
    } finally {
        loading.value = false;
    }
}

function startReview() {
    if (dueVerses.value.length > 0) {
        reviewing.value = true;
        currentReviewIndex.value = 0;
        showAnswer.value = false;
    }
}

function toggleAnswer() {
    showAnswer.value = !showAnswer.value;
}

async function submitReview(quality: number) {
    if (!currentVerse.value) return;

    try {
        let csrfToken = document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content');
        if (!csrfToken && page.props.csrf_token) {
            csrfToken = String(page.props.csrf_token);
        }

        const response = await fetch(
            `/api/memory-verses/${currentVerse.value.id}/review`,
            {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || '',
                    Accept: 'application/json',
                },
                body: JSON.stringify({ quality }),
            },
        );

        if (response.ok) {
            // Move to next verse or finish
            if (currentReviewIndex.value < dueVerses.value.length - 1) {
                currentReviewIndex.value++;
                showAnswer.value = false;
            } else {
                // Review complete
                reviewing.value = false;
                await loadDueVerses();
            }
        }
    } catch (error) {
        console.error('Failed to submit review:', error);
    }
}

function resetReview() {
    reviewing.value = false;
    currentReviewIndex.value = 0;
    showAnswer.value = false;
}

function closeDialog() {
    emit('update:open', false);
}

onMounted(() => {
    if (props.open) {
        loadDueVerses();
    }
});
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent class="max-w-2xl">
            <DialogHeader>
                <DialogTitle class="flex items-center gap-2">
                    <Brain class="h-5 w-5" />
                    {{ t('Memory Verses Review') }}
                </DialogTitle>
                <DialogDescription>
                    {{
                        reviewing
                            ? t('Review your memory verses')
                            : t('Verses due for review today')
                    }}
                </DialogDescription>
            </DialogHeader>

            <!-- Loading state -->
            <div v-if="loading" class="flex items-center justify-center py-8">
                <LoaderCircle class="h-8 w-8 animate-spin text-primary" />
            </div>

            <!-- No verses due -->
            <div
                v-else-if="!loading && !reviewing && dueVerses.length === 0"
                class="py-8 text-center"
            >
                <CheckCircle class="mx-auto mb-4 h-16 w-16 text-green-500" />
                <h3 class="mb-2 text-lg font-semibold">
                    {{ t('All caught up!') }}
                </h3>
                <p class="text-sm text-muted-foreground">
                    {{ t('No memory verses are due for review today') }}
                </p>
            </div>

            <!-- List of due verses -->
            <div
                v-else-if="!loading && !reviewing && dueVerses.length > 0"
                class="space-y-4"
            >
                <div
                    class="rounded-lg border bg-muted/50 p-4 text-center"
                >
                    <p class="text-2xl font-bold text-primary">
                        {{ dueVerses.length }}
                    </p>
                    <p class="text-sm text-muted-foreground">
                        {{ t('verses due for review') }}
                    </p>
                </div>

                <ScrollArea class="h-64">
                    <div class="space-y-2 pr-4">
                        <div
                            v-for="verse in dueVerses"
                            :key="verse.id"
                            class="rounded-lg border p-3"
                        >
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <p class="font-semibold text-primary">
                                        {{ verse.book_name }}
                                        {{ verse.chapter_number }}:{{
                                            verse.verse_number
                                        }}
                                    </p>
                                    <p class="mt-1 text-sm text-muted-foreground">
                                        {{ verse.verse_text.substring(0, 80)
                                        }}{{
                                            verse.verse_text.length > 80
                                                ? '...'
                                                : ''
                                        }}
                                    </p>
                                </div>
                                <BookMarked
                                    class="ml-2 h-4 w-4 flex-shrink-0 text-primary"
                                />
                            </div>
                        </div>
                    </div>
                </ScrollArea>

                <DialogFooter>
                    <Button variant="outline" @click="closeDialog">
                        {{ t('Close') }}
                    </Button>
                    <Button @click="startReview">
                        {{ t('Start Review') }}
                    </Button>
                </DialogFooter>
            </div>

            <!-- Review mode -->
            <div v-else-if="reviewing && currentVerse" class="space-y-4">
                <div
                    class="flex items-center justify-between rounded-lg border bg-muted/50 p-3"
                >
                    <p class="text-sm font-medium">
                        {{ t('Verse') }} {{ currentReviewIndex + 1 }}
                        {{ t('of') }} {{ dueVerses.length }}
                    </p>
                    <p class="text-sm text-muted-foreground">
                        {{ currentVerse.repetitions }} {{ t('reviews') }}
                    </p>
                </div>

                <div class="rounded-lg border p-6">
                    <p class="mb-4 text-lg font-semibold text-primary">
                        {{ currentVerse.book_name }}
                        {{ currentVerse.chapter_number }}:{{
                            currentVerse.verse_number
                        }}
                    </p>

                    <div v-if="!showAnswer" class="py-8 text-center">
                        <p class="mb-4 text-muted-foreground">
                            {{ t('Try to recall the verse from memory...') }}
                        </p>
                        <Button @click="toggleAnswer">
                            {{ t('Show Answer') }}
                        </Button>
                    </div>

                    <div v-else class="space-y-6">
                        <p class="text-base leading-relaxed">
                            {{ currentVerse.verse_text }}
                        </p>

                        <div class="space-y-3">
                            <p class="text-sm font-medium text-muted-foreground">
                                {{ t('How well did you remember?') }}
                            </p>
                            <div class="grid grid-cols-2 gap-2">
                                <Button
                                    variant="outline"
                                    class="text-red-600 hover:bg-red-50"
                                    @click="submitReview(0)"
                                >
                                    {{ t('Forgot') }}
                                </Button>
                                <Button
                                    variant="outline"
                                    class="text-orange-600 hover:bg-orange-50"
                                    @click="submitReview(2)"
                                >
                                    {{ t('Hard') }}
                                </Button>
                                <Button
                                    variant="outline"
                                    class="text-blue-600 hover:bg-blue-50"
                                    @click="submitReview(4)"
                                >
                                    {{ t('Good') }}
                                </Button>
                                <Button
                                    variant="outline"
                                    class="text-green-600 hover:bg-green-50"
                                    @click="submitReview(5)"
                                >
                                    {{ t('Easy') }}
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>

                <Button
                    variant="ghost"
                    class="w-full"
                    @click="resetReview"
                >
                    <X class="mr-2 h-4 w-4" />
                    {{ t('Exit Review') }}
                </Button>
            </div>
        </DialogContent>
    </Dialog>
</template>
