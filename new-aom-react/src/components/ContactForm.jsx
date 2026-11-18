import { useState } from 'react';
import { API_BASE } from '../lib/api';
import { useLanguage } from '../context/LanguageContext';
import RecaptchaField from './RecaptchaField';

const EMPTY_FORM = { name: '', email: '', phone: '', subject: '', message: '' };

export default function ContactForm({ compact = false }) {
  const { t } = useLanguage();
  const [form, setForm] = useState(EMPTY_FORM);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [done, setDone] = useState(false);
  const [recaptchaToken, setRecaptchaToken] = useState('');

  const submit = async (event) => {
    event.preventDefault();
    setLoading(true);
    setError('');
    try {
      const response = await fetch(`${API_BASE}/contact_submit.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ ...form, recaptchaToken }),
      });
      const result = await response.json();
      if (!response.ok || result.status !== 'ok') {
        setError(result.message || t('contactError'));
      } else {
        setDone(true);
        setForm(EMPTY_FORM);
      }
    } catch {
      setError('Cannot connect to the server. Please try again.');
    } finally {
      setLoading(false);
    }
  };

  if (done) {
    return <div className="alert alert-success mb-0">{t('contactSuccess')}</div>;
  }

  const update = (key) => (event) => setForm({ ...form, [key]: event.target.value });

  return (
    <form onSubmit={submit}>
      {error && <div className="alert alert-danger small">{error}</div>}
      <div className="row g-3">
        <div className="col-md-6">
          <label className="form-label small">{t('name')}</label>
          <input type="text" className="form-control" required value={form.name} onChange={update('name')} />
        </div>
        <div className="col-md-6">
          <label className="form-label small">{t('email')}</label>
          <input type="email" className="form-control" required value={form.email} onChange={update('email')} />
        </div>
        <div className="col-md-6">
          <label className="form-label small">{t('phone')}</label>
          <input type="tel" className="form-control" value={form.phone} onChange={update('phone')} />
        </div>
        <div className="col-md-6">
          <label className="form-label small">{t('subject')}</label>
          <input type="text" className="form-control" required value={form.subject} onChange={update('subject')} />
        </div>
        <div className="col-12">
          <label className="form-label small">{t('message')}</label>
          <textarea className="form-control" rows={compact ? 3 : 4} required value={form.message} onChange={update('message')} />
        </div>
        <div className="col-12">
          <RecaptchaField onChange={setRecaptchaToken} />
          <button type="submit" className="btn btn-gold" disabled={loading}>
            {loading ? t('sending') : t('submitMessage')}
          </button>
        </div>
      </div>
    </form>
  );
}
