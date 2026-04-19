import './bootstrap';
import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();

// Sidebar toggle — drawer on mobile, collapse on desktop
document.addEventListener('DOMContentLoaded', () => {
    const btn     = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');

    if (!btn || !sidebar) return;

    const isDesktop = () => window.innerWidth >= 1024; // lg breakpoint

    function openDrawer() {
        sidebar.classList.remove('-translate-x-full');
        overlay.classList.remove('hidden');
        document.body.classList.add('overflow-hidden', 'lg:overflow-auto');
    }

    function closeDrawer() {
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('hidden');
        document.body.classList.remove('overflow-hidden', 'lg:overflow-auto');
    }

    function toggleDesktop() {
        sidebar.classList.toggle('lg:hidden');
    }

    btn.addEventListener('click', () => {
        if (isDesktop()) {
            toggleDesktop();
        } else {
            const isOpen = !sidebar.classList.contains('-translate-x-full');
            isOpen ? closeDrawer() : openDrawer();
        }
    });

    // Close drawer when clicking overlay
    overlay?.addEventListener('click', closeDrawer);

    // Close drawer on resize to desktop
    window.addEventListener('resize', () => {
        if (isDesktop()) {
            closeDrawer();
            sidebar.classList.remove('lg:hidden');
        }
    });
});
