import React, { useEffect, useState } from 'react';
import Navbar from '../../components/feature/Navbar';
import Footer from '../../components/feature/Footer';
export default function Contact() {
  const [isScrolled, setIsScrolled] = useState(false);
  const [formData, setFormData] = useState({
    name: '',
    phone: '',
    email: '',
    message: '',
  });
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [submitStatus, setSubmitStatus] = useState<'idle' | 'success' | 'error'>('idle');
  const [errors, setErrors] = useState<{ [key: string]: string }>({});
  const [apiError, setApiError] = useState<string | null>(null);
  const [showToast, setShowToast] = useState(false);
  useEffect(() => {
    const handleScroll = () => {
      setIsScrolled(window.scrollY > 50);
    };
    window.addEventListener('scroll', handleScroll);
    return () => window.removeEventListener('scroll', handleScroll);
  }, []);
  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setApiError(null);
    const newErrors: { [key: string]: string } = {};
    if (!formData.name.trim()) newErrors.name = 'Name is required';
    if (!formData.phone.trim()) newErrors.phone = 'Phone is required';
    if (formData.email && !/^[\w-.]+@([\w-]+\.)+[\w-]{2,4}$/.test(formData.email)) newErrors.email = 'Enter a valid email';
    if (!formData.message.trim()) newErrors.message = 'Message is required';
    if (formData.message && formData.message.length > 500) newErrors.message = 'Message must be 500 characters or less';

    setErrors(newErrors);
    if (Object.keys(newErrors).length > 0) return;
    setIsSubmitting(true);
    setSubmitStatus('idle');
    try {
      const formBody = new URLSearchParams();
      formBody.append('name', formData.name);
      formBody.append('phone', formData.phone);
      formBody.append('email', formData.email);
      formBody.append('message', formData.message);
      const response = await fetch('/server/api/contact.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: formBody.toString(),
      });
      let ok = false;
      try {
        const json = await response.json();
        if (json && json.success) {
          ok = true;
        } else if (json && json.message) {
          setApiError(json.message);
        }
      } catch (e) {
        // non-json response
        if (response.ok) ok = true;
      }

      if (ok) {
        setSubmitStatus('success');
        setFormData({ name: '', phone: '', email: '', message: '' });
        setErrors({});
        setShowToast(true);
        window.setTimeout(() => setShowToast(false), 4000);
      } else {
        setSubmitStatus('error');
        if (!apiError) setApiError('Something went wrong. Please try again.');
      }
    } catch (error) {
      setSubmitStatus('error');
    } finally {
      setIsSubmitting(false);
    }
  };
  return (
    <div className="min-h-screen bg-white">
      <Navbar isScrolled={isScrolled} />
      <section className="relative h-[50vh] w-full overflow-hidden mt-20">
        <div className="absolute inset-0">
          <img
            src="/assets/ContactUs.jpg"
            alt="Contact Us"
            className="w-full h-full object-cover object-center"
          />
          <div className="absolute inset-0 bg-gradient-to-b from-black/60 via-black/50 to-black/60"></div>
        </div>
        <div className="relative h-full flex items-center justify-center">
          <div className="text-center px-6">
            <div className="mb-4 inline-block">
              <div className="border-t-2 border-b-2 border-amber-400 py-2 px-6">
                <span className="text-amber-300 text-sm font-medium tracking-widest uppercase">
                  Get In Touch
                </span>
              </div>
            </div>
            <h1 className="text-5xl md:text-6xl font-bold text-white mb-4">
              Let's Bring Pure Water
              <br />
              to Every Home
            </h1>
          </div>
        </div>
      </section>
      <section className="py-20 bg-white">
        <div className="max-w-7xl mx-auto px-6">
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-16">
            <div>
              <h2 className="text-4xl font-bold text-amber-900 mb-6">
                Contact Information
              </h2>
              <p className="text-gray-700 mb-10 leading-relaxed">
                We're here to answer your questions and help you find the perfect water storage
                solution for your needs. Reach out to us through any of the following channels.
              </p>
              <div className="space-y-6">
                <div className="flex items-start gap-4 p-6 bg-amber-50 rounded-xl border border-amber-100">
                  <div className="w-12 h-12 flex items-center justify-center bg-amber-600 rounded-lg flex-shrink-0">
                    <i className="ri-phone-line text-xl text-white"></i>
                  </div>
                  <div>
                    <h3 className="font-bold text-amber-900 mb-2">Phone / WhatsApp</h3>
                    <p className="text-gray-700">+91 99774 21070</p>
                    <p className="text-sm text-gray-600 mt-1">Mon-Sat: 9:00 AM- 6:00 PM</p>
                  </div>
                </div>
                <div className="flex items-start gap-4 p-6 bg-amber-50 rounded-xl border border-amber-100">
                  <div className="w-12 h-12 flex items-center justify-center bg-amber-600 rounded-lg flex-shrink-0">
                    <i className="ri-mail-line text-xl text-white"></i>
                  </div>
                  <div>
                    <h3 className="font-bold text-amber-900 mb-2">Email</h3>
                    <p className="text-gray-700">info@akshayrajindustry.com</p>
                    <p className="text-sm text-gray-600 mt-1">We'll respond within 24 hours</p>
                  </div>
                </div>
                <div className="flex items-start gap-4 p-6 bg-amber-50 rounded-xl border border-amber-100">
                  <div className="w-12 h-12 flex items-center justify-center bg-amber-600 rounded-lg flex-shrink-0">
                    <i className="ri-map-pin-line text-xl text-white"></i>
                  </div>
                  <div>
                    <h3 className="font-bold text-amber-900 mb-2">Address</h3>
                    <p className="text-gray-700">
                      Akshayraj Industry Pvt. Ltd.
                      <br />
                      Ujjain, Madhya Pradesh
                      <br />
                      India
                    </p>
                  </div>
                </div>
              </div>
            </div>
            <div>
              <div className="bg-gradient-to-br from-amber-50 to-white p-8 rounded-xl shadow-lg border border-amber-100">
                <h3 className="text-2xl font-bold text-amber-900 mb-6">Send Us a Message</h3>
                <br />
                  <form id="contact-form" onSubmit={handleSubmit} data-readdy-form>
                    <div className="space-y-4">
                      <div>
                        <label htmlFor="name" className="block text-sm font-semibold text-amber-900 mb-2">
                          Name *
                        </label>
                        <input
                          type="text"
                          id="name"
                          name="name"
                          value={formData.name}
                          onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                          required
                          className="w-full px-4 py-3 border border-amber-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-600 text-sm"
                          placeholder="Your full name"
                        />
                          {errors.name && (
                            <p className="text-xs text-red-600 mt-1">{errors.name}</p>
                          )}
                      </div>
                      <div>
                        <label htmlFor="phone" className="block text-sm font-semibold text-amber-900 mb-2">
                          Phone *
                        </label>
                        <input
                          type="tel"
                          id="phone"
                          name="phone"
                          value={formData.phone}
                          onChange={(e) => setFormData({ ...formData, phone: e.target.value })}
                          required
                          className="w-full px-4 py-3 border border-amber-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-600 text-sm"
                          placeholder="Your phone number"
                        />
                          {errors.phone && (
                            <p className="text-xs text-red-600 mt-1">{errors.phone}</p>
                          )}
                      </div>
                      <div>
                        <label htmlFor="email" className="block text-sm font-semibold text-amber-900 mb-2">
                          Email
                        </label>
                        <input
                          type="email"
                          id="email"
                          name="email"
                          value={formData.email}
                          onChange={(e) => setFormData({ ...formData, email: e.target.value })}
                          className="w-full px-4 py-3 border border-amber-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-600 text-sm"
                          placeholder="your@email.com"
                        />
                          {errors.email && (
                            <p className="text-xs text-red-600 mt-1">{errors.email}</p>
                          )}
                      </div>
                      <div>
                        <label htmlFor="message" className="block text-sm font-semibold text-amber-900 mb-2">
                          Message * (Max 500 characters)
                        </label>
                        <textarea
                          id="message"
                          name="message"
                          value={formData.message}
                          onChange={(e) => setFormData({ ...formData, message: e.target.value })}
                          required
                          maxLength={500}
                          rows={5}
                          className="w-full px-4 py-3 border border-amber-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-600 text-sm resize-none"
                          placeholder="Tell us about your requirements..."
                        />
                        <p className="text-xs text-gray-500 mt-1">
                          {formData.message.length}/500 characters
                        </p>
                        {errors.message && (
                          <p className="text-xs text-red-600 mt-1">{errors.message}</p>
                        )}
                      </div>
                      {apiError && (
                        <div className="p-3 bg-red-50 border border-red-200 rounded-lg">
                          <p className="text-sm text-red-800">{apiError}</p>
                        </div>
                      )}

                      <button
                        type="submit"
                        disabled={isSubmitting}
                        aria-busy={isSubmitting}
                        className="w-full px-6 py-4 bg-amber-600 hover:bg-amber-700 text-white font-semibold rounded-lg transition-colors duration-300 disabled:opacity-50 disabled:cursor-not-allowed whitespace-nowrap cursor-pointer"
                      >
                        {isSubmitting ? 'Sending...' : 'Send Message'}
                      </button>
                      {submitStatus === 'success' && (
                        <div className="p-4 bg-green-50 border border-green-200 rounded-lg">
                          <p className="text-sm text-green-800 font-medium">
                            Thank you! Your message has been sent successfully.
                          </p>
                        </div>
                      )}
                      {submitStatus === 'error' && (
                        <div className="p-4 bg-red-50 border border-red-200 rounded-lg">
                          <p className="text-sm text-red-800 font-medium">
                            Something went wrong. Please try again.
                          </p>
                        </div>
                      )}
                    </div>
                  </form>
              </div>
            </div>
          </div>
        </div>
      </section>
      <section className="py-16 bg-gradient-to-b from-white to-amber-50/30">
        <div className="max-w-7xl mx-auto px-6">
          <h3 className="text-3xl font-bold text-amber-900 text-center mb-8">Find Us on Map</h3>
          <div className="rounded-xl overflow-hidden shadow-lg border border-amber-100">
            <iframe
              src="https://www.google.com/maps/embed?pb=!1m28!1m12!1m3!1d234711.70833183563!2d79.9753609!3d23.1931667!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!4m13!3e6!4m5!1s0x3964794a452ea601%3A0xca600630b8e1afc2!2sAKSHAYRAJ%20INDUSTRY%20PVT%20LTD%2C%20Surey%20number%203%2C%202%20gram%2C%20Kukni%2C%20Mahidpur%2C%20Madhya%20Pradesh%20456443!3m2!1d23.5104893!2d75.60711429999999!4m5!1s0x3964794a452ea601%3A0xca600630b8e1afc2!2sAKSHAYRAJ%20INDUSTRY%20PVT%20LTD%2C%20Surey%20number%203%2C%202%20gram%2C%20Kukni%2C%20Mahidpur%2C%20Madhya%20Pradesh%20456443!3m2!1d23.5104893!2d75.60711429999999!5e0!3m2!1sen!2sin!4v1771021048641!5m2!1sen!2sin"
              width="100%"
              height="450"
              style={{ border: 0 }}
              allowFullScreen
              loading="lazy"
              referrerPolicy="no-referrer-when-downgrade"
              title="Akshayraj Industry Location"
            ></iframe>
          </div>
        </div>
      </section>
      {/* Success toast */}
      {showToast && (
        <div className="fixed right-6 bottom-6 z-50">
          <div className="px-4 py-3 bg-green-600 text-white rounded-lg shadow-lg">
            <p className="text-sm">Message sent successfully.</p>
          </div>
        </div>
      )}

      <Footer />
    </div>
  );
}