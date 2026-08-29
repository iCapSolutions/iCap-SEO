(function () {
    'use strict';

    // Scan requests run synchronously on the backend and the page can't report
    // real progress percentage - this shows an indeterminate progress bar plus
    // an elapsed-seconds counter so it's clear a scan is running in the
    // background, rather than leaving the page looking unresponsive until the
    // full-page submit completes and reloads it.
    document.querySelectorAll('.icap-seo-async-scan-form').forEach(function (form) {
        form.addEventListener('submit', function () {
            var button = form.querySelector('button[type="submit"]');
            var progress = form.querySelector('.icap-seo-scan-progress');
            var label = progress ? progress.querySelector('.icap-seo-scan-progress-label') : null;
            if (button) {
                button.disabled = true;
            }
            if (!progress || !label) {
                return;
            }

            var scanningText = progress.getAttribute('data-scanning-label') || 'Scanning…';
            var startedAt = Date.now();
            progress.classList.add('is-active');
            label.textContent = scanningText + ' (0s)';

            window.setInterval(function () {
                var elapsedSeconds = Math.floor((Date.now() - startedAt) / 1000);
                label.textContent = scanningText + ' (' + elapsedSeconds + 's)';
            }, 1000);
        });
    });
}());
