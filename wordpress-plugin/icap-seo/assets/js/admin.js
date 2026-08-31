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

    // Solves the self-hosted proof-of-work captcha (Altcha protocol) for the
    // registration-request signup form before allowing submit. The challenge is
    // rendered server-side into a data attribute (see registration_challenge in
    // class-icap-seo-admin.php) rather than fetched client-side, since this
    // plugin has no CORS-enabled path for the browser to call the iCap SEO API
    // directly - only server-to-server calls exist today.
    document.querySelectorAll('.icap-seo-registration-request-form').forEach(function (form) {
        var challengeRaw = form.getAttribute('data-altcha-challenge');
        if (!challengeRaw) {
            return;
        }

        var challenge;
        try {
            challenge = JSON.parse(challengeRaw);
        } catch (e) {
            return;
        }
        if (!challenge || !challenge.challenge || !challenge.salt) {
            return;
        }

        var submitButton = form.querySelector('button[type="submit"]');
        var hiddenField = form.querySelector('input[name="altcha_payload"]');
        var originalButtonText = submitButton ? submitButton.textContent : '';

        if (submitButton) {
            submitButton.disabled = true;
            submitButton.textContent = 'Verifying…';
        }

        function toHex(buffer) {
            return Array.prototype.map
                .call(new Uint8Array(buffer), function (byte) {
                    return ('00' + byte.toString(16)).slice(-2);
                })
                .join('');
        }

        async function solve() {
            var maxNumber = challenge.maxnumber || 100000;
            for (var number = 0; number <= maxNumber; number += 1) {
                var data = new TextEncoder().encode(challenge.salt + number);
                var digestBuffer = await window.crypto.subtle.digest('SHA-256', data);
                if (toHex(digestBuffer) === challenge.challenge) {
                    return number;
                }
            }
            return null;
        }

        solve()
            .then(function (number) {
                if (number === null || !hiddenField) {
                    return;
                }
                hiddenField.value = window.btoa(
                    JSON.stringify({
                        algorithm: challenge.algorithm,
                        challenge: challenge.challenge,
                        salt: challenge.salt,
                        signature: challenge.signature,
                        number: number,
                    })
                );
            })
            .finally(function () {
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.textContent = originalButtonText;
                }
            });
    });
}());
