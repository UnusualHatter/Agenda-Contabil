import { prefersReducedMotion } from './motion';

const FAREWELL_DURATION = 1700;

function dismissOnEnd(curtain) {
    curtain.addEventListener('animationend', (event) => {
        if (event.target === curtain) {
            curtain.remove();
        }
    });
    curtain.addEventListener('click', () => curtain.remove());
}

function playFarewell(form, origin) {
    const template = document.querySelector('template[data-farewell]');
    const curtain = template.content.firstElementChild.cloneNode(true);
    const { left, top, width, height } = origin.getBoundingClientRect();

    curtain.style.setProperty('--origin-x', `${left + width / 2}px`);
    curtain.style.setProperty('--origin-y', `${top + height / 2}px`);
    document.body.append(curtain);

    const leave = () => {
        // The static preview cancels it.
        if (form.dispatchEvent(new CustomEvent('farewell:done', { bubbles: true, cancelable: true }))) {
            form.submit();
        }
    };

    curtain.addEventListener('click', leave, { once: true });
    window.setTimeout(leave, FAREWELL_DURATION);
}

export function startCurtains() {
    document.addEventListener('submit', (event) => {
        const form = event.target.closest('form[data-farewell]');

        if (! form || prefersReducedMotion()) {
            return;
        }

        event.preventDefault();
        playFarewell(form, event.submitter ?? form);
    });

    document.addEventListener('livewire:navigated', () => {
        document.querySelectorAll('[data-curtain]').forEach(dismissOnEnd);
    });
}
