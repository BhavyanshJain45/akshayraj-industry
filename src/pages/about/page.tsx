import { useEffect, useState } from 'react';
import Navbar from '../../components/feature/Navbar';
import Footer from '../../components/feature/Footer';
export default function About() {
  const [isScrolled, setIsScrolled] = useState(false);
  useEffect(() => {
    const handleScroll = () => {
      setIsScrolled(window.scrollY > 50);
    };
    window.addEventListener('scroll', handleScroll);
    return () => window.removeEventListener('scroll', handleScroll);
  }, []);
  const values = [
    {
      icon: 'ri-hand-heart-line',
      title: 'Trust',
      description: 'Building lasting relationships through integrity and transparency in every transaction.',
    },
    {
      icon: 'ri-star-line',
      title: 'Quality',
      description: 'Uncompromising standards in materials, manufacturing, and final product inspection.',
    },
    {
      icon: 'ri-leaf-line',
      title: 'Sustainability',
      description: 'Eco-friendly practices and durable products that serve generations.',
    },
    {
      icon: 'ri-heart-line',
      title: 'Indian Values',
      description: 'Rooted in traditional craftsmanship and commitment to community welfare.',
    },
  ];
  return (
    <div className="min-h-screen bg-white">
      <Navbar isScrolled={isScrolled} />
      <section className="relative h-[60vh] w-full overflow-hidden mt-20">
        <div className="absolute inset-0">
          <img
            src="/assets/About.jpg"
            alt="Our Heritage"
            className="w-full h-full object-cover object-center"
          />
          <div className="absolute inset-0 bg-gradient-to-b from-black/60 via-black/50 to-black/60"></div>
        </div>
        <div className="relative h-full flex items-center justify-center">
          <div className="text-center px-6">
            <div className="mb-4 inline-block">
              <div className="border-t-2 border-b-2 border-amber-400 py-2 px-6">
                <span className="text-amber-300 text-sm font-medium tracking-widest uppercase">
                  Our Story
                </span>
              </div>
            </div>
            <h1 className="text-5xl md:text-6xl font-bold text-white mb-4">
              Our Roots. Our Responsibility.
            </h1>
            <p className="text-xl text-white/90 max-w-2xl mx-auto">
              A legacy of trust, quality, and traditional Indian craftsmanship
            </p>
          </div>
        </div>
      </section>
      <section className="py-20 bg-white">
        <div className="max-w-7xl mx-auto px-6">
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
            <div>
              <h2 className="text-4xl font-bold text-amber-900 mb-6">
                Serving India Since 2025
              </h2>
              <p className="text-gray-700 mb-6 leading-relaxed">
                Akshayraj Industry Pvt. Ltd. was founded in the heart of Ujjain, Madhya Pradesh,
                with a vision to provide every Indian household with safe and reliable water storage
                solutions. What started as a small manufacturing unit has grown into a trusted name
                across India.
              </p>
              <p className="text-gray-700 mb-6 leading-relaxed">
                Our journey is deeply rooted in traditional Indian values of honesty, quality, and
                community service. We believe that access to clean water is not just a business—it's
                a responsibility we carry with pride.
              </p>
              <p className="text-gray-700 leading-relaxed">
                Today, we manufacture premium water tanks and milk cans using advanced PUF insulation
                technology while maintaining the craftsmanship and attention to detail that has been
                our hallmark for decades.
              </p>
            </div>
            <div className="relative">
              <div className="absolute -top-4 -right-4 w-32 h-32 border-t-4 border-r-4 border-amber-600"></div>
              <img
                src="/assets/AboutC.png"
                alt="Traditional Craftsmanship"
                className="w-full h-[500px] object-cover rounded-lg shadow-xl"
              />
            </div>
          </div>
        </div>
      </section>
      <section className="py-20 bg-gradient-to-b from-amber-50/30 to-white">
        <div className="max-w-7xl mx-auto px-6">
          <div className="text-center mb-16">
            <span className="text-amber-600 font-semibold text-sm tracking-widest uppercase">
              What Drives Us
            </span>
            <h2 className="text-4xl font-bold text-amber-900 mt-4 mb-6">
              Our Core Values
            </h2>
            <p className="text-gray-700 max-w-2xl mx-auto">
              These principles guide every decision we make and every product we manufacture
            </p>
          </div>
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            {values.map((value, index) => (
              <div
                key={index}
                className="bg-white p-8 rounded-xl shadow-sm hover:shadow-lg transition-shadow duration-300 border border-amber-100 text-center"
              >
                <div className="w-16 h-16 flex items-center justify-center bg-amber-100 rounded-full mx-auto mb-4">
                  <i className={`${value.icon} text-3xl text-amber-900`}></i>
                </div>
                <h3 className="text-xl font-bold text-amber-900 mb-3">{value.title}</h3>
                <p className="text-sm text-gray-600 leading-relaxed">{value.description}</p>
              </div>
            ))}
          </div>
        </div>
      </section>
      <Footer />
    </div>
  );
}