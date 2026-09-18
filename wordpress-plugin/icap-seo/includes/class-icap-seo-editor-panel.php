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
        private ICap_SEO_Output $output;

        public function __construct(ICap_SEO_Output $output)
        {
            $this->output = $output;
        }

        public function register(): void
        {
            add_action('enqueue_block_editor_assets', [$this, 'enqueue_assets']);
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
                ['wp-plugins', 'wp-edit-post', 'wp-element', 'wp-components', 'wp-i18n'],
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

            $checks = $this->compute_quick_checks($post, $title, $description);
            $score = $this->score_checks($checks);

            return [
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
        private function compute_quick_checks(WP_Post $post, string $title, string $description): array
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

            $content = (string) $post->post_content;
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

            return $checks;
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
