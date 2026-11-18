export const API_BASE =
  import.meta.env.VITE_API_BASE_URL || 'http://localhost/new-aom/new-aom-react/Backend/api';

const cmsRequests = new Map();

export function fetchCmsContent(locale) {
  const pending = cmsRequests.get(locale);
  if (pending) return pending;

  const request = fetch(`${API_BASE}/content.php?type=all&locale=${encodeURIComponent(locale)}`, {
    headers: { Accept: 'application/json' },
    cache: 'no-store',
  })
    .then(async (response) => {
      const result = await response.json();
      if (!response.ok || result.status !== 'ok' || typeof result.items !== 'object') {
        throw new Error(result.message || `Content request failed (${response.status}).`);
      }
      return result.items;
    })
    .finally(() => {
      cmsRequests.delete(locale);
    });

  cmsRequests.set(locale, request);
  return request;
}

export function resolveMediaUrl(value) {
  if (!value) return '';
  if (/^(https?:)?\/\//i.test(value) || value.startsWith('/')) return value;
  if (value.startsWith('uploads/cms/')) {
    return new URL(value, `${API_BASE}/../../`).href;
  }
  return value;
}
