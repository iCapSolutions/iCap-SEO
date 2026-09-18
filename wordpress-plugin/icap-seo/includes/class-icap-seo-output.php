<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * On-page SEO output: meta description, canonical, JSON-LD, social meta,
 * and local-business schema. Deliberately dependency-free (no admin UI, no
 * service client, no cloud API calls, no constants beyond ABSPATH) so it can
 * be required and run standalone anywhere the plugin proper isn't installed
 * -- e.g. as a small mu-plugin on a site that doesn't want the full product
 * surface. The iCap SEO plugin itself is the primary consumer; keep this
 * class as the single source of truth for this logic rather than forking it.
 */
if (!class_exists('ICap_SEO_Output')) {
    class ICap_SEO_Output
    {
        public function register(): void
        {
            add_action('wp_head', [$this, 'output_meta_description_fallback'], 20);
            add_action('wp_head', [$this, 'output_canonical_fallback'], 20);
            add_action('wp_head', [$this, 'output_jsonld_schema_fallback'], 20);
            add_action('wp_head', [$this, 'output_social_meta_fallback'], 20);
            add_action('wp_head', [$this, 'output_local_business_schema_fallback'], 20);
        }

        /**
         * A page configured as the site's Posts page (Settings > Reading) is
         * queried as the blog index, so is_singular() is false even though
         * get_queried_object() still returns that page's own WP_Post. Treat it
         * like a singular page here so its meta tags aren't silently dropped.
         */
        private function is_singular_or_posts_page(): bool
        {
            return is_singular() || (is_home() && !is_front_page());
        }

        /**
         * Whether another plugin already owns SEO meta/canonical/schema output
         * for this page, so our own fallback output should stay out of the way.
         * Public: also consulted by ICap_SEO_Plugin's IndexNow/llms.txt features,
         * which defer to another SEO plugin the same way this class's own output
         * methods do.
         */
        public function is_another_seo_plugin_active(): bool
        {
            return defined('RANK_MATH_VERSION')
                || defined('WPSEO_VERSION')
                || defined('AIOSEO_VERSION')
                || defined('SEOPRESS_VERSION')
                || class_exists('RankMath')
                || class_exists('WPSEO_Options')
                || class_exists('AIOSEO\\Plugin\\Common\\Main');
        }

        public function output_meta_description_fallback(): void
        {
            if (is_admin() || !$this->is_singular_or_posts_page()) {
                return;
            }
            // If another SEO plugin is active, let it own the description tag.
            if ($this->is_another_seo_plugin_active()) {
                return;
            }

            $post = get_queried_object();
            if (!$post instanceof WP_Post) {
                return;
            }

            $description = $this->get_effective_meta_description($post, 160);
            if ($description === '') {
                return;
            }

            echo '<meta name="description" content="' . esc_attr($description) . "\" />\n";
        }

        /**
         * Same stored-description -> excerpt -> stripped-content fallback chain
         * used by the meta description tag, shared with social meta output so
         * both stay in sync rather than drifting via separate copies. Public:
         * also used by ICap_SEO_Plugin's llms.txt generator for the same
         * per-page description.
         */
        public function get_effective_meta_description(WP_Post $post, int $max_length): string
        {
            $description = '';
            $stored = get_post_meta((int) $post->ID, '_icap_seo_meta_description', true);
            if (is_string($stored) && trim($stored) !== '') {
                $description = $stored;
            }
            if ($description === '' && has_excerpt($post)) {
                $description = (string) $post->post_excerpt;
            }
            if ($description === '') {
                $content = (string) $post->post_content;
                $content = str_replace(
                    [
                        '<!-- SEO by iCap - https://icapsolutions.com -->',
                        '<!-- /SEO by iCap - https://icapsolutions.com -->',
                    ],
                    ' ',
                    $content
                );
                $description = wp_strip_all_tags($content, true);
            }

            $description = preg_replace('/\s+/', ' ', trim((string) $description));
            if (!is_string($description) || $description === '') {
                return '';
            }

            if (function_exists('mb_strlen') && function_exists('mb_substr')) {
                if (mb_strlen($description) > $max_length) {
                    $description = trim((string) mb_substr($description, 0, $max_length));
                }
            } elseif (strlen($description) > $max_length) {
                $description = trim((string) substr($description, 0, $max_length));
            }

            return $description;
        }

        public function output_canonical_fallback(): void
        {
            if (is_admin() || !$this->is_singular_or_posts_page()) {
                return;
            }
            // If another SEO plugin is active, let it own the canonical tag.
            if ($this->is_another_seo_plugin_active()) {
                return;
            }
            // WordPress core's own rel_canonical() only fires for is_singular() queries
            // (see wp-includes/link-template.php), so only defer to it there. On the
            // Posts-page case (is_home() && !is_front_page()) core emits nothing, so we
            // still need to.
            if (is_singular() && has_action('wp_head', 'rel_canonical')) {
                return;
            }

            $post = get_queried_object();
            if (!$post instanceof WP_Post) {
                return;
            }

            $canonical_url = $this->get_effective_canonical_url($post);
            if ($canonical_url === '') {
                return;
            }

            echo '<link rel="canonical" href="' . esc_url($canonical_url) . "\" />\n";
        }

        /**
         * Public: also consulted by ICap_SEO_Editor_Panel for the in-editor
         * SERP/social preview, which needs the same resolved URL used in
         * wp_head output so the two never disagree.
         */
        public function get_effective_canonical_url(WP_Post $post): string
        {
            $canonical_url = get_post_meta((int) $post->ID, '_icap_seo_canonical_url', true);
            if (!is_string($canonical_url) || trim($canonical_url) === '') {
                $canonical_url = get_permalink($post);
            }
            if (!is_string($canonical_url) || trim($canonical_url) === '') {
                return '';
            }

            return $canonical_url;
        }

        public function output_jsonld_schema_fallback(): void
        {
            if (is_admin() || !$this->is_singular_or_posts_page()) {
                return;
            }
            // If another SEO plugin is active, let it own schema markup output.
            if ($this->is_another_seo_plugin_active()) {
                return;
            }

            $post = get_queried_object();
            if (!$post instanceof WP_Post) {
                return;
            }

            $schema_json = get_post_meta((int) $post->ID, '_icap_seo_jsonld_schema_json', true);
            if (!is_string($schema_json) || trim($schema_json) === '') {
                return;
            }

            // esc_html() would corrupt valid JSON (it HTML-entity-encodes quotes, which
            // browsers don't decode inside <script> content). The real risk here is a
            // stored value containing a literal "</script>" breaking out of the tag, so
            // neutralize that specifically instead - the standard JSON-in-<script> mitigation.
            echo '<script type="application/ld+json">' . str_replace('</', '<\/', $schema_json) . "</script>\n";
        }

        /**
         * Open Graph + X/Twitter Card tags. Competitor baseline (Yoast, AIOSEO,
         * SEOPress all ship this free) - iCap SEO had no social preview output
         * at all until this.
         */
        public function output_social_meta_fallback(): void
        {
            if (is_admin() || !$this->is_singular_or_posts_page()) {
                return;
            }
            // If another SEO plugin is active, let it own social meta output.
            if ($this->is_another_seo_plugin_active()) {
                return;
            }

            $post = get_queried_object();
            if (!$post instanceof WP_Post) {
                return;
            }

            $title = trim((string) $post->post_title);
            if ($title === '') {
                $title = get_bloginfo('name');
            }
            $description = $this->get_effective_meta_description($post, 200);
            $url = $this->get_effective_canonical_url($post);
            $site_name = get_bloginfo('name');
            $type = $post->post_type === 'post' ? 'article' : 'website';
            $image_url = get_the_post_thumbnail_url($post, 'large');

            echo '<meta property="og:type" content="' . esc_attr($type) . "\" />\n";
            echo '<meta property="og:title" content="' . esc_attr($title) . "\" />\n";
            if ($description !== '') {
                echo '<meta property="og:description" content="' . esc_attr($description) . "\" />\n";
            }
            if ($url !== '') {
                echo '<meta property="og:url" content="' . esc_url($url) . "\" />\n";
            }
            if ($site_name !== '') {
                echo '<meta property="og:site_name" content="' . esc_attr($site_name) . "\" />\n";
            }
            if (is_string($image_url) && $image_url !== '') {
                echo '<meta property="og:image" content="' . esc_url($image_url) . "\" />\n";
            }

            echo '<meta name="twitter:card" content="' . esc_attr($image_url ? 'summary_large_image' : 'summary') . "\" />\n";
            echo '<meta name="twitter:title" content="' . esc_attr($title) . "\" />\n";
            if ($description !== '') {
                echo '<meta name="twitter:description" content="' . esc_attr($description) . "\" />\n";
            }
            if (is_string($image_url) && $image_url !== '') {
                echo '<meta name="twitter:image" content="' . esc_url($image_url) . "\" />\n";
            }
        }

        /**
         * LocalBusiness structured data - a site-wide business profile, unlike
         * the per-post JSON-LD schema in output_jsonld_schema_fallback() which
         * describes one page's content. Output on every front-end page (not
         * gated by is_singular_or_posts_page()) since it describes the site's
         * owner, not any particular page. Silent no-op until an admin actually
         * fills in a business name and address on the Local SEO tab - never
         * emits a schema block with placeholder/empty required fields.
         */
        public function output_local_business_schema_fallback(): void
        {
            if (is_admin() || $this->is_another_seo_plugin_active()) {
                return;
            }

            $business = get_option('icap_seo_local_business', []);
            if (!is_array($business)) {
                return;
            }

            $name = trim((string) ($business['business_name'] ?? ''));
            $street = trim((string) ($business['street_address'] ?? ''));
            if ($name === '' || $street === '') {
                return;
            }

            $type = (string) ($business['business_type'] ?? 'LocalBusiness');
            $schema = [
                '@context' => 'https://schema.org',
                '@type' => $type,
                'name' => $name,
                'url' => home_url('/'),
                'address' => array_filter([
                    '@type' => 'PostalAddress',
                    'streetAddress' => $street,
                    'addressLocality' => trim((string) ($business['city'] ?? '')),
                    'addressRegion' => trim((string) ($business['region'] ?? '')),
                    'postalCode' => trim((string) ($business['postal_code'] ?? '')),
                    'addressCountry' => trim((string) ($business['country'] ?? '')),
                ], static fn($value): bool => $value !== ''),
            ];

            $phone = trim((string) ($business['phone'] ?? ''));
            if ($phone !== '') {
                $schema['telephone'] = $phone;
            }
            $price_range = trim((string) ($business['price_range'] ?? ''));
            if ($price_range !== '') {
                $schema['priceRange'] = $price_range;
            }

            $hours = is_array($business['hours'] ?? null) ? $business['hours'] : [];
            $hours_spec = [];
            foreach ($hours as $day => $day_hours) {
                if (!is_array($day_hours) || !empty($day_hours['closed'])) {
                    continue;
                }
                $opens = (string) ($day_hours['opens'] ?? '');
                $closes = (string) ($day_hours['closes'] ?? '');
                if ($opens === '' || $closes === '') {
                    continue;
                }
                $hours_spec[] = [
                    '@type' => 'OpeningHoursSpecification',
                    'dayOfWeek' => ucfirst((string) $day),
                    'opens' => $opens,
                    'closes' => $closes,
                ];
            }
            if (!empty($hours_spec)) {
                $schema['openingHoursSpecification'] = $hours_spec;
            }

            $json = wp_json_encode($schema);
            if (!is_string($json)) {
                return;
            }

            echo '<script type="application/ld+json">' . str_replace('</', '<\/', $json) . "</script>\n";
        }
    }
}
