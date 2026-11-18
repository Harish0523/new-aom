import { useState } from 'react';
import { API_BASE } from '../lib/api';
import RecaptchaField from './RecaptchaField';

const ADMIN_BASE = new URL('../../admin/', `${API_BASE}/`).href.replace(/\/$/, '');

export default function DonationOtpModal({ project, onClose }) {
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [otpInput, setOtpInput] = useState('');
  const [otpId, setOtpId] = useState(null);
  const [otpSent, setOtpSent] = useState(false);
  const [verified, setVerified] = useState(false);
  const [bankInfo, setBankInfo] = useState(null);
  const [loading, setLoading] = useState(false);
  const [errorMsg, setErrorMsg] = useState('');
  const [devOtpHint, setDevOtpHint] = useState('');
  const [recaptchaToken, setRecaptchaToken] = useState('');

  if (!project) return null;

  const qrUrl = bankInfo?.qr_image_path ? `${ADMIN_BASE}/${bankInfo.qr_image_path}` : '';

  const handleSendOtp = async (e) => {
    e.preventDefault();
    setErrorMsg('');
    setLoading(true);

    try {
      const res = await fetch(`${API_BASE}/donation_send_otp.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          name,
          email,
          project: project.title,
          currency: project.currency,
          recaptchaToken,
        }),
      });
      const data = await res.json();

      if (!res.ok || data.status !== 'ok') {
        setErrorMsg(data.message || 'Unable to send OTP.');
      } else {
        setOtpId(data.otp_id);
        setOtpSent(true);
        if (data.dev_otp) {
          setDevOtpHint(data.dev_otp);
        }
      }
    } catch {
      setErrorMsg('Cannot connect to PHP backend. Ensure Apache/MySQL is running in XAMPP.');
    } finally {
      setLoading(false);
    }
  };

  const handleVerifyOtp = async (e) => {
    e.preventDefault();
    setErrorMsg('');
    setLoading(true);

    try {
      const res = await fetch(`${API_BASE}/donation_verify_otp.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          otp_id: otpId,
          otp: otpInput.trim(),
        }),
      });
      const data = await res.json();

      if (!res.ok || data.status !== 'verified') {
        setErrorMsg(data.message || 'Invalid OTP.');
      } else {
        setBankInfo(data.bank);
        setVerified(true);
      }
    } catch {
      setErrorMsg('Verification failed due to server connection error.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="modal fade show d-block" style={{ backgroundColor: 'rgba(0,0,0,0.55)' }}>
      <div className="modal-dialog modal-dialog-centered">
        <div className="modal-content rounded-4 border-0">
          <div className="modal-header">
            <h5 className="modal-title">{project.title} ({project.currency})</h5>
            <button type="button" className="btn-close" onClick={onClose} />
          </div>
          <div className="modal-body">
            {errorMsg && <div className="alert alert-danger small">{errorMsg}</div>}

            {!verified ? (
              <form onSubmit={otpSent ? handleVerifyOtp : handleSendOtp}>
                <div className="mb-3">
                  <label className="form-label small">Full Name</label>
                  <input
                    type="text"
                    className="form-control"
                    value={name}
                    onChange={(e) => setName(e.target.value)}
                    required
                    disabled={otpSent || loading}
                  />
                </div>
                <div className="mb-3">
                  <label className="form-label small">Email Address</label>
                  <input
                    type="email"
                    className="form-control"
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    required
                    disabled={otpSent || loading}
                  />
                </div>

                {otpSent && (
                  <div className="mb-3">
                    <label className="form-label small">Enter Email OTP</label>
                    <input
                      type="text"
                      className="form-control"
                      value={otpInput}
                      onChange={(e) => setOtpInput(e.target.value)}
                      required
                      disabled={loading}
                    />
                    {devOtpHint && (
                      <div className="form-text text-muted">
                        Local XAMPP Dev OTP: <strong>{devOtpHint}</strong>
                      </div>
                    )}
                  </div>
                )}

                {!otpSent && <RecaptchaField onChange={setRecaptchaToken} />}

                <button type="submit" className="btn btn-gold w-100" disabled={loading}>
                  {loading ? 'Please wait...' : otpSent ? 'Verify OTP' : 'Send Email OTP'}
                </button>
              </form>
            ) : (
              <div>
                <div className="alert alert-success small">
                  Email OTP verified. Please use the bank transfer details or QR code below directly.
                </div>
                {bankInfo && (
                  <>
                    <ul className="list-group list-group-flush small mb-3">
                      <li className="list-group-item">
                        <strong>Bank Name:</strong> {bankInfo.bank_name}
                      </li>
                      <li className="list-group-item">
                        <strong>Account Holder:</strong> {bankInfo.account_holder}
                      </li>
                      <li className="list-group-item">
                        <strong>Account Number:</strong> {bankInfo.account_number}
                      </li>
                      <li className="list-group-item">
                        <strong>Branch:</strong> {bankInfo.branch_name}
                      </li>
                      {bankInfo.swift_code && (
                        <li className="list-group-item">
                          <strong>SWIFT:</strong> {bankInfo.swift_code}
                        </li>
                      )}
                    </ul>
                    <div className="text-center p-3 border rounded-3 bg-light">
                      <p className="small text-muted mb-2">Donation QR Code</p>
                      <img
                        src={qrUrl}
                        alt="Donation QR Code"
                        className="img-fluid rounded"
                        style={{ maxHeight: '180px' }}
                        onError={(e) => {
                          e.currentTarget.style.display = 'none';
                          e.currentTarget.nextSibling.style.display = 'block';
                        }}
                      />
                      <div style={{ display: 'none' }}>
                        <i className="bi bi-qr-code fs-1" />
                        <p className="small text-muted mt-1 mb-0">
                          Upload QR image via Admin Dashboard ({bankInfo.qr_image_path})
                        </p>
                      </div>
                      {qrUrl && (
                        <a
                          className="btn btn-sm btn-outline-dark mt-3"
                          href={qrUrl}
                          download="new-aom-donation-qr"
                          target="_blank"
                          rel="noreferrer"
                        >
                          <i className="bi bi-download me-1" /> Download QR Code
                        </a>
                      )}
                    </div>
                  </>
                )}
              </div>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}