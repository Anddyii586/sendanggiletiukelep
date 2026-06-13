import './animations';

function setExpanded(button, expanded) {
    button?.setAttribute('aria-expanded', expanded ? 'true' : 'false');
}

function initPublicMobileMenu() {
    document.querySelectorAll('[data-mobile-menu-root]').forEach((root) => {
        const toggle = root.querySelector('[data-mobile-menu-toggle]');
        const panel = root.querySelector('[data-mobile-menu-panel]');

        if (!toggle || !panel) {
            return;
        }

        const close = () => {
            panel.classList.add('hidden');
            setExpanded(toggle, false);
        };

        const open = () => {
            panel.classList.remove('hidden');
            setExpanded(toggle, true);
        };

        toggle.addEventListener('click', (event) => {
            event.stopPropagation();
            panel.classList.contains('hidden') ? open() : close();
        });

        document.addEventListener('click', (event) => {
            if (!root.contains(event.target)) {
                close();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                close();
            }
        });
    });
}

function initAdminDrawer() {
    const sidebar = document.querySelector('[data-admin-sidebar]');
    const backdrop = document.querySelector('[data-admin-sidebar-backdrop]');
    const toggles = document.querySelectorAll('[data-admin-menu-toggle]');
    const closeButtons = document.querySelectorAll('[data-admin-menu-close]');

    if (!sidebar || !toggles.length) {
        return;
    }

    const setOpen = (open) => {
        sidebar.classList.toggle('is-open', open);
        backdrop?.classList.toggle('hidden', !open);
        document.body.classList.toggle('overflow-hidden', open);
        toggles.forEach((toggle) => setExpanded(toggle, open));
    };

    toggles.forEach((toggle) => {
        toggle.addEventListener('click', () => setOpen(!sidebar.classList.contains('is-open')));
    });

    closeButtons.forEach((button) => {
        button.addEventListener('click', () => setOpen(false));
    });

    backdrop?.addEventListener('click', () => setOpen(false));

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            setOpen(false);
        }
    });

    const desktopQuery = window.matchMedia('(min-width: 1024px)');
    const handleDesktopChange = (event) => {
        if (event.matches) {
            setOpen(false);
        }
    };

    if (desktopQuery.addEventListener) {
        desktopQuery.addEventListener('change', handleDesktopChange);
    } else if (desktopQuery.addListener) {
        desktopQuery.addListener(handleDesktopChange);
    }
}

function initResponsiveNavigation() {
    initPublicMobileMenu();
    initAdminDrawer();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initResponsiveNavigation, { once: true });
} else {
    initResponsiveNavigation();
}
