<?php
/**
 * Local (on-site) detection for every free check: title and meta description,
 * H1, HTTPS, robots meta and robots.txt rules, security headers, content depth
 * and readability, image optimization, heading and paragraph structure, canonical
 * URL, structured data, and links. Page-level checks run against the page fetched
 * from this site; link and paragraph checks run against the post body. The rules
 * mirror the scan service's checks, so a page gets the same result either way.
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
        'non_https_url',
        'noindex_detected',
        'page_level_nofollow_detected',
        'noimageindex_detected',
        'nosnippet_detected',
        'robots_txt_missing',
        'robots_txt_blocks_page',
        'missing_security_headers',
        'no_visible_content',
        'insufficient_content_depth',
        'content_depth_improvement',
        'readability_score_low',
        'readability_long_sentences_high',
        'readability_low_transition_word_usage',
        'readability_passive_voice_high',
        'schema_type_missing',
        'schema_missing_required_properties',
        'no_external_references',
        'nofollow_on_internal_link',
    ];

    private const CACHE_SECONDS = 600;

    /** Post meta holding a page's latest on-site result: score, failed-check count, and time. */
    private const SCORE_META_KEY = '_icap_seo_local_score';

    private const LAST_CHECK_OPTION = 'icap_seo_local_last_check';

    /** Pages per request. Each one is a request from the site to itself, so a batch stays well under PHP's time limit. */
    private const BATCH_SIZE = 3;

    /** The most recently updated pages listed and checked at once. */
    public const MAX_PAGES = 1000;

    /** Mirrors the scan service's recommended security-header list (lowercase names, presence only). */
    private const REQUIRED_SECURITY_HEADERS = [
        'strict-transport-security',
        'x-content-type-options',
        'x-frame-options',
        'content-security-policy',
        'referrer-policy',
    ];

    /** Kept in sync with the JSON-LD generator, so a flagged missing property is one the generator fills. */
    private const SCHEMA_REQUIRED_PROPERTIES = [
        'Article' => ['headline', 'datePublished', 'author'],
        'WebPage' => ['name', 'description'],
        'FAQPage' => ['mainEntity'],
        'HowTo' => ['name', 'step'],
    ];

    /** A sentence counts as connected when it starts with one of these. */
    private const TRANSITION_WORDS = [
        'however', 'therefore', 'moreover', 'furthermore', 'additionally',
        'in addition', 'consequently', 'meanwhile', 'nevertheless', 'nonetheless',
        'thus', 'hence', 'similarly', 'likewise', 'in contrast', 'on the other hand',
        'for example', 'for instance', 'as a result', 'in conclusion', 'in summary',
        'subsequently', 'accordingly', 'indeed', 'specifically', 'notably',
        'first', 'second', 'third', 'finally', 'next', 'then', 'also', 'because',
        'since', 'although', 'though', 'while', 'unlike', 'whereas', 'in fact',
        'overall', 'importantly', 'in other words', 'as well',
    ];

    private const LONG_SENTENCE_WORDS = 25;
    private const LONG_SENTENCE_RATIO = 0.25;
    private const LOW_TRANSITION_RATIO = 0.10;
    private const PASSIVE_VOICE_RATIO = 0.25;
    private const PASSIVE_AUX_WORDS = ['am', 'is', 'are', 'was', 'were', 'be', 'been', 'being'];
    private const PASSIVE_MAX_GAP_WORDS = 2;
    private const IRREGULAR_PARTICIPLES = [
        'written', 'done', 'made', 'given', 'taken', 'known', 'shown', 'seen',
        'found', 'sent', 'built', 'chosen', 'spoken', 'broken', 'driven', 'eaten',
        'forgotten', 'gone', 'grown', 'hidden', 'held', 'kept', 'left', 'lost',
        'meant', 'paid', 'read', 'run', 'said', 'sold', 'sought', 'stolen',
        'taught', 'thought', 'told', 'understood', 'worn', 'won', 'brought',
        'bought', 'caught', 'put', 'set', 'spent', 'stood',
    ];

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

        if ($permalink !== '') {
            $issues = array_merge($issues, self::https_issues($permalink));
            $ran[] = 'non_https_url';
        }

        $page = self::fetch_rendered_page($permalink);
        if ($page !== null) {
            $page_html = $page['html'];
            $issues = array_merge(
                $issues,
                self::page_basics_issues($page_html),
                self::robots_meta_issues($page_html),
                self::security_header_issues($page['headers']),
                self::content_depth_issues($page_html),
                self::readability_issues($page_html),
                self::image_issues($page_html),
                self::heading_issues($page_html),
                self::canonical_issues($page_html),
                self::jsonld_issues($page_html)
            );
            $ran = array_merge($ran, [
                'missing_title_tag', 'title_length_out_of_range', 'missing_meta_description',
                'meta_description_length_out_of_range', 'missing_h1', 'thin_content',
                'noindex_detected', 'page_level_nofollow_detected', 'noimageindex_detected', 'nosnippet_detected',
                'missing_security_headers',
                'no_visible_content', 'insufficient_content_depth', 'content_depth_improvement',
                'readability_score_low', 'readability_long_sentences_high',
                'readability_low_transition_word_usage', 'readability_passive_voice_high',
                'no_images_detected', 'images_missing_alt', 'images_missing_dimensions', 'images_not_lazy_loaded',
                'limited_heading_structure', 'missing_canonical', 'missing_jsonld_schema',
                'schema_type_missing', 'schema_missing_required_properties',
            ]);
        }

        // Only a robots.txt this site actually answered for counts; a failed loopback request leaves both codes unscanned.
        $robots_txt = self::fetch_robots_txt();
        if ($robots_txt !== null) {
            $issues = array_merge($issues, self::robots_txt_issues($robots_txt, $permalink));
            $ran = array_merge($ran, ['robots_txt_missing', 'robots_txt_blocks_page']);
        }

        // Link and paragraph checks read the post body, so they run even when the page fetch fails.
        $link_base = $permalink !== '' ? $permalink : home_url();
        $issues = array_merge($issues, self::link_issues($body_html, $link_base), self::paragraph_issues($body_html));
        $ran = array_merge($ran, [
            'no_links_detected', 'low_internal_linking', 'no_external_references', 'nofollow_on_internal_link',
            'limited_paragraph_structure',
        ]);

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
     * Percentage of the checks that ran on this page and passed.
     */
    public static function score_for_detail(array $detail): int
    {
        $ran_count = count($detail['ran']);
        $failed_codes = array_unique(array_column($detail['issues'], 'issue_code'));

        return $ran_count > 0 ? (int) round(100 * max(0, $ran_count - count($failed_codes)) / $ran_count) : 0;
    }

    /**
     * Save a page's result, unless the page itself couldn't be read: a score from the
     * body-only checks that still run then wouldn't reflect the page.
     */
    public static function store_result(int $post_id, array $detail): bool
    {
        if (!in_array('missing_title_tag', $detail['ran'], true)) {
            return false;
        }

        update_post_meta($post_id, self::SCORE_META_KEY, [
            'score' => self::score_for_detail($detail),
            'failed' => count(array_unique(array_column($detail['issues'], 'issue_code'))),
            'checked_at' => gmdate('c'),
        ]);
        update_option(self::LAST_CHECK_OPTION, current_time('mysql'), false);

        return true;
    }

    /**
     * A page's saved score, or null if it hasn't been checked since it last changed.
     */
    public static function stored_score(int $post_id): ?int
    {
        $stored = get_post_meta($post_id, self::SCORE_META_KEY, true);

        return is_array($stored) && isset($stored['score']) ? (int) $stored['score'] : null;
    }

    public static function last_check_time(): string
    {
        return (string) get_option(self::LAST_CHECK_OPTION, '');
    }

    /**
     * Check the next few published pages. The admin's browser calls this repeatedly, so a
     * site of any size is covered without one long request. Password-protected pages are
     * skipped, since their content isn't public.
     *
     * @return array{total: int, next_offset: int, done: bool, checked: int, failed: int}
     */
    public static function run_batch(int $offset): array
    {
        $query = new WP_Query([
            'post_type' => ['post', 'page'],
            'post_status' => 'publish',
            'has_password' => false,
            'fields' => 'ids',
            'posts_per_page' => self::BATCH_SIZE,
            'offset' => $offset,
            'orderby' => 'modified',
            'order' => 'DESC',
            'update_post_term_cache' => false,
        ]);
        $total = min((int) $query->found_posts, self::MAX_PAGES);

        $checked = 0;
        $failed = 0;
        foreach ($query->posts as $index => $post_id) {
            if ($offset + $index >= $total) {
                break;
            }
            self::clear_cache_for_post((int) $post_id);
            $detail = self::detail_for_post((int) $post_id);
            if ($detail !== null && self::store_result((int) $post_id, $detail)) {
                $checked++;
            } else {
                $failed++;
            }
        }

        $next_offset = $offset + $checked + $failed;

        return [
            'total' => $total,
            'next_offset' => $next_offset,
            'done' => $checked + $failed === 0 || $next_offset >= $total,
            'checked' => $checked,
            'failed' => $failed,
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
            'non_https_url' => __('Page is served over HTTPS', 'icap-seo'),
            'noindex_detected' => __('Page is not accidentally excluded from search (noindex)', 'icap-seo'),
            'page_level_nofollow_detected' => __('Page does not use a page-level nofollow directive', 'icap-seo'),
            'noimageindex_detected' => __('Page images are not excluded from image search (noimageindex)', 'icap-seo'),
            'nosnippet_detected' => __('Page allows a search snippet (nosnippet)', 'icap-seo'),
            'robots_txt_missing' => __('Site has a robots.txt file', 'icap-seo'),
            'robots_txt_blocks_page' => __('robots.txt does not block this page', 'icap-seo'),
            'missing_security_headers' => __('Server sends recommended security headers', 'icap-seo'),
            'no_visible_content' => __('Page has visible content', 'icap-seo'),
            'insufficient_content_depth' => __('Content depth meets baseline (300+ words)', 'icap-seo'),
            'content_depth_improvement' => __('Content depth is competitive (600+ words)', 'icap-seo'),
            'readability_score_low' => __('Content is written in plain, easy-to-read language', 'icap-seo'),
            'readability_long_sentences_high' => __('Sentences are not too long to read easily', 'icap-seo'),
            'readability_low_transition_word_usage' => __('Sentences use transition words to connect ideas', 'icap-seo'),
            'readability_passive_voice_high' => __('Sentences favor active voice over passive voice', 'icap-seo'),
            'schema_type_missing' => __('Structured data includes a valid @type', 'icap-seo'),
            'schema_missing_required_properties' => __('Structured data includes required properties for its type', 'icap-seo'),
            'no_external_references' => __('Page cites at least one external source', 'icap-seo'),
            'nofollow_on_internal_link' => __('Internal links don\'t use rel="nofollow"', 'icap-seo'),
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
            delete_transient('icap_seo_local_page_' . md5($permalink));
        }
        delete_post_meta($post_id, self::SCORE_META_KEY);
    }

    /**
     * Fetch a page's rendered HTML from this site.
     */
    public static function fetch_rendered_html(string $permalink): ?string
    {
        $page = self::fetch_rendered_page($permalink);

        return $page !== null ? $page['html'] : null;
    }

    /**
     * Fetch a page from this site: its rendered HTML plus the response headers it
     * was served with (lowercase names), for the security-header check. Results
     * are cached briefly so repeat dashboard loads don't refetch the page.
     *
     * @return array{html: string, headers: array<string, string>}|null
     */
    public static function fetch_rendered_page(string $permalink): ?array
    {
        if ($permalink === '' || strpos($permalink, home_url()) !== 0) {
            return null;
        }

        $cache_key = 'icap_seo_local_page_' . md5($permalink);
        $cached = get_transient($cache_key);
        if (is_array($cached) && isset($cached['html'], $cached['headers'])) {
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

        $headers = [];
        $response_headers = wp_remote_retrieve_headers($response);
        $header_pairs = is_object($response_headers) && method_exists($response_headers, 'getAll')
            ? $response_headers->getAll()
            : (array) $response_headers;
        foreach ($header_pairs as $name => $value) {
            $headers[strtolower((string) $name)] = is_array($value) ? implode(', ', $value) : (string) $value;
        }

        $page = ['html' => $html, 'headers' => $headers];
        set_transient($cache_key, $page, self::CACHE_SECONDS);

        return $page;
    }

    /**
     * Fetch this site's robots.txt. Returns '' when the site says there is none
     * (404 or 410), and null for any other failure, so a blocked loopback request
     * or a momentary server error isn't reported as a missing file. Cached briefly.
     */
    public static function fetch_robots_txt(): ?string
    {
        $cache_key = 'icap_seo_local_robots_txt';
        $cached = get_transient($cache_key);
        if (is_string($cached)) {
            return $cached;
        }

        $response = wp_remote_get(home_url('/robots.txt'), [
            'timeout' => 10,
            'redirection' => 3,
            'limit_response_size' => 512 * KB_IN_BYTES,
        ]);
        if (is_wp_error($response)) {
            return null;
        }

        $status = (int) wp_remote_retrieve_response_code($response);
        if ($status === 200) {
            $body = (string) wp_remote_retrieve_body($response);
        } elseif ($status === 404 || $status === 410) {
            $body = '';
        } else {
            return null;
        }
        set_transient($cache_key, $body, self::CACHE_SECONDS);

        return $body;
    }

    /**
     * HTTPS check on the page's own URL.
     *
     * @return array<int, array<string, string>>
     */
    public static function https_issues(string $permalink): array
    {
        if (strpos($permalink, 'https://') === 0) {
            return [];
        }

        return [self::issue(
            'non_https_url',
            'high',
            'Page URL is not HTTPS.',
            'Serve canonical content over HTTPS only.'
        )];
    }

    /**
     * Robots meta directives: noindex, nofollow, noimageindex, nosnippet.
     *
     * @return array<int, array<string, string>>
     */
    public static function robots_meta_issues(string $html): array
    {
        $content = null;
        if (preg_match('/<meta[^>]+name=["\']robots["\'][^>]+content=["\']([^"\']*)["\']/i', $html, $match) === 1
            || preg_match('/<meta[^>]+content=["\']([^"\']*)["\'][^>]+name=["\']robots["\']/i', $html, $match) === 1) {
            $content = $match[1];
        }
        if ($content === null) {
            return [];
        }

        $directives = array_filter(array_map(static fn(string $part): string => strtolower(trim($part)), explode(',', $content)));
        $checks = [
            'noindex' => ['noindex_detected', 'high', 'A noindex directive is present.', 'Remove noindex unless this page should remain out of search results.'],
            'nofollow' => ['page_level_nofollow_detected', 'medium', 'A page-level nofollow directive is present.', 'Remove nofollow unless this page\'s outbound links should not pass link equity.'],
            'noimageindex' => ['noimageindex_detected', 'low', 'A noimageindex directive is present.', 'Remove noimageindex unless this page\'s images should be excluded from image search.'],
            'nosnippet' => ['nosnippet_detected', 'low', 'A nosnippet directive is present.', 'Remove nosnippet unless this page should not show a text/preview snippet in search results.'],
        ];

        $issues = [];
        foreach ($checks as $directive => $row) {
            if (in_array($directive, $directives, true)) {
                $issues[] = self::issue($row[0], $row[1], $row[2], $row[3]);
            }
        }

        return $issues;
    }

    /**
     * robots.txt checks: the file exists, and its "User-agent: *" Disallow rules
     * don't cover this page's path. Simple prefix matching, like the scan service.
     *
     * @return array<int, array<string, string>>
     */
    public static function robots_txt_issues(string $robots_txt, string $permalink): array
    {
        if (trim($robots_txt) === '') {
            return [self::issue(
                'robots_txt_missing',
                'low',
                'No robots.txt was found at the site root.',
                'Add a robots.txt file to explicitly define crawler access policy, including AI crawlers.'
            )];
        }
        if ($permalink === '') {
            return [];
        }

        $disallow_rules = [];
        $applies = false;
        foreach (preg_split('/\r\n|\r|\n/', $robots_txt) as $raw_line) {
            $line = trim(explode('#', $raw_line, 2)[0]);
            if ($line === '' || strpos($line, ':') === false) {
                continue;
            }
            [$directive, $value] = array_map('trim', explode(':', $line, 2));
            $directive = strtolower($directive);
            if ($directive === 'user-agent') {
                $applies = $value === '*';
            } elseif ($directive === 'disallow' && $applies && $value !== '') {
                $disallow_rules[] = $value;
            }
        }

        $path = (string) wp_parse_url($permalink, PHP_URL_PATH);
        $path = $path !== '' ? $path : '/';
        foreach ($disallow_rules as $rule) {
            if (strpos($path, $rule) === 0) {
                return [self::issue(
                    'robots_txt_blocks_page',
                    'high',
                    sprintf('robots.txt disallows crawling of this page\'s path (%s).', $path),
                    'Update robots.txt so this page\'s path is not disallowed, unless it should stay out of search results.'
                )];
            }
        }

        return [];
    }

    /**
     * Recommended security headers, read from the response this site served for
     * the page. Presence-only, matching the scan service's rule; headers added
     * by a CDN in front of the site are better verified by a Premium scan's
     * outside view, but the rule itself is the same.
     *
     * @param array<string, string> $headers Response headers with lowercase names.
     * @return array<int, array<string, string>>
     */
    public static function security_header_issues(array $headers): array
    {
        $missing = [];
        foreach (self::REQUIRED_SECURITY_HEADERS as $header_name) {
            if (!array_key_exists($header_name, $headers)) {
                $missing[] = $header_name;
            }
        }
        if ($missing === []) {
            return [];
        }

        return [self::issue(
            'missing_security_headers',
            'medium',
            sprintf('%d recommended security header(s) missing: %s.', count($missing), implode(', ', $missing)),
            'Add the missing security headers at the server/CDN level (not settable from WordPress content).'
        )];
    }

    /**
     * Content depth tiers on the page's visible text: none, under 300 words, under 600.
     *
     * @return array<int, array<string, string>>
     */
    public static function content_depth_issues(string $html): array
    {
        $word_count = self::count_words(self::visible_text($html));

        if ($word_count === 0) {
            return [self::issue(
                'no_visible_content',
                'high',
                'No visible content could be extracted from page HTML.',
                'Ensure renderable body content is present.'
            )];
        }
        if ($word_count < 300) {
            return [self::issue(
                'insufficient_content_depth',
                'medium',
                'Content depth is below baseline (<300 words).',
                'Expand content coverage for user intent and topical completeness.'
            )];
        }
        if ($word_count < 600) {
            return [self::issue(
                'content_depth_improvement',
                'low',
                'Content depth is moderate; additional detail may improve competitiveness.',
                'Add examples, FAQs, and deeper explanations where relevant.',
                'medium'
            )];
        }

        return [];
    }

    /**
     * Readability: Flesch reading ease, long sentences, transition words, and
     * passive voice. Only measured from 300 words up, where the ratios mean something.
     *
     * @return array<int, array<string, string>>
     */
    public static function readability_issues(string $html): array
    {
        $text = self::visible_text($html);
        if (self::count_words($text) < 300) {
            return [];
        }

        $issues = [];

        preg_match_all("/[A-Za-z']+/", $text, $word_matches);
        $words = $word_matches[0];
        $sentence_count = preg_match_all('/[.!?]+/', $text);
        $sentence_count = $sentence_count > 0 ? $sentence_count : 1;
        $syllables = 0;
        foreach ($words as $word) {
            $syllables += self::count_syllables($word);
        }
        $word_total = count($words);
        if ($word_total > 0) {
            $reading_ease = 206.835 - 1.015 * ($word_total / $sentence_count) - 84.6 * ($syllables / $word_total);
            $reading_ease = max(0.0, min(100.0, $reading_ease));
            if ($reading_ease < 50) {
                $issues[] = self::issue(
                    'readability_score_low',
                    'medium',
                    sprintf('Content reading ease score is %d/100 (fairly difficult or harder).', (int) round($reading_ease)),
                    'Shorten sentences and simplify word choice for a broader audience.',
                    'medium'
                );
            }
        }

        $sentences = array_values(array_filter(array_map('trim', (array) preg_split('/(?<=[.!?])\s+/', $text)), static fn(string $s): bool => $s !== ''));
        if (empty($sentences)) {
            return $issues;
        }
        $total = count($sentences);

        $long = 0;
        $connected = 0;
        $passive = 0;
        foreach ($sentences as $sentence) {
            if (self::count_words($sentence) > self::LONG_SENTENCE_WORDS) {
                $long++;
            }
            if (self::starts_with_transition($sentence)) {
                $connected++;
            }
            if (self::is_passive_voice($sentence)) {
                $passive++;
            }
        }

        if ($long / $total > self::LONG_SENTENCE_RATIO) {
            $issues[] = self::issue(
                'readability_long_sentences_high',
                'medium',
                sprintf('%d%% of sentences exceed %d words.', (int) round(100 * $long / $total), self::LONG_SENTENCE_WORDS),
                'Break long sentences into shorter ones.',
                'medium'
            );
        }
        if ($connected / $total < self::LOW_TRANSITION_RATIO) {
            $issues[] = self::issue(
                'readability_low_transition_word_usage',
                'low',
                sprintf('Only %d%% of sentences use a transition word (however, therefore, for example, etc.) to connect ideas.', (int) round(100 * $connected / $total)),
                'Add transition words to improve flow between sentences.',
                'medium'
            );
        }
        if ($passive / $total > self::PASSIVE_VOICE_RATIO) {
            $issues[] = self::issue(
                'readability_passive_voice_high',
                'low',
                sprintf('%d%% of sentences appear to use passive voice.', (int) round(100 * $passive / $total)),
                'Rewrite sentences in active voice where possible.',
                'medium'
            );
        }

        return $issues;
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

        $word_count = self::count_words(self::visible_text($html));
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
     * Structured data checks: a JSON-LD block exists, it declares an @type, and
     * each known type carries its required properties. Only the first failing
     * step is reported, since each one depends on the step before it.
     *
     * @return array<int, array<string, string>>
     */
    public static function jsonld_issues(string $html): array
    {
        preg_match_all('/<script[^>]+type=["\']application\/ld\+json["\'][^>]*>(.*?)<\/script>/is', $html, $blocks);

        if (count($blocks[0]) === 0) {
            return [self::issue(
                'missing_jsonld_schema',
                'medium',
                'No JSON-LD schema block detected.',
                'Add relevant JSON-LD schema markup for page/entity type.'
            )];
        }

        if (preg_match('/"@type"\s*:/i', $html) !== 1) {
            return [self::issue(
                'schema_type_missing',
                'low',
                'Schema block detected but @type marker was not found.',
                'Ensure schema blocks include valid @type values.'
            )];
        }

        $missing_by_type = [];
        foreach ($blocks[1] as $raw_block) {
            $decoded = json_decode(trim(html_entity_decode($raw_block, ENT_QUOTES | ENT_HTML5, 'UTF-8')), true);
            if (!is_array($decoded)) {
                continue;
            }
            if (isset($decoded['@graph']) && is_array($decoded['@graph'])) {
                $objects = $decoded['@graph'];
            } elseif (array_keys($decoded) === range(0, count($decoded) - 1)) {
                $objects = $decoded;
            } else {
                $objects = [$decoded];
            }
            foreach ($objects as $object) {
                if (!is_array($object)) {
                    continue;
                }
                $types = isset($object['@type']) ? (array) $object['@type'] : [];
                foreach ($types as $type) {
                    if (!is_string($type) || !isset(self::SCHEMA_REQUIRED_PROPERTIES[$type])) {
                        continue;
                    }
                    foreach (self::SCHEMA_REQUIRED_PROPERTIES[$type] as $property) {
                        if (!isset($object[$property]) || (is_scalar($object[$property]) && trim((string) $object[$property]) === '')) {
                            $missing_by_type[$type][$property] = true;
                        }
                    }
                }
            }
        }

        if (empty($missing_by_type)) {
            return [];
        }

        ksort($missing_by_type);
        $details = [];
        foreach ($missing_by_type as $type => $properties) {
            $names = array_keys($properties);
            sort($names);
            $details[] = $type . ': missing ' . implode(', ', $names);
        }

        return [self::issue(
            'schema_missing_required_properties',
            'low',
            'Schema block(s) missing required properties for their declared type. ' . implode('; ', $details) . '.',
            'Regenerate JSON-LD schema so required properties for the declared type are present.'
        )];
    }

    /**
     * Link checks, run against the post body. Mirrors the scan service: a page
     * with no crawlable links gets only no_links_detected; otherwise it checks for
     * fewer than two internal links, no external references, and internal links
     * marked rel="nofollow".
     *
     * @return array<int, array<string, string>>
     */
    public static function link_issues(string $body_html, string $permalink): array
    {
        $site_netloc = strtolower((string) wp_parse_url($permalink, PHP_URL_HOST));

        $links = [];
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
            $rel = preg_match('/rel=["\']([^"\']*)["\']/i', $tag, $rel_match) === 1 ? strtolower($rel_match[1]) : '';
            $links[] = ['href' => $href, 'rel' => $rel];
        }

        if (count($links) === 0) {
            return [self::issue(
                'no_links_detected',
                'medium',
                'No crawlable links were detected.',
                'Add contextual internal links to improve discoverability.'
            )];
        }

        $internal_count = 0;
        $external_count = 0;
        $internal_nofollow = [];
        foreach ($links as $link) {
            $href_netloc = strtolower((string) wp_parse_url($link['href'], PHP_URL_HOST));
            if (strpos($link['href'], '/') === 0 || $href_netloc === '' || $href_netloc === $site_netloc) {
                $internal_count++;
                if (in_array('nofollow', preg_split('/\s+/', $link['rel']), true)) {
                    $internal_nofollow[$link['href']] = true;
                }
            } else {
                $external_count++;
            }
        }

        $issues = [];
        if ($internal_count < 2) {
            $issues[] = self::issue(
                'low_internal_linking',
                'low',
                'Internal linking density is low.',
                'Add more relevant internal links to related content.'
            );
        }
        if ($external_count === 0) {
            $issues[] = self::issue(
                'no_external_references',
                'low',
                'No external references were detected.',
                'Consider citing authoritative external sources when appropriate.',
                'medium'
            );
        }
        if (!empty($internal_nofollow)) {
            $issues[] = self::issue(
                'nofollow_on_internal_link',
                'low',
                sprintf('%d internal link(s) use rel="nofollow", which is unusual within your own site.', count($internal_nofollow)),
                'Remove rel="nofollow" from internal links unless intentionally blocking crawl of the target page.'
            );
        }

        return $issues;
    }

    private static function normalize_text(string $text): string
    {
        return trim((string) preg_replace('/\s+/u', ' ', html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    }

    private static function text_length(string $text): int
    {
        return function_exists('mb_strlen') ? mb_strlen($text, 'UTF-8') : strlen($text);
    }

    /** Page text with scripts, styles, and tags removed. */
    private static function visible_text(string $html): string
    {
        $text = (string) preg_replace('/<(script|style)\b[^>]*>.*?<\/\1>/is', ' ', $html);

        return self::normalize_text((string) preg_replace('/<[^>]+>/s', ' ', $text));
    }

    private static function count_words(string $text): int
    {
        return $text === '' ? 0 : (int) preg_match_all("/[A-Za-z0-9']+/", $text);
    }

    /** Vowel-group syllable estimate, with a silent trailing "e" dropped. */
    private static function count_syllables(string $word): int
    {
        $normalized = (string) preg_replace('/[^a-z]/', '', strtolower($word));
        if ($normalized === '') {
            return 0;
        }
        $count = (int) preg_match_all('/[aeiouy]+/', $normalized);
        if (substr($normalized, -1) === 'e' && $count > 1) {
            $count--;
        }

        return max(1, $count);
    }

    private static function starts_with_transition(string $sentence): bool
    {
        $normalized = strtolower(trim($sentence));
        foreach (self::TRANSITION_WORDS as $phrase) {
            if ($normalized === $phrase
                || strpos($normalized, $phrase . ' ') === 0
                || strpos($normalized, $phrase . ',') === 0) {
                return true;
            }
        }

        return false;
    }

    /** A "be" auxiliary followed within a short window by a past participle. */
    private static function is_passive_voice(string $sentence): bool
    {
        preg_match_all("/[a-z']+/", strtolower($sentence), $matches);
        $words = $matches[0];
        foreach ($words as $index => $word) {
            if (!in_array($word, self::PASSIVE_AUX_WORDS, true)) {
                continue;
            }
            foreach (array_slice($words, $index + 1, self::PASSIVE_MAX_GAP_WORDS + 1) as $candidate) {
                if (in_array($candidate, self::IRREGULAR_PARTICIPLES, true)
                    || (substr($candidate, -2) === 'ed' && strlen($candidate) > 3)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @return array<string, string>
     */
    private static function issue(string $code, string $severity, string $description, string $fix, string $effort = 'low'): array
    {
        return [
            'issue_code' => $code,
            'severity' => $severity,
            'description' => $description,
            'recommended_fix' => $fix,
            'estimated_effort' => $effort,
        ];
    }
}
