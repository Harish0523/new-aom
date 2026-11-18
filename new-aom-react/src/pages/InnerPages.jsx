import { useEffect, useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import {
  SERVICES_DATA,
  ARTICLES_DATA,
  EVENTS_DATA,
  GALLERY_DATA,
  DONATION_PROJECTS,
} from '../data/siteData';
import DonationOtpModal from '../components/DonationOtpModal';
import { API_BASE, resolveMediaUrl } from '../lib/api';
import { useCmsCollection } from '../hooks/useCmsCollection';
import { useLanguage } from '../context/LanguageContext';
import CmsLink from '../components/CmsLink';
import RecaptchaField from '../components/RecaptchaField';
import { DONATION_CURRENCIES, formatDonationAmount } from '../lib/currency';

const ABOUT_FALLBACK = [{
  id: 'about',
  title: 'Wisdom, Wellness & Cultural Harmony',
  body: 'NEW AOM is dedicated to Spirituality and Wisdom, Meditation and AUM Chanting, Happiness and Personal Development, Yoga and Wellness, Cultural Heritage, Community Services, and Educational Knowledge Sharing.',
  vision: 'To inspire peaceful, joyful, and conscious living across global communities through timeless wisdom and holistic wellness.',
  mission: 'To provide accessible meditation, AUM chanting, yoga, cultural learning, and community support programmes in five languages.',
  values: 'Compassion, authenticity, inner stillness, cultural respect, and selfless community service.',
}];
const WISDOM_FALLBACK = [{
  id: 'spiritual-wisdom',
  title: 'Spiritual Wisdom',
  summary: 'Spiritual wisdom invites us to look beyond surface distractions and recognize the quiet awareness present within every breath. Through self-reflection, compassion, and disciplined practice, inner peace becomes a lived reality.',
}];

export function InnerPageView({ slug }) {
  const { id } = useParams();
  const { t, lang } = useLanguage();
  const services = useCmsCollection('services', SERVICES_DATA);
  const articles = useCmsCollection('knowledge', ARTICLES_DATA);
  const wisdomItems = useCmsCollection('spiritual-wisdom', WISDOM_FALLBACK);
  const gallery = useCmsCollection('gallery', GALLERY_DATA);
  const events = useCmsCollection('events', EVENTS_DATA);
  const donationProjects = useCmsCollection('crowdfunding', DONATION_PROJECTS);
  const aboutItems = useCmsCollection('about', ABOUT_FALLBACK);
  const settingsItems = useCmsCollection('settings', []);
  const about = aboutItems[0] || ABOUT_FALLBACK[0];
  const settings = settingsItems[0] || {};
  const aumContent = wisdomItems.find((item) => item.id === 'aum-chanting');

  // Gallery state
  const [galleryFilter, setGalleryFilter] = useState('');
  const [lightboxItem, setLightboxItem] = useState(null);

  // Knowledge Centre filter
  const [articleCategory, setArticleCategory] = useState('');

  // Online Booking Form state
  const [bookingForm, setBookingForm] = useState({
    fullName: '',
    email: '',
    phone: '',
    service: SERVICES_DATA[0].title,
    preferredDate: '',
    preferredTime: '',
    notes: '',
  });
  const [bookingSubmitted, setBookingSubmitted] = useState(false);
  const [bookingError, setBookingError] = useState('');
  const [bookingLoading, setBookingLoading] = useState(false);
  const [bookingRecaptcha, setBookingRecaptcha] = useState('');

  // Contact Form state
  const [contactForm, setContactForm] = useState({
    name: '',
    email: '',
    phone: '',
    subject: '',
    message: '',
  });
  const [contactSubmitted, setContactSubmitted] = useState(false);
  const [contactError, setContactError] = useState('');
  const [contactLoading, setContactLoading] = useState(false);
  const [contactRecaptcha, setContactRecaptcha] = useState('');

  // Donation currency & OTP modal state
  const [currency, setCurrency] = useState('LKR');
  const [selectedProject, setSelectedProject] = useState(null);

  useEffect(() => {
    if (!services.some((service) => service.title === bookingForm.service) && services[0]) {
      setBookingForm((current) => ({ ...current, service: services[0].title }));
    }
  }, [services, bookingForm.service]);

  const handleBookingSubmit = async (e) => {
    e.preventDefault();
    setBookingError('');
    setBookingLoading(true);
    try {
      const res = await fetch(`${API_BASE}/booking_submit.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ ...bookingForm, recaptchaToken: bookingRecaptcha }),
      });
      const data = await res.json();
      if (!res.ok || data.status !== 'ok') {
        setBookingError(data.message || 'Could not submit booking.');
      } else {
        setBookingSubmitted(true);
      }
    } catch {
      setBookingError('Server connection failed. Check XAMPP Apache/MySQL.');
    } finally {
      setBookingLoading(false);
    }
  };

  const handleContactSubmit = async (e) => {
    e.preventDefault();
    setContactError('');
    setContactLoading(true);
    try {
      const res = await fetch(`${API_BASE}/contact_submit.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ ...contactForm, recaptchaToken: contactRecaptcha }),
      });
      const data = await res.json();
      if (!res.ok || data.status !== 'ok') {
        setContactError(data.message || 'Could not send message.');
      } else {
        setContactSubmitted(true);
      }
    } catch {
      setContactError('Server connection failed. Check XAMPP Apache/MySQL.');
    } finally {
      setContactLoading(false);
    }
  };

  // 1. About Us (No Founder Profile allowed)
  if (slug === 'about') {
    return (
      <section className="py-5">
        <div className="container">
          <div className="wellness-card p-4 p-md-5">
            <span className="badge badge-natural-green mb-2">{t('aboutNewAom')}</span>
            <h1 className="fw-bold mb-3">{about.title}</h1>
            <p className="text-muted">
              {about.body}
            </p>
            <div className="row g-4 mt-2">
              <div className="col-md-4">
                <div className="p-3 rounded-3 bg-light h-100">
                  <h3 className="h5">{t('ourVision')}</h3>
                  <p className="small text-muted mb-0">
                    {about.vision}
                  </p>
                </div>
              </div>
              <div className="col-md-4">
                <div className="p-3 rounded-3 bg-light h-100">
                  <h3 className="h5">{t('ourMission')}</h3>
                  <p className="small text-muted mb-0">
                    {about.mission}
                  </p>
                </div>
              </div>
              <div className="col-md-4">
                <div className="p-3 rounded-3 bg-light h-100">
                  <h3 className="h5">{t('ourValues')}</h3>
                  <p className="small text-muted mb-0">
                    {about.values}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    );
  }

  // 2. Services
  if (slug === 'services') {
    return (
      <section className="py-5">
        <div className="container">
          <h1 className="fw-bold mb-4">{t('ourServices')}</h1>
          <div className="row g-4">
            {services.map((srv) => (
              <div key={srv.id} className="col-md-6 col-lg-4">
                <div className="wellness-card p-4 h-100 d-flex flex-column justify-content-between">
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
                    <i className={`bi ${srv.icon} fs-2 text-gold`} />
                    <h2 className="h5 mt-3">{srv.title}</h2>
                    <p className="small text-muted">{srv.shortDesc}</p>
                  </div>
                  <div className="d-flex gap-2 mt-3">
                    <Link to={`/services/${srv.id}`} className="btn btn-sm btn-outline-dark">{t('learnMore')}</Link>
                    <Link to="/booking" className="btn btn-sm btn-gold">{t('bookNow')}</Link>
                  </div>
                </div>
              </div>
            ))}
          </div>
        </div>
      </section>
    );
  }

  // 3. Individual Service Details
  if (slug === 'service-details') {
    const service = services.find((s) => s.id === id) || services[0];
    return (
      <section className="py-5">
        <div className="container">
          <div className="wellness-card p-4 p-md-5">
            {service.imageUrl && (
              <img
                src={resolveMediaUrl(service.imageUrl)}
                alt={service.title}
                className="img-fluid rounded-4 mb-4 w-100"
                style={{ maxHeight: '360px', objectFit: 'cover' }}
              />
            )}
            <i className={`bi ${service.icon} fs-1 text-gold`} />
            <h1 className="fw-bold mt-2 mb-3">{service.title}</h1>
            <p className="lead text-muted">{service.shortDesc}</p>
            <p>{service.fullDesc}</p>
            <div className="d-flex flex-wrap gap-3 my-4 small">
              <span className="badge badge-natural-green px-3 py-2">{t('duration')}: {service.duration}</span>
              <span className="badge bg-light text-dark border px-3 py-2">{t('mode')}: {service.mode}</span>
            </div>
            <div className="d-flex gap-2">
              <Link to="/booking" className="btn btn-gold">{t('bookThisService')}</Link>
              <Link to="/services" className="btn btn-outline-secondary">{t('allServices')}</Link>
            </div>
          </div>
        </div>
      </section>
    );
  }

  // 4. Knowledge Centre
  if (slug === 'knowledge-centre') {
    const categories = [...new Set(articles.map((a) => a.category).filter(Boolean))];
    const filtered = articleCategory === ''
      ? articles
      : articles.filter((a) => a.category === articleCategory);

    return (
      <section className="py-5">
        <div className="container">
          <h1 className="fw-bold mb-3">{t('knowledgeHeading')}</h1>
          <div className="d-flex flex-wrap gap-2 mb-4">
            {[{ value: '', label: t('all') }, ...categories.map((cat) => ({ value: cat, label: cat }))].map(({ value, label }) => (
              <button
                key={value || 'all'}
                type="button"
                className={`btn btn-sm ${articleCategory === value ? 'btn-dark' : 'btn-outline-dark'}`}
                onClick={() => setArticleCategory(value)}
              >
                {label}
              </button>
            ))}
          </div>
          <div className="row g-4">
            {filtered.map((art) => (
              <div key={art.id} className="col-md-6">
                <div className="wellness-card p-4 h-100 d-flex flex-column justify-content-between">
                  <div>
                    {art.imageUrl && (
                      <img
                        src={resolveMediaUrl(art.imageUrl)}
                        alt={art.title}
                        className="img-fluid rounded-3 mb-3 w-100"
                        style={{ maxHeight: '170px', objectFit: 'cover' }}
                        loading="lazy"
                      />
                    )}
                    <span className="badge badge-natural-green mb-2">{art.category}</span>
                    <h2 className="h5">{art.title}</h2>
                    <p className="small text-muted">{art.summary}</p>
                  </div>
                  <Link to={`/articles/${art.id}`} className="btn btn-sm btn-outline-dark align-self-start">
                    {t('readArticle')}
                  </Link>
                </div>
              </div>
            ))}
          </div>
        </div>
      </section>
    );
  }

  // 5. Individual Articles
  if (slug === 'article-details') {
    const article = articles.find((a) => a.id === id) || articles[0];
    return (
      <section className="py-5">
        <div className="container">
          <article className="wellness-card p-4 p-md-5">
            <span className="badge badge-natural-green mb-2">{article.category}</span>
            <h1 className="fw-bold mb-2">{article.title}</h1>
            <p className="small text-muted mb-4">{t('published')}: {article.date}</p>
            {article.imageUrl && (
              <img
                src={resolveMediaUrl(article.imageUrl)}
                alt={article.title}
                className="img-fluid rounded-4 mb-4 w-100"
                style={{ maxHeight: '360px', objectFit: 'cover' }}
              />
            )}
            <p className="lead">{article.summary}</p>
            <p className="text-muted">{article.content}</p>
            <Link to="/knowledge-centre" className="btn btn-sm btn-outline-dark mt-3">{t('backToKnowledge')}</Link>
          </article>
        </div>
      </section>
    );
  }

  // 6. Spiritual Wisdom
  if (slug === 'spiritual-wisdom') {
    return (
      <section className="py-5">
        <div className="container">
          <div className="row g-4">
            {wisdomItems.map((item) => (
              <article key={item.id} className="col-12">
                <div className="wellness-card p-4 p-md-5">
                  <span className="badge badge-natural-green mb-2">{t('spiritualWisdom')}</span>
                  <h1 className="fw-bold mb-3">{item.locale === undefined || item.locale === lang ? item.title : t('spiritualWisdom')}</h1>
                  <p className="text-muted">{item.summary}</p>
                  {item.content && <p className="mb-0">{item.content}</p>}
                </div>
              </article>
            ))}
          </div>
          <div className="mt-4">
            <Link to="/knowledge-centre" className="btn btn-gold btn-sm">{t('exploreWisdom')}</Link>
          </div>
        </div>
      </section>
    );
  }

  // 7. AUM Chanting
  if (slug === 'aum-chanting') {
    return (
      <section className="py-5">
        <div className="container">
          <div className="wellness-card p-4 p-md-5">
            <span className="badge badge-natural-green mb-2">{t('sacredSound')}</span>
            <h1 className="fw-bold mb-3">{aumContent?.title || t('aumPractice')}</h1>
            <p className="text-muted">
              {aumContent?.summary || t('aumIntro')}
            </p>
            {aumContent?.content
              ? <p className="small text-muted mb-4">{aumContent.content}</p>
              : (
                <ul className="small text-muted mb-4">
                  <li>{t('aumStepOne')}</li>
                  <li>{t('aumStepTwo')}</li>
                  <li>{t('aumStepThree')}</li>
                </ul>
              )}
            <Link to="/booking" className="btn btn-gold">{t('joinAumSession')}</Link>
          </div>
        </div>
      </section>
    );
  }

  // 8. Gallery (Category filters, Image Lightbox & Video Player)
  if (slug === 'gallery') {
    const categories = [...new Set(gallery.map((item) => item.category).filter(Boolean))];
    const cats = [{ value: '', label: t('all') }, ...categories.map((category) => ({ value: category, label: category }))];
    const items = galleryFilter === ''
      ? gallery
      : gallery.filter((g) => g.category === galleryFilter);

    return (
      <section className="py-5">
        <div className="container">
          <h1 className="fw-bold mb-3">{t('photoVideoGallery')}</h1>
          <div className="d-flex flex-wrap gap-2 mb-4">
            {cats.map(({ value, label }) => (
              <button
                key={value || 'all'}
                type="button"
                className={`btn btn-sm ${galleryFilter === value ? 'btn-dark' : 'btn-outline-dark'}`}
                onClick={() => setGalleryFilter(value)}
              >
                {label}
              </button>
            ))}
          </div>
          <div className="row g-4">
            {items.map((item) => (
              <div key={item.id} className="col-md-6 col-lg-4">
                <div
                  className="wellness-card p-4 h-100 text-center"
                  role="button"
                  tabIndex={0}
                  onClick={() => setLightboxItem(item)}
                  onKeyDown={(event) => event.key === 'Enter' && setLightboxItem(item)}
                >
                  <div className="py-2 bg-light rounded-3 mb-3 d-flex align-items-center justify-content-center" style={{ minHeight: '180px', overflow: 'hidden' }}>
                    {item.mediaUrl && item.type === 'video' ? (
                      <video
                        className="img-fluid rounded-3 w-100"
                        controls
                        autoPlay
                        muted
                        loop
                        playsInline
                        preload="metadata"
                        poster={item.poster ? resolveMediaUrl(item.poster) : undefined}
                        src={resolveMediaUrl(item.mediaUrl)}
                        style={{ maxHeight: '200px', objectFit: 'cover' }}
                      >
                        {t('browserVideoFallback')}
                      </video>
                    ) : item.mediaUrl ? (
                      <img
                        className="img-fluid rounded-3 w-100"
                        src={resolveMediaUrl(item.mediaUrl)}
                        alt={item.title}
                        loading="lazy"
                        style={{ maxHeight: '200px', objectFit: 'cover' }}
                      />
                    ) : (
                      <i className={`bi ${item.icon || 'bi-image'} fs-1 text-gold`} />
                    )}
                  </div>
                  <span className="badge badge-natural-green mb-2">
                    {item.type === 'video' ? t('video') : t('photo')} • {item.category}
                  </span>
                  <h2 className="h6 mb-0">{item.title}</h2>
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
                      <video
                        className="img-fluid rounded-3"
                        controls
                        autoPlay
                        loop
                        poster={lightboxItem.poster ? resolveMediaUrl(lightboxItem.poster) : undefined}
                        src={resolveMediaUrl(lightboxItem.mediaUrl)}
                      >
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
    );
  }

  // 9. Events
  if (slug === 'events') {
    return (
      <section className="py-5">
        <div className="container">
          <h1 className="fw-bold mb-4">{t('workshopsEvents')}</h1>
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
                  <span className="badge badge-natural-green mb-2">{ev.date} • {ev.time}</span>
                  <h2 className="h5">{ev.title}</h2>
                  <p className="small text-muted mb-2"><i className="bi bi-geo-alt me-1" />{ev.location}</p>
                  <p className="small text-muted">{ev.description}</p>
                  <div className="d-flex gap-2">
                    <Link to={`/events/${ev.id}`} className="btn btn-sm btn-outline-dark">{t('eventDetailsAction')}</Link>
                    <CmsLink to={ev.bookingUrl || '/booking'} className="btn btn-sm btn-gold">{t('registerNow')}</CmsLink>
                  </div>
                </div>
              </div>
            ))}
          </div>
        </div>
      </section>
    );
  }

  // 10. Event Details
  if (slug === 'event-details') {
    const event = events.find((e) => e.id === id) || events[0];
    return (
      <section className="py-5">
        <div className="container">
          <div className="wellness-card p-4 p-md-5">
            {event.imageUrl && (
              <img
                src={resolveMediaUrl(event.imageUrl)}
                alt={event.title}
                className="img-fluid rounded-4 mb-4 w-100"
                style={{ maxHeight: '360px', objectFit: 'cover' }}
              />
            )}
            <span className="badge badge-natural-green mb-2">{event.date} • {event.time}</span>
            <h1 className="fw-bold mb-2">{event.title}</h1>
            <p className="text-muted small mb-3"><i className="bi bi-geo-alt me-1" />{event.location}</p>
            <p>{event.description}</p>
            <CmsLink to={event.bookingUrl || '/booking'} className="btn btn-gold">{t('registerNow')}</CmsLink>
          </div>
        </div>
      </section>
    );
  }

  // 11. Online Booking (Form + WhatsApp integration + PHP API)
  if (slug === 'booking') {
    const whatsappText = encodeURIComponent(
      `Hello NEW AOM, I would like to book:\nName: ${bookingForm.fullName}\nService: ${bookingForm.service}\nDate: ${bookingForm.preferredDate}\nTime: ${bookingForm.preferredTime}`
    );
    const whatsappDigits = String(settings.whatsappNumber || '').replace(/\D/g, '');
    const whatsappHref = `https://wa.me/${whatsappDigits}?text=${whatsappText}`;

    return (
      <section className="py-5">
        <div className="container">
          <div className="wellness-card p-4 p-md-5 mx-auto" style={{ maxWidth: '720px' }}>
            <h1 className="fw-bold mb-2">{t('bookingHeading')}</h1>
            <p className="text-muted small mb-4">{t('bookingDescription')}</p>

            {bookingSubmitted ? (
              <div className="alert alert-success">
                <p className="mb-2">{t('bookingSuccess')} {whatsappDigits ? t('whatsappDirect') : ''}</p>
                {whatsappDigits && (
                  <a
                    href={whatsappHref}
                    target="_blank"
                    rel="noreferrer"
                    className="btn btn-success btn-sm"
                  >
                    <i className="bi bi-whatsapp me-1" /> {t('continueOnWhatsapp')}
                  </a>
                )}
              </div>
            ) : (
              <form onSubmit={handleBookingSubmit}>
                {bookingError && <div className="alert alert-danger small">{bookingError}</div>}
                <div className="row g-3">
                  <div className="col-md-6">
                    <label className="form-label small">{t('fullName')}</label>
                    <input
                      type="text"
                      className="form-control"
                      required
                      value={bookingForm.fullName}
                      onChange={(e) => setBookingForm({ ...bookingForm, fullName: e.target.value })}
                    />
                  </div>
                  <div className="col-md-6">
                    <label className="form-label small">{t('emailAddress')}</label>
                    <input
                      type="email"
                      className="form-control"
                      required
                      value={bookingForm.email}
                      onChange={(e) => setBookingForm({ ...bookingForm, email: e.target.value })}
                    />
                  </div>
                  <div className="col-md-6">
                    <label className="form-label small">{t('phoneWhatsapp')}</label>
                    <input
                      type="tel"
                      className="form-control"
                      required
                      value={bookingForm.phone}
                      onChange={(e) => setBookingForm({ ...bookingForm, phone: e.target.value })}
                    />
                  </div>
                  <div className="col-md-6">
                    <label className="form-label small">{t('selectService')}</label>
                    <select
                      className="form-select"
                      value={bookingForm.service}
                      onChange={(e) => setBookingForm({ ...bookingForm, service: e.target.value })}
                    >
                      {services.map((s) => (
                        <option key={s.id} value={s.title}>{s.title}</option>
                      ))}
                    </select>
                  </div>
                  <div className="col-md-6">
                    <label className="form-label small">{t('preferredDate')}</label>
                    <input
                      type="date"
                      className="form-control"
                      required
                      value={bookingForm.preferredDate}
                      onChange={(e) => setBookingForm({ ...bookingForm, preferredDate: e.target.value })}
                    />
                  </div>
                  <div className="col-md-6">
                    <label className="form-label small">{t('preferredTime')}</label>
                    <input
                      type="time"
                      className="form-control"
                      required
                      value={bookingForm.preferredTime}
                      onChange={(e) => setBookingForm({ ...bookingForm, preferredTime: e.target.value })}
                    />
                  </div>
                  <div className="col-12">
                    <label className="form-label small">{t('optionalNotes')}</label>
                    <textarea
                      className="form-control"
                      rows="3"
                      value={bookingForm.notes}
                      onChange={(e) => setBookingForm({ ...bookingForm, notes: e.target.value })}
                    />
                  </div>
                  <div className="col-12">
                    <RecaptchaField onChange={setBookingRecaptcha} />
                  </div>
                  <div className="col-12 d-flex flex-wrap gap-2">
                    <button type="submit" className="btn btn-gold" disabled={bookingLoading}>
                      {bookingLoading ? t('sending') : t('submitBooking')}
                    </button>
                    {whatsappDigits && (
                      <a
                        href={whatsappHref}
                        target="_blank"
                        rel="noreferrer"
                        className="btn btn-outline-success"
                      >
                        <i className="bi bi-whatsapp me-1" /> {t('whatsappDirect')}
                      </a>
                    )}
                  </div>
                </div>
              </form>
            )}
          </div>
        </div>
      </section>
    );
  }

  // 12 & 13. Donation Projects & Donation Details (Email OTP -> Bank & QR only)
  if (slug === 'donations' || slug === 'donation-details') {
    const list = slug === 'donation-details'
      ? [donationProjects.find((p) => p.id === id) || donationProjects[0]]
      : donationProjects;

    return (
      <section className="py-5">
        <div className="container">
          <div className="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
            <h1 className="fw-bold mb-0">{t('donationHeading')}</h1>
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
            {list.map((proj) => {
              const pct = Math.min(100, Math.round((proj.raised / proj.target) * 100));
              return (
                <div key={proj.id} className="col-md-6">
                  <div className="wellness-card p-4 h-100">
                    {proj.imageUrl && (
                      <img
                        src={resolveMediaUrl(proj.imageUrl)}
                        alt={proj.title}
                        className="img-fluid rounded-3 mb-3 w-100"
                        style={{ maxHeight: '190px', objectFit: 'cover' }}
                        loading="lazy"
                      />
                    )}
                    <h2 className="h5">{proj.title}</h2>
                    <p className="small text-muted">{proj.description}</p>
                    <div className="progress mb-2" style={{ height: '10px' }}>
                      <div className="progress-bar bg-success" style={{ width: `${pct}%` }} />
                    </div>
                    <div className="d-flex justify-content-between small text-muted mb-3">
                      <span>{t('raised')}: {formatDonationAmount(proj.raised, currency)}</span>
                      <span>{t('target')}: {formatDonationAmount(proj.target, currency)}</span>
                    </div>
                    {proj.endDate && (
                      <p className="small text-muted mb-3">{t('endsOn')}: {proj.endDate}</p>
                    )}
                    <div className="d-flex gap-2">
                      <button
                        type="button"
                        className="btn btn-gold btn-sm"
                        onClick={() => setSelectedProject({ title: proj.title, currency })}
                      >
                        {t('donateNow')}
                      </button>
                      {slug === 'donations' && (
                        <Link to={`/donations/${proj.id}`} className="btn btn-outline-dark btn-sm">
                          {t('details')}
                        </Link>
                      )}
                    </div>
                  </div>
                </div>
              );
            })}
          </div>

          <DonationOtpModal project={selectedProject} onClose={() => setSelectedProject(null)} />
        </div>
      </section>
    );
  }

  // 14. Contact Us (Form + Google Maps + PHP API)
  if (slug === 'contact') {
    return (
      <section className="py-5">
        <div className="container">
          <h1 className="fw-bold mb-4">{t('contactHeading')}</h1>
          <div className="row g-4">
            <div className="col-lg-6">
              <div className="wellness-card p-4">
                {contactSubmitted ? (
                  <div className="alert alert-success mb-0">
                    {t('contactSuccess')}
                  </div>
                ) : (
                  <form onSubmit={handleContactSubmit}>
                    {contactError && <div className="alert alert-danger small">{contactError}</div>}
                    <div className="mb-3">
                      <label className="form-label small">{t('name')}</label>
                      <input
                        type="text"
                        className="form-control"
                        required
                        value={contactForm.name}
                        onChange={(e) => setContactForm({ ...contactForm, name: e.target.value })}
                      />
                    </div>
                    <div className="mb-3">
                      <label className="form-label small">{t('email')}</label>
                      <input
                        type="email"
                        className="form-control"
                        required
                        value={contactForm.email}
                        onChange={(e) => setContactForm({ ...contactForm, email: e.target.value })}
                      />
                    </div>
                    <div className="mb-3">
                      <label className="form-label small">{t('phone')}</label>
                      <input
                        type="tel"
                        className="form-control"
                        value={contactForm.phone}
                        onChange={(e) => setContactForm({ ...contactForm, phone: e.target.value })}
                      />
                    </div>
                    <div className="mb-3">
                      <label className="form-label small">{t('subject')}</label>
                      <input
                        type="text"
                        className="form-control"
                        required
                        value={contactForm.subject}
                        onChange={(e) => setContactForm({ ...contactForm, subject: e.target.value })}
                      />
                    </div>
                    <div className="mb-3">
                      <label className="form-label small">{t('message')}</label>
                      <textarea
                        className="form-control"
                        rows="4"
                        required
                        value={contactForm.message}
                        onChange={(e) => setContactForm({ ...contactForm, message: e.target.value })}
                      />
                    </div>
                    <RecaptchaField onChange={setContactRecaptcha} />
                    <button type="submit" className="btn btn-gold" disabled={contactLoading}>
                      {contactLoading ? t('sending') : t('submitMessage')}
                    </button>
                  </form>
                )}
              </div>
            </div>

            <div className="col-lg-6">
              <div className="wellness-card p-4 h-100">
                <h2 className="h5 mb-3">{t('location')}</h2>
                <p className="small text-muted mb-3">
                {settings.address || t('contactDescription')}
                </p>
                {settings.phone && <p className="small mb-2"><a href={`tel:${settings.phone}`}>{settings.phone}</a></p>}
                {settings.contactEmail && <p className="small mb-3"><a href={`mailto:${settings.contactEmail}`}>{settings.contactEmail}</a></p>}
                <div className="ratio ratio-16x9 rounded-3 overflow-hidden border">
                  <iframe
                    title="NEW AOM Location"
                  src={settings.mapEmbedUrl || 'https://maps.google.com/maps?q=Colombo%20Sri%20Lanka&t=&z=12&ie=UTF8&iwloc=&output=embed'}
                    loading="lazy"
                  />
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    );
  }

  // 15. Privacy Policy
  if (slug === 'privacy-policy') {
    return (
      <section className="py-5">
        <div className="container">
          <div className="wellness-card p-4 p-md-5">
            <h1 className="fw-bold mb-3">{t('privacyPolicy')}</h1>
            <p className="text-muted small">
              NEW AOM respects your privacy. Personal details submitted through booking, contact, or donation OTP verification forms are used solely for communication, verification, and service coordination, and are never sold to third parties.
            </p>
          </div>
        </div>
      </section>
    );
  }

  // 16. Terms & Conditions
  return (
    <section className="py-5">
      <div className="container">
        <div className="wellness-card p-4 p-md-5">
          <h1 className="fw-bold mb-3">{t('termsConditions')}</h1>
          <p className="text-muted small">
            All educational materials, meditation guidance, and wellness programmes provided by NEW AOM are intended for personal growth and holistic wellbeing. Voluntary donations are made directly via bank transfer or QR code after email OTP verification.
          </p>
        </div>
      </div>
    </section>
  );
}

export function NotFoundPage() {
  const { t } = useLanguage();
  return (
    <section className="py-5 text-center">
      <div className="container py-4">
        <div className="wellness-card p-5 mx-auto" style={{ maxWidth: '540px' }}>
          <h1 className="display-5 fw-bold">404</h1>
          <p className="text-muted mb-4">{t('notFound')}</p>
          <Link to="/" className="btn btn-gold">{t('returnHome')}</Link>
        </div>
      </div>
    </section>
  );
}
