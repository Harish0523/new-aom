import { useEffect, useLayoutEffect, useRef, useState } from 'react';
import { Link } from 'react-router-dom';
import Collapse from 'bootstrap/js/dist/collapse';
import { useLanguage, LANGUAGES } from '../context/LanguageContext';
import { useCmsCollection } from '../hooks/useCmsCollection';
import { resolveMediaUrl } from '../lib/api';

export default function Navbar() {
  const { lang, setLang, t } = useLanguage();
  const settingsItems = useCmsCollection('settings', []);
  const settings = settingsItems[0] || {};
  const current = LANGUAGES.find((l) => l.code === lang) || LANGUAGES[0];
  const [langOpen, setLangOpen] = useState(false);
  const langRef = useRef(null);
  const headerRef = useRef(null);

  // Fixed header: keep the page offset in sync when the header wraps
  // (mobile actions row, long language labels).
  useLayoutEffect(() => {
    const el = headerRef.current;
    if (!el) return undefined;
    const apply = () => {
      const nav = el.querySelector('.header-nav');
      let height = el.offsetHeight;
      // The mobile menu overlays content; don't shift the page when it opens.
      if (window.innerWidth < 992 && nav) height -= nav.offsetHeight;
      document.documentElement.style.setProperty('--header-height', `${height}px`);
    };
    apply();
    const observer = new ResizeObserver(apply);
    observer.observe(el);
    return () => observer.disconnect();
  }, []);

  useEffect(() => {
    if (!langOpen) return undefined;
    const closeOnOutside = (event) => {
      if (!langRef.current?.contains(event.target)) setLangOpen(false);
    };
    const closeOnEscape = (event) => {
      if (event.key === 'Escape') setLangOpen(false);
    };
    document.addEventListener('click', closeOnOutside);
    document.addEventListener('keydown', closeOnEscape);
    return () => {
      document.removeEventListener('click', closeOnOutside);
      document.removeEventListener('keydown', closeOnEscape);
    };
  }, [langOpen]);

  useEffect(() => {
    if (settings.siteTitle) document.title = settings.siteTitle;
    if (settings.siteDescription) {
      document.querySelector('meta[name="description"]')?.setAttribute('content', settings.siteDescription);
    }
  }, [settings.siteTitle, settings.siteDescription]);

  const closeMenu = () => {
    const menu = document.getElementById('mainNavbar');
    if (menu && menu.classList.contains('show')) {
      Collapse.getOrCreateInstance(menu, { toggle: false }).hide();
    }
  };

  return (
    <header className="site-header bg-white shadow-sm" ref={headerRef}>
      <nav className="navbar navbar-expand-lg container header-inner">
        <div className="header-top d-flex align-items-center flex-wrap">
          <Link className="navbar-brand fw-bold fs-4" to="/">
            {settings.logoUrl
              ? <img src={resolveMediaUrl(settings.logoUrl)} alt={settings.siteTitle || 'NEW AOM'} style={{ maxHeight: '42px', maxWidth: '180px' }} />
              : <img className="site-logo" src={`${import.meta.env.BASE_URL}aom-logo.svg`} alt={settings.siteTitle || 'NEW AOM'} width="440" height="112" />}
          </Link>

          <button
            className="navbar-toggler ms-auto"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#mainNavbar"
            aria-controls="mainNavbar"
            aria-expanded="false"
            aria-label={t('toggleNavigation')}
          >
            <span className="navbar-toggler-icon" />
          </button>

          <div className="header-actions d-flex align-items-center gap-2 flex-wrap">
            <Link className="header-donate-btn btn btn-sm px-3" to="/donations" onClick={closeMenu}>
              <i className="bi bi-heart-fill me-1" aria-hidden="true" />
              {t('navDonations')}
            </Link>
            <div className="dropdown" ref={langRef}>
            <button
              className="language-switcher btn btn-sm dropdown-toggle d-flex align-items-center gap-2"
              type="button"
              aria-expanded={langOpen}
              aria-haspopup="true"
              onClick={() => setLangOpen((open) => !open)}
            >
              <i className="bi bi-globe2" />
              <span>{current.label}</span>
            </button>
            <ul className={`dropdown-menu dropdown-menu-end ${langOpen ? 'show' : ''}`}>
              {LANGUAGES.map((item) => (
                <li key={item.code}>
                  <button
                    type="button"
                    className={`dropdown-item ${item.code === lang ? 'active' : ''}`}
                    onClick={() => {
                      setLang(item.code);
                      setLangOpen(false);
                    }}
                  >
                    {item.label}
                  </button>
                </li>
              ))}
            </ul>
            </div>
          </div>
        </div>

        <div className="collapse navbar-collapse header-nav" id="mainNavbar">
          <ul className="navbar-nav gap-lg-2 my-2 my-lg-0 justify-content-lg-center">
            <li className="nav-item"><Link className="nav-link" to="/" onClick={closeMenu}>{t('navHome')}</Link></li>
            <li className="nav-item"><Link className="nav-link" to="/about" onClick={closeMenu}>{t('navAbout')}</Link></li>
            <li className="nav-item"><Link className="nav-link" to="/services" onClick={closeMenu}>{t('navServices')}</Link></li>
            <li className="nav-item"><Link className="nav-link" to="/knowledge-centre" onClick={closeMenu}>{t('navKnowledge')}</Link></li>
            <li className="nav-item"><Link className="nav-link" to="/gallery" onClick={closeMenu}>{t('navGallery')}</Link></li>
            <li className="nav-item"><Link className="nav-link" to="/events" onClick={closeMenu}>{t('navEvents')}</Link></li>
            <li className="nav-item"><Link className="nav-link" to="/booking" onClick={closeMenu}>{t('navBooking')}</Link></li>
            <li className="nav-item"><Link className="nav-link" to="/contact" onClick={closeMenu}>{t('navContact')}</Link></li>
          </ul>
        </div>
      </nav>
    </header>
  );
}
