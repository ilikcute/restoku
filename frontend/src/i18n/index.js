import { createI18n } from 'vue-i18n';
import id from './locales/id.json';
import en from './locales/en.json';

const i18n = createI18n({
  legacy: false, // use Composition API
  locale: localStorage.getItem('locale') || 'id', // set locale from localStorage or default to 'id'
  fallbackLocale: 'en', // set fallback locale
  messages: {
    id,
    en
  },
});

export default i18n;
