const badges = [
  'ISO-Aligned Processes',
  'Experienced Engineering Team',
  'Timely Bulk Deliveries',
  'Custom Product Development',
];

export default function TrustBadges() {
  return (
    <section className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
      {badges.map((badge) => (
        <div
          key={badge}
          className="rounded-xl border border-slate-200 bg-white px-5 py-4 text-center text-sm font-semibold text-slate-700 shadow-sm"
        >
          {badge}
        </div>
      ))}
    </section>
  );
}
