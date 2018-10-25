/**
 * App.
 */
import React from 'react';
import { Router, Route } from 'react-router';
import history from './util/history';
// Old routes.
import OldRouter from '../../webapp/App';
// components.
import AppComponent from './component/AppComponent';

const App = () => (
  <Router history={history}>
    <Route path="/new" component={AppComponent}>
    </Route>
    {OldRouter}
  </Router>
);

export default App;
