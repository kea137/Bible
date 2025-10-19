<script setup lang="ts">
import { SidebarProvider } from '@/components/ui/sidebar';
import Sonner from '@/components/ui/sonner/Sonner.vue';
import { usePage } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import { watch } from 'vue';

interface Props {
    variant?: 'header' | 'sidebar';
}

defineProps<Props>();

const isOpen = usePage().props.sidebarOpen;
const page = usePage();

// Watch for flash messages and display them using Sonner
watch(
    () => page.props,
    (props: any) => {
        if (props.success) {
            toast.success(props.success);
        }
        if (props.error) {
            toast.error(props.error);
        }
        if (props.info) {
            toast.info(props.info);
        }
    },
    { immediate: true, deep: true },
);
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
