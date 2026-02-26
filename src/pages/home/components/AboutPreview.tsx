import { Link } from 'react-router-dom';
export default function AboutPreview() {
  return (
    <section className="py-12 sm:py-16 md:py-20 bg-white">
      <div className="max-w-7xl mx-auto px-4 sm:px-6">
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 md:gap-12 items-center">
          <div>
            <div className="relative max-w-md mx-auto lg:mx-0">
              <div className="absolute -top-2 -left-2 w-16 h-16 sm:w-20 sm:h-20 md:w-24 md:h-24 border-t-4 border-l-4 border-amber-600"></div>
              <div className="absolute -bottom-2 -right-2 w-16 h-16 sm:w-20 sm:h-20 md:w-24 md:h-24 border-b-4 border-r-4 border-amber-600"></div>
              <img
                src="/assets/HomeA.png"
                alt="Our Heritage"
                className="w-full h-56 sm:h-72 md:h-96 object-contain rounded-lg shadow-xl bg-white"
              />
            </div>
          </div>
          <div className="mt-8 lg:mt-0 text-center lg:text-left">
            <h2 className="text-2xl sm:text-3xl md:text-5xl font-bold text-amber-900 mb-4 md:mb-6">
              Our Story
            </h2>
            <p className="text-base sm:text-lg text-gray-700 mb-4 md:mb-6 leading-relaxed">
              Since 1995, Akshayraj Industry has been manufacturing premium water tanks and milk cans that blend traditional Indian craftsmanship with modern quality standards.
            </p>
            <p className="text-base sm:text-lg text-gray-700 mb-6 md:mb-8 leading-relaxed">
              Rooted in the heritage of Ujjain, Madhya Pradesh, we serve communities across India with products that ensure pure water storage and safe dairy handling.
            </p>
            <Link
              to="/about"
              className="inline-flex items-center gap-2 px-6 sm:px-8 py-3 sm:py-4 bg-amber-600 hover:bg-amber-700 text-white font-semibold rounded-lg transition-all duration-300 shadow-md hover:shadow-lg whitespace-nowrap cursor-pointer text-base sm:text-lg"
            >
              Learn More About Us
              <i className="ri-arrow-right-line"></i>
            </Link>
          </div>
        </div>
      </div>
      <section className="py-12 sm:py-16 md:py-20 bg-gradient-to-b from-amber-50/30 to-white">
        <div className="max-w-7xl mx-auto px-6">
          <div className="mb-12 text-center">
            <span className="text-amber-600 font-semibold text-sm tracking-widest uppercase">Our Products</span>
            <h2 className="text-4xl md:text-5xl font-bold text-amber-900 mt-4 mb-4">Quality Products for Every Need</h2>
            <p className="text-lg text-gray-700 max-w-2xl mx-auto">Discover our range of premium water tanks and milk cans, manufactured with precision and care to serve Indian households and dairy industries.</p>
          </div>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-10 justify-center items-stretch mb-12">
            {/* Water Tanks Card */}
            <div className="bg-white rounded-2xl shadow-lg border border-amber-100 flex flex-col items-center p-8">
              <img src="/assets/500Lit.png" alt="Water Tanks" className="w-40 h-40 object-contain mb-6" />
              <h3 className="text-2xl font-bold text-amber-900 mb-2">Water Tanks</h3>
              <p className="text-gray-700 mb-4 text-center">Premium PUF insulated water storage tanks available in 500L, 750L, and 1000L capacities.</p>
              <div className="flex flex-wrap gap-2 justify-center mb-4">
                <span className="px-2 py-1 bg-amber-50 text-amber-800 text-xs font-medium rounded">PUF Insulation</span>
                <span className="px-2 py-1 bg-amber-50 text-amber-800 text-xs font-medium rounded">Climate Resistant</span>
                <span className="px-2 py-1 bg-amber-50 text-amber-800 text-xs font-medium rounded">Food Grade Material</span>
              </div>
              <a href="/products" className="text-amber-600 font-semibold flex items-center gap-1 hover:underline">View Details <span>&rarr;</span></a>
            </div>
            {/* Milk Cans Card */}
            <div className="bg-white rounded-2xl shadow-lg border border-amber-100 flex flex-col items-center p-8">
              <img src="/assets/40Lit.png" alt="Milk Cans" className="w-40 h-40 object-contain mb-6" />
              <h3 className="text-2xl font-bold text-amber-900 mb-2">Milk Cans</h3>
              <p className="text-gray-700 mb-4 text-center">Food-grade stainless steel milk cans designed for dairy industry, available in 40L and 50L.</p>
              <div className="flex flex-wrap gap-2 justify-center mb-4">
                <span className="px-2 py-1 bg-amber-50 text-amber-800 text-xs font-medium rounded">100% Food Grade</span>
                <span className="px-2 py-1 bg-amber-50 text-amber-800 text-xs font-medium rounded">Dairy Approved</span>
                <span className="px-2 py-1 bg-amber-50 text-amber-800 text-xs font-medium rounded">Durable Design</span>
              </div>
              <a href="/products" className="text-amber-600 font-semibold flex items-center gap-1 hover:underline">View Details <span>&rarr;</span></a>
            </div>
          </div>
          <div className="flex justify-center">
            <a href="/products" className="px-8 py-4 bg-amber-600 hover:bg-amber-700 text-white font-semibold rounded-lg transition-all duration-300 shadow-lg hover:shadow-xl text-lg">View All Products</a>
          </div>
        </div>
      </section>
    </section>
  );
}