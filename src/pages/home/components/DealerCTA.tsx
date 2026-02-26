import { Link } from 'react-router-dom';

export default function DealerCTA() {
  return (
    <section className="py-20 bg-gradient-to-r from-amber-900 via-amber-800 to-amber-900 text-white relative overflow-hidden">
      {/* Decorative Background Elements */}
      <div className="absolute top-0 right-0 w-96 h-96 bg-amber-700/30 rounded-full blur-3xl -mr-48 -mt-48"></div>
      <div className="absolute bottom-0 left-0 w-96 h-96 bg-amber-700/30 rounded-full blur-3xl -ml-48 -mb-48"></div>

      <div className="max-w-7xl mx-auto px-6 relative z-10">
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
          {/* Left Content */}
          <div>
            <h2 className="text-4xl sm:text-5xl font-bold mb-6 leading-tight">
              We Are Appointing Dealers & Distributors Across India
            </h2>
            <p className="text-xl text-amber-100 mb-8 leading-relaxed">
              Join our rapidly growing network of successful partners. We offer high-quality products with Direct manufacturer pricing and comprehensive support.
            </p>
            
            <div className="space-y-4 mb-8">
              <div className="flex items-start gap-4">
                <div className="w-8 h-8 rounded-full bg-amber-400 flex items-center justify-center flex-shrink-0 mt-1">
                  <span className="text-amber-900 font-bold">✓</span>
                </div>
                <div>
                  <h3 className="font-semibold text-lg mb-1">High Demand Products</h3>
                  <p className="text-amber-100">Proven market presence with strong customer demand</p>
                </div>
              </div>

              <div className="flex items-start gap-4">
                <div className="w-8 h-8 rounded-full bg-amber-400 flex items-center justify-center flex-shrink-0 mt-1">
                  <span className="text-amber-900 font-bold">✓</span>
                </div>
                <div>
                  <h3 className="font-semibold text-lg mb-1">Direct Pricing</h3>
                  <p className="text-amber-100">Manufacturer rates for maximum profit margins</p>
                </div>
              </div>

              <div className="flex items-start gap-4">
                <div className="w-8 h-8 rounded-full bg-amber-400 flex items-center justify-center flex-shrink-0 mt-1">
                  <span className="text-amber-900 font-bold">✓</span>
                </div>
                <div>
                  <h3 className="font-semibold text-lg mb-1">Business Growth</h3>
                  <p className="text-amber-100">Marketing support and dedicated partnership team</p>
                </div>
              </div>
            </div>

            <Link
              to="/dealer-distributor"
              className="inline-block px-8 py-3 bg-amber-400 text-amber-900 font-bold rounded-lg hover:bg-amber-300 transition-all duration-300 shadow-lg hover:shadow-xl hover:scale-105"
            >
              Apply Now →
            </Link>
          </div>

          {/* Right Image/Stats */}
          <div className="relative">
            <div className="bg-white/10 backdrop-blur-sm rounded-2xl p-8 border border-white/20">
              <div className="space-y-6">
                <div className="text-center">
                  <div className="text-5xl font-bold text-amber-300 mb-2">25+</div>
                  <p className="text-amber-100 font-semibold">Years of Industry Experience</p>
                </div>

                <div className="h-px bg-white/20"></div>

                <div className="text-center">
                  <div className="text-5xl font-bold text-amber-300 mb-2">500+</div>
                  <p className="text-amber-100 font-semibold">Active Dealer Network</p>
                </div>

                <div className="h-px bg-white/20"></div>

                <div className="text-center">
                  <div className="text-5xl font-bold text-amber-300 mb-2">100%</div>
                  <p className="text-amber-100 font-semibold">Quality Guaranteed</p>
                </div>

                <div className="h-px bg-white/20"></div>

                <div className="text-center pt-4">
                  <p className="text-sm text-amber-100">
                    <span className="font-semibold">🌟 Trusted by</span> thousands of businesses across India
                  </p>
                </div>
              </div>
            </div>

            {/* Decorative Card */}
            <div className="absolute -bottom-6 -right-6 bg-amber-400 text-amber-900 rounded-xl p-6 shadow-xl max-w-xs">
              <p className="font-bold text-center">
                Ready to start your journey? <br />
                <span className="text-lg">Contact us today!</span>
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
}
