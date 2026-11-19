import { useEffect, useRef } from 'react';

// Autoplay-in-view video: skips downloading until it is close to the viewport.
export default function LazyVideo({ src, poster, label, className, style, controls, children }) {
  const ref = useRef(null);

  useEffect(() => {
    const video = ref.current;
    if (!video || typeof IntersectionObserver === 'undefined') {
      video?.play?.().catch(() => {});
      return undefined;
    }
    const observer = new IntersectionObserver(
      (entries) => {
        if (entries.some((entry) => entry.isIntersecting)) {
          video.play().catch(() => {});
          observer.disconnect();
        }
      },
      { rootMargin: '300px 0px' }
    );
    observer.observe(video);
    return () => observer.disconnect();
  }, []);

  return (
    <video
      ref={ref}
      src={src}
      poster={poster}
      muted
      loop
      playsInline
      preload="none"
      controls={controls}
      aria-label={label}
      className={className}
      style={style}
    >
      {children}
    </video>
  );
}
