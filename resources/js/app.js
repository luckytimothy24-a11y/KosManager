import './bootstrap';
import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';

window.Alpine = Alpine;
window.Chart = Chart;

// ---------------------------------------------------------------------------
// Global submit feedback.
// - Mencegah double-submit pada form server-rendered.
// - Form yang sudah mengelola state sendiri (Alpine x-on:submit) atau memakai
//   data-no-loading dilewati agar tidak bentrok.
// - Tombol dinonaktifkan secara asinkron supaya nilai tombol yang diklik tetap
//   ikut terkirim.
// ---------------------------------------------------------------------------
const resetFormBusy = (form) => {
    if (!(form instanceof HTMLFormElement)) return;

    delete form.dataset.submitting;
    form.removeAttribute('aria-busy');

    form.querySelectorAll('[data-was-disabled="false"]').forEach((button) => {
        button.disabled = false;
        delete button.dataset.wasDisabled;
    });
};

document.addEventListener('submit', (event) => {
    const form = event.target;

    if (!(form instanceof HTMLFormElement)) return;

    if (form.dataset.submitting === 'true') {
        event.preventDefault();
        return;
    }

    if (form.dataset.noLoading !== undefined) return;
    if ((form.getAttribute('method') || 'get').toLowerCase() === 'get') return;
    if (form.hasAttribute('x-on:submit') || form.hasAttribute('@submit')) return;

    form.dataset.submitting = 'true';
    form.setAttribute('aria-busy', 'true');

    window.setTimeout(() => {
        form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach((button) => {
            if (!button.disabled) {
                button.dataset.wasDisabled = 'false';
                button.disabled = true;
            }
        });
    }, 0);
});

window.addEventListener('pageshow', () => {
    document.querySelectorAll('form[data-submitting="true"]').forEach(resetFormBusy);
});

Alpine.start();
