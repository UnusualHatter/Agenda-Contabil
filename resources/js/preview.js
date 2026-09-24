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
    const { previewDashboard, previewLogin } = document.documentElement.dataset;

    fillDemoLogin();
    document.addEventListener('livewire:navigated', fillDemoLogin);

    Livewire.hook('request', ({ fail }) => {
        fail(({ preventDefault }) => {
            preventDefault();
            notice();
        });
    });

    document.addEventListener('submit', (event) => {
        if (event.target.matches('form[data-farewell]') && ! prefersReducedMotion()) {
            return;
        }

        if (event.target.matches('form[data-farewell]')) {
            event.preventDefault();
            window.location.href = previewLogin;

            return;
        }

        event.preventDefault();

        if (event.target.matches('[data-login-form]')) {
            Livewire.navigate(previewDashboard);

            return;
        }

        notice();
    }, true);

    document.addEventListener('farewell:done', (event) => {
        event.preventDefault();
        window.location.href = previewLogin;
    });
}
