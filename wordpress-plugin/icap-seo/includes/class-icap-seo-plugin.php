<?php

if (!defined('ABSPATH')) {
    exit;
}

require_once ICAP_SEO_PLUGIN_DIR . 'includes/class-icap-seo-service-client.php';
require_once ICAP_SEO_PLUGIN_DIR . 'admin/class-icap-seo-admin.php';

class ICap_SEO_Plugin
{
    public function run(): void
    {
        add_action('admin_menu', [$this, 'register_admin']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function register_admin(): void
    {
        $admin = new ICap_SEO_Admin(new ICap_SEO_Service_Client());
        $admin->register_menu();
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
}
