import { useEffect, useState } from 'react';
import Navbar from '../../components/feature/Navbar';
import Footer from '../../components/feature/Footer';

interface Product {
  id: number;
  title: string;
  capacity: string;
  image_path: string | any;
  features: string[];
  price: number;
  category: string;
  description: string;
}

export default function Products() {
  const [isScrolled, setIsScrolled] = useState(false);
  const [selectedCategory, setSelectedCategory] = useState('all');
  const [products, setProducts] = useState<Product[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [categories, setCategories] = useState<string[]>([]);

  // Fetch products from API
  useEffect(() => {
    const fetchProducts = async () => {
      try {
        setLoading(true);
        const response = await fetch('/server/api/products.php');

        if (!response.ok) {
          throw new Error('Failed to fetch products');
        }

        const data = await response.json();
        const productsList = data.data?.products || data.data || [];

        setProducts(productsList);

        // Extract unique categories
        const uniqueCategories = [...new Set(productsList.map((p: Product) => p.category))] as string[];
        setCategories(uniqueCategories);
      } catch (err) {
        setError('Unable to load products. Showing offline mode.');
        console.error('Error fetching products:', err);
        // Fallback to hardcoded products if API fails
        setProducts([]);
      } finally {
        setLoading(false);
      }
    };

    fetchProducts();
  }, []);

  useEffect(() => {
    const handleScroll = () => {
      setIsScrolled(window.scrollY > 50);
    };
    window.addEventListener('scroll', handleScroll);
    return () => window.removeEventListener('scroll', handleScroll);
  }, []);

  const filteredProducts = selectedCategory === 'all'
    ? products
    : products.filter(p => p.category === selectedCategory);
  return (
    <div className="min-h-screen bg-white">
      <Navbar isScrolled={isScrolled} />
      <section className="relative h-[400px] w-full flex items-center justify-center bg-white mt-20">
        <div className="absolute inset-0 flex items-center justify-center">
          <img
            src="/assets/Products.png"
            alt="Our Products"
            className="object-contain h-full w-auto max-w-full max-h-full p-4"
            style={{ margin: '0 auto' }}
          />
          <div className="absolute inset-0 bg-gradient-to-b from-black/60 via-black/50 to-black/60 pointer-events-none"></div>
        </div>
        <div className="relative z-10 w-full flex flex-col items-center justify-center text-center">
          <div className="mb-4 inline-block">
            <div className="border-t-2 border-b-2 border-amber-400 py-2 px-6">
              <span className="text-amber-300 text-sm font-medium tracking-widest uppercase">
                Our Range
              </span>
            </div>
          </div>
          <h1 className="text-5xl md:text-6xl font-bold text-white mb-4 drop-shadow-lg">
            Quality Products for Every Need
          </h1>
          <p className="text-xl text-white/90 max-w-2xl mx-auto drop-shadow">
            Premium water tanks and milk cans manufactured with precision
          </p>
        </div>
      </section>
      <section className="py-20 bg-white">
        <div className="max-w-7xl mx-auto px-6">
          <div className="flex flex-col sm:flex-row items-center justify-center gap-4 mb-12">
            <button
              onClick={() => setSelectedCategory('all')}
              className={`w-full sm:w-auto px-6 py-3 rounded-lg font-semibold transition-all duration-300 whitespace-nowrap cursor-pointer ${selectedCategory === 'all'
                  ? 'bg-amber-600 text-white shadow-lg'
                  : 'bg-amber-100 text-amber-900 hover:bg-amber-200'
                }`}
            >
              All Products
            </button>
            {categories.map((category) => (
              <button
                key={category}
                onClick={() => setSelectedCategory(category)}
                className={`w-full sm:w-auto px-6 py-3 rounded-lg font-semibold transition-all duration-300 whitespace-nowrap cursor-pointer capitalize ${selectedCategory === category
                    ? 'bg-amber-600 text-white shadow-lg'
                    : 'bg-amber-100 text-amber-900 hover:bg-amber-200'
                  }`}
              >
                {category.replace('-', ' ')}
              </button>
            ))}
          </div>

          {loading && (
            <div className="text-center py-20">
              <div className="text-amber-900 text-lg">Loading products...</div>
            </div>
          )}

          {error && (
            <div className="text-center py-8 px-6 bg-amber-50 rounded-lg border border-amber-200 mb-12">
              <p className="text-amber-900">{error}</p>
            </div>
          )}

          {!loading && filteredProducts.length === 0 && (
            <div className="text-center py-20">
              <div className="text-amber-900 text-lg">No products found in this category.</div>
            </div>
          )}

          {!loading && filteredProducts.length > 0 && (
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8" data-product-shop>
              {filteredProducts.map((product) => (
                <div
                  key={product.id}
                  className="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300 border border-amber-100"
                >
                  <div className="relative h-80 bg-gradient-to-br from-amber-50 to-white overflow-hidden">
                    <img
                      src={(() => {
                        const v: any = product.image_path;
                        const fallback = '/assets/500Lit.png';
                        if (!v) return fallback;
                        // If already an object with url/path/filename
                        if (typeof v === 'object') {
                          return v.url || v.path || (v.filename ? `/uploads/products/${v.filename}` : fallback);
                        }
                        // If string, try to parse JSON
                        if (typeof v === 'string') {
                          // JSON string stored
                          try {
                            const parsed = JSON.parse(v);
                            if (parsed && typeof parsed === 'object') {
                              return parsed.url || parsed.path || (parsed.filename ? `/uploads/products/${parsed.filename}` : fallback);
                            }
                          } catch (e) {
                            // not JSON
                          }

                          // If full URL or absolute path
                          if (v.startsWith('http') || v.startsWith('/')) return v;

                          // Otherwise treat as filename and build uploads path
                          return `/uploads/products/${v}`;
                        }

                        return fallback;
                      })()}
                      alt={product.title}
                      className="w-full h-full object-contain object-center p-8"
                      onError={(e) => { (e.currentTarget as HTMLImageElement).src = '/assets/500Lit.png'; }}
                    />
                  </div>
                  <div className="p-6">
                    <div className="flex items-center justify-between mb-3">
                      <h3 className="text-xl font-bold text-amber-900">{product.title}</h3>
                      <span className="px-3 py-1 bg-amber-100 text-amber-900 text-xs font-semibold rounded-full">
                        {product.capacity}
                      </span>
                    </div>
                    <p className="text-sm text-amber-700 mb-4 line-clamp-2">{product.description}</p>
                    <div className="flex flex-wrap gap-2 mb-4">
                      {product.features && Array.isArray(product.features) && product.features.map((feature, idx) => (
                        <span
                          key={idx}
                          className="px-2 py-1 bg-amber-50 text-amber-800 text-xs font-medium rounded"
                        >
                          {feature}
                        </span>
                      ))}
                    </div>
                    <div className="flex items-center justify-between pt-4 border-t border-amber-100">
                      <span className="text-amber-900 font-bold">
                        {product.price > 0 ? `₹${product.price.toLocaleString()}` : 'Contact for Price'}
                      </span>
                      <button className="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-semibold rounded-lg transition-colors whitespace-nowrap cursor-pointer">
                        Enquire Now
                      </button>
                    </div>
                  </div>
                </div>
              ))}
            </div>
          )}
        </div>
      </section>
      <Footer />
    </div>
  );
}