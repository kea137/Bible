<script setup lang="ts">
import { SidebarProvider } from '@/components/ui/sidebar';
import Sonner from '@/components/ui/sonner/Sonner.vue';
import { router, usePage } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import { onMounted, onUnmounted } from 'vue';

interface Props {
    variant?: 'header' | 'sidebar';
}

defineProps<Props>();

const isOpen = usePage().props.sidebarOpen;
const page = usePage();

// Handle flash messages on page navigation
const handleSuccess = () => {
    const props = page.props as any;
    
    if (props.success) {
        toast.success(props.success);
    }
    if (props.error) {
        toast.error(props.error);
    }
    if (props.info) {
        toast.info(props.info);
    }
};

onMounted(() => {
    // Listen for Inertia navigation events
    router.on('success', handleSuccess);
    
    // Handle initial page load
    handleSuccess();
});

onUnmounted(() => {
    router.off('success', handleSuccess);
});
</script>

<template>
    <div v-if="variant === 'header'" class="flex min-h-screen w-full flex-col">
        <Sonner position="top-right" />
        <slot />
    </div>
    <SidebarProvider v-else :default-open="isOpen">
        <Sonner position="top-right" />
        <slot />
    </SidebarProvider>
</template>
