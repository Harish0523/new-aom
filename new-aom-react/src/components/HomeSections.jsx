import { useState } from 'react';
import { Link } from 'react-router-dom';
import {
  SERVICES_DATA,
  ARTICLES_DATA,
  EVENTS_DATA,
  GALLERY_DATA,
  DONATION_PROJECTS,
} from '../data/siteData';
import { useLanguage } from '../context/LanguageContext';
import DonationOtpModal from './DonationOtpModal';
import ContactForm from './ContactForm';
import CmsLink from './CmsLink';
import { resolveMediaUrl } from '../lib/api';
import { useCmsCollection } from '../hooks/useCmsCollection';
import { DONATION_CURRENCIES, formatDonationAmount } from '../lib/currency';

const FAQ_DATA = [
  {
    id: 'participation',
    q: 'Who can participate in NEW AOM sessions and workshops?',
    a: 'Anyone seeking spiritual reflection, meditation, AUM chanting, yoga, or cultural learning is welcome, regardless of prior experience.',
  },
  {
    id: 'donation-process',
    q: 'How do donations work on NEW AOM?',
    a: 'Select a project, verify your email with a one-time OTP, and use the displayed bank transfer details or QR code directly.',
  },
  {
    id: 'languages',
    q: 'Are sessions available in multiple languages?',
    a: 'Yes, the website supports English, Tamil, French, Chinese (Simplified), and Swiss German.',
  },
];
const ABOUT_FALLBACK = [{
  id: 'about',
  title: 'A quieter mind. A kinder world.',
  body: 'NEW AOM fosters Spirituality and Wisdom, Meditation and AUM Chanting, Happiness and Personal Development, Yoga and Wellness, Cultural Heritage, Community Services, and Educational Knowledge Sharing.',
  vision: 'Peaceful, conscious, and compassionate living across global communities.',
  mission: 'Accessible meditation, AUM chanting, yoga, and cultural wisdom in five languages.',
  values: 'Authenticity, stillness, cultural respect, and selfless community service.',
}];

export default function HomeSections() {
  const { t, lang } = useLanguage();
  const services = useCmsCollection('services', SERVICES_DATA);
  const articles = useCmsCollection('knowledge', ARTICLES_DATA);
  const events = useCmsCollection('events', EVENTS_DATA);
  const gallery = useCmsCollection('gallery', GALLERY_DATA);
  const projects = useCmsCollection('crowdfunding', DONATION_PROJECTS);
  const faqs = useCmsCollection('faq', FAQ_DATA);
  const testimonials = useCmsCollection('testimonials', []);
  const aboutItems = useCmsCollection('about', ABOUT_FALLBACK);
  const homepageItems = useCmsCollection('homepage', []);
  const ads = useCmsCollection('ads', []);
  const about = aboutItems[0] || ABOUT_FALLBACK[0];
  const hero = homepageItems.find((item) => item.id === 'hero') || {};
  const localizedHero = hero.locale === undefined || hero.locale === lang;
  const adFor = (...positions) => ads.find((item) => positions.includes(item.position));
  const promoAd = adFor('top-banner', 'top') || ads.find((item) => !item.position);
  const heroAd = adFor('hero');
  const homepageAd = adFor('homepage-content');
  const serviceAd = adFor('services');
  const knowledgeAd = adFor('knowledge-centre', 'knowledge');
  const eventAd = adFor('events');
  const closingAd = adFor('above-footer', 'closing');
  const promoAdIsLocalized = !promoAd || promoAd.locale === lang;
  const heroAdIsLocalized = !heroAd || heroAd.locale === lang;
  const homepageAdIsLocalized = !homepageAd || homepageAd.locale === lang;
  const serviceAdIsLocalized = !serviceAd || serviceAd.locale === lang;
  const knowledgeAdIsLocalized = !knowledgeAd || knowledgeAd.locale === lang;
  const eventAdIsLocalized = !eventAd || eventAd.locale === lang;
  const closingAdIsLocalized = !closingAd || closingAd.locale === lang;
  const publishedTestimonials = testimonials.filter(
    (item) => String(item.consent || '').toLowerCase() === 'yes' && item.quote,
  );
  const [currency, setCurrency] = useState('LKR');
  const [selectedProject, setSelectedProject] = useState(null);
  const [openFaq, setOpenFaq] = useState(0);
  const [galleryFilter, setGalleryFilter] = useState('');
  const [lightboxItem, setLightboxItem] = useState(null);
  const galleryCategories = [...new Set(gallery.map((item) => item.category).filter(Boolean))];
  const galleryItems = galleryFilter === ''
    ? gallery
    : gallery.filter((item) => item.category === galleryFilter);

  return (
    <div className="home-sections">
      {/* Section 01: Hero Banner */}
      <section id="sec-01" className="hero-section">
        <div className="container position-relative">
          <div className="row align-items-center g-4">
            <div className="col-lg-7">
              <p className="hero-eyebrow mb-3">
                <span className="hero-eyebrow-mark" aria-hidden="true">✦</span>
                {localizedHero ? (hero.eyebrow || t('tagline')) : t('tagline')}
              </p>
              <h1 className="hero-title mb-4">{localizedHero ? (hero.title || t('heroTitle')) : t('heroTitle')}</h1>
              <p className="hero-description mb-4">{localizedHero ? (hero.body || t('heroSubtitle')) : t('heroSubtitle')}</p>
              <div className="d-flex flex-wrap gap-3">
                <CmsLink to={hero.button_url || '/services'} className="btn btn-gold btn-lg px-4">
                  {localizedHero ? (hero.button_label || t('ctaExplore')) : t('ctaExplore')} <i className="bi bi-arrow-up-right ms-2" aria-hidden="true" />
                </CmsLink>
                <Link to="/knowledge-centre" className="btn btn-outline-light btn-lg px-4">{t('ctaDiscover')}</Link>
              </div>
              <div className="hero-highlights mt-5">
                <span><i className="bi bi-globe2" aria-hidden="true" /> {t('fiveLanguages')}</span>
                <span><i className="bi bi-flower1" aria-hidden="true" /> {t('holisticWellbeing')}</span>
                <span><i className="bi bi-heart" aria-hidden="true" /> {t('communityFirst')}</span>
              </div>
            </div>
            <div className="col-lg-5">
              <div className="hero-art" aria-hidden="true">
                <img className="hero-image" src={localizedHero && hero.imageUrl ? resolveMediaUrl(hero.imageUrl) : '/images/meditation-hero.jpg'} alt="" />
                <div className="hero-image-overlay" />
                <div className="hero-art-halo" />
                <div className="hero-art-orbit hero-art-orbit-outer" />
                <div className="hero-art-orbit hero-art-orbit-inner" />
                <span className="hero-art-symbol">ॐ</span>
                <div className="hero-art-note">
                  <i className="bi bi-soundwave" />
                  <span><strong>{t('heroPause')}</strong><small>{t('heroStillness')}</small></span>
                </div>
              </div>
            </div>
          </div>
          {heroAdIsLocalized && heroAd?.imageUrl && (
            <CmsLink to={heroAd.linkUrl || '/'} className="d-block mt-4 hero-ad-banner">
              <img src={resolveMediaUrl(heroAd.imageUrl)} alt={heroAd.title || ''} className="img-fluid rounded-4 w-100" style={{ maxHeight: '160px', objectFit: 'cover' }} />
            </CmsLink>
          )}
        </div>
      </section>
      {/* Section 02: Promotional Advertisement Banner */}
      <section id="sec-02" className="py-4">
        <div className="container">
          <div className="wellness-card promo-banner p-4 p-md-5 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            {promoAdIsLocalized && promoAd?.imageUrl && (
              <img src={resolveMediaUrl(promoAd.imageUrl)} alt="" className="rounded-3 flex-shrink-0" style={{ maxHeight: '120px', maxWidth: '220px', objectFit: 'cover' }} />
            )}
            <div>
              <span className="promo-kicker mb-2">{promoAdIsLocalized ? (promoAd?.eyebrow || t('upcomingGathering')) : t('upcomingGathering')}</span>
              <h2 className="h3 mb-2">{promoAdIsLocalized && promoAd?.title ? promoAd.title : events[0]?.title}</h2>
              <p className="mb-0">
                {promoAdIsLocalized && promoAd?.body
                  ? promoAd.body
                  : [events[0]?.date, events[0]?.time, events[0]?.location].filter(Boolean).join(' • ')}
              </p>
            </div>
            {promoAdIsLocalized && promoAd?.linkUrl ? (
              <a href={promoAd.linkUrl} className="btn btn-gold flex-shrink-0">{t('learnMore')} <i className="bi bi-arrow-right ms-2" aria-hidden="true" /></a>
            ) : events[0] ? (
              <Link to={`/events/${events[0].id}`} className="btn btn-gold flex-shrink-0">
                {t('exploreEvent')} <i className="bi bi-arrow-right ms-2" aria-hidden="true" />
              </Link>
            ) : null}
          </div>
        </div>
      </section>

      {/* Section 03: About NEW AOM (No Founder Profile) */}
      <section id="sec-03" className="py-5 home-section">
        <div className="container">
          <div className="wellness-card p-4 p-md-5">
            <span className="section-eyebrow">{t('aboutNewAom')}</span>
            <h2 className="section-title mb-3">{about.title}</h2>
            <p className="text-muted">{about.body}</p>
            <div className="row g-3 my-2">
              <div className="col-md-4">
                <div className="p-3 rounded-3 bg-light h-100">
                  <h3 className="h6 fw-bold">{t('ourVision')}</h3>
                  <p className="small text-muted mb-0">{about.vision}</p>
                </div>
              </div>
              <div className="col-md-4">
                <div className="p-3 rounded-3 bg-light h-100">
                  <h3 className="h6 fw-bold">{t('ourMission')}</h3>
                  <p className="small text-muted mb-0">{about.mission}</p>
                </div>
              </div>
              <div className="col-md-4">
                <div className="p-3 rounded-3 bg-light h-100">
                  <h3 className="h6 fw-bold">{t('ourValues')}</h3>
                  <p className="small text-muted mb-0">{about.values}</p>
                </div>
              </div>
            </div>
            <Link to="/about" className="btn btn-outline-dark btn-sm mt-3">{t('readMore')}</Link>
          </div>
          {homepageAdIsLocalized && homepageAd?.imageUrl && (
            <CmsLink to={homepageAd.linkUrl || '/'} className="d-block mt-4">
              <img src={resolveMediaUrl(homepageAd.imageUrl)} alt={homepageAd.title || ''} className="img-fluid rounded-4 w-100" style={{ maxHeight: '200px', objectFit: 'cover' }} />
            </CmsLink>
          )}
        </div>
      </section>

      {/* Section 04: Main Services (All 7 Core Services) */}
      <section id="sec-04" className="py-5 bg-light home-section">
        <div className="container">
          <div className="section-heading mb-4">
            <span className="section-eyebrow">{t('growAtPace')}</span>
            <h2 className="section-title">{t('servicesHeading')}</h2>
            <p className="text-muted mb-0">{t('servicesIntro')}</p>
          </div>
          <div className="row g-4">
            {services.map((srv) => (
              <div key={srv.id} className="col-md-6 col-lg-4">
                <div className="wellness-card service-card p-4 h-100 d-flex flex-column justify-content-between">
                  <div>
                    {srv.imageUrl && (
                      <img
                        src={resolveMediaUrl(srv.imageUrl)}
                        alt={srv.title}
                        className="img-fluid rounded-3 mb-3 w-100"
                        style={{ maxHeight: '170px', objectFit: 'cover' }}
                        loading="lazy"
                      />
                    )}
                    <span className="service-icon"><i className={`bi ${srv.icon}`} /></span>
                    <h3 className="h5 mt-2">{srv.title}</h3>
                    <p className="small text-muted">{srv.shortDesc}</p>
                  </div>
                  <Link to={`/services/${srv.id}`} className="service-link align-self-start">
                    {t('learnMore')} <i className="bi bi-arrow-up-right ms-1" aria-hidden="true" />
                  </Link>
                </div>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Section 05: Service Advertisement */}
      <section id="sec-05" className="py-4">
        <div className="container">
          <div className="wellness-card service-promo p-4 p-md-5 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            {serviceAdIsLocalized && serviceAd?.imageUrl && (
              <img src={resolveMediaUrl(serviceAd.imageUrl)} alt="" className="rounded-3 flex-shrink-0" style={{ maxHeight: '120px', maxWidth: '220px', objectFit: 'cover' }} />
            )}
            <div>
              <span className="section-eyebrow">{serviceAdIsLocalized ? (serviceAd?.eyebrow || t('oneStep')) : t('oneStep')}</span>
              <h2 className="h4 mb-2">{serviceAdIsLocalized ? (serviceAd?.title || t('makeSpace')) : t('makeSpace')}</h2>
              <p className="text-muted mb-0">{serviceAdIsLocalized ? (serviceAd?.body || t('findSession')) : t('findSession')}</p>
            </div>
            {serviceAdIsLocalized && serviceAd?.linkUrl
              ? <a href={serviceAd.linkUrl} className="btn btn-gold flex-shrink-0">{serviceAd.button_label || t('ctaBook')}</a>
              : <Link to="/booking" className="btn btn-gold flex-shrink-0">{t('ctaBook')}</Link>}
          </div>
        </div>
      </section>

      {/* Section 06: Knowledge Centre (All 6 Categories) */}
      <section id="sec-06" className="py-5 home-section">
        <div className="container">
          <div className="d-flex justify-content-between align-items-center mb-4">
            <div className="section-heading">
              <span className="section-eyebrow">{t('ideasToTake')}</span>
              <h2 className="section-title mb-0">{t('knowledgeHeading')}</h2>
            </div>
            <Link to="/knowledge-centre" className="btn btn-sm btn-outline-dark">{t('viewAll')}</Link>
          </div>
          <div className="row g-4">
            {articles.map((art) => (
              <div key={art.id} className="col-md-6 col-lg-4">
                <div className="wellness-card p-4 h-100 d-flex flex-column justify-content-between">
                  <div>
                    {art.imageUrl && (
                      <img
                        src={resolveMediaUrl(art.imageUrl)}
                        alt={art.title}
                        className="img-fluid rounded-3 mb-3 w-100"
                        style={{ maxHeight: '160px', objectFit: 'cover' }}
                        loading="lazy"
                      />
                    )}
                    <span className="badge badge-natural-green mb-2">{art.category}</span>
                    <h3 className="h6 fw-bold">{art.title}</h3>
                    <p className="small text-muted">{art.summary}</p>
                  </div>
                  <Link to={`/articles/${art.id}`} className="btn btn-sm btn-outline-dark align-self-start">
                    {t('readMore')}
                  </Link>
                </div>
              </div>
            ))}
          </div>
          {knowledgeAdIsLocalized && knowledgeAd?.imageUrl && (
            <CmsLink to={knowledgeAd.linkUrl || '/knowledge-centre'} className="d-block mt-4">
              <img src={resolveMediaUrl(knowledgeAd.imageUrl)} alt={knowledgeAd.title || ''} className="img-fluid rounded-4 w-100" style={{ maxHeight: '180px', objectFit: 'cover' }} />
            </CmsLink>
          )}
        </div>
      </section>

      {/* Section 07: Photo and Video Gallery */}
      <section id="sec-07" className="py-5 bg-light home-section">
        <div className="container">
          <div className="d-flex justify-content-between align-items-center mb-4">
            <div className="section-heading">
              <span className="section-eyebrow">{t('momentsRemember')}</span>
              <h2 className="section-title mb-0">{t('galleryHeading')}</h2>
            </div>
            <Link to="/gallery" className="btn btn-sm btn-outline-dark">{t('viewAll')}</Link>
          </div>
          <div className="d-flex flex-wrap gap-2 mb-4">
            <button
              type="button"
              className={`btn btn-sm ${galleryFilter === '' ? 'btn-dark' : 'btn-outline-dark'}`}
              onClick={() => setGalleryFilter('')}
            >
              {t('all')}
            </button>
            {galleryCategories.map((category) => (
              <button
                key={category}
                type="button"
                className={`btn btn-sm ${galleryFilter === category ? 'btn-dark' : 'btn-outline-dark'}`}
                onClick={() => setGalleryFilter(category)}
              >
                {category}
              </button>
            ))}
          </div>
          <div className="row g-4">
            {galleryItems.map((item) => (
              <div key={item.id} className="col-md-6 col-lg-4">
                <div
                  className="wellness-card gallery-card p-3 h-100"
                  role="button"
                  tabIndex={0}
                  onClick={() => setLightboxItem(item)}
                  onKeyDown={(event) => event.key === 'Enter' && setLightboxItem(item)}
                >
                  <div className="gallery-art">
                    {item.mediaUrl ? (
                      item.type === 'video'
                        ? <video src={resolveMediaUrl(item.mediaUrl)} poster={item.poster ? resolveMediaUrl(item.poster) : undefined} aria-label={item.title} autoPlay muted loop playsInline preload="metadata" />
                        : <img src={resolveMediaUrl(item.mediaUrl)} alt={item.title} loading="lazy" />
                    ) : <i className={`bi ${item.icon || 'bi-image'}`} aria-hidden="true" />}
                    <span>{item.type === 'video' ? t('watch') : t('explore')}</span>
                  </div>
                  <div className="p-2">
                    <span className="section-eyebrow">{item.category}</span>
                    <h3 className="h6 mt-1 mb-0">{item.title}</h3>
                  </div>
                </div>
              </div>
            ))}
          </div>

          {lightboxItem && (
            <div className="modal fade show d-block" style={{ backgroundColor: 'rgba(0,0,0,0.65)' }} role="dialog" aria-modal="true">
              <div className="modal-dialog modal-dialog-centered">
                <div className="modal-content rounded-4">
                  <div className="modal-header">
                    <h5 className="modal-title">{lightboxItem.title}</h5>
                    <button type="button" className="btn-close" onClick={() => setLightboxItem(null)} aria-label="Close" />
                  </div>
                  <div className="modal-body text-center py-4">
                    {lightboxItem.mediaUrl && lightboxItem.type === 'video' ? (
                      <video className="img-fluid rounded-3" controls autoPlay loop poster={lightboxItem.poster ? resolveMediaUrl(lightboxItem.poster) : undefined} src={resolveMediaUrl(lightboxItem.mediaUrl)}>
                        {t('browserVideoFallback')}
                      </video>
                    ) : lightboxItem.mediaUrl ? (
                      <img className="img-fluid rounded-3" src={resolveMediaUrl(lightboxItem.mediaUrl)} alt={lightboxItem.title} />
                    ) : (
                      <>
                        <i className={`bi ${lightboxItem.icon || 'bi-image'} display-3 text-gold`} />
                        <p className="small text-muted mt-3 mb-0">{t('uploadMediaHelp')}</p>
                      </>
                    )}
                    {lightboxItem.caption && <p className="small text-muted mt-3 mb-0">{lightboxItem.caption}</p>}
                  </div>
                </div>
              </div>
            </div>
          )}
        </div>
      </section>

      {/* Section 08: Workshops and Events */}
      <section id="sec-08" className="py-5 home-section">
        <div className="container">
          <div className="section-heading mb-4">
            <span className="section-eyebrow">{t('comeTogether')}</span>
            <h2 className="section-title">{t('eventsHeading')}</h2>
          </div>
          <div className="row g-4">
            {events.map((ev) => (
              <div key={ev.id} className="col-md-6">
                <div className="wellness-card p-4 h-100">
                  {ev.imageUrl && (
                    <img
                      src={resolveMediaUrl(ev.imageUrl)}
                      alt={ev.title}
                      className="img-fluid rounded-3 mb-3 w-100"
                      style={{ maxHeight: '180px', objectFit: 'cover' }}
                      loading="lazy"
                    />
                  )}
                  <span className="event-date mb-3"><i className="bi bi-calendar-event me-2" aria-hidden="true" />{ev.date} &bull; {ev.time}</span>
                  <h3 className="h5">{ev.title}</h3>
                  <p className="small text-muted mb-1"><i className="bi bi-geo-alt me-1" />{ev.location}</p>
                  <p className="small text-muted">{ev.description}</p>
                  <CmsLink to={ev.bookingUrl || '/booking'} className="btn btn-gold btn-sm">{t('registerNow')}</CmsLink>
                </div>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Section 09: Event Advertisement */}
      <section id="sec-09" className="py-4">
        <div className="container">
          <div className="wellness-card event-promo p-4 p-md-5 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            {eventAdIsLocalized && eventAd?.imageUrl && (
              <img src={resolveMediaUrl(eventAd.imageUrl)} alt="" className="rounded-3 flex-shrink-0" style={{ maxHeight: '120px', maxWidth: '220px', objectFit: 'cover' }} />
            )}
            <div>
              <span className="section-eyebrow">{eventAdIsLocalized ? (eventAd?.eyebrow || t('saveYourPlace')) : t('saveYourPlace')}</span>
              <h2 className="h4 mb-2">{eventAdIsLocalized ? (eventAd?.title || events[1]?.title || t('upcomingProgrammes')) : events[1]?.title || t('upcomingProgrammes')}</h2>
              <p className="text-muted mb-0">{eventAdIsLocalized && eventAd?.body ? eventAd.body : [events[1]?.date, events[1]?.location].filter(Boolean).join(' • ')}</p>
            </div>
            {eventAdIsLocalized && eventAd?.linkUrl
              ? <a href={eventAd.linkUrl} className="btn btn-outline-dark flex-shrink-0">{eventAd.button_label || t('eventDetails')}</a>
              : events[1] && <Link to={`/events/${events[1].id}`} className="btn btn-outline-dark flex-shrink-0">{t('eventDetails')}</Link>}
          </div>
        </div>
      </section>

      {/* Section 10: Testimonials and Experiences (Client-approved only) */}
      <section id="sec-10" className="py-5 bg-light home-section">
        <div className="container">
          <div className="section-heading mb-4">
            <span className="section-eyebrow">{t('sharedPermission')}</span>
            <h2 className="section-title">{t('communityStories')}</h2>
          </div>
          {publishedTestimonials.length ? (
            <div className="row g-4">
              {publishedTestimonials.map((item) => (
                <div key={item.id} className="col-md-6">
                  <figure className="wellness-card p-4 h-100 mb-0">
                    <blockquote className="mb-3">“{item.quote}”</blockquote>
                    <figcaption className="d-flex align-items-center gap-2 small text-muted">
                      {item.photo && (
                        <img
                          src={resolveMediaUrl(item.photo)}
                          alt={item.person || item.title}
                          className="rounded-circle"
                          style={{ width: '48px', height: '48px', objectFit: 'cover' }}
                          loading="lazy"
                        />
                      )}
                      <span>
                        {item.person || item.title}
                        {Number(item.rating) > 0 && (
                          <span className="d-block text-warning" aria-label={`${item.rating} out of 5`}>
                            {'★'.repeat(Math.min(5, Math.max(0, Math.round(Number(item.rating)))))}
                            {'☆'.repeat(5 - Math.min(5, Math.max(0, Math.round(Number(item.rating)))))}
                          </span>
                        )}
                      </span>
                    </figcaption>
                  </figure>
                </div>
              ))}
            </div>
          ) : (
            <div className="wellness-card testimonial-empty p-4 p-md-5">
              <i className="bi bi-chat-heart" aria-hidden="true" />
              <p className="mb-0">{t('testimonialsEmpty')}</p>
            </div>
          )}
        </div>
      </section>

      {/* Section 11: Crowdfunding and Donations */}
      <section id="sec-11" className="py-5">
        <div className="container">
          <div className="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
            <div className="section-heading">
              <span className="section-eyebrow">{t('supportCommunity')}</span>
              <h2 className="section-title mb-0">{t('donationHeading')}</h2>
            </div>
            <div className="btn-group btn-group-sm">
              {DONATION_CURRENCIES.map((cur) => (
                <button
                  key={cur}
                  type="button"
                  className={`btn ${currency === cur ? 'btn-dark' : 'btn-outline-dark'}`}
                  onClick={() => setCurrency(cur)}
                  aria-pressed={currency === cur}
                >
                  {cur}
                </button>
              ))}
            </div>
          </div>
          <div className="row g-4">
            {projects.map((proj) => {
              const target = Number(proj.target) || 0;
              const raised = Number(proj.raised) || 0;
              const pct = target > 0 ? Math.min(100, Math.round((raised / target) * 100)) : 0;
              return (
                <div key={proj.id} className="col-md-6">
                  <div className="wellness-card p-4 h-100">
                    {proj.imageUrl && (
                      <img
                        src={resolveMediaUrl(proj.imageUrl)}
                        alt={proj.title}
                        className="img-fluid rounded-3 mb-3 w-100"
                        style={{ maxHeight: '180px', objectFit: 'cover' }}
                        loading="lazy"
                      />
                    )}
                    <h3 className="h5">{proj.title}</h3>
                    <p className="small text-muted">{proj.description}</p>
                    <div className="progress mb-2" style={{ height: '10px' }}>
                      <div className="progress-bar bg-success" style={{ width: `${pct}%` }} />
                    </div>
                    <div className="d-flex justify-content-between small text-muted mb-3">
                      <span>{t('raised')}: {formatDonationAmount(raised, currency)}</span>
                      <span>{t('target')}: {formatDonationAmount(target, currency)}</span>
                    </div>
                    {proj.endDate && (
                      <p className="small text-muted mb-3">{t('endsOn')}: {proj.endDate}</p>
                    )}
                    <button
                      type="button"
                      className="btn btn-gold btn-sm"
                      onClick={() => setSelectedProject({ title: proj.title, currency })}
                    >
                      {t('donateNow')}
                    </button>
                  </div>
                </div>
              );
            })}
          </div>
          <DonationOtpModal project={selectedProject} onClose={() => setSelectedProject(null)} />
        </div>
      </section>

      {/* Section 12: Frequently Asked Questions */}
      <section id="sec-12" className="py-5 bg-light home-section">
        <div className="container">
          <div className="section-heading mb-4">
            <span className="section-eyebrow">{t('hereToHelp')}</span>
            <h2 className="section-title">{t('faqHeading')}</h2>
          </div>
          <div className="accordion" id="newAomFaq">
            {faqs.map((item, idx) => (
              <div key={item.q} className="accordion-item rounded-3 mb-2 border overflow-hidden">
                <h3 className="accordion-header">
                  <button
                    className={`accordion-button ${openFaq === idx ? '' : 'collapsed'}`}
                    type="button"
                    onClick={() => setOpenFaq(openFaq === idx ? -1 : idx)}
                  >
                    {item.q}
                  </button>
                </h3>
                {openFaq === idx && (
                  <div className="accordion-body small text-muted">{item.a}</div>
                )}
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Section 13: Contact Us and Location */}
      <section id="sec-13" className="py-5 home-section">
        <div className="container">
          <div className="section-heading mb-4">
            <span className="section-eyebrow">{t('contactInvite')}</span>
            <h2 className="section-title">{t('contactHeading')}</h2>
          </div>
          <div className="row g-4">
            <div className="col-lg-6">
              <div className="wellness-card contact-card p-4 p-md-5 h-100">
                <span className="contact-icon"><i className="bi bi-envelope-heart" aria-hidden="true" /></span>
                <h3 className="h4 mt-3">{t('letsConnect')}</h3>
                <p className="text-muted">
                  {t('contactPrompt')}
                </p>
                <div className="mt-3">
                  <ContactForm compact />
                </div>
              </div>
            </div>
            <div className="col-lg-6">
              <div className="ratio ratio-16x9 rounded-4 overflow-hidden border contact-map">
                <iframe
                  title="NEW AOM Location Map"
                  src="https://maps.google.com/maps?q=Colombo%20Sri%20Lanka&t=&z=12&ie=UTF8&iwloc=&output=embed"
                  loading="lazy"
                />
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* Section 14: Additional Advertisement Display */}
      <section id="sec-14" className="py-4">
        <div className="container">
          <div className="wellness-card closing-banner p-4 p-md-5 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            {closingAdIsLocalized && closingAd?.imageUrl && (
              <img src={resolveMediaUrl(closingAd.imageUrl)} alt="" className="rounded-3 flex-shrink-0" style={{ maxHeight: '120px', maxWidth: '220px', objectFit: 'cover' }} />
            )}
            <div>
              <span className="section-eyebrow">{closingAdIsLocalized ? (closingAd?.eyebrow || t('continueJourney')) : t('continueJourney')}</span>
              <h2 className="h4 mb-2">{closingAdIsLocalized ? (closingAd?.title || t('wisdomShared')) : t('wisdomShared')}</h2>
              <p className="text-muted mb-0">{closingAdIsLocalized ? (closingAd?.body || t('explorePerspectives')) : t('explorePerspectives')}</p>
            </div>
            {closingAdIsLocalized && closingAd?.linkUrl
              ? <a href={closingAd.linkUrl} className="btn btn-outline-dark btn-sm">{closingAd.button_label || t('learnMore')}</a>
              : <Link to="/knowledge-centre" className="btn btn-outline-dark btn-sm">{t('learnMore')}</Link>}
          </div>
        </div>
      </section>
    </div>
  );
}
