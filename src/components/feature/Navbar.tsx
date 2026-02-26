import { Link, useLocation } from 'react-router-dom';
import { useState } from 'react';
interface NavbarProps {
  isScrolled: boolean;
}
export default function Navbar({ isScrolled }: NavbarProps) {
  const location = useLocation();
  const navLinks = [
    { name: 'Home', path: '/' },
    { name: 'About Us', path: '/about' },
    { name: 'Products', path: '/products' },
    { name: 'Manufacturing', path: '/manufacturing' },
    { name: 'Contact Us', path: '/contact' },
    { name: 'Dealer/Distributor', path: '/dealer-distributor' },
  ];
  const [menuOpen, setMenuOpen] = useState(false);
  return (
    <nav
      className={`fixed top-0 left-0 right-0 z-50 transition-all duration-300
        bg-black/40 backdrop-blur-md shadow-md
      `}
    >
      <div className="max-w-7xl mx-auto px-4 sm:px-6 py-4">
        <div className="flex items-center justify-between">
          <Link to="/" className="flex items-center gap-3">
            <img
              src="/assets/Logo.png"
              alt="Akshayraj Industry Logo"
              className="h-10 w-auto object-contain"
            />
            <div className="flex flex-col">
              <span className="text-lg sm:text-xl font-bold text-white">
                Akshayraj Industry
              </span>
              <span className="text-xs text-amber-100">
                Pvt. Ltd.
              </span>
            </div>
          </Link>
          <button className="sm:hidden p-2" onClick={() => setMenuOpen(!menuOpen)} aria-label="Toggle menu">
            <i className={`ri-menu-line text-2xl ${menuOpen ? 'text-amber-900' : 'text-white'}`}></i>
          </button>
          <div className="hidden sm:flex items-center gap-8">
            <ul className="flex items-center gap-8">
              {navLinks.map((link) => (
                <li key={link.path}>
                  <Link
                    to={link.path}
                    className={`text-sm font-medium transition-colors whitespace-nowrap cursor-pointer ${
                      location.pathname === link.path
                        ? isScrolled
                          ? 'text-amber-900'
                          : 'text-white'
                        : isScrolled
                        ? 'text-gray-700 hover:text-amber-900'
                        : 'text-white/90 hover:text-white'
                    }`}
                  >
                    {link.name}
                  </Link>
                </li>
              ))}
            </ul>
            <div className={`flex items-center gap-2 px-4 py-2 rounded-lg border-2 ${
              isScrolled ? 'border-amber-900 bg-amber-50' : 'border-white/30 bg-white/10'
            }`}>
              <i className={`ri-award-line text-lg ${isScrolled ? 'text-amber-900' : 'text-white'}`}></i>
              <span className={`text-xs font-semibold whitespace-nowrap ${isScrolled ? 'text-amber-900' : 'text-white'}`}>
                ISO Certified
              </span>
            </div>
          </div>
        </div>
        {/* Mobile menu */}
        {menuOpen && (
          <div className="sm:hidden mt-4 bg-white/90 rounded-lg shadow-lg p-4">
            <ul className="flex flex-col gap-4">
              {navLinks.map((link) => (
                <li key={link.path}>
                  <Link
                    to={link.path}
                    className="text-base font-medium text-amber-900 block py-2"
                    onClick={() => setMenuOpen(false)}
                  >
                    {link.name}
                  </Link>
                </li>
              ))}
            </ul>
            <div className="flex items-center gap-2 px-4 py-2 rounded-lg border-2 border-amber-900 bg-amber-50 mt-4">
              <i className="ri-award-line text-lg text-amber-900"></i>
              <span className="text-xs font-semibold whitespace-nowrap text-amber-900">
                ISO Certified
              </span>
            </div>
          </div>
        )}
      </div>
    </nav>
  );
}