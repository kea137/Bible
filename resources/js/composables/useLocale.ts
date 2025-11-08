import { usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';
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

export function initializeLocale(i18n: any) {
    if (typeof window === 'undefined') {
        return;
    }

    // Initialize locale from user's database preference passed via Inertia
    const page = usePage();
    const userLanguage = (page.props.language as Locale) || 'en';
    
    if (i18n.global) {
        i18n.global.locale.value = userLanguage;
    }
}

const locale = ref<Locale>('en');

export function useLocale() {
    const { locale: i18nLocale, t } = useI18n();
    const page = usePage();
    const userLanguage = computed(() => (page.props.language as Locale) || 'en');

    onMounted(() => {
        locale.value = userLanguage.value;
        i18nLocale.value = userLanguage.value;
    });

    function changeLocale(value: Locale) {
        locale.value = value;
        i18nLocale.value = value;
    }

    return {
        locale,
        changeLocale,
        t,
    };
}
