<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { Switch } from '@/components/ui/switch';
import {
    getFeatureFlagMetadata,
    toggleFeature,
    useFeatureFlags,
    type FeatureFlags,
} from '@/composables/useFeatureFlags';
import { Flag, Info, RotateCcw } from 'lucide-vue-next';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const { resetFeatureFlags } = useFeatureFlags();

const featureFlags = ref(getFeatureFlagMetadata());

const handleToggle = (key: keyof FeatureFlags): void => {
    toggleFeature(key);
    // Update the local state
    featureFlags.value = getFeatureFlagMetadata();
};

const handleReset = (): void => {
    resetFeatureFlags();
    featureFlags.value = getFeatureFlagMetadata();
};
</script>

<template>
    <div class="space-y-6">
        <div>
            <div class="flex items-center gap-2">
                <Flag class="h-5 w-5" />
                <h3 class="text-lg font-medium">{{ t('Feature Flags') }}</h3>
            </div>
            <p class="text-sm text-muted-foreground">
                {{
                    t(
                        'Enable or disable features for gradual rollout and testing',
                    )
                }}
            </p>
        </div>

        <Separator />

        <div class="space-y-4">
            <div
                v-for="flag in featureFlags"
                :key="flag.key"
                class="flex items-start justify-between gap-4 rounded-lg border p-4"
            >
                <div class="flex-1 space-y-1">
                    <div class="flex items-center gap-2">
                        <Label
                            :for="`flag-${flag.key}`"
                            class="text-sm font-medium"
                        >
                            {{ flag.label }}
                        </Label>
                    </div>
                    <p class="text-sm text-muted-foreground">
                        {{ flag.description }}
                    </p>
                </div>
                <Switch
                    :id="`flag-${flag.key}`"
                    :checked="flag.enabled"
                    :aria-label="`Toggle ${flag.label}`"
                    @update:checked="() => handleToggle(flag.key)"
                />
            </div>
        </div>

        <Separator />

        <div
            class="flex items-start gap-3 rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-950/30"
        >
            <Info class="mt-0.5 h-5 w-5 text-blue-600 dark:text-blue-400" />
            <div class="flex-1 space-y-2">
                <p class="text-sm font-medium text-blue-900 dark:text-blue-100">
                    {{ t('About Feature Flags') }}
                </p>
                <p class="text-sm text-blue-700 dark:text-blue-300">
                    {{
                        t(
                            'Feature flags allow you to enable or disable features without code changes. This is useful for gradual rollouts, A/B testing, or quickly disabling problematic features.',
                        )
                    }}
                </p>
            </div>
        </div>

        <div class="flex justify-end">
            <Button
                variant="outline"
                @click="handleReset"
                class="gap-2"
                aria-label="Reset all feature flags to defaults"
            >
                <RotateCcw class="h-4 w-4" />
                {{ t('Reset to Defaults') }}
            </Button>
        </div>
    </div>
</template>
