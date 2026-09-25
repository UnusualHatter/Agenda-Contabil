import Lenis from 'lenis';
import { prefersReducedMotion } from './motion';

export function startSmoothScroll() {
    if (prefersReducedMotion()) {
        return;
    }

    const lenis = new Lenis({ autoRaf: true, anchors: { offset: -88 }, lerp: 0.09 });

    // Follow Livewire's scroll reset instead of easing back to the old position.
    document.addEventListener('livewire:navigated', () => {
        lenis.scrollTo(window.scrollY, { immediate: true, force: true });
        lenis.resize();
    });
}
