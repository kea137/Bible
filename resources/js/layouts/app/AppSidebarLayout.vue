<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppFooter from '@/components/AppFooter.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import MobileFooter from '@/components/MobileFooter.vue';
import OfflineStatus from '@/components/OfflineStatus.vue';
import SkipNavigation from '@/components/SkipNavigation.vue';
import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppShell variant="sidebar">
        <SkipNavigation />
        <AppSidebar />
        <AppContent
            variant="sidebar"
            class="flex min-h-screen flex-col overflow-x-hidden"
        >
            <AppSidebarHeader :breadcrumbs="breadcrumbs" />
            <div id="main-content" class="flex-1" tabindex="-1">
                <slot />
            </div>
            <AppFooter />
            <MobileFooter />
        </AppContent>
        <OfflineStatus />
    </AppShell>
</template>
