export default function TrustBadges() {
  const badges = [
    {
      icon: 'ri-shield-check-line',
      title: 'Durable & Reliable',
      description: 'Built to last for years',
    },
    {
      icon: 'ri-checkbox-circle-line',
      title: '100% Food Grade',
      description: 'Safe for drinking water',
    },
    {
      icon: 'ri-award-line',
      title: '10 Year Warranty',
      description: 'Quality guaranteed',
    },
    {
      icon: 'ri-star-line',
      title: 'Trusted Quality',
      description: 'ISO & BIS certified',
    },
  ];
  return (
    <section className="py-16 bg-gradient-to-b from-amber-50 to-white">
      <div className="max-w-7xl mx-auto px-6">
        <div className="grid grid-cols-1 md:grid-cols-4 gap-8">
          {badges.map((badge, index) => (
            <div
              key={index}
              className="flex flex-col items-center text-center p-6 bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300"
            >
              <div className="w-16 h-16 flex items-center justify-center bg-amber-100 rounded-full mb-4">
                <i className={`${badge.icon} text-3xl text-amber-700`}></i>
              </div>
              <h3 className="text-lg font-bold text-amber-900 mb-2">{badge.title}</h3>
              <p className="text-sm text-gray-600">{badge.description}</p>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}