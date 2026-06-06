<?php

if (!defined('ABSPATH')) {
    exit;
}

class ICap_SEO_Admin
{
    private ICap_SEO_Service_Client $service_client;

    public function __construct(ICap_SEO_Service_Client $service_client)
    {
        $this->service_client = $service_client;
    }

    public function register_menu(): void
    {
        add_menu_page(
            __('iCap SEO', 'icap-seo'),
            __('iCap SEO', 'icap-seo'),
            'manage_options',
            'icap-seo',
            [$this, 'render_dashboard'],
            ICAP_SEO_PLUGIN_URL . 'assets/images/icap-seo-icon.svg',
            58
        );
    }

    public function render_dashboard(): void
    {
        $active_tab = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : 'home';
        $score_snapshot = $this->service_client->get_site_score_snapshot();
        $recommendation_preview = $this->service_client->get_recommendation_preview();

        include ICAP_SEO_PLUGIN_DIR . 'admin/views/dashboard.php';
    }
}
