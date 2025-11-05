import { onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';

type Locale =
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
    | 'ko';

export function updateLocale(value: Locale) {
    if (typeof window === 'undefined') {
        return;
    }

    // Store in localStorage for persistence
    localStorage.setItem('locale', value);

    // Store in cookie for SSR
    const maxAge = 365 * 24 * 60 * 60;
    document.cookie = `locale=${value};path=/;max-age=${maxAge};SameSite=Lax`;
}

const getStoredLocale = () => {
    if (typeof window === 'undefined') {
        return null;
    }

    return localStorage.getItem('locale') as Locale | null;
};

export function initializeLocale(i18n: any) {
    if (typeof window === 'undefined') {
        return;
    }

    // Initialize locale from saved preference or default to 'en'
    const savedLocale = getStoredLocale();
    if (savedLocale && i18n.global) {
        i18n.global.locale.value = savedLocale;
    }
}

const locale = ref<Locale>('en');

export function useLocale() {
    const { locale: i18nLocale, t } = useI18n();

    onMounted(() => {
        const savedLocale = localStorage.getItem('locale') as Locale | null;

        if (savedLocale) {
            locale.value = savedLocale;
            i18nLocale.value = savedLocale;
        }
    });

    function changeLocale(value: Locale) {
        locale.value = value;
        i18nLocale.value = value;

        updateLocale(value);
    }

    return {
        locale,
        changeLocale,
        t,
    };
}
