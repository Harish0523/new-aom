import { createContext, useContext, useState, useEffect } from 'react';
import { fetchCmsContent } from '../lib/api';
import { COMMON_TRANSLATIONS } from '../data/commonTranslations';

export const LANGUAGES = [
  { code: 'en', label: 'English', short: 'EN' },
  { code: 'ta', label: 'தமிழ் (Tamil)', short: 'TA' },
  { code: 'fr', label: 'Français (French)', short: 'FR' },
  { code: 'zh', label: '简体中文 (Chinese)', short: 'ZH' },
  { code: 'de-CH', label: 'Deutsch (Swiss German)', short: 'CH' },
];

export const TRANSLATIONS = {
  en: {
    brand: 'NEW AOM',
    tagline: 'Spirituality, Wisdom, Meditation & Cultural Harmony',
    navHome: 'Home',
    navAbout: 'About',
    navServices: 'Services',
    navKnowledge: 'Knowledge Centre',
    navGallery: 'Gallery',
    navEvents: 'Events',
    navBooking: 'Booking',
    navDonations: 'Donations',
    navContact: 'Contact',
    heroTitle: 'Discover the Wisdom Within',
    heroSubtitle:
      'Meditation, AUM Chanting, Yoga, Personal Development, Cultural Heritage, and Community Services.',
    ctaBook: 'Book a Session',
    ctaDonate: 'Donate Now',
    ctaExplore: 'Explore Our Services',
    ctaDiscover: 'Discover Knowledge',
    donationHeading: 'Together, We Can Make a Difference.',
    donationOtpNote: 'Verify your email with a one-time OTP to view bank transfer details and QR code.',
    bookingHeading: 'Online Booking',
    contactHeading: 'Contact Us and Location',
  },
  ta: {
    brand: 'நியூ ஓம் (NEW AOM)',
    tagline: 'ஆன்மீகம், ஞானம், தியானம் மற்றும் பண்பாட்டு நல்லிணக்கம்',
    navHome: 'முகப்பு',
    navAbout: 'எங்களைப் பற்றி',
    navServices: 'சேவைகள்',
    navKnowledge: 'அறிவு மையம்',
    navGallery: 'புகைப்பட & காணொளி',
    navEvents: 'நிகழ்வுகள்',
    navBooking: 'முன்பதிவு',
    navDonations: 'நன்கொடை',
    navContact: 'தொடர்பு கொள்ள',
    heroTitle: 'உள்ளார்ந்த அமைதியையும் காலத்தால் அழியாத ஞானத்தையும் உணருங்கள்',
    heroSubtitle:
      'தியானம், ஓம் மந்திர உச்சாடனம், யோகா, ஆளுமை வளர்ச்சி, பண்பாட்டு மரபு மற்றும் சமூக நலப் பணிகள்.',
    ctaBook: 'முன்பதிவு செய்க',
    ctaDonate: 'நன்கொடை வழங்குக',
    ctaExplore: 'எங்கள் சேவைகளைக் காண்க',
    ctaDiscover: 'ஞானத்தைக் கண்டறியுங்கள்',
    donationHeading: 'ஒன்றிணைந்து மாற்றத்தை உருவாக்குவோம்.',
    donationOtpNote: 'வங்கிக் கணக்கு விவரங்கள் மற்றும் QR குறியீட்டைப் பார்க்க உங்கள் மின்னஞ்சல் OTP-ஐச் சரிபார்க்கவும்.',
    bookingHeading: 'இணையவழி முன்பதிவு',
    contactHeading: 'தொடர்பு கொள்ள மற்றும் முகவரி',
  },
  fr: {
    brand: 'NEW AOM',
    tagline: 'Spiritualité, Sagesse, Méditation & Harmonie Culturelle',
    navHome: 'Accueil',
    navAbout: 'À propos',
    navServices: 'Services',
    navKnowledge: 'Centre de Savoir',
    navGallery: 'Galerie',
    navEvents: 'Événements',
    navBooking: 'Réservation',
    navDonations: 'Dons',
    navContact: 'Contact',
    heroTitle: 'Éveillez le Calme Intérieur et la Sagesse Éternelle',
    heroSubtitle:
      'Méditation, Chant AUM, Yoga, Développement Personnel, Héritage Culturel et Services Communautaires.',
    ctaBook: 'Réserver une séance',
    ctaDonate: 'Faire un don',
    ctaExplore: 'Découvrir nos services',
    ctaDiscover: 'Découvrir le savoir',
    donationHeading: 'Ensemble, faisons la différence.',
    donationOtpNote: 'Vérifiez votre e-mail par OTP pour afficher les coordonnées bancaires et le QR code.',
    bookingHeading: 'Réservation en ligne',
    contactHeading: 'Contactez-nous et localisation',
  },
  zh: {
    brand: 'NEW AOM',
    tagline: '灵性、智慧、冥想与文化和谐',
    navHome: '首页',
    navAbout: '关于我们',
    navServices: '服务项目',
    navKnowledge: '知识中心',
    navGallery: '图库与视频',
    navEvents: '活动课程',
    navBooking: '在线预约',
    navDonations: '爱心捐赠',
    navContact: '联系我们',
    heroTitle: '唤醒内在宁静与永恒智慧',
    heroSubtitle:
      '冥想、AUM唱诵、瑜伽、个人成长、文化遗产与社区服务。',
    ctaBook: '立即预约',
    ctaDonate: '立即捐赠',
    ctaExplore: '浏览我们的服务',
    ctaDiscover: '探索知识',
    donationHeading: '携手同行，共创美好改变。',
    donationOtpNote: '请通过邮箱一次性验证码（OTP）验证后查看银行转账信息与二维码。',
    bookingHeading: '在线预约',
    contactHeading: '联系我们与地址',
  },
  'de-CH': {
    brand: 'NEW AOM',
    tagline: 'Spiritualität, Weisheit, Meditation & Kulturelle Harmonie',
    navHome: 'Startseite',
    navAbout: 'Über uns',
    navServices: 'Angebote',
    navKnowledge: 'Wissenszentrum',
    navGallery: 'Galerie',
    navEvents: 'Anlässe',
    navBooking: 'Buchung',
    navDonations: 'Spenden',
    navContact: 'Kontakt',
    heroTitle: 'Innere Ruhe und zeitlose Weisheit entdecken',
    heroSubtitle:
      'Meditation, AUM-Chanting, Yoga, Persönlichkeitsentwicklung, Kulturerbe und Gemeinschaftsdienste.',
    ctaBook: 'Termin buchen',
    ctaDonate: 'Jetzt spenden',
    ctaExplore: 'Unsere Angebote ansehen',
    ctaDiscover: 'Wissen entdecken',
    donationHeading: 'Gemeinsam können wir etwas bewirken.',
    donationOtpNote: 'Bestätigen Sie Ihre E-Mail per OTP, um Bankdaten und QR-Code anzuzeigen.',
    bookingHeading: 'Online-Buchung',
    contactHeading: 'Kontakt & Standort',
  },
};

const LanguageContext = createContext(null);

export function LanguageProvider({ children }) {
  const [lang, setLang] = useState(() => {
    try {
      const saved = localStorage.getItem('new_aom_lang');
      return LANGUAGES.some((language) => language.code === saved) ? saved : 'en';
    } catch {
      return 'en';
    }
  });
  const [cmsTranslations, setCmsTranslations] = useState({});

  useEffect(() => {
    try {
      localStorage.setItem('new_aom_lang', lang);
      document.documentElement.lang = lang;
    } catch {
      // ignore storage errors
    }
  }, [lang]);

  useEffect(() => {
    let active = true;
    fetchCmsContent(lang)
      .then((collections) => {
        if (!active) return;
        const translations = {};
        for (const item of collections.translations || []) {
          if (item.locale !== lang) continue;
          const key = item.data?.translationKey || item.id;
          if (key && typeof item.data?.text === 'string') {
            translations[key] = item.data.text;
          }
        }
        setCmsTranslations(translations);
      })
      .catch(() => {
        if (active) {
          setCmsTranslations({});
        }
      });
    return () => {
      active = false;
    };
  }, [lang]);

  const dict = {
    ...(COMMON_TRANSLATIONS.en || {}),
    ...(TRANSLATIONS.en || {}),
    ...(COMMON_TRANSLATIONS[lang] || {}),
    ...(TRANSLATIONS[lang] || {}),
  };
  const t = (key) => cmsTranslations[key] ?? dict[key] ?? TRANSLATIONS.en[key] ?? key;

  return (
    <LanguageContext.Provider value={{ lang, setLang, t, languages: LANGUAGES }}>
      {children}
    </LanguageContext.Provider>
  );
}

export function useLanguage() {
  const ctx = useContext(LanguageContext);
  if (!ctx) {
    throw new Error('useLanguage must be used within LanguageProvider');
  }
  return ctx;
}
