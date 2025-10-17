<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

import AppearanceTabs from '@/components/AppearanceTabs.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import LanguageSelector from '@/components/LanguageSelector.vue';
import { type BreadcrumbItem } from '@/types';

import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { edit } from '@/routes/appearance';

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Appearance settings',
        href: edit().url,
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Appearance settings" />

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall
                    title="Appearance settings"
                    description="Update your account's appearance settings"
                />

                <div class="space-y-4">
                    <div>
                        <h3 class="mb-2 text-sm font-medium">Theme</h3>
                        <AppearanceTabs />
                    </div>

                    <div>
                        <h3 class="mb-2 text-sm font-medium">Language</h3>
                        <LanguageSelector />
                    </div>
                </div>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
