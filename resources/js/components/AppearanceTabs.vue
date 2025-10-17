<script setup lang="ts">
import { useAppearance } from '@/composables/useAppearance';
import { useSidebar } from '@/components/ui/sidebar';
import { Monitor, Moon, Sun } from 'lucide-vue-next';

const { appearance, updateAppearance } = useAppearance();
const { state } = useSidebar();

const tabs = [
    { value: 'light', Icon: Sun, label: 'Light' },
    { value: 'dark', Icon: Moon, label: 'Dark' },
    { value: 'system', Icon: Monitor, label: 'System' },
] as const;
</script>

<template>
    <div
        class="inline-flex gap-1 rounded-lg bg-neutral-100 p-1 dark:bg-neutral-800 transition-all duration-200"
    >
        <button
            v-for="{ value, Icon, label } in tabs"
            :key="value"
            @click="updateAppearance(value)"
            :class="[
                'flex items-center rounded-md transition-colors',
                state === 'collapsed' ? 'px-2 py-1.5' : 'px-3.5 py-1.5',
                appearance === value
                    ? 'bg-white shadow-xs dark:bg-neutral-700 dark:text-neutral-100'
                    : 'text-neutral-500 hover:bg-neutral-200/60 hover:text-black dark:text-neutral-400 dark:hover:bg-neutral-700/60',
            ]"
            :title="state === 'collapsed' ? label : undefined"
        >
            <component :is="Icon" :class="state === 'collapsed' ? 'h-4 w-4' : '-ml-1 h-4 w-4'" />
            <span v-if="state !== 'collapsed'" class="ml-1.5 text-sm">{{ label }}</span>
        </button>
    </div>
</template>
