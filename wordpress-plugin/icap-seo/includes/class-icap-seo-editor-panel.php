<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * The iCap SEO panel on the post edit screen (block editor sidebar, or a meta
 * box in the classic editor).
 *
 * - Score: the same number Content Scores shows for this page (the last full
 *   scan's score, else the saved on-site check score), with a Check now button
 *   that re-runs the on-site checks for this one page.
 * - Live checks: a few of the on-site checks, re-run against the unsaved draft
 *   as the user types. They use ICap_SEO_Local_Checks' own functions and limits,
 *   so they can't disagree with Content Scores. They aren't scored.
 * - Headline tips: writing suggestions only, not an SEO check, not scored.
 * - Search and social previews: how the page looks as a Google result and when
 *   its link is shared, using the same title and picture the page's head prints.
 *
 * Every check in the panel runs on this site; nothing is sent to an external service.
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
        private ICap_SEO_Service_Client $service_client;

        public function __construct(ICap_SEO_Output $output, ICap_SEO_Service_Client $service_client)
        {
            $this->output = $output;
            $this->service_client = $service_client;
        }

        public function register(): void
        {
            add_action('enqueue_block_editor_assets', [$this, 'enqueue_assets']);
            add_action('add_meta_boxes', [$this, 'register_meta_box'], 10, 2);
            add_action('rest_api_init', [$this, 'register_rest_route']);
        }

        /**
         * Two local routes for the block editor sidebar: quick-check re-runs the
         * live checks on the unsaved draft (debounced as the user types), and
         * recheck runs the full on-site checks on the saved page and stores the
         * result, the same as Check all pages does for every page.
         */
        public function register_rest_route(): void
        {
            $can_edit = static function (WP_REST_Request $request): bool {
                $post_id = (int) $request->get_param('post_id');
                return $post_id > 0 && current_user_can('edit_post', $post_id);
            };
            register_rest_route('icap-seo/v1', '/editor-panel/quick-check', [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [$this, 'handle_quick_check_request'],
                'permission_callback' => $can_edit,
                'args' => [
                    'post_id' => ['required' => true, 'type' => 'integer'],
                    'title' => ['required' => false, 'type' => 'string', 'default' => ''],
                    'content' => ['required' => false, 'type' => 'string', 'default' => ''],
                    'excerpt' => ['required' => false, 'type' => 'string', 'default' => ''],
                    'featured_media' => ['required' => false, 'type' => 'integer', 'default' => 0],
                ],
            ]);
            register_rest_route('icap-seo/v1', '/editor-panel/recheck', [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [$this, 'handle_recheck_request'],
                'permission_callback' => $can_edit,
                'args' => [
                    'post_id' => ['required' => true, 'type' => 'integer'],
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
            $search_title = ICap_SEO_Local_Checks::document_title($title);

            return new WP_REST_Response([
                'title' => $title !== '' ? $title : get_bloginfo('name'),
                'searchTitle' => $search_title,
                'description' => $description,
                'checks' => $this->compute_quick_checks($search_title, $description, $content),
                'tip' => $this->compute_headline_quality_check($title),
                'image' => $this->draft_social_image($post_id, $content, (int) $request->get_param('featured_media')),
            ]);
        }

        public function handle_recheck_request(WP_REST_Request $request): WP_REST_Response
        {
            $post_id = (int) $request->get_param('post_id');
            $detail = ICap_SEO_Local_Checks::detail_for_post($post_id);
            if ($detail === null || !ICap_SEO_Local_Checks::store_result($post_id, $detail)) {
                return new WP_REST_Response([
                    'saved' => $this->saved_score_data($post_id),
                    'error' => __('This page couldn\'t be checked right now. Try again, or use Check all pages in Content Scores.', 'icap-seo'),
                ]);
            }

            return new WP_REST_Response(['saved' => $this->saved_score_data($post_id)]);
        }

        /**
         * The social picture for the draft: the featured image picked in the
         * editor, else the first image in the draft content, else the site logo or icon.
         */
        private function draft_social_image(int $post_id, string $content, int $featured_id): array
        {
            $post = get_post($post_id);
            if (!$post instanceof WP_Post) {
                return ['url' => '', 'source' => ''];
            }
            $draft = clone $post;
            $draft->post_content = $content;
            // Only an image the user may see is used for the preview.
            if ($featured_id > 0 && (get_post_type($featured_id) !== 'attachment' || !current_user_can('read_post', $featured_id))) {
                $featured_id = 0;
            }

            return $this->output->get_effective_social_image($draft, max(0, $featured_id));
        }

        private function saved_score_data(int $post_id): array
        {
            $saved = $this->service_client->get_saved_content_score($post_id);
            $checked_at = $saved['source'] === 'local' ? ICap_SEO_Local_Checks::stored_checked_at($post_id) : '';
            $timestamp = $checked_at !== '' ? strtotime($checked_at) : false;

            return [
                'score' => $saved['score'],
                'source' => $saved['source'],
                'partial' => $saved['partial'],
                'checkedAt' => $timestamp ? wp_date(get_option('date_format') . ' ' . get_option('time_format'), $timestamp) : '',
                'detailUrl' => add_query_arg(
                    ['page' => 'icap-seo', 'tab' => 'content-scores', 'content_key' => $saved['content_key']],
                    admin_url('admin.php')
                ),
            ];
        }

        /**
         * Same order as ICap_SEO_Output::get_effective_meta_description(), against
         * the live draft: a meta description set on purpose, then the excerpt, then
         * the content as plain text.
         */
        private function resolve_draft_description(int $post_id, string $excerpt, string $content): string
        {
            $post = get_post($post_id);
            $is_protected = $post instanceof WP_Post && $post->post_password !== '';
            // 160 characters, the length the page's meta description tag prints.
            $stored = get_post_meta($post_id, '_icap_seo_meta_description', true);
            if (is_string($stored) && trim($stored) !== '') {
                return $this->truncate(trim($stored), 160);
            }

            if ($is_protected) {
                return '';
            }
            if ($excerpt !== '') {
                return $this->truncate($excerpt, 160);
            }

            $stripped = trim((string) preg_replace('/\s+/', ' ', wp_strip_all_tags($content, true)));
            return $this->truncate($stripped, 160);
        }

        private function truncate(string $value, int $max_length): string
        {
            if ($this->strlen($value) <= $max_length) {
                return $value;
            }
            $truncated = function_exists('mb_substr') ? mb_substr($value, 0, $max_length) : substr($value, 0, $max_length);
            return trim($truncated);
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
            $saved = $data['saved'];

            echo '<div class="icap-seo-editor-panel icap-seo-editor-panel--metabox">';

            echo '<div class="icap-seo-quick-score">';
            if ($saved['score'] !== null) {
                $score = (int) $saved['score'];
                echo '<div class="icap-seo-quick-score__circle" style="border-color:' . esc_attr(ICap_SEO_Admin::score_color($score)) . ';color:' . esc_attr(ICap_SEO_Admin::score_text_color($score)) . ';">' . esc_html((string) $score) . '</div>';
            } else {
                echo '<div class="icap-seo-quick-score__circle icap-seo-quick-score__circle--empty">&ndash;</div>';
            }
            echo '<div class="icap-seo-quick-score__label"><strong>' . esc_html__('Content Scores', 'icap-seo') . '</strong>';
            echo '<div class="icap-seo-quick-score__hint">' . esc_html($this->saved_score_hint($saved)) . '</div>';
            echo '<a class="icap-seo-quick-score__link" href="' . esc_url($saved['detailUrl']) . '">' . esc_html__('Open in Content Scores', 'icap-seo') . '</a>';
            echo '</div></div>';

            echo '<p class="icap-seo-metabox-heading"><strong>' . esc_html__('Live checks', 'icap-seo') . '</strong></p>';
            echo '<p class="icap-seo-quick-check__intro">' . esc_html__('The basics from Content Scores, for the last saved version. Content Scores runs every check on the published page.', 'icap-seo') . '</p>';
            foreach (array_merge($data['checks'], [$data['tip']]) as $check) {
                $this->render_check_item($check);
            }

            echo '<p class="icap-seo-metabox-heading"><strong>' . esc_html__('Search preview', 'icap-seo') . '</strong></p>';
            echo '<p class="icap-seo-quick-check__intro">' . esc_html__('How this page can look as a Google result.', 'icap-seo') . '</p>';
            echo '<div class="icap-seo-serp-preview">';
            echo '<div class="icap-seo-serp-preview__url">' . esc_html($data['url']) . '</div>';
            echo '<div class="icap-seo-serp-preview__title" id="icap-seo-metabox-serp-title">' . esc_html($data['searchTitle']) . '</div>';
            echo '<div class="icap-seo-serp-preview__description">' . esc_html($data['description']) . '</div>';
            echo '<p class="icap-seo-serp-preview__note" id="icap-seo-metabox-serp-note" hidden>' . esc_html__('Title may be truncated in search results at this pixel width.', 'icap-seo') . '</p>';
            echo '</div>';

            echo '<p class="icap-seo-metabox-heading"><strong>' . esc_html__('Social preview', 'icap-seo') . '</strong></p>';
            echo '<p class="icap-seo-quick-check__intro">' . esc_html__('How this page looks when its link is shared on Facebook, LinkedIn or X.', 'icap-seo') . '</p>';
            echo '<div class="icap-seo-social-preview">';
            if ($data['image']['url'] !== '') {
                echo '<img class="icap-seo-social-preview__image" src="' . esc_url($data['image']['url']) . '" alt="" />';
            } else {
                echo '<div class="icap-seo-social-preview__image icap-seo-social-preview__image--empty">' . esc_html__('No picture', 'icap-seo') . '</div>';
            }
            echo '<div class="icap-seo-social-preview__body">';
            echo '<div class="icap-seo-social-preview__site">' . esc_html(strtoupper((string) $data['siteName'])) . '</div>';
            echo '<div class="icap-seo-social-preview__title">' . esc_html($data['title']) . '</div>';
            if ($data['description'] !== '') {
                echo '<div class="icap-seo-social-preview__description">' . esc_html($data['description']) . '</div>';
            }
            echo '</div></div>';
            $image_note = self::social_image_note($data['image']['source']);
            if ($image_note !== '') {
                echo '<p class="icap-seo-social-preview__note">' . esc_html($image_note) . '</p>';
            }

            echo '</div>';

            // Pixel-width overflow detection, mirroring assets/js/editor-panel.js's
            // measurePixelWidth() against the rendered text itself, so the same
            // measurement logic isn't duplicated in PHP against a second set of
            // font-metric assumptions.
            wp_enqueue_script(
                'icap-seo-serp-overflow',
                ICAP_SEO_PLUGIN_URL . 'assets/js/serp-overflow.js',
                [],
                ICAP_SEO_VERSION,
                true
            );
        }

        private function render_check_item(array $check): void
        {
            $symbols = ['pass' => ['#1a7f37', '✓'], 'warn' => ['#9a6700', '!'], 'fail' => ['#cf222e', '✕'], 'tip' => ['#2271b1', 'i']];
            [$color, $symbol] = $symbols[$check['status']] ?? $symbols['fail'];
            echo '<div class="icap-seo-quick-check icap-seo-quick-check--' . esc_attr($check['status']) . '">';
            echo '<span class="icap-seo-quick-check__icon" style="color:' . esc_attr($color) . ';">' . esc_html($symbol) . '</span>';
            echo '<span class="icap-seo-quick-check__body"><strong>' . esc_html($check['label']) . '</strong><div class="icap-seo-quick-check__detail">' . esc_html($check['detail']) . '</div></span>';
            echo '</div>';
        }

        private function saved_score_hint(array $saved): string
        {
            if ($saved['source'] === 'scan') {
                return __('From the last full scan.', 'icap-seo');
            }
            if ($saved['source'] === 'local') {
                return $saved['checkedAt'] !== ''
                    /* translators: %s: date and time the page was last checked */
                    ? sprintf(__('Checked %s.', 'icap-seo'), $saved['checkedAt'])
                    : __('From the on-site checks.', 'icap-seo');
            }

            return __('Not checked since this page last changed.', 'icap-seo');
        }

        /**
         * Says where the shared picture comes from when it isn't the featured
         * image. Kept in sync with socialImageNote() in assets/js/editor-panel.js.
         */
        public static function social_image_note(string $source): string
        {
            switch ($source) {
                case 'featured':
                    return '';
                case 'content':
                    return __('No featured image, so shares use the first image in this page.', 'icap-seo');
                case 'logo':
                    return __('No featured image or page image, so shares use the site logo.', 'icap-seo');
                case 'site_icon':
                    return __('No featured image or page image, so shares use the site icon.', 'icap-seo');
                default:
                    return __('Shares of this page show no picture. Set a featured image, or add an image to the page.', 'icap-seo');
            }
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
                ['wp-plugins', 'wp-editor', 'wp-edit-post', 'wp-element', 'wp-components', 'wp-i18n', 'wp-data', 'wp-api-fetch'],
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
            $description = $this->output->get_effective_meta_description($post, 160);
            $url = $this->output->get_effective_canonical_url($post);
            if ($url === '') {
                $permalink = get_permalink($post);
                $url = is_string($permalink) ? $permalink : '';
            }
            $search_title = ICap_SEO_Local_Checks::document_title($title);

            return [
                'postId' => $post->ID,
                'title' => $title !== '' ? $title : $site_name,
                'searchTitle' => $search_title,
                'description' => $description,
                'url' => $url,
                'siteName' => $site_name,
                'image' => $this->output->get_effective_social_image($post),
                'checks' => $this->compute_quick_checks($search_title, $description, (string) $post->post_content),
                'tip' => $this->compute_headline_quality_check($title),
                'saved' => $this->saved_score_data((int) $post->ID),
            ];
        }

        /**
         * The basics from the on-site checks, run on a draft: title, meta
         * description, content length, subheadings and images. Uses
         * ICap_SEO_Local_Checks' own functions on a page built the way its
         * WordPress-data fallback builds one, so a result here matches the
         * result Content Scores gets for the same text. Low-severity issues show
         * as warnings, the rest as failures.
         */
        private function compute_quick_checks(string $search_title, string $description, string $content): array
        {
            // Core hook: render the body the way a visitor gets it, as the on-site checks do.
            // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- the_content is a WordPress core hook, not a plugin hook.
            $body = (string) apply_filters('the_content', $content);
            $head = '<title>' . esc_html($search_title) . '</title>';
            if ($description !== '') {
                $head .= '<meta name="description" content="' . esc_attr($description) . '">';
            }
            $html = '<html><head>' . $head . '</head><body>' . $body . '</body></html>';

            $failed = [];
            foreach (array_merge(
                ICap_SEO_Local_Checks::page_basics_issues($html),
                ICap_SEO_Local_Checks::heading_issues($body),
                ICap_SEO_Local_Checks::image_issues($body)
            ) as $issue) {
                $failed[$issue['issue_code']] = $issue['severity'] === 'low' ? 'warn' : 'fail';
            }

            $checks = [];

            $title_length = $this->strlen($search_title);
            if (isset($failed['missing_title_tag'])) {
                $checks[] = $this->check('title', __('Title', 'icap-seo'), 'fail', __('No title yet.', 'icap-seo'));
            } else {
                $checks[] = $this->check(
                    'title',
                    __('Title', 'icap-seo'),
                    $failed['title_length_out_of_range'] ?? 'pass',
                    sprintf(
                        /* translators: 1: title length, 2: minimum length, 3: maximum length */
                        __('%1$d characters, with the site name. Aim for %2$d–%3$d.', 'icap-seo'),
                        $title_length,
                        ICap_SEO_Local_Checks::TITLE_MIN_LENGTH,
                        ICap_SEO_Local_Checks::TITLE_MAX_LENGTH
                    )
                );
            }

            if (isset($failed['missing_meta_description'])) {
                $checks[] = $this->check('meta_description', __('Meta description', 'icap-seo'), $failed['missing_meta_description'], __('No description yet. Add an excerpt, or let iCap SEO write one.', 'icap-seo'));
            } else {
                $checks[] = $this->check(
                    'meta_description',
                    __('Meta description', 'icap-seo'),
                    $failed['meta_description_length_out_of_range'] ?? 'pass',
                    sprintf(
                        /* translators: 1: description length, 2: minimum length, 3: maximum length */
                        __('%1$d characters. Aim for %2$d–%3$d.', 'icap-seo'),
                        $this->strlen($description),
                        ICap_SEO_Local_Checks::DESCRIPTION_MIN_LENGTH,
                        ICap_SEO_Local_Checks::DESCRIPTION_MAX_LENGTH
                    )
                );
            }

            $checks[] = $this->check(
                'content_length',
                __('Content length', 'icap-seo'),
                $failed['thin_content'] ?? 'pass',
                sprintf(
                    /* translators: 1: word count, 2: minimum word count */
                    __('%1$d words. Aim for at least %2$d.', 'icap-seo'),
                    ICap_SEO_Local_Checks::visible_word_count($body),
                    ICap_SEO_Local_Checks::THIN_CONTENT_WORDS
                )
            );

            $checks[] = $this->check(
                'subheadings',
                __('Subheadings', 'icap-seo'),
                $failed['limited_heading_structure'] ?? 'pass',
                sprintf(
                    /* translators: 1: number of H2/H3 headings, 2: minimum number */
                    __('%1$d H2 or H3 headings. Aim for at least %2$d.', 'icap-seo'),
                    (int) preg_match_all('/<h[23][\s>]/i', $body),
                    ICap_SEO_Local_Checks::MIN_SECONDARY_HEADINGS
                )
            );

            $image_count = (int) preg_match_all('/<img\b[^>]*>/i', $body);
            if (isset($failed['no_images_detected'])) {
                $checks[] = $this->check('images', __('Images', 'icap-seo'), $failed['no_images_detected'], __('No images yet.', 'icap-seo'));
            } elseif (isset($failed['images_missing_alt'])) {
                $checks[] = $this->check('images', __('Images', 'icap-seo'), $failed['images_missing_alt'], sprintf(
                    /* translators: %d: number of images */
                    _n('%d image; some are missing alt text.', '%d images; some are missing alt text.', $image_count, 'icap-seo'),
                    $image_count
                ));
            } else {
                $checks[] = $this->check('images', __('Images', 'icap-seo'), 'pass', sprintf(
                    /* translators: %d: number of images */
                    _n('%d image, with alt text.', 'All %d images have alt text.', $image_count, 'icap-seo'),
                    $image_count
                ));
            }

            return $checks;
        }

        /**
         * Headline quality, distinct from the plain length check above: word
         * count balance, a hand-authored power-word list, and whether a number
         * is present (numbered-list headlines are a well-known engagement
         * pattern). Deliberately NOT sentiment analysis - that needs an LLM or
         * a sentiment library, out of scope for a client-computable quick
         * check. A writing tip only: it isn't one of the SEO checks and isn't scored.
         */
        private function compute_headline_quality_check(string $title): array
        {
            if ($title === '') {
                return $this->check('headline_quality', __('Headline tip', 'icap-seo'), 'tip', __('No title set yet.', 'icap-seo'));
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
                    __('Headline tip', 'icap-seo'),
                    'tip',
                    sprintf(
                        /* translators: %d: word count of the post title */
                        __('%d words, includes a power word and a number.', 'icap-seo'),
                        $word_count
                    )
                );
            }

            return $this->check(
                'headline_quality',
                __('Headline tip', 'icap-seo'),
                'tip',
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

        private function strlen(string $value): int
        {
            return function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);
        }
    }
}
