<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Phase 1 of the live in-editor panel (competitor-audit gap: a real-time score
 * badge + SERP/social preview in the block editor, which every major competitor
 * ships and iCap SEO didn't have at all). Deliberately "static": everything here
 * is computed once, server-side, from the post's last-saved content when the
 * editor screen loads - no live-as-you-type updates and no new cloud API calls.
 * Live-typing (debounced re-computation) and cloud-backed checks are later phases,
 * not this one.
 *
 * The quick-check codes here intentionally reuse this plugin's existing issue_code
 * naming (missing_title_tag, missing_meta_description, limited_heading_structure,
 * images_missing_alt) so a later phase that wires these into the real remediation-
 * apply flow doesn't have to invent a second taxonomy.
 */
if (!class_exists('ICap_SEO_Editor_Panel')) {
    class ICap_SEO_Editor_Panel
    {
        /**
         * Modest, hand-authored list spanning urgency/exclusivity/value - a
         * heuristic nudge for the headline-quality quick check, not a scored
         * ranking. Deliberately short; not meant to be exhaustive.
         */
        private const POWER_WORDS = [
            'free', 'new', 'proven', 'essential', 'ultimate', 'guide', 'how to',
            'best', 'top', 'easy', 'simple', 'secret', 'exclusive', 'limited',
            'now', 'today', 'instant', 'save', 'boost', 'complete',
        ];

        private ICap_SEO_Output $output;

        public function __construct(ICap_SEO_Output $output)
        {
            $this->output = $output;
        }

        public function register(): void
        {
            add_action('enqueue_block_editor_assets', [$this, 'enqueue_assets']);
            add_action('add_meta_boxes', [$this, 'register_meta_box'], 10, 2);
            add_action('rest_api_init', [$this, 'register_rest_route']);
        }

        /**
         * Phase 2 (live-typing): a local REST route the block editor sidebar
         * calls (debounced, not per-keystroke) to recompute the quick checks
         * against the CURRENT in-editor draft - title/content/excerpt the user
         * hasn't saved yet - rather than only the last-saved post row. Runs the
         * exact same compute_quick_checks()/score_checks() logic Phase 1 already
         * uses for the last-saved version, so the two phases can never drift
         * into two different sets of scoring rules. Purely local WP computation,
         * no cloud API call - this is not the authoritative scan.
         */
        public function register_rest_route(): void
        {
            register_rest_route('icap-seo/v1', '/editor-panel/quick-check', [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [$this, 'handle_quick_check_request'],
                'permission_callback' => function (WP_REST_Request $request) {
                    $post_id = (int) $request->get_param('post_id');
                    return $post_id > 0 && current_user_can('edit_post', $post_id);
                },
                'args' => [
                    'post_id' => ['required' => true, 'type' => 'integer'],
                    'title' => ['required' => false, 'type' => 'string', 'default' => ''],
                    'content' => ['required' => false, 'type' => 'string', 'default' => ''],
                    'excerpt' => ['required' => false, 'type' => 'string', 'default' => ''],
                ],
            ]);
        }

        public function handle_quick_check_request(WP_REST_Request $request): WP_REST_Response
        {
            $post_id = (int) $request->get_param('post_id');
            $title = trim((string) $request->get_param('title'));
            $content = (string) $request->get_param('content');
            $excerpt = trim((string) $request->get_param('excerpt'));

            $description = $this->resolve_draft_description($post_id, $excerpt, $content);
            $checks = $this->compute_quick_checks($title, $description, $content);
            $score = $this->score_checks($checks);

            return new WP_REST_Response([
                'title' => $title !== '' ? $title : get_bloginfo('name'),
                'description' => $description,
                'checks' => $checks,
                'score' => $score,
            ]);
        }

        /**
         * Mirrors ICap_SEO_Output::get_effective_meta_description()'s fallback
         * order but against the live draft: an in-progress excerpt field wins
         * (the user is actively editing it), then the previously-saved
         * `_icap_seo_meta_description` postmeta (unrelated to this draft
         * session, but still the best available signal), then the draft
         * content itself stripped to plain text.
         */
        private function resolve_draft_description(int $post_id, string $excerpt, string $content): string
        {
            if ($excerpt !== '') {
                return $this->truncate($excerpt, 200);
            }

            $stored = get_post_meta($post_id, '_icap_seo_meta_description', true);
            if (is_string($stored) && trim($stored) !== '') {
                return $this->truncate(trim($stored), 200);
            }

            $stripped = trim(wp_strip_all_tags($content, true));
            return $this->truncate($stripped, 200);
        }

        private function truncate(string $value, int $max_length): string
        {
            if ($this->strlen($value) <= $max_length) {
                return $value;
            }
            $truncated = function_exists('mb_substr') ? mb_substr($value, 0, $max_length) : substr($value, 0, $max_length);
            return trim($truncated) . '…';
        }

        /**
         * Classic Editor fallback: the block editor sidebar above is invisible on
         * any post the block editor doesn't render for (Classic Editor plugin
         * active site-wide or per-post - one of the most-installed plugins on
         * WordPress.org, confirmed as a real gap via live testing on a site that
         * runs it). Registers the same score badge / SERP preview / social
         * preview as a classic meta box, but only for posts that will actually
         * use the classic screen - `WP_Screen::is_block_editor()` is core's own
         * resolution of Classic Editor's site/user/post-level overrides, so this
         * defers to it rather than re-deriving that logic, and avoids ever
         * showing the same panel twice for one post.
         */
        public function register_meta_box(string $post_type, WP_Post $post): void
        {
            if (!post_type_supports($post_type, 'title')) {
                return;
            }
            if (!current_user_can('edit_post', $post->ID)) {
                return;
            }
            $screen = get_current_screen();
            if ($screen && method_exists($screen, 'is_block_editor') && $screen->is_block_editor()) {
                return;
            }

            add_meta_box(
                'icap-seo-quick-panel',
                __('iCap SEO', 'icap-seo'),
                [$this, 'render_meta_box'],
                $post_type,
                'side',
                'high'
            );
        }

        public function render_meta_box(WP_Post $post): void
        {
            if (!current_user_can('edit_post', $post->ID)) {
                echo '<p>' . esc_html__('No permission to view this.', 'icap-seo') . '</p>';
                return;
            }

            wp_enqueue_style(
                'icap-seo-editor-panel',
                ICAP_SEO_PLUGIN_URL . 'assets/css/editor-panel.css',
                [],
                ICAP_SEO_VERSION
            );

            $data = $this->build_panel_data($post);
            $score = (int) $data['score'];
            if ($score >= 80) {
                $score_label = __('Good', 'icap-seo');
                $score_color = '#1a7f37';
            } elseif ($score >= 50) {
                $score_label = __('Needs work', 'icap-seo');
                $score_color = '#9a6700';
            } else {
                $score_label = __('Poor', 'icap-seo');
                $score_color = '#cf222e';
            }

            echo '<div class="icap-seo-editor-panel icap-seo-editor-panel--metabox">';

            echo '<div class="icap-seo-quick-score">';
            echo '<div class="icap-seo-quick-score__circle" style="border-color:' . esc_attr($score_color) . ';color:' . esc_attr($score_color) . ';">' . esc_html((string) $score) . '</div>';
            echo '<div class="icap-seo-quick-score__label"><strong>' . esc_html($score_label) . '</strong>';
            echo '<div class="icap-seo-quick-score__hint">' . esc_html__('Quick check based on this page\'s saved content only. Run a full scan in iCap SEO for the authoritative score.', 'icap-seo') . '</div>';
            echo '</div></div>';

            echo '<p class="icap-seo-metabox-heading"><strong>' . esc_html__('Quick checks', 'icap-seo') . '</strong></p>';
            foreach ($data['checks'] as $check) {
                if ($check['status'] === 'pass') {
                    $color = '#1a7f37';
                    $symbol = '✓';
                } elseif ($check['status'] === 'warn') {
                    $color = '#9a6700';
                    $symbol = '!';
                } else {
                    $color = '#cf222e';
                    $symbol = '✕';
                }
                echo '<div class="icap-seo-quick-check icap-seo-quick-check--' . esc_attr($check['status']) . '">';
                echo '<span class="icap-seo-quick-check__icon" style="color:' . esc_attr($color) . ';">' . esc_html($symbol) . '</span>';
                echo '<span class="icap-seo-quick-check__body"><strong>' . esc_html($check['label']) . '</strong><div class="icap-seo-quick-check__detail">' . esc_html($check['detail']) . '</div></span>';
                echo '</div>';
            }

            echo '<p class="icap-seo-metabox-heading"><strong>' . esc_html__('Search preview', 'icap-seo') . '</strong></p>';
            echo '<div class="icap-seo-serp-preview">';
            echo '<div class="icap-seo-serp-preview__url">' . esc_html($data['url']) . '</div>';
            echo '<div class="icap-seo-serp-preview__title" id="icap-seo-metabox-serp-title">' . esc_html($data['title']) . '</div>';
            echo '<div class="icap-seo-serp-preview__description">' . esc_html($data['description']) . '</div>';
            echo '<p class="icap-seo-serp-preview__note" id="icap-seo-metabox-serp-note" hidden>' . esc_html__('Title may be truncated in search results at this pixel width.', 'icap-seo') . '</p>';
            echo '</div>';

            echo '<p class="icap-seo-metabox-heading"><strong>' . esc_html__('Social preview', 'icap-seo') . '</strong></p>';
            echo '<div class="icap-seo-social-preview">';
            if ($data['imageUrl'] !== '') {
                echo '<img class="icap-seo-social-preview__image" src="' . esc_url($data['imageUrl']) . '" alt="" />';
            } else {
                echo '<div class="icap-seo-social-preview__image icap-seo-social-preview__image--empty"></div>';
            }
            echo '<div class="icap-seo-social-preview__body">';
            echo '<div class="icap-seo-social-preview__site">' . esc_html(strtoupper((string) $data['siteName'])) . '</div>';
            echo '<div class="icap-seo-social-preview__title">' . esc_html($data['title']) . '</div>';
            if ($data['description'] !== '') {
                echo '<div class="icap-seo-social-preview__description">' . esc_html($data['description']) . '</div>';
            }
            echo '</div></div>';

            echo '</div>';

            // Pixel-width overflow detection, mirroring assets/js/editor-panel.js's
            // measurePixelWidth() against the rendered text itself, so the same
            // measurement logic isn't duplicated in PHP against a second set of
            // font-metric assumptions.
            ?>
            <script>
            ( function () {
                try {
                    var titleEl = document.getElementById( 'icap-seo-metabox-serp-title' );
                    var noteEl = document.getElementById( 'icap-seo-metabox-serp-note' );
                    if ( ! titleEl || ! noteEl ) {
                        return;
                    }
                    var canvas = document.createElement( 'canvas' );
                    var ctx = canvas.getContext( '2d' );
                    if ( ! ctx ) {
                        return;
                    }
                    ctx.font = '400 20px Arial, sans-serif';
                    var width = ctx.measureText( titleEl.textContent || '' ).width;
                    if ( width > 600 ) {
                        titleEl.classList.add( 'is-overflow' );
                        noteEl.hidden = false;
                    }
                } catch ( err ) {}
            } )();
            </script>
            <?php
        }

        public function enqueue_assets(): void
        {
            $post = get_post();
            if (!$post instanceof WP_Post) {
                return;
            }
            if (!post_type_supports($post->post_type, 'title')) {
                return;
            }
            if (!current_user_can('edit_post', $post->ID)) {
                return;
            }

            wp_enqueue_script(
                'icap-seo-editor-panel',
                ICAP_SEO_PLUGIN_URL . 'assets/js/editor-panel.js',
                ['wp-plugins', 'wp-edit-post', 'wp-element', 'wp-components', 'wp-i18n', 'wp-data', 'wp-api-fetch'],
                ICAP_SEO_VERSION,
                true
            );

            wp_enqueue_style(
                'icap-seo-editor-panel',
                ICAP_SEO_PLUGIN_URL . 'assets/css/editor-panel.css',
                [],
                ICAP_SEO_VERSION
            );

            wp_localize_script('icap-seo-editor-panel', 'icapSeoEditorPanel', $this->build_panel_data($post));
        }

        private function build_panel_data(WP_Post $post): array
        {
            $title = trim((string) $post->post_title);
            $site_name = get_bloginfo('name');
            $description = $this->output->get_effective_meta_description($post, 200);
            $url = $this->output->get_effective_canonical_url($post);
            if ($url === '') {
                $permalink = get_permalink($post);
                $url = is_string($permalink) ? $permalink : '';
            }
            $image_url = get_the_post_thumbnail_url($post, 'large');

            $checks = $this->compute_quick_checks($title, $description, (string) $post->post_content);
            $score = $this->score_checks($checks);

            return [
                'postId' => $post->ID,
                'title' => $title !== '' ? $title : $site_name,
                'description' => $description,
                'url' => $url,
                'siteName' => $site_name,
                'imageUrl' => is_string($image_url) ? $image_url : '',
                'checks' => $checks,
                'score' => $score,
            ];
        }

        /**
         * Only checks computable from the post's own already-saved fields, with
         * no network call - the explicit Phase 1 scope boundary. Thresholds are
         * deliberately simple/conservative; this is a lightweight preview, not a
         * restatement of the authoritative cloud scan's own scoring logic.
         */
        private function compute_quick_checks(string $title, string $description, string $content): array
        {
            $checks = [];

            $title_len = $this->strlen($title);
            if ($title === '') {
                $checks[] = $this->check('missing_title_tag', __('Title', 'icap-seo'), 'fail', __('No title set yet.', 'icap-seo'));
            } elseif ($title_len < 15 || $title_len > 60) {
                $checks[] = $this->check(
                    'missing_title_tag',
                    __('Title', 'icap-seo'),
                    'warn',
                    sprintf(
                        /* translators: %d: character count of the post title */
                        __('%d characters - aim for 15-60 so it isn\'t truncated in search results.', 'icap-seo'),
                        $title_len
                    )
                );
            } else {
                $checks[] = $this->check(
                    'missing_title_tag',
                    __('Title', 'icap-seo'),
                    'pass',
                    sprintf(
                        /* translators: %d: character count of the post title */
                        __('%d characters.', 'icap-seo'),
                        $title_len
                    )
                );
            }

            $description_len = $this->strlen($description);
            if ($description === '') {
                $checks[] = $this->check('missing_meta_description', __('Meta description', 'icap-seo'), 'fail', __('No description found - add an excerpt or let iCap SEO generate one.', 'icap-seo'));
            } elseif ($description_len < 50 || $description_len > 160) {
                $checks[] = $this->check(
                    'missing_meta_description',
                    __('Meta description', 'icap-seo'),
                    'warn',
                    sprintf(
                        /* translators: %d: character count of the meta description */
                        __('%d characters - aim for 50-160.', 'icap-seo'),
                        $description_len
                    )
                );
            } else {
                $checks[] = $this->check(
                    'missing_meta_description',
                    __('Meta description', 'icap-seo'),
                    'pass',
                    sprintf(
                        /* translators: %d: character count of the meta description */
                        __('%d characters.', 'icap-seo'),
                        $description_len
                    )
                );
            }

            $has_heading = (bool) preg_match('/<h[1-6][\s>]/i', $content) || (bool) preg_match('/wp:heading/i', $content);
            $word_count = str_word_count(wp_strip_all_tags($content));
            if ($word_count >= 300 && !$has_heading) {
                $checks[] = $this->check('limited_heading_structure', __('Headings', 'icap-seo'), 'warn', __('No subheadings found in a longer page - consider breaking it up with H2/H3s.', 'icap-seo'));
            } else {
                $checks[] = $this->check(
                    'limited_heading_structure',
                    __('Headings', 'icap-seo'),
                    'pass',
                    $has_heading ? __('Subheadings present.', 'icap-seo') : __('Short content - no subheadings needed yet.', 'icap-seo')
                );
            }

            preg_match_all('/<img\b[^>]*>/i', $content, $image_matches);
            $images = $image_matches[0];
            $total_images = count($images);
            $missing_alt = 0;
            foreach ($images as $image_tag) {
                if (!preg_match('/\balt\s*=\s*"[^"]+"/i', $image_tag)) {
                    $missing_alt++;
                }
            }
            if ($total_images === 0) {
                $checks[] = $this->check('images_missing_alt', __('Image alt text', 'icap-seo'), 'pass', __('No images in this content yet.', 'icap-seo'));
            } elseif ($missing_alt > 0) {
                $checks[] = $this->check(
                    'images_missing_alt',
                    __('Image alt text', 'icap-seo'),
                    'warn',
                    sprintf(
                        /* translators: 1: images missing alt text, 2: total images in the content */
                        __('%1$d of %2$d images missing alt text.', 'icap-seo'),
                        $missing_alt,
                        $total_images
                    )
                );
            } else {
                $checks[] = $this->check(
                    'images_missing_alt',
                    __('Image alt text', 'icap-seo'),
                    'pass',
                    sprintf(
                        /* translators: %d: total images, all of which have alt text */
                        __('All %d images have alt text.', 'icap-seo'),
                        $total_images
                    )
                );
            }

            $checks[] = $this->compute_headline_quality_check($title);

            return $checks;
        }

        /**
         * Headline quality, distinct from the plain length check above: word
         * count balance, a hand-authored power-word list, and whether a number
         * is present (numbered-list headlines are a well-known engagement
         * pattern). Deliberately NOT sentiment analysis - that needs an LLM or
         * a sentiment library, out of scope for a client-computable quick
         * check. This is a heuristic nudge, not a scored ranking.
         */
        private function compute_headline_quality_check(string $title): array
        {
            if ($title === '') {
                return $this->check('headline_quality', __('Headline quality', 'icap-seo'), 'pass', __('No title set yet.', 'icap-seo'));
            }

            $word_count = str_word_count($title);
            $has_power_word = (bool) preg_match('/\b(' . implode('|', self::POWER_WORDS) . ')\b/i', $title);
            $has_number = (bool) preg_match('/\d/', $title);

            $notes = [];
            if ($word_count < 4) {
                $notes[] = __('quite short - consider a more descriptive headline', 'icap-seo');
            } elseif ($word_count > 14) {
                $notes[] = __('quite long - consider tightening it', 'icap-seo');
            }
            if (!$has_power_word) {
                $notes[] = __('consider a word like "free", "best", "guide", or "essential" to draw interest', 'icap-seo');
            }
            if (!$has_number) {
                $notes[] = __('consider adding a number or specific detail (e.g. "7 Ways to...")', 'icap-seo');
            }

            if (empty($notes)) {
                return $this->check(
                    'headline_quality',
                    __('Headline quality', 'icap-seo'),
                    'pass',
                    sprintf(
                        /* translators: %d: word count of the post title */
                        __('%d words, includes a power word and a number.', 'icap-seo'),
                        $word_count
                    )
                );
            }

            return $this->check(
                'headline_quality',
                __('Headline quality', 'icap-seo'),
                'warn',
                sprintf(
                    /* translators: %d: word count, %s: comma-separated suggestions */
                    __('%1$d words - %2$s.', 'icap-seo'),
                    $word_count,
                    implode('; ', $notes)
                )
            );
        }

        private function check(string $code, string $label, string $status, string $detail): array
        {
            return [
                'code' => $code,
                'label' => $label,
                'status' => $status,
                'detail' => $detail,
            ];
        }

        private function score_checks(array $checks): int
        {
            if (empty($checks)) {
                return 0;
            }

            $points = 0.0;
            foreach ($checks as $check) {
                if ($check['status'] === 'pass') {
                    $points += 1.0;
                } elseif ($check['status'] === 'warn') {
                    $points += 0.5;
                }
            }

            return (int) round(($points / count($checks)) * 100);
        }

        private function strlen(string $value): int
        {
            return function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);
        }
    }
}
