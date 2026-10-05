<?php
/**
 * Local (on-site) detection for the free checks: image optimization and heading
 * structure. The rendered page is fetched from this site and checked inside
 * WordPress, so no backend call is needed. The rules mirror the scan service's
 * image and heading checks, so a page gets the same result either way.
 */

if (!defined('ABSPATH')) {
    exit;
}

class ICap_SEO_Local_Checks
{
    /** Issue codes this class can detect locally. */
    public const CHECKED_CODES = [
        'no_images_detected',
        'images_missing_alt',
        'images_missing_dimensions',
        'images_not_lazy_loaded',
        'limited_heading_structure',
    ];

    private const CACHE_SECONDS = 600;

    /**
     * Fetch the rendered page and run every local check on it.
     *
     * @return array<int, array<string, string>>|null Issue rows, or null when the page could not be fetched.
     */
    public static function run_for_permalink(string $permalink): ?array
    {
        $html = self::fetch_rendered_html($permalink);
        if ($html === null) {
            return null;
        }

        return array_merge(self::image_issues($html), self::heading_issues($html));
    }

    /**
     * Fetch a page's rendered HTML from this site. Results are cached briefly so
     * repeat dashboard loads don't refetch the page.
     */
    public static function fetch_rendered_html(string $permalink): ?string
    {
        if ($permalink === '' || strpos($permalink, home_url()) !== 0) {
            return null;
        }

        $cache_key = 'icap_seo_local_html_' . md5($permalink);
        $cached = get_transient($cache_key);
        if (is_string($cached)) {
            return $cached;
        }

        $response = wp_remote_get($permalink, [
            'timeout' => 10,
            'redirection' => 3,
            'limit_response_size' => 2 * MB_IN_BYTES,
        ]);
        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            return null;
        }

        $html = wp_remote_retrieve_body($response);
        if (!is_string($html) || $html === '') {
            return null;
        }

        set_transient($cache_key, $html, self::CACHE_SECONDS);

        return $html;
    }

    /**
     * Image checks. Returns issue rows in the same shape the scan service uses.
     *
     * @return array<int, array<string, string>>
     */
    public static function image_issues(string $html): array
    {
        preg_match_all('/<img\b[^>]*>/i', $html, $matches);
        $image_tags = $matches[0];
        $image_count = count($image_tags);

        if ($image_count === 0) {
            return [self::issue(
                'no_images_detected',
                'low',
                'No image tags detected on page.',
                'Add relevant images where they improve user understanding.'
            )];
        }

        $issues = [];

        $missing_alt = 0;
        foreach ($image_tags as $tag) {
            if (!preg_match('/alt=["\'](.*?)["\']/is', $tag, $alt_match)
                || trim(preg_replace('/\s+/', ' ', html_entity_decode($alt_match[1], ENT_QUOTES))) === '') {
                $missing_alt++;
            }
        }
        if ($missing_alt > 0) {
            $issues[] = self::issue(
                'images_missing_alt',
                'medium',
                sprintf('%d image(s) are missing descriptive alt text.', $missing_alt),
                'Add descriptive alt attributes to non-decorative images.'
            );
        }

        $missing_dimensions = 0;
        foreach ($image_tags as $tag) {
            $has_width = preg_match('/\bwidth\s*=\s*["\']?\d/i', $tag) === 1;
            $has_height = preg_match('/\bheight\s*=\s*["\']?\d/i', $tag) === 1;
            if (!($has_width && $has_height)) {
                $missing_dimensions++;
            }
        }
        if ($missing_dimensions > 0) {
            $issues[] = self::issue(
                'images_missing_dimensions',
                'medium',
                sprintf('%d image(s) are missing explicit width/height attributes.', $missing_dimensions),
                'Add width and height attributes so browsers can reserve space and avoid layout shift.'
            );
        }

        // The first image may be the LCP candidate, and lazy-loading it can slow the
        // page, so it is exempt. Only images after it are checked.
        $below_first = array_slice($image_tags, 1);
        $missing_lazy = 0;
        foreach ($below_first as $tag) {
            if (preg_match('/\bloading\s*=\s*["\']lazy["\']/i', $tag) !== 1) {
                $missing_lazy++;
            }
        }
        if ($missing_lazy > 0) {
            $issues[] = self::issue(
                'images_not_lazy_loaded',
                'low',
                sprintf('%d image(s) below the first are not using loading="lazy".', $missing_lazy),
                'Add loading="lazy" to below-the-fold images to improve page load performance.'
            );
        }

        return $issues;
    }

    /**
     * Heading check. Flags pages with fewer than two H2/H3 headings, as the scan
     * service does.
     *
     * @return array<int, array<string, string>>
     */
    public static function heading_issues(string $html): array
    {
        $secondary_headings = preg_match_all('/<h[23][\s>]/i', $html);

        if ($secondary_headings < 2) {
            return [self::issue(
                'limited_heading_structure',
                'low',
                'Few secondary headings detected (H2/H3).',
                'Improve heading hierarchy for readability and topic structure.'
            )];
        }

        return [];
    }

    /**
     * @return array<string, string>
     */
    private static function issue(string $code, string $severity, string $description, string $fix): array
    {
        return [
            'issue_code' => $code,
            'severity' => $severity,
            'description' => $description,
            'recommended_fix' => $fix,
            'estimated_effort' => 'low',
        ];
    }
}
