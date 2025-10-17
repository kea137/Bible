<script setup lang="ts">
import { useSidebar } from '@/components/ui/sidebar';
import { useLanguage } from '@/composables/useLanguage';
import { Languages } from 'lucide-vue-next';

const { language, updateLanguage } = useLanguage();
const { state } = useSidebar();

const languages = [
    { value: 'en', label: 'English' },
    { value: 'sw', label: 'Swahili' },
] as const;
</script>

<template>
    <div
        :class="[
            'inline-flex rounded-lg bg-neutral-100 p-1 transition-all duration-300 dark:bg-neutral-800',
            state === 'collapsed' ? 'flex-col gap-1' : 'gap-1',
        ]"
    >
        <button
            v-for="{ value, label } in languages"
            :key="value"
            @click="updateLanguage(value)"
            :class="[
                'flex items-center rounded-md transition-colors',
                state === 'collapsed' ? 'px-2 py-1.5' : 'px-3.5 py-1.5',
                language === value
                    ? 'bg-white shadow-xs dark:bg-neutral-700 dark:text-neutral-100'
                    : 'text-neutral-500 hover:bg-neutral-200/60 hover:text-black dark:text-neutral-400 dark:hover:bg-neutral-700/60',
            ]"
            :title="state === 'collapsed' ? label : undefined"
        >
            <component
                :is="Languages"
                :class="state === 'collapsed' ? 'h-4 w-4' : '-ml-1 h-4 w-4'"
            />
            <span v-if="state !== 'collapsed'" class="ml-1.5 text-sm">{{
                label
            }}</span>
        </button>
    </div>
</template>
