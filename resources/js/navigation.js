import { EASE_OUT, EASE_SPRING, prefersReducedMotion } from './motion';

// The menu is rebuilt on every page, so the indicator slides from the last position kept here.
let lastPosition = null;

function slideIndicator() {
    const indicator = document.querySelector('[data-nav-indicator]');
    const active = indicator?.parentElement.querySelector('[aria-current="page"]');

    if (! active) {
        lastPosition = null;

        return;
    }

    const to = { x: active.offsetLeft, width: active.offsetWidth };
    const from = lastPosition ?? to;
    lastPosition = to;

    indicator.animate(
        [
            { transform: `translateX(${from.x}px)`, width: `${from.width}px` },
            { transform: `translateX(${to.x}px)`, width: `${to.width}px` },
        ],
        { duration: prefersReducedMotion() ? 0 : 480, easing: EASE_SPRING, fill: 'forwards' },
    );
}

function revealPage() {
    const main = document.querySelector('main');

    if (! main || prefersReducedMotion()) {
        return;
    }

    main.animate(
        [
            { opacity: 0, transform: 'translateY(10px)' },
            { opacity: 1, transform: 'none' },
        ],
        { duration: 420, easing: EASE_OUT },
    );
}

export function trackNavigation() {
    let firstLoad = true;

    document.addEventListener('livewire:navigated', () => {
        slideIndicator();

        if (! firstLoad) {
            revealPage();
        }

        firstLoad = false;
    });

    window.addEventListener('resize', () => {
        lastPosition = null;
        slideIndicator();
    });
}
