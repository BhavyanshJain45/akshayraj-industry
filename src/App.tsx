import { Outlet } from 'react-router-dom';
import Navbar from './components/feature/Navbar';
import Footer from './components/feature/Footer';

export default function App() {
  return (
    <div className="min-h-screen bg-slate-50 text-slate-900">
      <Navbar />
      <main className="mx-auto w-full max-w-7xl px-4 pb-16 pt-28 sm:px-6 lg:px-8">
        <Outlet />
      </main>
      <Footer />
    </div>
  );
}
