import { Link } from 'react-router-dom';
import { useLanguage } from '../context/LanguageContext';
import { useCmsCollection } from '../hooks/useCmsCollection';
import { resolveMediaUrl } from '../lib/api';

export default function Footer() {
  const { t } = useLanguage();
  const settingsItems = useCmsCollection('settings', []);
  const settings = settingsItems[0] || {};
  const socialLinks = (settings.socialLinks || '').split(/\r?\n/).filter((url) => /^https:\/\/\S+$/i.test(url));

  return (
    <footer id="sec-15" className="bg-navy text-white py-5 mt-5">
      <div className="container">
        <div className="row g-4">
          <div className="col-lg-4">
            <Link to="/" className="footer-logo-link d-inline-block mb-3" aria-label={settings.siteTitle || 'NEW AOM'}>
              {settings.logoUrl
                ? <img className="footer-logo" src={resolveMediaUrl(settings.logoUrl)} alt={settings.siteTitle || 'NEW AOM'} />
                : <img className="footer-logo" src="/aom-logo-light.svg" alt={settings.siteTitle || 'NEW AOM'} />}
            </Link>
            <p className="small text-white-50">
              {settings.siteDescription || t('siteSummary')}
            </p>
          </div>

          <div className="col-6 col-lg-2">
            <h6 className="text-gold fw-semibold">{t('quickLinks')}</h6>
            <ul className="list-unstyled small">
              <li><Link className="text-white-50 text-decoration-none" to="/about">{t('navAbout')}</Link></li>
              <li><Link className="text-white-50 text-decoration-none" to="/services">{t('navServices')}</Link></li>
              <li><Link className="text-white-50 text-decoration-none" to="/events">{t('navEvents')}</Link></li>
              <li><Link className="text-white-50 text-decoration-none" to="/booking">{t('navBooking')}</Link></li>
            </ul>
          </div>

          <div className="col-6 col-lg-3">
            <h6 className="text-gold fw-semibold">{t('navKnowledge')}</h6>
            <ul className="list-unstyled small">
              <li><Link className="text-white-50 text-decoration-none" to="/knowledge-centre">{t('navKnowledge')}</Link></li>
              <li><Link className="text-white-50 text-decoration-none" to="/spiritual-wisdom">{t('spiritualWisdom')}</Link></li>
              <li><Link className="text-white-50 text-decoration-none" to="/aum-chanting">{t('aumPractice')}</Link></li>
              <li><Link className="text-white-50 text-decoration-none" to="/donations">{t('navDonations')}</Link></li>
            </ul>
          </div>

          <div className="col-lg-3">
            <h6 className="text-gold fw-semibold">{t('navContact')}</h6>
            <p className="small text-white-50 mb-2">
              <Link className="text-white-50 text-decoration-none" to="/contact">{t('navContact')}</Link>
            </p>
            {settings.contactEmail && <p className="small"><a className="text-white-50" href={`mailto:${settings.contactEmail}`}>{settings.contactEmail}</a></p>}
            {socialLinks.length > 0 && <div className="d-flex gap-3 mb-3">{socialLinks.map((url) => <a key={url} className="text-white-50 small" href={url} target="_blank" rel="noreferrer">{new URL(url).hostname}</a>)}</div>}
            <ul className="list-unstyled small">
              <li><Link className="text-white-50 text-decoration-none" to="/privacy-policy">{t('privacyPolicy')}</Link></li>
              <li><Link className="text-white-50 text-decoration-none" to="/terms">{t('termsConditions')}</Link></li>
            </ul>
          </div>
        </div>

        <hr className="border-secondary my-4" />
        <p className="small text-white-50 text-center mb-0">
          © {new Date().getFullYear()} NEW AOM. {t('allRightsReserved')}
        </p>
      </div>
    </footer>
  );
}
