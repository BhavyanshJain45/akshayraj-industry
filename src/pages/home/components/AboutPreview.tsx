import { Link } from 'react-router-dom';

export default function AboutPreview() {
  return (
    <section className="grid gap-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:grid-cols-3 lg:p-8">
      <div className="lg:col-span-2">
        <h2 className="text-2xl font-bold text-slate-900">About Akshayraj Industry Pvt. Ltd.</h2>
        <p className="mt-4 text-slate-600">
          Akshayraj Industry Pvt. Ltd. is dedicated to manufacturing durable industrial products
          and customized engineering solutions for diverse sectors. Our integrated production
          approach ensures consistency, cost-efficiency, and strict quality control at every step.
        </p>
      </div>
      <div className="flex items-end">
        <Link
          to="/about"
          className="inline-flex rounded-md bg-brand-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-brand-700"
        >
          Learn More
        </Link>
      </div>
    </section>
  );
}
