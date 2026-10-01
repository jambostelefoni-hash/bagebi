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

class RegistrationBoundary extends React.Component {
  state = { failed: false };

  static getDerivedStateFromError() {
    return { failed: true };
  }

  render() {
    if (this.state.failed) {
      return <div role="alert" className="registration-waiting-note">
        <p>ფორმის ჩატვირთვა ვერ მოხერხდა. განაახლეთ გვერდი და სცადეთ ხელახლა.</p>
        <button type="button" onClick={() => window.location.reload()}>გვერდის განახლება</button>
      </div>;
    }
    return this.props.children;
  }
}

function App() {
  return (
    <RegistrationBoundary>
    <Suspense fallback={<div role="status">სარეგისტრაციო ფორმა იტვირთება…</div>}>
      <MaterialLayout>
        <CheckoutPage />
      </MaterialLayout>
    </Suspense>
    </RegistrationBoundary>
  );
}

export default App;
