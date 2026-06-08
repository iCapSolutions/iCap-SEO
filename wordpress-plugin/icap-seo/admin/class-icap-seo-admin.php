<?php

if (!defined('ABSPATH')) {
    exit;
}

class ICap_SEO_Admin
{
    private ICap_SEO_Service_Client $service_client;
    private const SCORE_COLUMN_KEY = 'icap_seo_score';
    private const DELTA_COLUMN_KEY = 'icap_seo_delta';
    private const NOTICE_QUERY_KEY = 'icap_seo_notice';

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

    public function register_admin_actions(): void
    {
        add_action('admin_post_icap_seo_save_settings', [$this, 'handle_save_settings']);
        add_action('admin_post_icap_seo_register_site', [$this, 'handle_register_site']);
        add_action('admin_post_icap_seo_trigger_scan', [$this, 'handle_trigger_scan']);
    }
    public function register_list_table_columns(): void
    {
        add_filter('manage_posts_columns', [$this, 'add_score_columns']);
        add_filter('manage_pages_columns', [$this, 'add_score_columns']);
        add_action('manage_posts_custom_column', [$this, 'render_score_columns'], 10, 2);
        add_action('manage_pages_custom_column', [$this, 'render_score_columns'], 10, 2);
        add_action('admin_head-edit.php', [$this, 'output_list_column_styles']);
    }

    public function add_score_columns(array $columns): array
    {
        $inserted = [];

        foreach ($columns as $key => $value) {
            $inserted[$key] = $value;

            if ($key === 'title') {
                $inserted[self::SCORE_COLUMN_KEY] = __('iCap Score', 'icap-seo');
                $inserted[self::DELTA_COLUMN_KEY] = __('iCap vs Rank Math', 'icap-seo');
            }
        }

        return $inserted;
    }

    public function render_score_columns(string $column_name, int $post_id): void
    {
        if ($column_name !== self::SCORE_COLUMN_KEY && $column_name !== self::DELTA_COLUMN_KEY) {
            return;
        }

        $score_data = $this->service_client->get_content_score_for_post($post_id);

        if ($column_name === self::SCORE_COLUMN_KEY) {
            echo esc_html($score_data['icap_score']);
            return;
        }

        echo esc_html($score_data['rank_math_delta']);
    }

    public function output_list_column_styles(): void
    {
        echo '<style>
            .column-icap_seo_score { width: 9%; }
            .column-icap_seo_delta { width: 11%; }
        </style>';
    }

    public function render_dashboard(): void
    {
        $active_tab = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : 'home';
        $notice_code = isset($_GET[self::NOTICE_QUERY_KEY]) ? sanitize_key(wp_unslash($_GET[self::NOTICE_QUERY_KEY])) : '';
        $connection_settings = $this->service_client->get_connection_settings();
        $score_snapshot = $this->service_client->get_site_score_snapshot();
        $recommendation_preview = $this->service_client->get_recommendation_preview();
        $content_scores = $this->service_client->get_content_scores_overview();
        $scan_status_result = $this->service_client->get_scan_status();
        $scan_status_data = $scan_status_result['success'] ? $scan_status_result['data'] : [];

        include ICAP_SEO_PLUGIN_DIR . 'admin/views/dashboard.php';
    }

    public function handle_save_settings(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to do that.', 'icap-seo'));
        }
        check_admin_referer('icap_seo_save_settings');

        $api_base_url = isset($_POST['api_base_url']) ? esc_url_raw((string) wp_unslash($_POST['api_base_url'])) : '';
        $site_id = isset($_POST['site_id']) ? sanitize_text_field((string) wp_unslash($_POST['site_id'])) : '';
        $site_token = isset($_POST['site_token']) ? sanitize_text_field((string) wp_unslash($_POST['site_token'])) : '';

        $this->service_client->update_connection_settings([
            'api_base_url' => $api_base_url,
            'site_id' => $site_id,
            'site_token' => $site_token,
        ]);

        $this->redirect_with_notice('settings_saved', 'settings');
    }

    public function handle_register_site(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to do that.', 'icap-seo'));
        }
        check_admin_referer('icap_seo_register_site');

        $result = $this->service_client->register_site([
            'site_url' => home_url('/'),
            'wp_version' => get_bloginfo('version'),
            'plugin_version' => ICAP_SEO_VERSION,
            'site_name' => get_bloginfo('name'),
            'timezone' => wp_timezone_string(),
        ]);

        if ($result['success']) {
            $this->redirect_with_notice('register_success', 'settings');
            return;
        }

        $this->redirect_with_notice('register_failed', 'settings');
    }

    public function handle_trigger_scan(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to do that.', 'icap-seo'));
        }
        check_admin_referer('icap_seo_trigger_scan');

        $result = $this->service_client->trigger_scan('full_site');
        if ($result['success']) {
            $this->redirect_with_notice('scan_queued', 'setup-wizard');
            return;
        }

        $this->redirect_with_notice('scan_failed', 'setup-wizard');
    }

    private function redirect_with_notice(string $notice_code, string $tab): void
    {
        $url = add_query_arg(
            [
                'page' => 'icap-seo',
                'tab' => $tab,
                self::NOTICE_QUERY_KEY => $notice_code,
            ],
            admin_url('admin.php')
        );

        wp_safe_redirect($url);
        exit;
    }
}
