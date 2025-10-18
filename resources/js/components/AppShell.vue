<script setup lang="ts">
import { SidebarProvider } from '@/components/ui/sidebar';
import Sonner from '@/components/ui/sonner/Sonner.vue';
import { usePage } from '@inertiajs/vue3';

interface Props {
    variant?: 'header' | 'sidebar';
}

defineProps<Props>();

const isOpen = usePage().props.sidebarOpen;
</script>

<template>
    <div v-if="variant === 'header'" class="flex min-h-screen w-full flex-col">
        <slot />
        <Sonner position="top-right" />
    </div>
    <SidebarProvider v-else :default-open="isOpen">
        <slot />
        <Sonner position="top-right" />
    </SidebarProvider>
</template>
