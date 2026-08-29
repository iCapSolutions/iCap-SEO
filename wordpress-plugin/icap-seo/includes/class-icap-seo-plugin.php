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

    public function output_meta_description_fallback(): void
    {
        if (is_admin() || !is_singular()) {
            return;
        }
        // If another SEO plugin is active, let it own the description tag.
        if (
            defined('RANK_MATH_VERSION')
            || defined('WPSEO_VERSION')
            || defined('AIOSEO_VERSION')
            || class_exists('RankMath')
            || class_exists('WPSEO_Options')
            || class_exists('AIOSEO\\Plugin\\Common\\Main')
        ) {
            return;
        }

        $post = get_queried_object();
        if (!$post instanceof WP_Post) {
            return;
        }

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
            return;
        }

        if (function_exists('mb_strlen') && function_exists('mb_substr')) {
            if (mb_strlen($description) > 160) {
                $description = trim((string) mb_substr($description, 0, 160));
            }
        } elseif (strlen($description) > 160) {
            $description = trim((string) substr($description, 0, 160));
        }

        echo '<meta name="description" content="' . esc_attr($description) . "\" />\n";
    }

    public function output_canonical_fallback(): void
    {
        if (is_admin() || !is_singular()) {
            return;
        }
        // If another SEO plugin is active, let it own the canonical tag.
        if (
            defined('RANK_MATH_VERSION')
            || defined('WPSEO_VERSION')
            || defined('AIOSEO_VERSION')
            || class_exists('RankMath')
            || class_exists('WPSEO_Options')
            || class_exists('AIOSEO\\Plugin\\Common\\Main')
        ) {
            return;
        }
        // WordPress core (or another plugin) already emits a canonical link tag by default.
        if (has_action('wp_head', 'rel_canonical')) {
            return;
        }

        $post = get_queried_object();
        if (!$post instanceof WP_Post) {
            return;
        }

        $canonical_url = get_post_meta((int) $post->ID, '_icap_seo_canonical_url', true);
        if (!is_string($canonical_url) || trim($canonical_url) === '') {
            $canonical_url = get_permalink($post);
        }
        if (!is_string($canonical_url) || trim($canonical_url) === '') {
            return;
        }

        echo '<link rel="canonical" href="' . esc_url($canonical_url) . "\" />\n";
    }

    public function output_jsonld_schema_fallback(): void
    {
        if (is_admin() || !is_singular()) {
            return;
        }
        // If another SEO plugin is active, let it own schema markup output.
        if (
            defined('RANK_MATH_VERSION')
            || defined('WPSEO_VERSION')
            || defined('AIOSEO_VERSION')
            || class_exists('RankMath')
            || class_exists('WPSEO_Options')
            || class_exists('AIOSEO\\Plugin\\Common\\Main')
        ) {
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

        echo '<script type="application/ld+json">' . $schema_json . "</script>\n";
    }
}
