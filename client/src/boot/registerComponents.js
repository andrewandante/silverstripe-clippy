/* global document */
import Injector from 'lib/Injector';
import UserGuide from '../components/UserGuide';

export default () => {
  Injector.component.registerMany({
    // List your React components here so Injector is aware of them
    UserGuide,
  });
};
