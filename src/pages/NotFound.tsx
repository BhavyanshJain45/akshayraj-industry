import { Link } from 'react-router-dom';

export default function NotFoundPage() {
  return (
    <div className="rounded-2xl bg-white p-10 text-center shadow-sm">
      <p className="text-sm font-semibold uppercase tracking-wider text-brand-600">404</p>
      <h1 className="mt-2 text-3xl font-bold text-slate-900">Page Not Found</h1>
      <p className="mt-3 text-slate-600">The page you requested does not exist.</p>
      <Link
        to="/"
        className="mt-6 inline-flex rounded-md bg-brand-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-brand-700"
      >
        Back to Home
      </Link>
    </div>
  );
}
