// resources/js/app.js
console.log('app.js LOADED');

import 'bootstrap'; // to ładuje bundle z Popperem


import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap/dist/js/bootstrap.bundle.min.js'; // <-- bundle = Bootstrap + Popper


import { Dropdown } from 'bootstrap';
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-bs-toggle="dropdown"]').forEach(el => new Dropdown(el));
  console.log('Bootstrap dropdowns initialized');
});


