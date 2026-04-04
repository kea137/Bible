import { router } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

type Language =
    | 'en'
    | 'sw'
    | 'fr'
    | 'es'
    | 'de'
    | 'it'
    | 'ru'
    | 'zh'
    | 'ja'
    | 'ar'
    | 'hi'
    | 'bn'
    | 'pa'
    | 'jv'
    | 'ko'
    | 'vi'
    | 'te'
    | 'mr'
    | 'ta';

const setCookie = (name: string, value: string, days = 365) => {
    if (typeof document === 'undefined') {
        return;
    }

    const maxAge = days * 24 * 60 * 60;

    document.cookie = `${name}=${value};path=/;max-age=${maxAge};SameSite=Lax`;
};

const language = ref<Language>('en');

const getStoredLanguage = (): Language | null => {
    if (typeof window === 'undefined') {
        return null;
    }

    try {
        return localStorage.getItem('language') as Language | null;
    } catch (error) {
        console.error('[Language] Failed to read local storage:', error);
        return null;
    }
};

const setStoredLanguage = (value: Language) => {
    if (typeof window === 'undefined') {
        return;
    }

    try {
        localStorage.setItem('language', value);
    } catch (error) {
        console.error('[Language] Failed to write local storage:', error);
    }
};

export function useLanguage() {
    onMounted(() => {
        const savedLanguage = getStoredLanguage();

        if (savedLanguage) {
            language.value = savedLanguage;
        }
    });

    function updateLanguage(value: Language) {
        language.value = value;

        // Store in localStorage for client-side persistence...
        setStoredLanguage(value);

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
