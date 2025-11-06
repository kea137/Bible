import { createI18n } from 'vue-i18n';

import en from './locales/en.json';
import ar from './locales/ar.json';
import de from './locales/de.json';
import es from './locales/es.json';
import fr from './locales/fr.json';
import hi from './locales/hi.json';
import it from './locales/it.json';
import ja from './locales/ja.json';
import ko from './locales/ko.json';
import ru from './locales/ru.json';
import sw from './locales/sw.json';
import zh from './locales/zh.json';

export const i18n = createI18n({
    legacy: false,
    locale: 'en',
    fallbackLocale: 'en',
    messages: {
        en,
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
