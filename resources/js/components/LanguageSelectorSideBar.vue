<script setup lang="ts">
import { useSidebar } from '@/components/ui/sidebar';
import { useLocale } from '@/composables/useLocale';
import { usePage } from '@inertiajs/vue3';
import { Languages } from 'lucide-vue-next';
import { computed } from 'vue';

const { locale, changeLocale, t } = useLocale();
const { state } = useSidebar();
const page = usePage();
const userLanguage = computed(() => page.props.language as string);

const other_languages = [
    { value: 'en', label: 'English' },
    { value: 'sw', label: 'Kiswahili' },
    { value: 'fr', label: 'Français' },
    { value: 'es', label: 'Español' },
    { value: 'de', label: 'Deutsch' },
    { value: 'it', label: 'Italiano' },
    { value: 'ru', label: 'Русский' },
    { value: 'zh', label: '中文' },
    { value: 'ja', label: '日本語' },
    { value: 'ar', label: 'العربية' },
    { value: 'hi', label: 'हिन्दी' },
    { value: 'ko', label: '한국어' },
] as const;

const languages = computed(() => {
    // Always show English and the user's language (even if user's language is English)
    const langs = [{ value: 'en', label: 'English' }];
    if (userLanguage.value && userLanguage.value !== 'en') {
        const found = other_languages.find(
            (lang) => lang.value === userLanguage.value,
        );
        langs.push(
            found || { value: userLanguage.value, label: userLanguage.value },
        );
    }
    // If user's language is English, only show English
    return langs;
});

function handleLanguageChange(value: string) {
    changeLocale(value);
    window.location.reload();
}
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
            @click="handleLanguageChange(value)"
            :class="[
                'flex items-center rounded-md transition-colors',
                state === 'collapsed' ? 'px-2 py-1.5' : 'px-3.5 py-1.5',
                locale === value
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
