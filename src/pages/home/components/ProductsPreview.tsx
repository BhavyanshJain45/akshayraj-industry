const products = [
  'Industrial Fabricated Components',
  'Material Handling Assemblies',
  'Precision-Machined Parts',
  'Custom Heavy-Duty Structures',
];

export default function ProductsPreview() {
  return (
    <section>
      <div className="mb-6 flex items-end justify-between">
        <h2 className="text-2xl font-bold text-slate-900">Featured Product Categories</h2>
      </div>
      <div className="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
        {products.map((product) => (
          <article
            key={product}
            className="rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-md"
          >
            <h3 className="text-base font-semibold text-slate-900">{product}</h3>
            <p className="mt-3 text-sm text-slate-600">
              Engineered with precision for superior performance and longer operational lifecycle.
            </p>
          </article>
        ))}
      </div>
    </section>
  );
}
