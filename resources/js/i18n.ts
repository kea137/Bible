import { createI18n } from 'vue-i18n';
import sw from './locales/sw.json';
import fr from './locales/fr.json';
import ja from './locales/ja.json';
import es from './locales/es.json';
import de from './locales/de.json';
import it from './locales/it.json';
import ru from './locales/ru.json';
import zh from './locales/zh.json';
import ar from './locales/ar.json';
import hi from './locales/hi.json';
import ko from './locales/ko.json';

export const i18n = createI18n({
    legacy: false,
    locale: 'en',
    fallbackLocale: 'en',
    messages: {
        sw,
        fr,
        es,
        de,
        it,
        ru,
        zh,
        ja,
        ar,
        hi,
        ko,
    },
});
