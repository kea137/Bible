<script setup lang="ts">
import { useSidebar } from '@/components/ui/sidebar';
import { useLocale } from '@/composables/useLocale';
import { Languages } from 'lucide-vue-next';

const { locale, changeLocale } = useLocale();
const { state } = useSidebar();

// store language in database when changed
async function updateLocale(newLocale: any) {
    // Change locale in frontend
    changeLocale(newLocale);

    if (!page.props.auth?.user) {
        alert('Please log in to change your language');
        return;
    }

    try {
        // Get CSRF token
        let csrfToken = document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content');
        if (!csrfToken && page.props.csrf_token) {
            csrfToken = String(page.props.csrf_token);
        }
        if (!csrfToken) {
            alert(
                'CSRF token not found. Refreshing page to fix authentication...',
            );
            window.location.reload();
            return;
        }

        // Post new language to user's database data
        const response = await fetch('/api/user/locale', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                Accept: 'application/json',
            },
            body: JSON.stringify({
                locale: newLocale,
            }),
        });

        const result = await response.json();
        if (response.ok && result?.success) {
            // Optionally show success message
        } else {
            alert(result?.message || 'Failed to update language.');
        }
    } catch (error) {
        alert('Failed to update language.');
        console.error(error);
    }

    window.location.reload();
}

const languages = [
    { value: 'en', label: 'English' },
    { value: 'sw', label: 'Swahili' },
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
</script>

<template>
    <div
        class="flex flex-wrap gap-1 rounded-lg bg-neutral-100 p-1 transition-all duration-200 dark:bg-neutral-800"
    >
        <button
            v-for="{ value, label } in languages"
            :key="value"
            @click="updateLocale(value)"
            :class="[
                'flex flex-shrink-0 items-center rounded-md transition-colors',
                'whitespace-nowrap',
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
            <span class="ml-1.5 text-sm">{{ label }}</span>
        </button>
    </div>
</template>
