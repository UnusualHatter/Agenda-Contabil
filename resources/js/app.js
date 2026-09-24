// Livewire 4 ships its own Alpine; importing alpinejs separately would start
// two copies on pages that contain Livewire components.
import { Alpine, Livewire } from '../../vendor/livewire/livewire/dist/livewire.esm';
import themeToggle from './theme';

Alpine.data('themeToggle', themeToggle);

Livewire.start();
