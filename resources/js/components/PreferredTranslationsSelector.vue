<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import ScrollArea from '@/components/ui/scroll-area/ScrollArea.vue';
import { usePage } from '@inertiajs/vue3';
import { Loader2 } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const page = usePage();

interface Bible {
    id: number;
    name: string;
    abbreviation: string;
    language: string;
    version: string;
}

const bibles = ref<Bible[]>([]);
const selectedTranslations = ref<number[]>([]);
const loading = ref(false);
const saving = ref(false);

const fetchBibles = async () => {
    loading.value = true;
    try {
        const response = await fetch('/api/bibles');
        if (response.ok) {
            const data = await response.json();
            bibles.value = data.data || [];
        }
    } catch (error) {
        console.error('Failed to fetch bibles:', error);
    } finally {
        loading.value = false;
    }
};

const fetchUserPreferences = async () => {
    if (page.props.auth?.user) {
        const user = page.props.auth.user as any;
        if (user.preferred_translations && Array.isArray(user.preferred_translations)) {
            selectedTranslations.value = user.preferred_translations;
        }
    }
};

const toggleTranslation = (id: number) => {
    const index = selectedTranslations.value.indexOf(id);
    if (index > -1) {
        selectedTranslations.value.splice(index, 1);
    } else {
        selectedTranslations.value.push(id);
    }
};

const savePreferences = async () => {
    if (!page.props.auth?.user) {
        return;
    }

    saving.value = true;
    try {
        // Get CSRF token
        let csrfToken = document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content');
        if (!csrfToken && page.props.csrf_token) {
            csrfToken = String(page.props.csrf_token);
        }
        if (!csrfToken) {
            console.error('CSRF token not found');
            return;
        }

        // Update preferred translations
        const response = await fetch('/api/user/translations', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                Accept: 'application/json',
            },
            body: JSON.stringify({
                preferred_translations: selectedTranslations.value,
            }),
        });

        const result = await response.json();
        if (response.ok && result?.success) {
            // Reload page to update user data
            window.location.reload();
        } else {
            console.error(result?.message || 'Failed to update translations.');
        }
    } catch (error) {
        console.error('Failed to update translations.', error);
    } finally {
        saving.value = false;
    }
};

onMounted(() => {
    fetchBibles();
    fetchUserPreferences();
});
</script>

<template>
    <div class="space-y-4">
        <p class="text-sm text-muted-foreground">
            {{ t('Select Bible translations for parallel reading and study') }}
        </p>

        <div v-if="loading" class="flex items-center justify-center p-8">
            <Loader2 class="h-6 w-6 animate-spin text-muted-foreground" />
        </div>

        <div v-else-if="bibles.length > 0" class="space-y-3">
            <ScrollArea class="h-[300px] rounded-md border p-4">
                <div class="space-y-3">
                    <div
                        v-for="bible in bibles"
                        :key="bible.id"
                        class="flex items-start space-x-3 rounded-lg border p-3 transition-colors hover:bg-accent"
                    >
                        <Checkbox
                            :id="`translation-${bible.id}`"
                            :checked="selectedTranslations.includes(bible.id)"
                            @update:checked="() => toggleTranslation(bible.id)"
                        />
                        <div class="flex-1">
                            <Label
                                :for="`translation-${bible.id}`"
                                class="cursor-pointer font-medium"
                            >
                                {{ bible.name }}
                            </Label>
                            <p class="text-xs text-muted-foreground">
                                {{ bible.abbreviation }} - {{ bible.version }} ({{ bible.language }})
                            </p>
                        </div>
                    </div>
                </div>
            </ScrollArea>

            <Button
                @click="savePreferences"
                :disabled="saving || selectedTranslations.length === 0"
                class="w-full"
            >
                <Loader2
                    v-if="saving"
                    class="mr-2 h-4 w-4 animate-spin"
                />
                {{ saving ? t('Saving...') : t('Save Preferences') }}
            </Button>
        </div>

        <div
            v-else
            class="flex items-center justify-center rounded-md border p-8 text-muted-foreground"
        >
            {{ t('No translations available') }}
        </div>
    </div>
</template>
