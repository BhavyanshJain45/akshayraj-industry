import { Link } from 'react-router-dom';
export default function Hero() {
  return (
    <section className="relative min-h-screen flex items-center justify-center overflow-hidden">
      <div
        className="absolute inset-0 bg-cover bg-center"
        style={{
            backgroundImage: 'url(/assets/Hero.jpg)',
        }}
      >
        <div className="absolute inset-0 bg-gradient-to-b from-black/40 via-black/30 to-black/40"></div>
      </div>
      {/* Removed top border and gradient above hero banner */}
      <div className="relative z-10 max-w-7xl mx-auto px-6 py-32 text-center w-full">
        <div className="mb-8">
          <div className="flex flex-col items-center mb-6">
            <span className="text-amber-300 font-semibold text-lg md:text-xl tracking-widest uppercase mb-2">Since 1995 • Made in India</span>
          </div>
          <h1 className="text-5xl md:text-7xl font-bold text-white mb-4 drop-shadow-lg">
            Pure Water Solutions,<br />
            <span className="text-amber-400" style={{ WebkitTextStroke: '1px #fff', fontWeight: 900 }}>
              Rooted in Indian Tradition
            </span>
          </h1>
        </div>
        <p className="text-lg md:text-xl text-white/90 max-w-3xl mx-auto mb-8 leading-relaxed drop-shadow-md">
          Manufacturing premium water tanks and milk cans with PUF insulation technology, combining traditional Indian craftsmanship with modern quality standards.
        </p>
        <div className="flex flex-col sm:flex-row items-center justify-center gap-6">
          <Link
            to="/products"
            className="px-8 py-4 bg-amber-600 hover:bg-amber-700 text-white font-semibold rounded-lg transition-all duration-300 shadow-lg hover:shadow-xl whitespace-nowrap cursor-pointer"
          >
            View Products
          </Link>
          <Link
            to="/contact"
            className="px-8 py-4 bg-white/10 hover:bg-white/20 text-white font-semibold rounded-lg border-2 border-white/50 backdrop-blur-sm transition-all duration-300 whitespace-nowrap cursor-pointer"
          >
            Contact Us
          </Link>
        </div>
      </div>
      <div className="absolute bottom-16 left-0 right-0 h-8 border-t-4 border-b-4 border-amber-700/30" style={{
        backgroundImage: 'repeating-linear-gradient(90deg, transparent, transparent 20px, rgba(180, 83, 9, 0.3) 20px, rgba(180, 83, 9, 0.3) 40px)',
      }}></div>
      <div className="absolute bottom-0 left-0 right-0 h-16 bg-gradient-to-t from-amber-900/20 to-transparent"></div>
    </section>
  );
}