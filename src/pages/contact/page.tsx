import { FormEvent, useState } from 'react';

type FormState = {
  name: string;
  phone: string;
  email: string;
  message: string;
};

const initialForm: FormState = {
  name: '',
  phone: '',
  email: '',
  message: '',
};

export default function ContactPage() {
  const [formData, setFormData] = useState<FormState>(initialForm);
  const [status, setStatus] = useState<'idle' | 'submitting' | 'success' | 'error'>('idle');

  const handleSubmit = async (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    setStatus('submitting');

    try {
      await new Promise((resolve) => setTimeout(resolve, 1000));
      setStatus('success');
      setFormData(initialForm);
    } catch {
      setStatus('error');
    }
  };

  return (
    <div className="grid gap-8 lg:grid-cols-2">
      <section className="rounded-2xl bg-white p-8 shadow-sm">
        <h1 className="text-3xl font-bold text-slate-900">Contact Us</h1>
        <p className="mt-3 text-slate-600">
          Tell us your requirements and our team will get back to you shortly.
        </p>

        <form onSubmit={handleSubmit} className="mt-6 space-y-4">
          <div>
            <label htmlFor="name" className="mb-1 block text-sm font-medium text-slate-700">
              Full Name
            </label>
            <input
              id="name"
              type="text"
              required
              value={formData.name}
              onChange={(event) => setFormData((prev) => ({ ...prev, name: event.target.value }))}
              className="w-full rounded-md border border-slate-300 px-4 py-2 text-slate-900 outline-none ring-brand-500 focus:ring"
            />
          </div>
          <div>
            <label htmlFor="phone" className="mb-1 block text-sm font-medium text-slate-700">
              Phone Number
            </label>
            <input
              id="phone"
              type="tel"
              required
              value={formData.phone}
              onChange={(event) => setFormData((prev) => ({ ...prev, phone: event.target.value }))}
              className="w-full rounded-md border border-slate-300 px-4 py-2 text-slate-900 outline-none ring-brand-500 focus:ring"
            />
          </div>
          <div>
            <label htmlFor="email" className="mb-1 block text-sm font-medium text-slate-700">
              Email Address
            </label>
            <input
              id="email"
              type="email"
              required
              value={formData.email}
              onChange={(event) => setFormData((prev) => ({ ...prev, email: event.target.value }))}
              className="w-full rounded-md border border-slate-300 px-4 py-2 text-slate-900 outline-none ring-brand-500 focus:ring"
            />
          </div>
          <div>
            <label htmlFor="message" className="mb-1 block text-sm font-medium text-slate-700">
              Message
            </label>
            <textarea
              id="message"
              rows={5}
              required
              value={formData.message}
              onChange={(event) => setFormData((prev) => ({ ...prev, message: event.target.value }))}
              className="w-full rounded-md border border-slate-300 px-4 py-2 text-slate-900 outline-none ring-brand-500 focus:ring"
            />
          </div>

          <button
            type="submit"
            disabled={status === 'submitting'}
            className="rounded-md bg-brand-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-brand-700 disabled:cursor-not-allowed disabled:opacity-70"
          >
            {status === 'submitting' ? 'Submitting...' : 'Submit Inquiry'}
          </button>

          {status === 'success' && (
            <p className="text-sm font-medium text-emerald-600">
              Thank you! Your message has been submitted successfully.
            </p>
          )}
          {status === 'error' && (
            <p className="text-sm font-medium text-red-600">
              Something went wrong while submitting. Please try again.
            </p>
          )}
        </form>
      </section>

      <section className="overflow-hidden rounded-2xl bg-white shadow-sm">
        <iframe
          title="Akshayraj Industry Location"
          src="https://www.google.com/maps?q=21.1458,79.0882&z=12&output=embed"
          width="100%"
          height="100%"
          className="min-h-[560px] w-full border-0"
          loading="lazy"
          referrerPolicy="no-referrer-when-downgrade"
        />
      </section>
    </div>
  );
}
