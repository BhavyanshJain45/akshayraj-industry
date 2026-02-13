export default function Footer() {
  return (
    <footer className="border-t border-slate-200 bg-white">
      <div className="mx-auto flex w-full max-w-7xl flex-col items-center justify-between gap-3 px-4 py-6 text-sm text-slate-600 sm:flex-row sm:px-6 lg:px-8">
        <p>© {new Date().getFullYear()} Akshayraj Industry Pvt. Ltd. All rights reserved.</p>
        <p>Precision Manufacturing • Quality Assured • Trusted Delivery</p>
      </div>
    </footer>
  );
}
