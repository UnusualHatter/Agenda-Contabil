// Alpine component behind <x-theme-toggle>. The first paint is handled by
// layouts/partials/theme.blade.php; this only reacts to the user's choice.
const prefersDark = window.matchMedia('(prefers-color-scheme: dark)');
const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
const options = ['light', 'dark', 'system'];

const REVEAL_DURATION = 900;
const REVEAL_EASING = 'cubic-bezier(0.65, 0, 0.35, 1)';

const resolve = (choice) => (choice === 'dark' || (choice === 'system' && prefersDark.matches) ? 'dark' : 'light');

const paint = (theme) => {
    document.documentElement.dataset.theme = theme;
};

// Swaps without any transition, for browsers without view transitions or
// people who prefer reduced motion. The class is kept for two frames so the
// new colours are painted before hover transitions come back.
function swap(theme) {
    const root = document.documentElement;

    root.classList.add('theme-transition');
    paint(theme);
    requestAnimationFrame(() => requestAnimationFrame(() => root.classList.remove('theme-transition')));
}

// Circular reveal from the pressed button: one view transition, with a
// growing clip-path on the new snapshot.
function reveal(theme, origin) {
    const root = document.documentElement;
    const { left, top, width, height } = origin.getBoundingClientRect();
    const x = left + width / 2;
    const y = top + height / 2;
    const radius = Math.hypot(Math.max(x, window.innerWidth - x), Math.max(y, window.innerHeight - y));

    root.classList.add('theme-transition');

    const transition = document.startViewTransition(() => paint(theme));

    // `ready` rejects when the browser skips the transition (a hidden tab,
    // for instance); the new theme is already painted by then.
    transition.ready.then(
        () => root.animate(
            { clipPath: [`circle(0px at ${x}px ${y}px)`, `circle(${radius}px at ${x}px ${y}px)`] },
            { duration: REVEAL_DURATION, easing: REVEAL_EASING, pseudoElement: '::view-transition-new(root)' },
        ),
        () => {},
    );
    transition.finished.finally(() => root.classList.remove('theme-transition'));
}

function apply(choice, origin = null) {
    const theme = resolve(choice);
    const animate = origin !== null
        && 'startViewTransition' in document
        && ! prefersReducedMotion.matches
        && document.documentElement.dataset.theme !== theme;

    if (! animate) {
        swap(theme);

        return;
    }

    reveal(theme, origin);
}

export default () => ({
    choice: localStorage.getItem('theme') ?? 'system',

    get thumbOffset() {
        return `translateX(${options.indexOf(this.choice) * 100}%)`;
    },

    init() {
        prefersDark.addEventListener('change', () => apply(this.choice));
    },

    choose(choice, origin) {
        this.choice = choice;
        apply(choice, origin);

        if (choice === 'system') {
            localStorage.removeItem('theme');

            return;
        }

        localStorage.setItem('theme', choice);
    },
});
