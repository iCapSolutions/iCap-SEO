<?php

if (!defined('ABSPATH')) {
    exit;
}

require_once ICAP_SEO_PLUGIN_DIR . 'includes/class-icap-seo-service-client.php';
require_once ICAP_SEO_PLUGIN_DIR . 'admin/class-icap-seo-admin.php';

class ICap_SEO_Plugin
{
    private ICap_SEO_Admin $admin;

    public function __construct()
    {
        $this->admin = new ICap_SEO_Admin(new ICap_SEO_Service_Client());
    }
    public function run(): void
    {
        add_action('admin_menu', [$this, 'register_admin']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_head', [$this, 'output_meta_description_fallback'], 20);
        add_action('wp_head', [$this, 'output_canonical_fallback'], 20);
        add_action('wp_head', [$this, 'output_jsonld_schema_fallback'], 20);
        add_action('wp_head', [$this, 'output_social_meta_fallback'], 20);
        add_action('template_redirect', [$this, 'maybe_apply_redirect'], 1);
        add_action('template_redirect', [$this, 'serve_indexnow_key_file'], 1);
        add_action('save_post', [$this, 'ping_indexnow_on_publish'], 20, 3);
        add_action('before_delete_post', [$this, 'ping_indexnow_on_delete']);
        add_action('wp_trash_post', [$this, 'ping_indexnow_on_delete']);
        $this->admin->register_admin_actions();
        $this->admin->register_list_table_columns();
    }

    public function register_admin(): void
    {
        $this->admin->register_menu();
    }

    public function enqueue_assets(string $hook): void
    {
        if (strpos($hook, 'icap-seo') === false) {
            return;
        }

        wp_enqueue_style(
            'icap-seo-admin',
            ICAP_SEO_PLUGIN_URL . 'assets/css/admin.css',
            [],
            ICAP_SEO_VERSION
        );

        wp_enqueue_script(
            'icap-seo-admin',
            ICAP_SEO_PLUGIN_URL . 'assets/js/admin.js',
            [],
            ICAP_SEO_VERSION,
            true
        );
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
     */
    private function is_another_seo_plugin_active(): bool
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
     * both stay in sync rather than drifting via separate copies.
     */
    private function get_effective_meta_description(WP_Post $post, int $max_length): string
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

    private function get_effective_canonical_url(WP_Post $post): string
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
     * Redirects are admin-configured (manage_options capability, validated with
     * esc_url_raw() at save time in ICap_SEO_Admin::handle_add_redirect()), not
     * request-controlled input being reflected back - unlike this plugin's OAuth/
     * Stripe redirects, an external destination here is a deliberate, trusted
     * setting, so wp_redirect() (not wp_safe_redirect()) is the correct choice:
     * redirect managers in every competitor plugin support sending visitors to
     * another domain, and wp_safe_redirect() would silently rewrite that to the
     * homepage instead.
     */
    public function maybe_apply_redirect(): void
    {
        $redirects = get_option('icap_seo_redirects', []);
        if (!is_array($redirects) || empty($redirects)) {
            return;
        }

        $request_uri = isset($_SERVER['REQUEST_URI']) ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])) : '';
        $request_path = '/' . ltrim((string) wp_parse_url($request_uri, PHP_URL_PATH), '/');
        if ($request_path !== '/') {
            $request_path = rtrim($request_path, '/');
        }

        foreach ($redirects as $row) {
            if (!is_array($row) || ($row['source'] ?? '') !== $request_path) {
                continue;
            }
            $target = (string) ($row['target'] ?? '');
            if ($target === '') {
                continue;
            }
            $status = ((string) ($row['type'] ?? '301')) === '302' ? 302 : 301;
            wp_redirect($target, $status);
            exit;
        }
    }

    /**
     * IndexNow (api.indexnow.org) requires a per-site key served as a plain-text
     * file at the site root before it will accept submissions from that key -
     * this serves it without needing a rewrite-rule flush, matching how simple
     * key-verification files are commonly handled from a plugin.
     */
    private function get_or_create_indexnow_key(): string
    {
        $key = get_option('icap_seo_indexnow_key');
        if (is_string($key) && $key !== '') {
            return $key;
        }

        $key = bin2hex(random_bytes(16));
        update_option('icap_seo_indexnow_key', $key, false);

        return $key;
    }

    public function serve_indexnow_key_file(): void
    {
        $request_uri = isset($_SERVER['REQUEST_URI']) ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])) : '';
        $request_path = trim((string) wp_parse_url($request_uri, PHP_URL_PATH), '/');
        $key = $this->get_or_create_indexnow_key();

        if ($request_path !== $key . '.txt') {
            return;
        }

        header('Content-Type: text/plain; charset=utf-8');
        echo esc_html($key);
        exit;
    }

    /**
     * Fire-and-forget: IndexNow has no response we act on, and a slow/unreachable
     * endpoint must never delay the actual publish/update/delete action.
     */
    private function ping_indexnow_for_url(string $url): void
    {
        if ($url === '' || $this->is_another_seo_plugin_active()) {
            return;
        }

        $key = $this->get_or_create_indexnow_key();
        $endpoint = 'https://api.indexnow.org/indexnow'
            . '?url=' . rawurlencode($url)
            . '&key=' . rawurlencode($key)
            . '&keyLocation=' . rawurlencode(home_url('/' . $key . '.txt'));

        wp_remote_get($endpoint, [
            'timeout' => 3,
            'blocking' => false,
        ]);
    }

    public function ping_indexnow_on_publish(int $post_id, WP_Post $post, bool $update): void
    {
        unset($update);
        if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
            return;
        }
        if ($post->post_status !== 'publish' || !in_array($post->post_type, ['post', 'page'], true)) {
            return;
        }

        $url = get_permalink($post);
        if (is_string($url)) {
            $this->ping_indexnow_for_url($url);
        }
    }

    public function ping_indexnow_on_delete(int $post_id): void
    {
        $post = get_post($post_id);
        if (!$post instanceof WP_Post || !in_array($post->post_type, ['post', 'page'], true)) {
            return;
        }

        $url = get_permalink($post);
        if (is_string($url)) {
            $this->ping_indexnow_for_url($url);
        }
    }
}
