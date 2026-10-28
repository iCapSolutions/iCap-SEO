<?php
/**
 * Local (on-site) detection for every free check: title and meta description,
 * H1, content depth, image optimization, heading and paragraph structure,
 * canonical URL, JSON-LD presence, and internal links. Page-level checks run
 * against the page fetched from this site; link and paragraph checks run against
 * the post body. The rules mirror the scan service's checks, so a page gets the
 * same result either way.
 */

if (!defined('ABSPATH')) {
    exit;
}

class ICap_SEO_Local_Checks
{
    /** Issue codes this class can detect locally. */
    public const CHECKED_CODES = [
        'missing_title_tag',
        'title_length_out_of_range',
        'missing_meta_description',
        'meta_description_length_out_of_range',
        'missing_h1',
        'thin_content',
        'limited_paragraph_structure',
        'no_images_detected',
        'images_missing_alt',
        'images_missing_dimensions',
        'images_not_lazy_loaded',
        'limited_heading_structure',
        'missing_canonical',
        'missing_jsonld_schema',
        'no_links_detected',
        'low_internal_linking',
    ];

    private const CACHE_SECONDS = 600;

    /**
     * Run every local check for a post.
     *
     * @return array{issues: array<int, array<string, string>>, ran: string[]}
     *         `ran` lists the codes that were actually evaluated. Page-level codes are
     *         absent when the page couldn't be fetched.
     */
    public static function run_for_post(string $permalink, string $body_html): array
    {
        $issues = [];
        $ran = [];

        $page_html = self::fetch_rendered_html($permalink);
        if ($page_html !== null) {
            $issues = array_merge(
                $issues,
                self::page_basics_issues($page_html),
                self::image_issues($page_html),
                self::heading_issues($page_html),
                self::canonical_issues($page_html),
                self::jsonld_issues($page_html)
            );
            $ran = array_merge($ran, [
                'missing_title_tag', 'title_length_out_of_range', 'missing_meta_description',
                'meta_description_length_out_of_range', 'missing_h1', 'thin_content',
                'no_images_detected', 'images_missing_alt', 'images_missing_dimensions', 'images_not_lazy_loaded',
                'limited_heading_structure', 'missing_canonical', 'missing_jsonld_schema',
            ]);
        }

        // Link and paragraph checks read the post body, so they run even when the page fetch fails.
        $link_base = $permalink !== '' ? $permalink : home_url();
        $issues = array_merge($issues, self::link_issues($body_html, $link_base), self::paragraph_issues($body_html));
        $ran = array_merge($ran, ['no_links_detected', 'low_internal_linking', 'limited_paragraph_structure']);

        return ['issues' => $issues, 'ran' => $ran];
    }

    /**
     * Build the local-only detail for a post. Used for sites that aren't registered,
     * so the free checks still show without any backend call.
     *
     * @return array{title: string, permalink: string, issues: array<int, array<string, string>>, ran: string[]}|null
     */
    public static function detail_for_post(int $post_id): ?array
    {
        $post = get_post($post_id);
        if (!$post instanceof WP_Post) {
            return null;
        }

        $permalink = (string) get_permalink($post);
        // Core hook: render the post body the way WordPress does, so link and image checks see the same HTML a visitor gets.
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- the_content is a WordPress core hook, not a plugin hook.
        $body_html = (string) apply_filters('the_content', $post->post_content);
        $result = self::run_for_post($permalink, $body_html);

        return [
            'title' => get_the_title($post),
            'permalink' => $permalink,
            'issues' => $result['issues'],
            'ran' => $result['ran'],
        ];
    }

    /**
     * Labels for each locally checked code, in display order.
     *
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            'missing_title_tag' => __('Page has a title tag', 'icap-seo'),
            'title_length_out_of_range' => __('Title length is optimized (20–65 characters)', 'icap-seo'),
            'missing_meta_description' => __('Page has a meta description', 'icap-seo'),
            'meta_description_length_out_of_range' => __('Meta description length is optimized (120–170 characters)', 'icap-seo'),
            'missing_h1' => __('Page has an H1 heading', 'icap-seo'),
            'thin_content' => __('Page has enough visible content (250+ words)', 'icap-seo'),
            'limited_paragraph_structure' => __('Content is split into enough paragraphs', 'icap-seo'),
            'images_missing_alt' => __('Images have descriptive alt text', 'icap-seo'),
            'images_missing_dimensions' => __('Images have explicit width/height attributes', 'icap-seo'),
            'images_not_lazy_loaded' => __('Below-the-fold images use lazy loading', 'icap-seo'),
            'no_images_detected' => __('Page includes relevant images', 'icap-seo'),
            'limited_heading_structure' => __('Page has enough secondary headings (H2/H3)', 'icap-seo'),
            'missing_canonical' => __('Page has a canonical URL', 'icap-seo'),
            'missing_jsonld_schema' => __('Page has JSON-LD structured data', 'icap-seo'),
            'no_links_detected' => __('Page includes crawlable links', 'icap-seo'),
            'low_internal_linking' => __('Page links to enough related content', 'icap-seo'),
        ];
    }

    /**
     * Drop a post's cached page HTML so the next check sees its current content.
     * Runs on save_post and after a fix is applied.
     */
    public static function clear_cache_for_post(int $post_id): void
    {
        $permalink = get_permalink($post_id);
        if (is_string($permalink) && $permalink !== '') {
            delete_transient('icap_seo_local_html_' . md5($permalink));
        }
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
     * Title, meta description, H1, and visible word count checks.
     *
     * @return array<int, array<string, string>>
     */
    public static function page_basics_issues(string $html): array
    {
        $issues = [];

        $title = '';
        if (preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $title_match) === 1) {
            $title = self::normalize_text($title_match[1]);
        }
        $title_length = self::text_length($title);
        if ($title === '') {
            $issues[] = self::issue(
                'missing_title_tag',
                'high',
                'Title tag is missing.',
                'Set a unique <title> tag for this page.'
            );
        } elseif ($title_length < 20 || $title_length > 65) {
            $issues[] = self::issue(
                'title_length_out_of_range',
                'medium',
                'Title length is outside recommended range (20-65 characters).',
                'Adjust title text to stay within recommended SEO range.'
            );
        }

        $description = '';
        if (preg_match('/<meta[^>]+name=["\']description["\'][^>]+content=["\'](.*?)["\'][^>]*>/is', $html, $description_match) === 1
            || preg_match('/<meta[^>]+content=["\'](.*?)["\'][^>]+name=["\']description["\'][^>]*>/is', $html, $description_match) === 1) {
            $description = self::normalize_text($description_match[1]);
        }
        $description_length = self::text_length($description);
        if ($description === '') {
            $issues[] = self::issue(
                'missing_meta_description',
                'medium',
                'Meta description is missing.',
                'Add a 120-170 character meta description summarizing page intent.'
            );
        } elseif ($description_length < 120 || $description_length > 170) {
            $issues[] = self::issue(
                'meta_description_length_out_of_range',
                'low',
                'Meta description length is outside recommended range (120-170).',
                'Adjust meta description length for better search snippets.'
            );
        }

        if (preg_match('/<h1\b/i', $html) !== 1) {
            $issues[] = self::issue(
                'missing_h1',
                'medium',
                'No H1 heading detected.',
                'Add one clear H1 heading aligned with page topic.'
            );
        }

        $visible_text = (string) preg_replace('/<(script|style)\b[^>]*>.*?<\/\1>/is', ' ', $html);
        $visible_text = self::normalize_text((string) preg_replace('/<[^>]+>/s', ' ', $visible_text));
        $word_count = preg_match_all("/[A-Za-z0-9']+/", $visible_text);
        if ($word_count < 250) {
            $issues[] = self::issue(
                'thin_content',
                'medium',
                'Visible content appears thin (<250 words).',
                'Expand page content depth with useful topic-specific detail.'
            );
        }

        return $issues;
    }

    /**
     * Paragraph structure check, run against the post body. Flags content with
     * fewer than three paragraph blocks.
     *
     * @return array<int, array<string, string>>
     */
    public static function paragraph_issues(string $body_html): array
    {
        if (preg_match_all('/<p\b/i', $body_html) >= 3) {
            return [];
        }

        return [self::issue(
            'limited_paragraph_structure',
            'low',
            'Page has very few paragraph blocks.',
            'Increase paragraph segmentation for readability.'
        )];
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
     * Heading check. Flags pages with fewer than two H2/H3 headings.
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
     * Canonical check. Flags pages with no rel="canonical" link tag.
     *
     * @return array<int, array<string, string>>
     */
    public static function canonical_issues(string $html): array
    {
        if (preg_match('/<link[^>]+rel=["\']canonical["\'][^>]*>/i', $html) === 1) {
            return [];
        }

        return [self::issue(
            'missing_canonical',
            'medium',
            'Canonical link tag is missing.',
            'Add <link rel="canonical"> to enforce preferred URL.'
        )];
    }

    /**
     * JSON-LD presence check. Flags pages with no application/ld+json block. Checking
     * required properties and types stays with the scan service.
     *
     * @return array<int, array<string, string>>
     */
    public static function jsonld_issues(string $html): array
    {
        $count = preg_match_all('/<script[^>]+type=["\']application\/ld\+json["\'][^>]*>/i', $html);

        if ($count > 0) {
            return [];
        }

        return [self::issue(
            'missing_jsonld_schema',
            'medium',
            'No JSON-LD schema block detected.',
            'Add relevant JSON-LD schema markup for page/entity type.'
        )];
    }

    /**
     * Internal link checks, run against the post body. Mirrors the scan service:
     * a page with no crawlable links gets only no_links_detected; otherwise fewer
     * than two internal links gets low_internal_linking.
     *
     * @return array<int, array<string, string>>
     */
    public static function link_issues(string $body_html, string $permalink): array
    {
        $site_netloc = strtolower((string) wp_parse_url($permalink, PHP_URL_HOST));

        $hrefs = [];
        preg_match_all('/<a\b[^>]*>/i', $body_html, $anchor_tags);
        foreach ($anchor_tags[0] as $tag) {
            if (!preg_match('/href=["\']([^"\']+)["\']/i', $tag, $href_match)) {
                continue;
            }
            $href = trim($href_match[1]);
            if ($href === ''
                || strpos($href, '#') === 0
                || preg_match('/^(javascript|mailto|tel):/i', $href) === 1) {
                continue;
            }
            $hrefs[] = $href;
        }

        if (count($hrefs) === 0) {
            return [self::issue(
                'no_links_detected',
                'medium',
                'No crawlable links were detected.',
                'Add contextual internal links to improve discoverability.'
            )];
        }

        $internal_count = 0;
        foreach ($hrefs as $href) {
            $href_netloc = strtolower((string) wp_parse_url($href, PHP_URL_HOST));
            if (strpos($href, '/') === 0 || $href_netloc === '' || $href_netloc === $site_netloc) {
                $internal_count++;
            }
        }

        if ($internal_count < 2) {
            return [self::issue(
                'low_internal_linking',
                'low',
                'Internal linking density is low.',
                'Add more relevant internal links to related content.'
            )];
        }

        return [];
    }

    private static function normalize_text(string $text): string
    {
        return trim((string) preg_replace('/\s+/u', ' ', html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    }

    private static function text_length(string $text): int
    {
        return function_exists('mb_strlen') ? mb_strlen($text, 'UTF-8') : strlen($text);
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
