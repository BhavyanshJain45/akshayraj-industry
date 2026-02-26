import { Link } from 'react-router-dom';
export default function Footer() {
  return (
    <footer className="bg-gradient-to-br from-amber-900 via-amber-800 to-amber-900 text-white">
      <div className="max-w-7xl mx-auto px-6 py-16">
        <div className="grid grid-cols-1 md:grid-cols-4 gap-12 mb-12">
          <div>
            <div className="flex items-center gap-3 mb-4">
              <img
                 src="/assets/Logo.png"
                alt="Akshayraj Industry"
                className="h-12 w-auto object-contain"
              />
            </div>
            <h3 className="text-xl font-bold mb-3">Akshayraj Industry</h3>
            <p className="text-amber-100 text-sm leading-relaxed">
              Manufacturing premium water tanks and milk cans with traditional Indian values and modern quality standards.
            </p>
          </div>
          <div>
            <h4 className="text-lg font-bold mb-4">Quick Links</h4>
            <ul className="space-y-2">
              <li>
                <Link to="/" className="text-amber-100 hover:text-white transition-colors text-sm cursor-pointer">
                  Home
                </Link>
              </li>
              <li>
                <Link to="/about" className="text-amber-100 hover:text-white transition-colors text-sm cursor-pointer">
                  About Us
                </Link>
              </li>
              <li>
                <Link to="/products" className="text-amber-100 hover:text-white transition-colors text-sm cursor-pointer">
                  Products
                </Link>
              </li>
              <li>
                <Link to="/manufacturing" className="text-amber-100 hover:text-white transition-colors text-sm cursor-pointer">
                  Manufacturing
                </Link>
              </li>
              <li>
                <Link to="/contact" className="text-amber-100 hover:text-white transition-colors text-sm cursor-pointer">
                  Contact Us
                </Link>
              </li>
              <li>
                <Link to="/dealer-distributor" className="text-amber-100 hover:text-white transition-colors text-sm cursor-pointer font-semibold">
                  📌 Become a Dealer/Distributor
                </Link>
              </li>
            </ul>
          </div>
          <div>
            <h4 className="text-lg font-bold mb-4">Contact Info</h4>
            <ul className="space-y-3">
              <li className="flex items-start gap-3">
                <i className="ri-map-pin-line text-amber-300 mt-1"></i>
                <span className="text-amber-100 text-sm">
                  Ujjain, Madhya Pradesh, India
                </span>
              </li>
              <li className="flex items-start gap-3">
                <i className="ri-phone-line text-amber-300 mt-1"></i>
                <span className="text-amber-100 text-sm">
                  +91 99774 21070
                </span>
              </li>
              <li className="flex items-start gap-3">
                <i className="ri-mail-line text-amber-300 mt-1"></i>
                <span className="text-amber-100 text-sm">
                  info@akshayrajindustry.in
                </span>
              </li>
            </ul>
          </div>
          <div>
            <h4 className="text-lg font-bold mb-4">Certifications</h4>
            <div className="space-y-3">
              <div className="flex items-center gap-2">
                <i className="ri-award-line text-amber-300 text-xl"></i>
                <span className="text-amber-100 text-sm">ISO Certified</span>
              </div>
              <div className="flex items-center gap-2">
                <i className="ri-shield-check-line text-amber-300 text-xl"></i>
                <span className="text-amber-100 text-sm">BIS Approved</span>
              </div>
              <div className="flex items-center gap-2">
                <i className="ri-checkbox-circle-line text-amber-300 text-xl"></i>
                <span className="text-amber-100 text-sm">100% Food Grade</span>
              </div>
            </div>
          </div>
        </div>
        <div className="border-t border-amber-700 pt-8">
          <div className="flex flex-col md:flex-row items-center justify-between gap-4">
            <p className="text-amber-100 text-sm">
              © 2026 Akshayraj Industry Pvt. Ltd. All rights reserved.
            </p>
          </div>
        </div>
      </div>
    </footer>
  );
}