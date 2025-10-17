import { router } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

type Language = 'en' | 'sw';

const setCookie = (name: string, value: string, days = 365) => {
    if (typeof document === 'undefined') {
        return;
    }

    const maxAge = days * 24 * 60 * 60;

    document.cookie = `${name}=${value};path=/;max-age=${maxAge};SameSite=Lax`;
};

const language = ref<Language>('en');

export function useLanguage() {
    onMounted(() => {
        const savedLanguage = localStorage.getItem(
            'language',
        ) as Language | null;

        if (savedLanguage) {
            language.value = savedLanguage;
        }
    });

    function updateLanguage(value: Language) {
        language.value = value;

        // Store in localStorage for client-side persistence...
        localStorage.setItem('language', value);

        // Store in cookie for SSR...
        setCookie('language', value);

        // Update user preference in database...
        router.patch(
            '/settings/language',
            { language: value },
            {
                preserveState: true,
                preserveScroll: true,
            },
        );
    }

    return {
        language,
        updateLanguage,
    };
}
