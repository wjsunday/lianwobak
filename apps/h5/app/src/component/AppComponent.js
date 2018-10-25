/**
 * AppComponent
 */
import React, { Component } from 'react';

class AppComponent extends Component {
  render() {
    return (
      <div>{this.props.location.pathname}</div>
    );
  }
}

export default AppComponent;
