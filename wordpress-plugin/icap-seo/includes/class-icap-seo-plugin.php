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
    }

    public function output_meta_description_fallback(): void
    {
        if (is_admin() || !is_singular()) {
            return;
        }
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
        if (has_excerpt($post)) {
            $description = (string) $post->post_excerpt;
        }
        if ($description === '') {
            $description = wp_strip_all_tags((string) $post->post_content, true);
        }

        $description = preg_replace('/\s+/', ' ', trim($description));
        if (!is_string($description) || $description === '') {
            return;
        }

        if (function_exists('mb_strlen') && function_exists('mb_substr')) {
            if (mb_strlen($description) > 170) {
                $description = trim((string) mb_substr($description, 0, 170));
            }
        } elseif (strlen($description) > 170) {
            $description = trim((string) substr($description, 0, 170));
        }

        echo '<meta name="description" content="' . esc_attr($description) . "\" />\n";
    }
}
