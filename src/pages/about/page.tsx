const coreValues = [
  { title: 'Quality First', description: 'Every product is validated through strict inspection.' },
  { title: 'Integrity', description: 'Transparent communication and ethical manufacturing practices.' },
  { title: 'Innovation', description: 'Continuous investment in process and product improvements.' },
  { title: 'Customer Focus', description: 'Tailored solutions aligned to specific industry needs.' },
  { title: 'Safety', description: 'Safe workplace standards across all operations.' },
  { title: 'Reliability', description: 'Consistent delivery schedules with dependable performance.' },
];

export default function AboutPage() {
  return (
    <div className="space-y-8">
      <header className="rounded-2xl bg-white p-8 shadow-sm">
        <h1 className="text-3xl font-bold text-slate-900">About Us</h1>
        <p className="mt-4 max-w-3xl text-slate-600">
          Akshayraj Industry Pvt. Ltd. provides robust industrial manufacturing solutions backed by
          experienced engineers, modern machinery, and a customer-first mindset.
        </p>
      </header>

      <section className="rounded-2xl bg-white p-8 shadow-sm">
        <h2 className="text-2xl font-bold text-slate-900">Our Core Values</h2>
        <div className="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
          {coreValues.map((value) => (
            <article key={value.title} className="rounded-xl border border-slate-200 p-5">
              <h3 className="text-lg font-semibold text-slate-900">{value.title}</h3>
              <p className="mt-2 text-sm text-slate-600">{value.description}</p>
            </article>
          ))}
        </div>
      </section>
    </div>
  );
}
