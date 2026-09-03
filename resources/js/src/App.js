import React, { Suspense } from 'react';
// import MaterialLayout from './components/Layout/MaterialLayout';
// import CheckoutPage from './components/CheckoutPage';

const MaterialLayout = React.lazy(() => import(
  /* webpackChunkName: "materialLayout" */
  /* webpackPrefetch: true */
  /* webpackPreload: true */ 
  './components/Layout/MaterialLayout'))
const CheckoutPage = React.lazy(() => import(
  /* webpackChunkName: "checkoutPage" */
  /* webpackPrefetch: true */
  /* webpackPreload: true */
  './components/CheckoutPage'))

import './index.css';

import './App.css';

function App() {
  return (
    <Suspense fallback={<div>Loading...</div>}>
      <MaterialLayout>
        <CheckoutPage />
      </MaterialLayout>
    </Suspense>
  );
}

export default App;
