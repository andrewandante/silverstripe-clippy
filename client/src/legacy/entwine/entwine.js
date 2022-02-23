import jQuery from 'jquery';
import React from 'react';
import ReactDOM from 'react-dom';
import { loadComponent } from 'lib/Injector';

jQuery.entwine('userguide', ($) => {
  $('.cms .cms-panel-link').entwine({
    onclick: (e) => {
      const target = new URL(e.target.href);
      const isActive = target.pathname.indexOf('/admin/pages/guide/show/') === 0 ? 1 : 0;

      $('#userguide_toggle').val(isActive);

      // Communication between Entwine and React components via custom events
      const event = new CustomEvent('userguide:change', { detail: isActive });
      document.dispatchEvent(event);
    }
  });

/**
 * Uses entwine to inject the UserGuide React component into the DOM, when used
 * outside of a React context e.g. in the CMS
 */
  $('.js-injector-boot #userguide_frame').entwine({
    onmatch() {
      const Component = loadComponent('UserGuide');
      const subscribe = document.getElementById('userguide_toggle');

      ReactDOM.render(
        <Component subscribe={subscribe} />,
        this[0]
      );
    },

    onunmatch() {
      ReactDOM.unmountComponentAtNode(this[0]);
    }
  });
});
