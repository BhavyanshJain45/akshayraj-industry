import { useState, useEffect, ChangeEvent, FormEvent } from 'react';
import Navbar from '../../components/feature/Navbar';
import Footer from '../../components/feature/Footer';

export default function DealerDistributorPage() {
  const [isScrolled, setIsScrolled] = useState(false);
  const [isLoading, setIsLoading] = useState(false);
  const [submitMessage, setSubmitMessage] = useState<{type: string; text: string} | ''>('');
  const [errors, setErrors] = useState<{ [key: string]: string }>({});
  const [apiError, setApiError] = useState<string | null>(null);
  const [showToast, setShowToast] = useState(false);
  const [inquiryType, setInquiryType] = useState('dealer');
  const [formData, setFormData] = useState({
    full_name: '',
    company_name: '',
    email: '',
    phone: '',
    city: '',
    state: '',
    business_experience: '',
    message: ''
  });

  // Update document meta tags for SEO
  useEffect(() => {
    // Set page title
    document.title = 'Become Our Dealer or Distributor | Akshay Raj Industry';
    
    // Set meta description
    let metaDescription = document.querySelector('meta[name="description"]');
    if (!metaDescription) {
      metaDescription = document.createElement('meta');
      metaDescription.setAttribute('name', 'description');
      document.head.appendChild(metaDescription);
    }
    metaDescription.setAttribute('content', 'Join our growing network of dealers and distributors across India. High demand products, direct manufacturer pricing, competitive margins, and dedicated support.');
    
    // Set keywords
    let metaKeywords = document.querySelector('meta[name="keywords"]');
    if (!metaKeywords) {
      metaKeywords = document.createElement('meta');
      metaKeywords.setAttribute('name', 'keywords');
      document.head.appendChild(metaKeywords);
    }
    metaKeywords.setAttribute('content', 'dealer, distributor, partnership, franchise, water tank dealer, milk can distributor, business opportunity');
    
    // Set Open Graph tags
    const updateMetaProperty = (property: string, content: string) => {
      let meta = document.querySelector(`meta[property="${property}"]`);
      if (!meta) {
        meta = document.createElement('meta');
        meta.setAttribute('property', property);
        document.head.appendChild(meta);
      }
      meta.setAttribute('content', content);
    };
    
    updateMetaProperty('og:title', 'Become Our Dealer or Distributor - Akshay Raj Industry');
    updateMetaProperty('og:description', 'Join our growing network. Direct manufacturer pricing, reliable supply chain, and business growth opportunities.');
    updateMetaProperty('og:type', 'website');
    updateMetaProperty('og:url', 'https://akshayrajindustry.in/dealer-distributor');
    
    // Add Schema.org JSON-LD
    let schemaScript = document.getElementById('dealer-schema') as HTMLScriptElement | null;
    if (!schemaScript) {
      schemaScript = document.createElement('script') as HTMLScriptElement;
      schemaScript.id = 'dealer-schema';
      (schemaScript as any).type = 'application/ld+json';
      schemaScript.textContent = JSON.stringify({
        '@context': 'https://schema.org',
        '@type': ['Organization', 'LocalBusiness'],
        'name': 'Akshayraj Industry',
        'url': 'https://akshayrajindustry.in',
        'logo': 'https://akshayrajindustry.in/assets/Logo.png',
        'description': 'Manufacturer of premium water tanks and milk cans seeking dealers and distributors',
        'foundingDate': '1995',
        'areaServed': 'IN',
        'address': {
          '@type': 'PostalAddress',
          'addressRegion': 'Madhya Pradesh',
          'addressCountry': 'IN'
        },
        'contactPoint': {
          '@type': 'ContactPoint',
          'telephone': '+91-9877421070',
          'contactType': 'Partnership Inquiry'
        }
      });
      document.head.appendChild(schemaScript);
    }
  }, []);

  const handleInputChange = (e: ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
    const { name, value } = e.currentTarget;
    setFormData(prev => ({
      ...prev,
      [name]: value
    }));
  };

  const handleScroll = () => {
    setIsScrolled(window.scrollY > 50);
  };

  useEffect(() => {
    window.addEventListener('scroll', handleScroll);
    return () => window.removeEventListener('scroll', handleScroll);
  }, []);

  const handleSubmit = async (e: FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    setApiError(null);
    setSubmitMessage('');

    const newErrors: { [key: string]: string } = {};
    if (!formData.full_name.trim()) newErrors.full_name = 'Full name is required';
    if (!formData.company_name.trim()) newErrors.company_name = 'Company name is required';
    if (!formData.email.trim()) newErrors.email = 'Email is required';
    if (formData.email && !/^[\w-.]+@([\w-]+\.)+[\w-]{2,4}$/.test(formData.email)) newErrors.email = 'Enter a valid email';
    if (!formData.phone.trim()) newErrors.phone = 'Phone is required';
    if (!formData.city.trim()) newErrors.city = 'City is required';
    if (!formData.state.trim()) newErrors.state = 'State is required';
    if (!formData.business_experience.trim()) newErrors.business_experience = 'Business experience is required';
    if (!formData.message.trim()) newErrors.message = 'Message is required';
    if (formData.message && formData.message.length > 1000) newErrors.message = 'Message must be 1000 characters or less';

    setErrors(newErrors);
    if (Object.keys(newErrors).length > 0) return;

    setIsLoading(true);

    try {
      const response = await fetch('/server/api/dealer.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
          inquiry_type: inquiryType,
          full_name: formData.full_name,
          company_name: formData.company_name,
          email: formData.email,
          phone: formData.phone,
          city: formData.city,
          state: formData.state,
          business_experience: formData.business_experience,
          message: formData.message
        })
      });

      let data: any = null;
      try {
        data = await response.json();
      } catch (e) {
        // non-json response
        if (!response.ok) {
          throw new Error('Invalid response from server');
        }
      }

      if (data && data.success) {
        setSubmitMessage({
          type: 'success',
          text: `✓ ${data.message} (Ref: ${data.data?.reference_number || 'N/A'})`
        });
        setFormData({
          full_name: '',
          company_name: '',
          email: '',
          phone: '',
          city: '',
          state: '',
          business_experience: '',
          message: ''
        });
        setErrors({});
        setShowToast(true);
        window.setTimeout(() => setShowToast(false), 4000);
      } else {
        setSubmitMessage({
          type: 'error',
          text: `⚠ ${data?.message || 'An error occurred. Please try again.'}`
        });
        if (data && data?.message) setApiError(data.message);
      }
    } catch (error) {
      setSubmitMessage({
        type: 'error',
        text: 'Network error. Please check your connection and try again.'
      });
      console.error('Form submission error:', error);
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <>
      <div className="min-h-screen" onScroll={handleScroll}>
        <Navbar isScrolled={isScrolled} />

        {/* Hero Section */}
        <section className="relative h-96 mt-20 flex items-center justify-center text-white text-center overflow-hidden">
          <div 
            className="absolute inset-0 bg-cover bg-center"
            style={{
              backgroundImage: 'linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url(/assets/Manufacturing.jpg)',
              backgroundSize: 'cover'
            }}
          ></div>
          <div className="relative z-10 max-w-2xl px-6">
            <h1 className="text-5xl sm:text-6xl font-bold mb-6">
              Become Our Dealer or Distributor
            </h1>
            <p className="text-xl text-gray-100 mb-8 leading-relaxed">
              Join our growing network of successful partners across India. We offer high-quality products,
              competitive pricing, and comprehensive support.
            </p>
            <button 
              onClick={() => document.getElementById('apply-form')?.scrollIntoView({ behavior: 'smooth' })}
              className="bg-amber-400 text-amber-900 font-bold px-8 py-3 rounded-lg hover:bg-amber-300 transition-all duration-300 shadow-lg hover:shadow-xl">
              Apply Now ↓
            </button>
          </div>
        </section>

        {/* Benefits Section */}
        <section className="py-20 bg-gray-50 px-6">
          <div className="max-w-7xl mx-auto">
            <h2 className="text-4xl font-bold text-center mb-16 text-gray-900">
              Why Partner With Us?
            </h2>
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
              {[
                {
                  icon: '📦',
                  title: 'High Demand Products',
                  desc: 'Our water tanks and milk cans are in high demand with proven market presence.'
                },
                {
                  icon: '💰',
                  title: 'Direct Manufacturer Pricing',
                  desc: 'Get exclusive wholesale pricing directly from manufacturer for maximum margins.'
                },
                {
                  icon: '🚚',
                  title: 'Reliable Supply Chain',
                  desc: 'Consistent, timely delivery and uninterrupted supply of quality products.'
                },
                {
                  icon: '📈',
                  title: 'Business Growth',
                  desc: 'Marketing support, training, and exclusive territory opportunities.'
                },
                {
                  icon: '🤝',
                  title: 'Dedicated Support',
                  desc: 'Our team is committed to your success with ongoing assistance.'
                },
                {
                  icon: '⭐',
                  title: 'Established Reputation',
                  desc: 'Leverage our 25+ years of industry experience and strong brand.'
                }
              ].map((benefit, idx) => (
                <div key={idx} className="bg-white p-8 rounded-xl shadow-md hover:shadow-lg transition-shadow">
                  <div className="text-5xl mb-4">{benefit.icon}</div>
                  <h3 className="text-xl font-semibold mb-3 text-amber-900">{benefit.title}</h3>
                  <p className="text-gray-600 leading-relaxed">{benefit.desc}</p>
                </div>
              ))}
            </div>
          </div>
        </section>

        {/* Requirements Section */}
        <section className="py-20 px-6 bg-white">
          <div className="max-w-7xl mx-auto">
            <h2 className="text-4xl font-bold text-center mb-16 text-gray-900">
              Partnership Requirements
            </h2>
            <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
              {[
                {
                  title: '✓ GST Registration',
                  desc: 'GST is preferred but not mandatory. We work with new businesses too.'
                },
                {
                  title: '✓ Business Experience',
                  desc: 'Distribution, trading, or retail experience preferred. Entrepreneurs welcome.'
                },
                {
                  title: '✓ Basic Infrastructure',
                  desc: 'Storage space and transportation facilities essential for operations.'
                }
              ].map((req, idx) => (
                <div key={idx} className="bg-gray-50 p-8 rounded-lg border-l-4 border-amber-900">
                  <h3 className="text-xl font-semibold mb-3 text-amber-900">{req.title}</h3>
                  <p className="text-gray-600 leading-relaxed">{req.desc}</p>
                </div>
              ))}
            </div>
          </div>
        </section>

        {/* Application Form Section */}
        <section id="apply-form" className="py-20 px-6 bg-gray-50">
          <div className="max-w-2xl mx-auto">
            <h2 className="text-4xl font-bold text-center mb-3 text-gray-900">
              Submit Your Inquiry
            </h2>
            <p className="text-center text-gray-600 mb-12 text-lg">
              Tell us about your business and partnership interests
            </p>

            {submitMessage && (
              <div className={`p-4 rounded-lg mb-8 border ${
                submitMessage.type === 'success' 
                  ? 'bg-green-100 border-green-400 text-green-800' 
                  : 'bg-red-100 border-red-400 text-red-800'
              }`}>
                {submitMessage.text}
              </div>
            )}

            <form onSubmit={handleSubmit} className="bg-white p-10 rounded-xl shadow-lg">
              {/* Inquiry Type Selection */}
              <div className="mb-8">
                <label className="block text-lg font-semibold text-gray-700 mb-4">
                  I am interested in becoming a:
                </label>
                <div className="flex gap-8">
                  {['dealer', 'distributor'].map(type => (
                    <label key={type} className="flex items-center cursor-pointer">
                      <input
                        type="radio"
                        name="inquiry_type"
                        value={type}
                        checked={inquiryType === type}
                        onChange={(e) => setInquiryType(e.target.value)}
                        className="w-5 h-5 mr-3"
                      />
                      <span className="text-base font-semibold text-gray-700">
                        {type.charAt(0).toUpperCase() + type.slice(1)}
                      </span>
                    </label>
                  ))}
                </div>
              </div>

              {/* Form Fields */}
              <FormField label="Full Name" name="full_name" type="text" value={formData.full_name} onChange={handleInputChange} required />
              <FormField label="Company Name" name="company_name" type="text" value={formData.company_name} onChange={handleInputChange} required error={errors.company_name} />
              <FormField label="Email Address" name="email" type="email" value={formData.email} onChange={handleInputChange} required error={errors.email} />
              <FormField label="Phone Number" name="phone" type="tel" value={formData.phone} onChange={handleInputChange} required error={errors.phone} />
              <FormField label="City" name="city" type="text" value={formData.city} onChange={handleInputChange} required error={errors.city} />
              <FormField label="State/Province" name="state" type="text" value={formData.state} onChange={handleInputChange} required error={errors.state} />

              {/* Business Experience */}
              <div className="mb-6">
                <label className="block text-gray-700 font-semibold mb-2">Business Experience</label>
                <textarea
                  name="business_experience"
                  value={formData.business_experience}
                  onChange={handleInputChange}
                  placeholder="Tell us about your business background and experience..."
                  required
                  className="w-full min-h-24 p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500"
                />
                {errors.business_experience && (
                  <p className="text-xs text-red-600 mt-2">{errors.business_experience}</p>
                )}
              </div>

              {/* Message */}
              <div className="mb-8">
                <label className="block text-gray-700 font-semibold mb-2">Additional Message</label>
                <textarea
                  name="message"
                  value={formData.message}
                  onChange={handleInputChange}
                  placeholder="Share any queries, expectations, or partnership details..."
                  required
                  className="w-full min-h-24 p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500"
                />
                {errors.message && (
                  <p className="text-xs text-red-600 mt-2">{errors.message}</p>
                )}
              </div>

              {/* Submit Button */}
              {apiError && (
                <div className="p-3 bg-red-50 border border-red-200 rounded-lg mb-4">
                  <p className="text-sm text-red-800">{apiError}</p>
                </div>
              )}
              <button
                type="submit"
                disabled={isLoading}
                aria-busy={isLoading}
                className={`w-full py-3 px-6 rounded-lg font-bold text-lg transition-all duration-300 ${
                  isLoading 
                    ? 'bg-gray-400 cursor-not-allowed' 
                    : 'bg-amber-900 text-white hover:bg-amber-800 shadow-lg hover:shadow-xl'
                }`}
              >
                {isLoading ? 'Submitting...' : 'Submit Partnership Inquiry'}
              </button>
            </form>
          </div>
        </section>

        {/* Contact Section */}
        <section className="py-20 px-6 bg-white">
          <div className="max-w-2xl mx-auto text-center">
            <h2 className="text-4xl font-bold mb-6 text-gray-900">
              Have Questions?
            </h2>
            <p className="text-gray-600 mb-12 text-lg leading-relaxed">
              Our partnership team is ready to discuss opportunities with you. Reach out directly for faster response.
            </p>
            <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
              <div>
                <p className="font-semibold text-amber-900 mb-2">📱 Phone</p>
                <p className="text-gray-700">+91-9877421070</p>
              </div>
              <div>
                <p className="font-semibold text-amber-900 mb-2">📧 Email</p>
                <p className="text-gray-700">partnerships@yourdomain.com</p>
              </div>
              <div>
                <p className="font-semibold text-amber-900 mb-2">💬 WhatsApp</p>
                <a href="https://wa.me/918877421070" className="text-blue-600 hover:underline">Chat with us</a>
              </div>
            </div>
          </div>
        </section>

        {showToast && <DealerToast />}
        <Footer />
      </div>
    </>
  );
}

// Success toast for dealer form
function DealerToast() {
  return (
    <div className="fixed right-6 bottom-6 z-50">
      <div className="px-4 py-3 bg-green-600 text-white rounded-lg shadow-lg">
        <p className="text-sm">Inquiry submitted successfully.</p>
      </div>
    </div>
  );
}

// Form Input Component
interface FormFieldProps {
  label: string;
  name: string;
  type?: string;
  value: string;
  onChange: (e: ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => void;
  required?: boolean;
  error?: string | undefined;
}

function FormField({ label, name, type = 'text', value, onChange, required = false, error }: FormFieldProps) {
  return (
    <div className="mb-6">
      <label className="block text-gray-700 font-semibold mb-2">
        {label} {required && <span className="text-red-500">*</span>}
      </label>
      <input
        type={type}
        name={name}
        value={value}
        onChange={onChange as any}
        required={required}
        className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500"
      />
      {error && <p className="text-xs text-red-600 mt-2">{error}</p>}
    </div>
  );
}
