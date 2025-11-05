<script setup lang="ts">
import AppearanceTabs from '@/components/AppearanceTabs.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import LanguageSelector from '@/components/LanguageSelector.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { edit } from '@/routes/appearance';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: t('Appearance settings'),
        href: edit().url,
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="t('Appearance settings')" />

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall
                    :title="t('Appearance settings')"
                    :description="
                        t('Update your account\'s appearance settings')
                    "
                />

                <div class="space-y-4">
                    <div>
                        <h3 class="mb-2 text-sm font-medium">
                            {{ t('Theme') }}
                        </h3>
                        <AppearanceTabs />
                    </div>

                    <div>
                        <h3 class="mb-2 text-sm font-medium">
                            {{ t('Language') }}
                        </h3>
                        <LanguageSelector />
                    </div>
                </div>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
