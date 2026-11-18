import { useEffect, useRef, useState } from 'react';
import { fetchCmsContent } from '../lib/api';
import { useLanguage } from '../context/LanguageContext';

function normalizeCmsItem(item, type) {
  const data = item.data || {};
  const normalized = { ...data, id: item.slug, locale: item.locale, title: item.title };
  if (type === 'gallery' && data.mediaType) normalized.type = data.mediaType;
  if (type === 'faq') {
    normalized.q = data.question || item.title;
    normalized.a = data.answer || '';
  }
  return normalized;
}

function mergeContent(fallback, items, type) {
  if (!items.length) return fallback;
  const current = new Map(fallback.map((item, index) => [String(item.id ?? item.slug ?? index), item]));
  for (const item of items) {
    const normalized = normalizeCmsItem(item, type);
    current.set(String(normalized.id), { ...(current.get(String(normalized.id)) || {}), ...normalized });
  }
  return Array.from(current.values());
}

export function useCmsCollection(type, fallback = []) {
  const { lang } = useLanguage();
  const [items, setItems] = useState(fallback);
  const loadedKey = useRef('');

  useEffect(() => {
    let active = true;
    const key = `${type}:${lang}`;
    if (loadedKey.current !== key) {
      loadedKey.current = key;
      setItems(fallback);
    }

    const load = () => {
      fetchCmsContent(lang)
        .then((collections) => {
          if (active) setItems(mergeContent(fallback, collections[type] || [], type));
        })
        .catch(() => {
          // The bundled content remains visible if the CMS is temporarily unavailable.
        });
    };

    load();

    // Pick up admin changes as soon as the visitor returns to the site tab/window.
    const refresh = () => {
      if (document.visibilityState !== 'hidden') load();
    };
    window.addEventListener('focus', load);
    document.addEventListener('visibilitychange', refresh);

    return () => {
      active = false;
      window.removeEventListener('focus', load);
      document.removeEventListener('visibilitychange', refresh);
    };
  }, [type, lang]);

  return items;
}
