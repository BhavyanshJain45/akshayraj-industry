const products = [
  'Structural Fabrication Units',
  'Heavy-Duty Machine Frames',
  'Industrial Conveyor Components',
  'Custom Precision Parts',
  'Plant Utility Assemblies',
  'Maintenance Replacement Components',
];

export default function ProductsPage() {
  return (
    <div className="space-y-8">
      <header className="rounded-2xl bg-white p-8 shadow-sm">
        <h1 className="text-3xl font-bold text-slate-900">Products</h1>
        <p className="mt-4 text-slate-600">
          Discover our broad portfolio of durable and high-performance industrial products.
        </p>
      </header>
      <section className="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        {products.map((product) => (
          <article key={product} className="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 className="text-lg font-semibold text-slate-900">{product}</h2>
            <p className="mt-2 text-sm text-slate-600">
              Built for demanding environments with precision and quality assurance.
            </p>
          </article>
        ))}
      </section>
    </div>
  );
}
