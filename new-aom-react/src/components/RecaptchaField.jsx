import { useEffect, useRef } from 'react';

const SITE_KEY = import.meta.env.VITE_RECAPTCHA_SITE_KEY;

export default function RecaptchaField({ onChange }) {
  const holder = useRef(null);
  const callbackRef = useRef(onChange);
  callbackRef.current = onChange;

  useEffect(() => {
    if (!SITE_KEY) return undefined;

    const render = () => {
      if (!window.grecaptcha?.render || !holder.current || holder.current.dataset.rendered) return;
      holder.current.dataset.rendered = '1';
      window.grecaptcha.render(holder.current, {
        sitekey: SITE_KEY,
        callback: (token) => callbackRef.current?.(token),
        'expired-callback': () => callbackRef.current?.(''),
      });
    };

    if (!document.getElementById('google-recaptcha-script')) {
      const script = document.createElement('script');
      script.id = 'google-recaptcha-script';
      script.src = 'https://www.google.com/recaptcha/api.js?render=explicit';
      script.async = true;
      script.defer = true;
      document.head.appendChild(script);
    }

    const timer = window.setInterval(render, 250);
    render();
    return () => window.clearInterval(timer);
  }, []);

  if (!SITE_KEY) return null;
  return <div ref={holder} className="my-3" />;
}
