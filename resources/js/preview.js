import { prefersReducedMotion } from './motion';

function notice() {
    const banner = document.querySelector('[data-preview-banner]');

    banner?.animate(
        [{ transform: 'scale(1)' }, { transform: 'scale(1.06)' }, { transform: 'scale(1)' }],
        { duration: 420 },
    );
}

function fillDemoLogin() {
    const form = document.querySelector('[data-login-form]');

    if (form === null) {
        return;
    }

    form.querySelector('#email').value = 'admin@agenda.local';
    form.querySelector('#password').value = 'demonstracao';
}

const normalize = (text) => text.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();

function filterClients() {
    const term = normalize(document.querySelector('[data-client-search]').value.trim());
    const digits = term.replace(/\D/g, '');
    const type = document.querySelector('[data-client-type]').value;

    document.querySelectorAll('[data-client-row]').forEach((row) => {
        const text = normalize(row.querySelector('[aria-controls]').textContent);
        const matchesTerm = term === '' || text.includes(term) || (digits !== '' && text.replace(/\D/g, '').includes(digits));
        const matchesType = type === '' || row.dataset.clientRow === type;

        row.hidden = ! (matchesTerm && matchesType);
    });
}

function prefetch(url) {
    document.head.append(Object.assign(document.createElement('link'), { rel: 'prefetch', href: url }));
}

export function enablePreview(Livewire) {
    const base = document.documentElement.dataset.previewBase;
    const pages = {
        welcome: `${base}/boas-vindas`,
        signedOut: `${base}/sessao-encerrada`,
    };

    const settledPath = {
        '/boas-vindas/': `${base}/dashboard`,
        '/sessao-encerrada/': `${base}/login`,
    }[window.location.pathname.slice(new URL(base).pathname.replace(/\/$/, '').length)];

    if (settledPath !== undefined) {
        window.history.replaceState(null, '', settledPath);
    }

    fillDemoLogin();
    document.addEventListener('livewire:navigated', fillDemoLogin);

    if (document.querySelector('[data-login-form]')) {
        prefetch(pages.welcome);
    }

    ['input', 'change'].forEach((type) => document.addEventListener(type, (event) => {
        if (! event.target.matches('[data-client-search], [data-client-type]')) {
            return;
        }

        filterClients();
    }, true));

    Livewire.hook('request', ({ fail }) => {
        fail(({ preventDefault }) => {
            preventDefault();
            notice();
        });
    });

    document.addEventListener('submit', (event) => {
        const signingOut = event.target.matches('form[data-farewell]');

        if (signingOut && ! prefersReducedMotion()) {
            prefetch(pages.signedOut);

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
