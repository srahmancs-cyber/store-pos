import Alpine from 'alpinejs';
import { createIcons, icons } from 'lucide';

window.Alpine = Alpine;
Alpine.start();

// Initialize Lucide icons after DOM loads
document.addEventListener('DOMContentLoaded', () => {
    createIcons({ icons });
});

// Re-initialize icons after Alpine updates DOM
document.addEventListener('alpine:initialized', () => {
    createIcons({ icons });
});
