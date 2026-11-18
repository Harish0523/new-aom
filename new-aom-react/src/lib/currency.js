export const DONATION_CURRENCIES = ['LKR', 'USD', 'GBP', 'EUR', 'CHF'];

// Donation targets are stored in LKR. These display rates can be adjusted from
// one place whenever the organisation updates its published exchange rates.
const LKR_RATES = {
  LKR: 1,
  USD: 1 / 300,
  GBP: 1 / 385,
  EUR: 1 / 325,
  CHF: 1 / 345,
};

const CURRENCY_LOCALES = {
  LKR: 'en-LK',
  USD: 'en-US',
  GBP: 'en-GB',
  EUR: 'de-DE',
  CHF: 'de-CH',
};

export function formatDonationAmount(amountInLkr, currency) {
  const amount = (Number(amountInLkr) || 0) * (LKR_RATES[currency] || 1);
  const fractionDigits = currency === 'LKR' ? 0 : 2;

  return new Intl.NumberFormat(CURRENCY_LOCALES[currency] || 'en-US', {
    style: 'currency',
    currency,
    minimumFractionDigits: fractionDigits,
    maximumFractionDigits: fractionDigits,
  }).format(amount);
}
