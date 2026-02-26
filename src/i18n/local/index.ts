export const translationLoaders = {
  en: () => import('./en.json'),
  hi: () => import('./hi.json'),
} as const;

export type LocaleKey = keyof typeof translationLoaders;
