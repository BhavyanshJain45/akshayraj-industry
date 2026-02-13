import Hero from './components/Hero';
import TrustBadges from './components/TrustBadges';
import AboutPreview from './components/AboutPreview';
import ProductsPreview from './components/ProductsPreview';

export default function HomePage() {
  return (
    <div className="space-y-10">
      <Hero />
      <TrustBadges />
      <AboutPreview />
      <ProductsPreview />
    </div>
  );
}
