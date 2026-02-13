import { RouteObject } from 'react-router-dom';
import Home from '../pages/home/page';
import About from '../pages/about/page';
import Products from '../pages/products/page';
import Manufacturing from '../pages/manufacturing/page';
import Contact from '../pages/contact/page';
import NotFound from '../pages/NotFound';
const routes: RouteObject[] = [
  {
    path: '/',
    element: <Home />,
  },
  {
    path: '/about',
    element: <About />,
  },
  {
    path: '/products',
    element: <Products />,
  },
  {
    path: '/manufacturing',
    element: <Manufacturing />,
  },
  {
    path: '/contact',
    element: <Contact />,
  },
  {
    path: '*',
    element: <NotFound />,
  },
];
export default routes; 
import App from '../App';
import HomePage from '../pages/home/page';
import AboutPage from '../pages/about/page';
import ProductsPage from '../pages/products/page';
import ManufacturingPage from '../pages/manufacturing/page';
import ContactPage from '../pages/contact/page';
import NotFoundPage from '../pages/NotFound';

export const routes: RouteObject[] = [
  {
    path: '/',
    element: <App />,
    children: [
      { index: true, element: <HomePage /> },
      { path: 'about', element: <AboutPage /> },
      { path: 'products', element: <ProductsPage /> },
      { path: 'manufacturing', element: <ManufacturingPage /> },
      { path: 'contact', element: <ContactPage /> },
      { path: '*', element: <NotFoundPage /> },
    ],
  },
];
