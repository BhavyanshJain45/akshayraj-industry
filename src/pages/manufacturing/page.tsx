const processSteps = [
  'Requirement Analysis & Engineering Design',
  'Material Selection & Procurement',
  'Fabrication, Machining & Assembly',
  'Quality Checks & Performance Validation',
  'Packaging and On-Time Dispatch',
];

export default function ManufacturingPage() {
  return (
    <div className="space-y-8">
      <header className="rounded-2xl bg-white p-8 shadow-sm">
        <h1 className="text-3xl font-bold text-slate-900">Manufacturing</h1>
        <p className="mt-4 text-slate-600">
          Our end-to-end manufacturing pipeline ensures precision, efficiency, and scalability for
          varied industrial requirements.
        </p>
      </header>
      <section className="rounded-2xl bg-white p-8 shadow-sm">
        <h2 className="text-2xl font-bold text-slate-900">Our Process</h2>
        <ol className="mt-6 space-y-3">
          {processSteps.map((step, index) => (
            <li key={step} className="flex items-start gap-3 rounded-lg border border-slate-200 p-4">
              <span className="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-brand-100 text-sm font-semibold text-brand-700">
                {index + 1}
              </span>
              <span className="text-slate-700">{step}</span>
            </li>
          ))}
        </ol>
      </section>
    </div>
  );
}
