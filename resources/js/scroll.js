import Lenis from 'lenis';
import { prefersReducedMotion } from './motion';

// Eased wheel scrolling on top of the native scroll, so keyboard, find in
// page and scrollbars keep working. Off for people who ask for less motion.
export function startSmoothScroll() {
    if (prefersReducedMotion()) {
        return;
    }

    const lenis = new Lenis({ autoRaf: true, anchors: { offset: -88 }, lerp: 0.09 });

    // Livewire restores or resets the scroll on navigation; follow it
    // instead of easing back to where the previous page was.
    document.addEventListener('livewire:navigated', () => {
        lenis.scrollTo(window.scrollY, { immediate: true, force: true });
        lenis.resize();
    });
}
