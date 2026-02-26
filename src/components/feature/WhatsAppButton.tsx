// src/components/feature/WhatsAppButton.tsx
import { useState } from 'react';

export default function WhatsAppButton() {
  const [isHovered, setIsHovered] = useState(false);

  // WhatsApp Business Number - Replace with your actual number
  const whatsappNumber = '919977421070'; // +91 9877421070
  const whatsappMessage = 'Hello! I am interested in your water tanks and milk cans. Can you provide more information?';
  const whatsappUrl = `https://wa.me/${whatsappNumber}?text=${encodeURIComponent(whatsappMessage)}`;

  return (
    <div className="fixed bottom-6 right-6 z-50 md:block hidden">
      {/* Chat Bubble on Hover */}
      {isHovered && (
        <div className="absolute bottom-full right-0 mb-4 bg-white rounded-lg shadow-xl border border-green-100 p-4 w-64 animate-fadeInUp">
          <div className="flex items-center gap-2 mb-3">
            <div className="w-10 h-10 bg-gradient-to-br from-green-400 to-green-600 rounded-full flex items-center justify-center">
              <i className="ri-whatsapp-line text-lg text-white"></i>
            </div>
            <div>
              <h3 className="font-bold text-gray-800 text-sm">Chat with us!</h3>
              <p className="text-xs text-gray-600">Typically replies in minutes</p>
            </div>
          </div>
          <p className="text-sm text-gray-700 mb-3">
            Have questions about our products? We're here to help!
          </p>
          <a
            href={whatsappUrl}
            target="_blank"
            rel="noopener noreferrer"
            className="block w-full py-2 px-3 bg-gradient-to-r from-green-400 to-green-600 text-white rounded-lg text-center font-semibold text-sm hover:shadow-lg transition-all duration-300"
          >
            Start Chat →
          </a>
        </div>
      )}

      {/* WhatsApp Button */}
      <a
        href={whatsappUrl}
        target="_blank"
        rel="noopener noreferrer"
        onMouseEnter={() => setIsHovered(true)}
        onMouseLeave={() => setIsHovered(false)}
        className={`inline-flex items-center justify-center w-16 h-16 rounded-full shadow-lg hover:shadow-2xl transition-all duration-300 group ${
          isHovered
            ? 'bg-gradient-to-br from-green-400 to-green-600 scale-110'
            : 'bg-gradient-to-br from-green-400 to-green-600 hover:scale-105'
        }`}
        title="Chat with us on WhatsApp"
      >
        <i className="ri-whatsapp-line text-3xl text-white"></i>
        
        {/* Pulse Animation */}
        <span className="absolute w-16 h-16 rounded-full border-2 border-green-400 animate-pulse"></span>
      </a>
    </div>
  );
}
