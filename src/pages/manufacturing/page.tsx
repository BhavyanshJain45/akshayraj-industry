import { useEffect, useState } from 'react';
import Navbar from '../../components/feature/Navbar';
import Footer from '../../components/feature/Footer';
export default function Manufacturing() {
  const [isScrolled, setIsScrolled] = useState(false);
  useEffect(() => {
    const handleScroll = () => {
      setIsScrolled(window.scrollY > 50);
    };
    window.addEventListener('scroll', handleScroll);
    return () => window.removeEventListener('scroll', handleScroll);
  }, []);
  const processSteps = [
    {
      number: '01',
      title: 'Raw Material & Melting',
      description: 'We source premium food-grade materials and process them through controlled melting procedures to ensure purity and consistency.',
      icon: 'ri-fire-line',
    },
    {
      number: '02',
      title: 'Roto Moulding Technology',
      description: 'Advanced rotational moulding technology creates seamless, durable products with uniform wall thickness and superior strength.',
      icon: 'ri-settings-3-line',
    },
    {
      number: '03',
      title: 'Quality Inspection',
      description: 'Every product undergoes rigorous multi-point quality checks including pressure testing, insulation verification, and material certification.',
      icon: 'ri-search-eye-line',
    },
    {
      number: '04',
      title: 'Final Products',
      description: 'Finished products are carefully packaged with warranty documentation and ready for delivery to customers across India.',
      icon: 'ri-checkbox-circle-line',
    },
  ];
  const certifications = [
    {
      icon: 'ri-award-line',
      title: 'ISO Certified',
      description: 'International quality management standards',
    },
    {
      icon: 'ri-shield-check-line',
      title: 'BIS Approved',
      description: 'Bureau of Indian Standards certification',
    },
    {
      icon: 'ri-checkbox-circle-line',
      title: '100% Food Grade',
      description: 'Safe for drinking water and dairy products',
    },
  ];
  return (
    <div className="min-h-screen bg-white">
      <Navbar isScrolled={isScrolled} />
      <section className="relative h-[60vh] w-full overflow-hidden mt-20">
        <div className="absolute inset-0">
          <img
            src="/assets/Manufacturing.jpg"
            alt="Manufacturing Facility"
            className="w-full h-full object-cover object-center"
          />
          <div className="absolute inset-0 bg-gradient-to-b from-black/60 via-black/50 to-black/60"></div>
        </div>
        <div className="relative h-full flex items-center justify-center">
          <div className="text-center px-6">
            <div className="mb-4 inline-block">
              <div className="border-t-2 border-b-2 border-amber-400 py-2 px-6">
                <span className="text-amber-300 text-sm font-medium tracking-widest uppercase">
                  Our Process
                </span>
              </div>
            </div>
            <h1 className="text-5xl md:text-6xl font-bold text-white mb-4">
              Quality Manufacturing
            </h1>
            <p className="text-xl text-white/90 max-w-2xl mx-auto">
              Where tradition meets technology to create products you can trust
            </p>
          </div>
        </div>
      </section>
      <section className="py-20 bg-white">
        <div className="max-w-7xl mx-auto px-6">
          <div className="text-center mb-16">
            <span className="text-amber-600 font-semibold text-sm tracking-widest uppercase">
              How We Make It
            </span>
            <h2 className="text-4xl font-bold text-amber-900 mt-4 mb-6">
              Our Manufacturing Process
            </h2>
            <p className="text-gray-700 max-w-2xl mx-auto">
              Every product goes through a carefully controlled process to ensure the highest quality standards
            </p>
          </div>
          <div className="space-y-12">
            {processSteps.map((step, index) => (
              <div
                key={index}
                className={`flex flex-col ${
                  index % 2 === 0 ? 'lg:flex-row' : 'lg:flex-row-reverse'
                } items-center gap-12`}
              >
                <div className="flex-1">
                  <div className="relative">
                    <div className="absolute -top-6 -left-6 text-8xl font-bold text-amber-100">
                      {step.number}
                    </div>
                    <div className="relative bg-white p-8 rounded-xl shadow-lg border border-amber-100">
                      <div className="w-16 h-16 flex items-center justify-center bg-amber-100 rounded-full mb-4">
                        <i className={`${step.icon} text-3xl text-amber-900`}></i>
                      </div>
                      <h3 className="text-2xl font-bold text-amber-900 mb-4">{step.title}</h3>
                      <p className="text-gray-700 leading-relaxed">{step.description}</p>
                    </div>
                  </div>
                </div>
                <div className="flex-1">
                  <img
                    src={`/assets/Step${index + 1}.png`}
                    alt={step.title}
                    className="w-full h-80 object-cover rounded-xl shadow-lg"
                  />
                </div>
              </div>
            ))}
          </div>
        </div>
      </section>
      <section className="py-20 bg-gradient-to-b from-amber-50/30 to-white">
        <div className="max-w-7xl mx-auto px-6">
          <div className="text-center mb-16">
            <span className="text-amber-600 font-semibold text-sm tracking-widest uppercase">
              Trust & Quality
            </span>
            <h2 className="text-4xl font-bold text-amber-900 mt-4 mb-6">
              Our Certifications
            </h2>
            <p className="text-gray-700 max-w-2xl mx-auto">
              Recognized and certified by leading quality and safety organizations
            </p>
          </div>
          <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
            {certifications.map((cert, index) => (
              <div
                key={index}
                className="bg-white p-10 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300 border border-amber-100 text-center"
              >
                <div className="w-20 h-20 flex items-center justify-center bg-amber-100 rounded-full mx-auto mb-6">
                  <i className={`${cert.icon} text-4xl text-amber-900`}></i>
                </div>
                <h3 className="text-2xl font-bold text-amber-900 mb-3">{cert.title}</h3>
                <p className="text-gray-600">{cert.description}</p>
              </div>
            ))}
          </div>
        </div>
      </section>
      <Footer />
    </div>
  );
}