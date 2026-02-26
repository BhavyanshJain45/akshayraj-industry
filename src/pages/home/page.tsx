import { useState, useEffect } from 'react';
import Navbar from '../../components/feature/Navbar';
import Footer from '../../components/feature/Footer';
import Hero from './components/Hero';
import TrustBadges from './components/TrustBadges';
import AboutPreview from './components/AboutPreview';
import ProductsPreview from './components/ProductsPreview';
import DealerCTA from './components/DealerCTA';
export default function Home() {
  const [isScrolled, setIsScrolled] = useState(false);
  useEffect(() => {
    const handleScroll = () => {
      setIsScrolled(window.scrollY > 50);
    };
    window.addEventListener('scroll', handleScroll);
    return () => window.removeEventListener('scroll', handleScroll);
  }, []);
  return (
    <div className="min-h-screen">
      <Navbar isScrolled={isScrolled} />
      <Hero />
      <TrustBadges />
      <AboutPreview />
      <ProductsPreview />
      <DealerCTA />
      <Footer />
    </div>
  );
}