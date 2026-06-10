const reducedMotionQuery = '(prefers-reduced-motion: reduce)';

function prefersReducedMotion() {
    return window.matchMedia?.(reducedMotionQuery).matches ?? false;
}

function uniqueElements(selectors) {
    return [...new Set(selectors.flatMap((selector) => [...document.querySelectorAll(selector)]))];
}

function setDelay(element, index, step = 95, max = 380) {
    if (element.style.getPropertyValue('--motion-delay')) {
        return;
    }

    element.style.setProperty('--motion-delay', `${Math.min(index * step, max)}ms`);
}

function applyPageLoadMotion() {
    [
        ['.landing-hero h1', 'animate-fade-up', 0],
        ['.landing-hero p', 'animate-fade-up', 140],
        ['.landing-hero .btn-primary, .landing-hero a[href*="gallery"]', 'animate-fade-up', 260],
        ['.auth-image-panel h1, .auth-image-panel p', 'animate-fade-up', 140],
        ['.auth-card', 'animate-scale-in', 0],
    ].forEach(([selector, className, delay]) => {
        document.querySelectorAll(selector).forEach((element) => {
            element.classList.add(className);
            element.style.setProperty('--motion-delay', `${delay}ms`);
        });
    });
}

function enhanceHoverTargets() {
    uniqueElements([
        'article.surface-card',
        'article.soft-card',
        'a.surface-card',
        '.dashboard-main article.surface-card',
        '.dashboard-main section.surface-card',
        '.package-card',
        '.review-card',
        '.metric-card',
    ]).forEach((element) => element.classList.add('hover-lift'));

    document.querySelectorAll('.surface-card img, .soft-card img, .gallery-grid img, .motion-image').forEach((image) => {
        image.classList.add('image-hover-zoom');
    });
}

function revealTargets() {
    return uniqueElements([
        '.reveal-on-scroll',
        '[data-reveal]',
        '.surface-card',
        '.soft-card',
        '.gallery-grid > *',
        '[data-testid="ticket-qr"]',
        '.app-container > .mb-8',
        '.app-container > .mb-10',
        '.app-container > .mx-auto.max-w-2xl',
        '.app-container > .grid > article',
        '.app-container article.surface-card',
        '.app-container article.soft-card',
        '.app-container aside.surface-card',
        '.app-container > .surface-card',
        '.dashboard-main > section > .flex:first-child',
        '.dashboard-main article.surface-card',
        '.dashboard-main section.surface-card',
        '.ticket-card',
        '.booking-motion-card',
    ]).filter((element) => !element.closest('[data-no-reveal]'));
}

function initRevealOnScroll() {
    const targets = revealTargets();

    if (!targets.length) {
        return;
    }

    targets.forEach((element, index) => {
        element.classList.add('reveal-on-scroll');
        setDelay(element, index % 5);
    });

    if (!('IntersectionObserver' in window)) {
        targets.forEach((element) => element.classList.add('is-visible'));
        return;
    }

    document.documentElement.classList.add('motion-ready');

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) {
                    return;
                }

                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            });
        },
        {
            rootMargin: '0px 0px -10% 0px',
            threshold: 0.12,
        },
    );

    targets.forEach((element) => observer.observe(element));
}

function initMotion() {
    if (prefersReducedMotion()) {
        return;
    }

    try {
        applyPageLoadMotion();
        enhanceHoverTargets();
        initRevealOnScroll();
    } catch {
        document.documentElement.classList.remove('motion-ready');
        document.querySelectorAll('.reveal-on-scroll').forEach((element) => element.classList.add('is-visible'));
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initMotion, { once: true });
} else {
    initMotion();
}
