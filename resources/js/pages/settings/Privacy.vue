<script setup lang="ts">
import AnalyticsSettings from '@/components/AnalyticsSettings.vue';
import FeatureFlagManager from '@/components/FeatureFlagManager.vue';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { edit } from '@/routes/privacy';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: t('Privacy & Features'),
        href: edit().url,
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="t('Privacy & Features')" />

        <SettingsLayout>
            <div class="flex flex-col space-y-12">
                <!-- Analytics Settings Section -->
                <div>
                    <AnalyticsSettings />
                </div>

                <Separator />

                <!-- Feature Flags Section -->
                <div>
                    <FeatureFlagManager />
                </div>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
