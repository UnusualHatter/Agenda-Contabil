// Livewire 4 ships its own Alpine; importing alpinejs separately would start
// two copies on pages that contain Livewire components.
import { Alpine, Livewire } from '../../vendor/livewire/livewire/dist/livewire.esm';
import { startCurtains } from './curtain';
import { trackNavigation } from './navigation';
import { startSmoothScroll } from './scroll';
import themeToggle from './theme';

Alpine.data('themeToggle', themeToggle);

// The calendar bundle is only downloaded on the agenda page, and taken down
// before Livewire swaps the page for another one.
let unmountAgenda = null;

document.addEventListener('livewire:navigating', () => {
    unmountAgenda?.();
    unmountAgenda = null;
});

document.addEventListener('livewire:navigated', async () => {
    const element = document.querySelector('[data-agenda]');

    if (element === null) {
        return;
    }

    const { mountAgenda } = await import('./agenda');
    unmountAgenda = mountAgenda(element, (url) => Livewire.navigate(url));
});

trackNavigation();
startCurtains();
startSmoothScroll();

if (document.documentElement.dataset.preview !== undefined) {
    import('./preview').then(({ enablePreview }) => enablePreview(Livewire));
}

Livewire.start();
