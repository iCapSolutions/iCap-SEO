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

    // SEO Performance tab trend chart: adds a hover crosshair across all four
    // overlaid series at once, since each line's own native <title> tooltip
    // (kept above as a no-JS fallback) only ever surfaces one series at a
    // time. The chart's plotted points are precomputed server-side (see
    // icap_seo_sparkline_points() in dashboard.php) and passed through here
    // via a data-chart JSON attribute, so this only does hit-testing and
    // DOM/tooltip updates - no re-scaling logic duplicated in JS.
    var svgNs = 'http://www.w3.org/2000/svg';

    document.querySelectorAll('.icap-seo-performance-chart-plot[data-chart]').forEach(function (plotEl) {
        var raw = plotEl.getAttribute('data-chart');
        var data;
        try {
            data = JSON.parse(raw);
        } catch (e) {
            return;
        }
        if (!data || !Array.isArray(data.dates) || !data.dates.length) {
            return;
        }

        var svg = plotEl.querySelector('.icap-seo-performance-chart-svg');
        if (!svg) {
            return;
        }

        var seriesKeys = Object.keys(data.series || {});
        if (!seriesKeys.length) {
            return;
        }

        var n = data.dates.length;
        var plotWidth = data.width - (data.padX * 2);
        var stepX = n > 1 ? plotWidth / (n - 1) : 0;

        var crosshair = document.createElementNS(svgNs, 'line');
        crosshair.setAttribute('class', 'icap-seo-performance-chart-crosshair');
        crosshair.setAttribute('y1', String(data.padTop));
        crosshair.setAttribute('y2', String(data.height - data.padBottom));
        crosshair.setAttribute('visibility', 'hidden');
        svg.appendChild(crosshair);

        var hoverDots = {};
        seriesKeys.forEach(function (key) {
            var dot = document.createElementNS(svgNs, 'circle');
            dot.setAttribute('class', 'icap-seo-performance-chart-hoverdot icap-seo-performance-chart-series--' + key);
            dot.setAttribute('r', '5');
            dot.setAttribute('visibility', 'hidden');
            svg.appendChild(dot);
            hoverDots[key] = dot;
        });

        var hitRect = document.createElementNS(svgNs, 'rect');
        hitRect.setAttribute('class', 'icap-seo-performance-chart-hitrect');
        hitRect.setAttribute('x', String(data.padX));
        hitRect.setAttribute('y', '0');
        hitRect.setAttribute('width', String(plotWidth));
        hitRect.setAttribute('height', String(data.height));
        hitRect.setAttribute('fill', 'transparent');
        svg.appendChild(hitRect);

        var tooltip = document.createElement('div');
        tooltip.className = 'icap-seo-performance-chart-tooltip';
        plotEl.appendChild(tooltip);

        function formatValue(value, format) {
            if (format === 'percent') {
                return (value * 100).toFixed(1) + '%';
            }
            if (format === 'decimal1') {
                return value.toFixed(1);
            }
            return Math.round(value).toLocaleString();
        }

        function formatDate(iso) {
            var parts = String(iso).split('-');
            if (parts.length !== 3) {
                return iso;
            }
            var dt = new Date(Number(parts[0]), Number(parts[1]) - 1, Number(parts[2]));
            return dt.toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
        }

        function updateAt(idx) {
            idx = Math.max(0, Math.min(n - 1, idx));
            var px = data.padX + (idx * stepX);

            crosshair.setAttribute('x1', String(px));
            crosshair.setAttribute('x2', String(px));
            crosshair.setAttribute('visibility', 'visible');

            tooltip.innerHTML = '';
            var header = document.createElement('div');
            header.className = 'icap-seo-performance-chart-tooltip-date';
            header.textContent = formatDate(data.dates[idx]);
            tooltip.appendChild(header);

            seriesKeys.forEach(function (key) {
                var series = data.series[key];
                var point = series.points[idx];
                if (!point) {
                    return;
                }
                hoverDots[key].setAttribute('cx', String(point.x));
                hoverDots[key].setAttribute('cy', String(point.y));
                hoverDots[key].setAttribute('visibility', 'visible');

                var row = document.createElement('div');
                row.className = 'icap-seo-performance-chart-tooltip-row icap-seo-performance-chart-series--' + key;
                var swatch = document.createElement('span');
                swatch.className = 'icap-seo-performance-chart-tooltip-swatch';
                row.appendChild(swatch);
                row.appendChild(document.createTextNode(series.label + ': '));
                var strong = document.createElement('strong');
                strong.textContent = formatValue(point.value, series.format);
                row.appendChild(strong);
                tooltip.appendChild(row);
            });

            tooltip.style.display = 'block';
            var plotRect = plotEl.getBoundingClientRect();
            var scaleX = plotRect.width / data.width || 1;
            var left = px * scaleX;
            var tooltipWidth = tooltip.offsetWidth;
            if (left + tooltipWidth > plotRect.width) {
                left = Math.max(0, plotRect.width - tooltipWidth);
            }
            tooltip.style.left = left + 'px';
        }

        function hide() {
            crosshair.setAttribute('visibility', 'hidden');
            seriesKeys.forEach(function (key) {
                hoverDots[key].setAttribute('visibility', 'hidden');
            });
            tooltip.style.display = 'none';
        }

        hitRect.addEventListener('mousemove', function (e) {
            var rect = svg.getBoundingClientRect();
            var scaleX = rect.width / data.width || 1;
            var x = ((e.clientX - rect.left) / scaleX) - data.padX;
            var idx = stepX > 0 ? Math.round(x / stepX) : 0;
            updateAt(idx);
        });

        hitRect.addEventListener('mouseleave', hide);
    });
}());
