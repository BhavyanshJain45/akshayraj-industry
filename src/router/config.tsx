import { RouteObject } from 'react-router-dom';
import Home from '../pages/home/page';
import About from '../pages/about/page';
import Products from '../pages/products/page';
import Manufacturing from '../pages/manufacturing/page';
import Contact from '../pages/contact/page';
import DealerDistributor from '../pages/dealer-distributor/page';
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
    path: '/dealer-distributor',
    element: <DealerDistributor />,
  },
  {
    path: '*',
    element: <NotFound />,
  },
];
export default routes;