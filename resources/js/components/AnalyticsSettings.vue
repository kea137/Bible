<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { Switch } from '@/components/ui/switch';
import {
    getAnalyticsConsent,
    setAnalyticsConsent,
} from '@/composables/useAnalytics';
import { BarChart3, Info, ShieldCheck } from 'lucide-vue-next';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const analyticsEnabled = ref(getAnalyticsConsent());

const handleToggle = (enabled: boolean): void => {
    analyticsEnabled.value = enabled;
    setAnalyticsConsent(enabled);
};
</script>

<template>
    <div class="space-y-6">
        <div>
            <div class="flex items-center gap-2">
                <BarChart3 class="h-5 w-5" />
                <h3 class="text-lg font-medium">
                    {{ t('Privacy & Analytics') }}
                </h3>
            </div>
            <p class="text-sm text-muted-foreground">
                {{ t('Manage your privacy and analytics preferences') }}
            </p>
        </div>

        <Separator />

        <div
            class="flex items-start justify-between gap-4 rounded-lg border p-4"
        >
            <div class="flex-1 space-y-1">
                <Label for="analytics-toggle" class="text-sm font-medium">
                    {{ t('Enable Analytics') }}
                </Label>
                <p class="text-sm text-muted-foreground">
                    {{
                        t(
                            'Help us improve the app by sharing anonymous usage data',
                        )
                    }}
                </p>
            </div>
            <Switch
                id="analytics-toggle"
                :checked="analyticsEnabled"
                :aria-label="t('Toggle analytics')"
                @update:checked="handleToggle"
            />
        </div>

        <Separator />

        <div class="space-y-4">
            <div class="flex items-start gap-3 rounded-lg border border-green-200 bg-green-50 p-4 dark:border-green-800 dark:bg-green-950/30">
                <ShieldCheck class="mt-0.5 h-5 w-5 text-green-600 dark:text-green-400" />
                <div class="flex-1 space-y-2">
                    <p class="text-sm font-medium text-green-900 dark:text-green-100">
                        {{ t('Privacy-First Analytics') }}
                    </p>
                    <p class="text-sm text-green-700 dark:text-green-300">
                        {{
                            t(
                                'We take your privacy seriously. Our analytics system does not collect any personally identifiable information (PII).',
                            )
                        }}
                    </p>
                </div>
            </div>

            <div class="flex items-start gap-3 rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-950/30">
                <Info class="mt-0.5 h-5 w-5 text-blue-600 dark:text-blue-400" />
                <div class="flex-1 space-y-2">
                    <p class="text-sm font-medium text-blue-900 dark:text-blue-100">
                        {{ t('What We Track') }}
                    </p>
                    <ul class="list-inside list-disc space-y-1 text-sm text-blue-700 dark:text-blue-300">
                        <li>{{ t('Page views and navigation patterns') }}</li>
                        <li>
                            {{
                                t(
                                    'Bible reading activity (translations and chapters)',
                                )
                            }}
                        </li>
                        <li>{{ t('Feature usage (verse sharing, lessons)') }}</li>
                        <li>{{ t('Search queries (anonymized)') }}</li>
                    </ul>
                    <p class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                        {{
                            t(
                                'We do NOT track: Your name, email, specific verse content, or any personal information.',
                            )
                        }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
