import { BrowserRouter, Routes, Route, useLocation } from 'react-router-dom';
import { lazy, Suspense, useEffect } from 'react';
import { LanguageProvider } from './context/LanguageContext';
import Navbar from './components/Navbar';
import Footer from './components/Footer';
import HomeSections from './components/HomeSections';
import { API_BASE } from './lib/api';
import { useCmsCollection } from './hooks/useCmsCollection';

const InnerPageView = lazy(() =>
  import('./pages/InnerPages').then((module) => ({ default: module.InnerPageView }))
);
const NotFoundPage = lazy(() =>
  import('./pages/InnerPages').then((module) => ({ default: module.NotFoundPage }))
);

function ScrollToTop() {
  const location = useLocation();

  useEffect(() => {
    if (location.hash) return;
    window.scrollTo({ top: 0, left: 0, behavior: 'auto' });
  }, [location.pathname, location.key, location.hash]);

  return null;
}

const PAGE_TITLES = {
  '/': '',
  '/about': 'About Us',
  '/services': 'Services',
  '/knowledge-centre': 'Knowledge Centre',
  '/spiritual-wisdom': 'Spiritual Wisdom',
  '/aum-chanting': 'AUM Chanting',
  '/gallery': 'Gallery',
  '/events': 'Workshops & Events',
  '/booking': 'Online Booking',
  '/donations': 'Donation Projects',
  '/contact': 'Contact Us',
  '/privacy-policy': 'Privacy Policy',
  '/terms': 'Terms & Conditions',
};

function DocumentMeta() {
  const location = useLocation();
  const settingsItems = useCmsCollection('settings', []);
  const settings = settingsItems[0] || {};

  useEffect(() => {
    const base = settings.siteTitle || 'NEW AOM';
    const section = PAGE_TITLES[location.pathname]
      || (location.pathname.startsWith('/articles/') ? 'Article'
        : location.pathname.startsWith('/services/') ? 'Service Details'
          : location.pathname.startsWith('/events/') ? 'Event Details'
            : location.pathname.startsWith('/donations/') ? 'Donation Details'
              : '');
    document.title = section ? `${section} — ${base}` : base;
  }, [location.pathname, settings.siteTitle]);

  useEffect(() => {
    const analyticsId = String(settings.analyticsId || '').trim();
    if (!analyticsId || document.getElementById('aom-analytics')) return;
    const script = document.createElement('script');
    script.id = 'aom-analytics';
    script.async = true;
    script.src = `https://www.googletagmanager.com/gtag/js?id=${encodeURIComponent(analyticsId)}`;
    document.head.appendChild(script);
    window.dataLayer = window.dataLayer || [];
    window.gtag = function gtag() {
      window.dataLayer.push(arguments);
    };
    window.gtag('js', new Date());
    window.gtag('config', analyticsId);
  }, [settings.analyticsId]);

  return null;
}

function TrackVisit() {
  const location = useLocation();

  useEffect(() => {
    try {
      if (sessionStorage.getItem('aom_visit_tracked')) return;
      sessionStorage.setItem('aom_visit_tracked', '1');
    } catch {
      return;
    }
    fetch(`${API_BASE}/track_visit.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ page: location.pathname }),
    }).catch(() => {});
  }, []);

  return null;
}

export default function App() {
  return (
    <LanguageProvider>
      <BrowserRouter basename={import.meta.env.BASE_URL}>
        <ScrollToTop />
        <TrackVisit />
        <Navbar />
        <DocumentMeta />
        <main>
          <Suspense fallback={null}>
            <Routes>
            <Route path="/" element={<HomeSections />} />
            <Route path="/about" element={<InnerPageView slug="about" />} />
            <Route path="/services" element={<InnerPageView slug="services" />} />
            <Route path="/services/:id" element={<InnerPageView slug="service-details" />} />
            <Route path="/knowledge-centre" element={<InnerPageView slug="knowledge-centre" />} />
            <Route path="/articles/:id" element={<InnerPageView slug="article-details" />} />
            <Route path="/spiritual-wisdom" element={<InnerPageView slug="spiritual-wisdom" />} />
            <Route path="/aum-chanting" element={<InnerPageView slug="aum-chanting" />} />
            <Route path="/gallery" element={<InnerPageView slug="gallery" />} />
            <Route path="/events" element={<InnerPageView slug="events" />} />
            <Route path="/events/:id" element={<InnerPageView slug="event-details" />} />
            <Route path="/booking" element={<InnerPageView slug="booking" />} />
            <Route path="/donations" element={<InnerPageView slug="donations" />} />
            <Route path="/donations/:id" element={<InnerPageView slug="donation-details" />} />
            <Route path="/contact" element={<InnerPageView slug="contact" />} />
            <Route path="/privacy-policy" element={<InnerPageView slug="privacy-policy" />} />
            <Route path="/terms" element={<InnerPageView slug="terms" />} />
            <Route path="*" element={<NotFoundPage />} />
            </Routes>
          </Suspense>
        </main>
        <Footer />
      </BrowserRouter>
    </LanguageProvider>
  );
}
