import i18n from 'i18next';
import { initReactI18next } from 'react-i18next';
import ar from '../locales/ar.json';

i18n.use(initReactI18next).init({
  compatibilityJSON: 'v4',
  lng: 'ar',
  fallbackLng: 'ar',
  resources: {
    ar: { translation: ar },
  },
  interpolation: { escapeValue: false },
});

export default i18n;
