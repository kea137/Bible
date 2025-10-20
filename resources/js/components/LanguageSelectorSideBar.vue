<script setup lang="ts">
import { useSidebar } from '@/components/ui/sidebar';
import { useLanguage } from '@/composables/useLanguage';
import { Languages } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const { language, updateLanguage } = useLanguage();
const { state } = useSidebar();
const otherLanguageCode = ref('sw');
const otherLanguageName = ref('Kiswahili');

const other_languages = [
    { value: 'en', label: 'English' },
    { value: 'sw', label: 'Kiswahili' },
    { value: 'fr', label: 'Français' },
    { value: 'es', label: 'Español' },
    { value: 'de', label: 'Deutsch' },
    { value: 'pt', label: 'Português' },
    { value: 'it', label: 'Italiano' },
    { value: 'ru', label: 'Русский' },
    { value: 'zh', label: '中文' },
    { value: 'ja', label: '日本語' },
    { value: 'ar', label: 'العربية' },
    { value: 'hi', label: 'हिन्दी' },
    { value: 'bn', label: 'বাংলা' },
    { value: 'pa', label: 'ਪੰਜਾਬੀ' },
    { value: 'jv', label: 'Basa Jawa' },
    { value: 'ko', label: '한국어' },
    { value: 'vi', label: 'Tiếng Việt' },
    { value: 'te', label: 'తెలుగు' },
    { value: 'mr', label: 'मराठी' },
    { value: 'ta', label: 'தமிழ்' },
] as const;

const languages = computed(() => {
    // Always include English
    const langs = [{ value: 'en', label: 'English' }];
    // Add the current language if it's not English
    if (language.value !== 'en') {
        const found = other_languages.find(lang => lang.value === language.value);
        langs.push(found || { value: language.value, label: language.value });
    }
    return langs;
});
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
