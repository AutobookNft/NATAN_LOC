import './styles.css';
import { App } from './app';

// Initialize app
document.addEventListener('DOMContentLoaded', () => {
  const appElement = document.getElementById('app');
  if (appElement) {
    new App(appElement);
  }
});

