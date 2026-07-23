import { createApp } from 'vue';
import Alpine from 'alpinejs';
import CinemaManager from './components/admin/CinemaManager.vue';
import './chatbot.js';

// Initialize Alpine
window.Alpine = Alpine;
Alpine.start();

// Initialize Vue
const app = createApp({});
app.component('cinema-manager', CinemaManager);
app.mount('#app');
