import { Link } from 'react-router-dom';

export default function Hero() {
  return (
    <section className="rounded-2xl bg-gradient-to-r from-brand-700 to-brand-500 px-6 py-16 text-white sm:px-10">
      <p className="mb-4 inline-block rounded-full bg-white/20 px-4 py-1 text-sm font-medium">
        Engineering Excellence Since 2008
      </p>
      <h1 className="max-w-3xl text-3xl font-bold leading-tight sm:text-5xl">
        Akshayraj Industry Pvt. Ltd. — Trusted Partner in Industrial Manufacturing Solutions
      </h1>
      <p className="mt-5 max-w-2xl text-base text-blue-100 sm:text-lg">
        We deliver precision-fabricated components and high-performance industrial systems with
        a commitment to quality, safety, and long-term partnerships.
      </p>
      <div className="mt-8 flex flex-wrap gap-3">
        <Link
          to="/products"
          className="rounded-md bg-white px-5 py-3 text-sm font-semibold text-brand-700 transition hover:bg-blue-50"
        >
          Explore Products
        </Link>
        <Link
          to="/contact"
          className="rounded-md border border-white px-5 py-3 text-sm font-semibold text-white transition hover:bg-white/10"
        >
          Request a Quote
        </Link>
      </div>
    </section>
  );
}
