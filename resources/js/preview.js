import { prefersReducedMotion } from './motion';

// Behaviour of the static preview published on GitHub Pages: there is no
// server, so anything that would save is stopped and explained instead.
function notice() {
    const banner = document.querySelector('[data-preview-banner]');

    banner?.animate(
        [{ transform: 'scale(1)' }, { transform: 'scale(1.06)' }, { transform: 'scale(1)' }],
        { duration: 420 },
    );
}

// Visitors of the preview have no account: the login form comes filled with
// the demo one, which only exists in the seeded preview data.
function fillDemoLogin() {
    const form = document.querySelector('[data-login-form]');

    if (form === null) {
        return;
    }

    form.querySelector('#email').value = 'admin@agenda.local';
    form.querySelector('#password').value = 'demonstracao';
}

export function enablePreview(Livewire) {
    const base = document.documentElement.dataset.previewBase;
    const pages = {
        welcome: `${base}/boas-vindas`,
        signedOut: `${base}/sessao-encerrada`,
    };

    // The curtain pages are the dashboard and the login with the animation
    // on top; once shown, the address goes back to the page itself.
    const settledPath = {
        '/boas-vindas/': `${base}/dashboard`,
        '/sessao-encerrada/': `${base}/login`,
    }[window.location.pathname.slice(new URL(base).pathname.replace(/\/$/, '').length)];

    if (settledPath !== undefined) {
        window.history.replaceState(null, '', settledPath);
    }

    fillDemoLogin();
    document.addEventListener('livewire:navigated', fillDemoLogin);

    Livewire.hook('request', ({ fail }) => {
        fail(({ preventDefault }) => {
            preventDefault();
            notice();
        });
    });

    document.addEventListener('submit', (event) => {
        const signingOut = event.target.matches('form[data-farewell]');

        if (signingOut && ! prefersReducedMotion()) {
            return;
        }

        event.preventDefault();

        if (signingOut) {
            window.location.href = pages.signedOut;

            return;
        }

        if (event.target.matches('[data-login-form]')) {
            window.location.href = pages.welcome;

            return;
        }

        notice();
    }, true);

    document.addEventListener('farewell:done', (event) => {
        event.preventDefault();
        window.location.href = pages.signedOut;
    });
}
