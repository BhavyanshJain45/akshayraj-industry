import i18n from 'i18next';
import { initReactI18next } from 'react-i18next';
import LanguageDetector from 'i18next-browser-languagedetector';
import messages from './local/index';
i18n
  .use(LanguageDetector)
  .use(initReactI18next)
  .init({
    lng: 'en',
    fallbackLng: 'en',
    debug: false,
    resources: messages,
    interpolation: {
      escapeValue: false,
    },
  });
import { translationLoaders, type LocaleKey } from './local';

const fallbackLng: LocaleKey = 'en';

async function loadLocale(locale: LocaleKey) {
  try {
    const module = await translationLoaders[locale]();
    return module.default;
  } catch {
    const fallback = await translationLoaders[fallbackLng]();
    return fallback.default;
  }
}

const initialLocale = (navigator.language.split('-')[0] as LocaleKey) || fallbackLng;

void (async () => {
  const locale = initialLocale in translationLoaders ? initialLocale : fallbackLng;
  const messages = await loadLocale(locale);

  await i18n.use(initReactI18next).init({
    lng: locale,
    fallbackLng,
    interpolation: { escapeValue: false },
    resources: {
      [locale]: { translation: messages },
    },
  });
})();

export default i18n;
