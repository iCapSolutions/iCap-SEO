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
    private const SEO_CHANGE_COMMENT_START = '<!-- SEO by iCap - https://icapsolutions.com -->';
    private const SEO_CHANGE_COMMENT_END = '<!-- /SEO by iCap - https://icapsolutions.com -->';
    private const SEO_TITLE_META_KEYS = [
        'rank_math_title',
        '_yoast_wpseo_title',
        '_aioseo_title',
    ];
    private const SEO_DESCRIPTION_META_KEYS = [
        'rank_math_description',
        '_yoast_wpseo_metadesc',
        '_aioseo_description',
    ];

    public function __construct(ICap_SEO_Service_Client $service_client)
    {
        $this->service_client = $service_client;
    }

    private function resolve_seo_title_meta_update(int $post_id, string $candidate_title): array
    {
        $meta_key = '';
        $meta_before = '';

        foreach (self::SEO_TITLE_META_KEYS as $candidate_key) {
            if (metadata_exists('post', $post_id, $candidate_key)) {
                $meta_key = $candidate_key;
                $meta_before = sanitize_text_field((string) get_post_meta($post_id, $candidate_key, true));
                break;
            }
        }

        if ($meta_key === '') {
            $meta_key = $this->detect_preferred_seo_title_meta_key();
            if ($meta_key !== '') {
                $meta_before = sanitize_text_field((string) get_post_meta($post_id, $meta_key, true));
            }
        }

        if ($meta_key === '') {
            return [
                'updated' => false,
                'meta_key' => '',
                'before' => '',
            ];
        }

        return [
            'updated' => $candidate_title !== '' && $candidate_title !== $meta_before,
            'meta_key' => $meta_key,
            'before' => $meta_before,
        ];
    }

    private function detect_preferred_seo_title_meta_key(): string
    {
        if (defined('RANK_MATH_VERSION') || class_exists('RankMath')) {
            return 'rank_math_title';
        }
        if (defined('WPSEO_VERSION') || class_exists('WPSEO_Options')) {
            return '_yoast_wpseo_title';
        }
        if (defined('AIOSEO_VERSION') || class_exists('AIOSEO\\Plugin\\Common\\Main')) {
            return '_aioseo_title';
        }

        return '';
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
        add_action('admin_post_icap_seo_test_connection', [$this, 'handle_test_connection']);
        add_action('admin_post_icap_seo_trigger_scan', [$this, 'handle_trigger_scan']);
        add_action('admin_post_icap_seo_check_billing_status', [$this, 'handle_check_billing_status']);
        add_action('admin_post_icap_seo_start_billing_checkout', [$this, 'handle_start_billing_checkout']);
        add_action('admin_post_icap_seo_open_billing_portal', [$this, 'handle_open_billing_portal']);
        add_action('admin_post_icap_seo_preview_remediation', [$this, 'handle_preview_remediation']);
        add_action('admin_post_icap_seo_apply_remediation', [$this, 'handle_apply_remediation']);
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
        $billing_state = isset($_GET['billing']) ? sanitize_key(wp_unslash($_GET['billing'])) : '';
        if ($notice_code === '') {
            if ($billing_state === 'success') {
                $notice_code = 'billing_checkout_returned';
            } elseif ($billing_state === 'cancel') {
                $notice_code = 'billing_checkout_cancelled';
            } elseif ($billing_state === 'portal') {
                $notice_code = 'billing_portal_returned';
            }
        }
        $connection_settings = $this->service_client->get_connection_settings();
        $score_snapshot = [
            'score' => null,
            'last_scan' => $connection_settings['last_sync_at'] ?: null,
            'status' => $this->service_client->is_api_connection_configured_public() ? 'Connected (awaiting scan data)' : 'Not connected',
        ];
        $recommendation_preview = [
            'items' => [],
            'source' => 'placeholder',
        ];
        $content_scores = [];
        $scan_status_data = [];
        $latest_content_scores_meta = [];
        $selected_content_key = '';
        $content_score_detail = [];
        $content_score_detail_error = '';
        $remediation_preview = [];
        $remediation_preview_error = '';
        $allow_live_fetch = $this->service_client->is_api_connection_configured_public();

        try {
            if ($active_tab === 'site-health' || $active_tab === 'home') {
                $score_snapshot = $this->service_client->get_site_score_snapshot($allow_live_fetch);
            }

            if ($active_tab === 'content-scores' || $active_tab === 'site-health' || $active_tab === 'home') {
                $content_scores = $this->service_client->get_content_scores_overview($allow_live_fetch);
                $latest_content_scores_meta = $this->service_client->get_latest_content_scores_meta();

                if ($active_tab === 'content-scores') {
                    $selected_content_key = isset($_GET['content_key'])
                        ? sanitize_text_field((string) wp_unslash($_GET['content_key']))
                        : '';
                    if ($selected_content_key !== '') {
                        $detail_result = $this->service_client->get_content_score_detail($selected_content_key, $allow_live_fetch);
                        if (!empty($detail_result['success'])) {
                            $content_score_detail = isset($detail_result['data']) && is_array($detail_result['data']) ? $detail_result['data'] : [];

                            $preview_result = $this->service_client->get_content_remediation_preview($selected_content_key, [], $allow_live_fetch);
                            if (!empty($preview_result['success'])) {
                                $remediation_preview = isset($preview_result['data']) && is_array($preview_result['data']) ? $preview_result['data'] : [];
                            } else {
                                $remediation_preview_error = isset($preview_result['error']['message']) && is_string($preview_result['error']['message'])
                                    ? sanitize_text_field($preview_result['error']['message'])
                                    : __('Unable to load remediation preview right now.', 'icap-seo');
                            }
                        } else {
                            $content_score_detail_error = isset($detail_result['error']['message']) && is_string($detail_result['error']['message'])
                                ? sanitize_text_field($detail_result['error']['message'])
                                : __('Unable to load content score details right now.', 'icap-seo');
                        }
                    }
                }
            }

            if ($active_tab === 'setup-wizard') {
                $scan_status_result = $this->service_client->get_scan_status(null, $allow_live_fetch);
                $scan_status_data = $scan_status_result['success'] ? $scan_status_result['data'] : [];
                if (empty($latest_content_scores_meta)) {
                    $this->service_client->get_content_scores_overview($allow_live_fetch);
                    $latest_content_scores_meta = $this->service_client->get_latest_content_scores_meta();
                }
                if (empty($scan_status_data) && !empty($latest_content_scores_meta['scan_id'])) {
                    $scan_status_data = [
                        'scan_id' => $latest_content_scores_meta['scan_id'],
                        'scan_tier' => $latest_content_scores_meta['scan_tier'] ?? '',
                        'scan_layers' => $latest_content_scores_meta['scan_layers'] ?? [],
                        'status' => 'completed',
                    ];
                }
            }
        } catch (Throwable $e) {
            $notice_code = 'render_fallback';
            $score_snapshot = [
                'score' => null,
                'last_scan' => null,
                'status' => 'Degraded mode',
            ];
            $recommendation_preview = [
                'items' => [],
                'source' => 'fallback',
            ];
            $content_scores = [];
            $scan_status_data = [];
            $latest_content_scores_meta = [];
            $remediation_preview = [];
            $remediation_preview_error = '';
            error_log('ICap SEO dashboard fallback: ' . $e->getMessage());
        }

        include ICAP_SEO_PLUGIN_DIR . 'admin/views/dashboard.php';
    }

    public function handle_save_settings(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to do that.', 'icap-seo'));
        }
        check_admin_referer('icap_seo_save_settings');

        $api_base_url = isset($_POST['api_base_url']) ? esc_url_raw((string) wp_unslash($_POST['api_base_url'])) : '';
        $registration_token = isset($_POST['registration_token']) ? sanitize_text_field((string) wp_unslash($_POST['registration_token'])) : '';
        $site_id = isset($_POST['site_id']) ? sanitize_text_field((string) wp_unslash($_POST['site_id'])) : '';
        $site_token = isset($_POST['site_token']) ? sanitize_text_field((string) wp_unslash($_POST['site_token'])) : '';

        $this->service_client->update_connection_settings([
            'api_base_url' => $api_base_url,
            'registration_token' => $registration_token,
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
            'admin_email' => get_bloginfo('admin_email'),
            'timezone' => wp_timezone_string(),
        ]);

        if ($result['success']) {
            $this->redirect_with_notice('register_success', 'settings');
            return;
        }
        $error_code = $this->extract_error_code($result);

        if ($error_code === 'registration_token_missing') {
            $this->redirect_with_notice('registration_token_missing', 'settings');
            return;
        }
        if ($error_code === 'api_base_url_missing') {
            $this->redirect_with_notice('api_base_url_missing', 'settings');
            return;
        }

        $this->redirect_with_notice('register_failed', 'settings');
    }
    public function handle_test_connection(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to do that.', 'icap-seo'));
        }
        check_admin_referer('icap_seo_test_connection');

        $result = $this->service_client->test_connection();
        if ($result['success']) {
            $mode = isset($result['mode']) && is_string($result['mode']) ? sanitize_key($result['mode']) : '';
            if ($mode === 'authenticated') {
                $this->redirect_with_notice('connection_ok_authenticated', 'setup-wizard');
                return;
            }

            $this->redirect_with_notice('connection_ok_reachable', 'setup-wizard');
            return;
        }

        $error_code = $this->extract_error_code($result);
        if ($error_code === 'api_base_url_missing') {
            $this->redirect_with_notice('connection_api_base_url_missing', 'settings');
            return;
        }
        if ($error_code === 'invalid_token' || $error_code === 'forbidden') {
            $this->redirect_with_notice('connection_invalid_token', 'setup-wizard');
            return;
        }
        if ($error_code === 'endpoint_not_found') {
            $this->redirect_with_notice('connection_endpoint_not_found', 'setup-wizard');
            return;
        }
        if ($error_code === 'network_error' || $error_code === 'upstream_unavailable') {
            $this->redirect_with_notice('connection_unreachable', 'setup-wizard');
            return;
        }

        $this->redirect_with_notice('connection_failed', 'setup-wizard');
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

        $error_code = $this->extract_error_code($result);
        if ($error_code === 'payment_required') {
            $this->redirect_with_notice('payment_required', 'setup-wizard');
            return;
        }
        if ($error_code === 'subscription_required') {
            $this->redirect_with_notice('subscription_required', 'setup-wizard');
            return;
        }
        if ($error_code === 'account_suspended') {
            $this->redirect_with_notice('account_suspended', 'setup-wizard');
            return;
        }
        if ($error_code === 'invalid_token') {
            $this->redirect_with_notice('invalid_token', 'setup-wizard');
            return;
        }
        if ($error_code === 'rate_limited') {
            $this->redirect_with_notice('rate_limited', 'setup-wizard');
            return;
        }

        $this->redirect_with_notice('scan_failed', 'setup-wizard');
    }

    public function handle_check_billing_status(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to do that.', 'icap-seo'));
        }
        check_admin_referer('icap_seo_check_billing_status');

        $result = $this->service_client->get_subscription_status(true);
        if (!$result['success']) {
            $error_code = $this->extract_error_code($result);
            if ($error_code === 'api_base_url_missing') {
                $this->redirect_with_notice('api_base_url_missing', 'settings');
                return;
            }
            if ($error_code === 'site_not_configured' || $error_code === 'not_configured') {
                $this->redirect_with_notice('billing_status_not_configured', 'settings');
                return;
            }
            $this->redirect_with_notice('billing_status_unavailable', 'settings');
            return;
        }

        $state = 'unknown';
        if (isset($result['data']['entitlement_state']) && is_string($result['data']['entitlement_state'])) {
            $normalized_state = sanitize_key($result['data']['entitlement_state']);
            if ($normalized_state !== '') {
                $state = $normalized_state;
            }
        }

        if ($state === 'active' || $state === 'trialing') {
            $this->redirect_with_notice('billing_status_active', 'settings');
            return;
        }
        if ($state === 'past_due' || $state === 'grace_period') {
            $this->redirect_with_notice('billing_status_attention', 'settings');
            return;
        }
        if ($state === 'canceled' || $state === 'suspended') {
            $this->redirect_with_notice('billing_status_blocked', 'settings');
            return;
        }

        $this->redirect_with_notice('billing_status_unknown', 'settings');
    }

    public function handle_start_billing_checkout(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to do that.', 'icap-seo'));
        }
        check_admin_referer('icap_seo_start_billing_checkout');
        $result = $this->service_client->create_billing_checkout_session([
            'success_url' => $this->build_billing_settings_return_url('success'),
            'cancel_url' => $this->build_billing_settings_return_url('cancel'),
        ]);
        if (!$result['success']) {
            $error_code = $this->extract_error_code($result);
            if ($error_code === 'api_base_url_missing') {
                $this->redirect_with_notice('api_base_url_missing', 'settings');
                return;
            }
            if ($error_code === 'site_not_configured' || $error_code === 'not_configured') {
                $this->redirect_with_notice('billing_checkout_not_configured', 'settings');
                return;
            }
            if ($error_code === 'validation_error') {
                $this->redirect_with_notice('billing_checkout_misconfigured', 'settings');
                return;
            }
            if ($error_code === 'upstream_unavailable' || $error_code === 'network_error') {
                $this->redirect_with_notice('billing_checkout_unavailable', 'settings');
                return;
            }
            $this->redirect_with_notice('billing_checkout_failed', 'settings');
            return;
        }

        $checkout_url = '';
        if (isset($result['data']['checkout_url']) && is_string($result['data']['checkout_url'])) {
            $checkout_url = esc_url_raw($result['data']['checkout_url']);
        }
        if ($checkout_url === '' || !wp_http_validate_url($checkout_url)) {
            $this->redirect_with_notice('billing_checkout_failed', 'settings');
            return;
        }

        wp_redirect($checkout_url);
        exit;
    }

    public function handle_open_billing_portal(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to do that.', 'icap-seo'));
        }
        check_admin_referer('icap_seo_open_billing_portal');
        $result = $this->service_client->create_billing_portal_session([
            'return_url' => $this->build_billing_settings_return_url('portal'),
        ]);
        if (!$result['success']) {
            $error_code = $this->extract_error_code($result);
            if ($error_code === 'api_base_url_missing') {
                $this->redirect_with_notice('api_base_url_missing', 'settings');
                return;
            }
            if ($error_code === 'site_not_configured' || $error_code === 'not_configured') {
                $this->redirect_with_notice('billing_portal_not_configured', 'settings');
                return;
            }
            if ($error_code === 'subscription_required') {
                $this->redirect_with_notice('billing_portal_subscription_required', 'settings');
                return;
            }
            if ($error_code === 'validation_error') {
                $this->redirect_with_notice('billing_portal_misconfigured', 'settings');
                return;
            }
            if ($error_code === 'upstream_unavailable' || $error_code === 'network_error') {
                $this->redirect_with_notice('billing_portal_unavailable', 'settings');
                return;
            }
            $this->redirect_with_notice('billing_portal_failed', 'settings');
            return;
        }

        $portal_url = '';
        if (isset($result['data']['portal_url']) && is_string($result['data']['portal_url'])) {
            $portal_url = esc_url_raw($result['data']['portal_url']);
        }
        if ($portal_url === '' || !wp_http_validate_url($portal_url)) {
            $this->redirect_with_notice('billing_portal_failed', 'settings');
            return;
        }

        wp_redirect($portal_url);
        exit;
    }

    public function handle_preview_remediation(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to do that.', 'icap-seo'));
        }
        check_admin_referer('icap_seo_preview_remediation');

        $content_key = isset($_POST['content_key']) ? sanitize_text_field((string) wp_unslash($_POST['content_key'])) : '';
        if ($content_key === '') {
            $this->redirect_with_notice('remediation_content_key_missing', 'content-scores');
            return;
        }

        $approved_issue_codes_raw = isset($_POST['approved_issue_codes']) ? wp_unslash($_POST['approved_issue_codes']) : [];
        if (!is_array($approved_issue_codes_raw)) {
            $approved_issue_codes_raw = [];
        }
        $approved_issue_codes = array_map(
            static fn($value): string => sanitize_key((string) $value),
            $approved_issue_codes_raw
        );

        $result = $this->service_client->get_content_remediation_preview($content_key, $approved_issue_codes, true);
        if ($result['success']) {
            $this->redirect_with_notice('remediation_preview_ready', 'content-scores', ['content_key' => $content_key]);
            return;
        }

        $error_code = $this->extract_error_code($result);
        if ($error_code === 'validation_error') {
            $this->redirect_with_notice('remediation_validation_error', 'content-scores', ['content_key' => $content_key]);
            return;
        }
        if ($error_code === 'invalid_token' || $error_code === 'forbidden') {
            $this->redirect_with_notice('remediation_auth_error', 'content-scores', ['content_key' => $content_key]);
            return;
        }
        if ($error_code === 'upstream_unavailable' || $error_code === 'network_error') {
            $this->redirect_with_notice('remediation_preview_unavailable', 'content-scores', ['content_key' => $content_key]);
            return;
        }

        $this->redirect_with_notice('remediation_preview_failed', 'content-scores', ['content_key' => $content_key]);
    }

    public function handle_apply_remediation(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to do that.', 'icap-seo'));
        }
        check_admin_referer('icap_seo_apply_remediation');

        $content_key = isset($_POST['content_key']) ? sanitize_text_field((string) wp_unslash($_POST['content_key'])) : '';
        if ($content_key === '') {
            $this->redirect_with_notice('remediation_content_key_missing', 'content-scores');
            return;
        }

        $approved_issue_codes_raw = isset($_POST['approved_issue_codes']) ? wp_unslash($_POST['approved_issue_codes']) : [];
        if (!is_array($approved_issue_codes_raw)) {
            $approved_issue_codes_raw = [];
        }
        $approved_issue_codes = array_map(
            static fn($value): string => sanitize_key((string) $value),
            $approved_issue_codes_raw
        );

        $result = $this->service_client->apply_content_remediation($content_key, $approved_issue_codes, true);
        if ($result['success']) {
            $local_apply_result = $this->apply_supported_local_remediation($content_key, $approved_issue_codes);
            $status = isset($local_apply_result['status']) ? sanitize_key((string) $local_apply_result['status']) : '';
            if ($status === 'applied') {
                $this->redirect_with_notice(
                    'remediation_apply_title_updated',
                    'content-scores',
                    [
                        'content_key' => $content_key,
                        'title_before' => isset($local_apply_result['title_before']) ? (string) $local_apply_result['title_before'] : '',
                        'title_after' => isset($local_apply_result['title_after']) ? (string) $local_apply_result['title_after'] : '',
                        'seo_title_meta_key' => isset($local_apply_result['seo_title_meta_key']) ? (string) $local_apply_result['seo_title_meta_key'] : '',
                        'seo_title_before' => isset($local_apply_result['seo_title_before']) ? (string) $local_apply_result['seo_title_before'] : '',
                        'seo_title_after' => isset($local_apply_result['seo_title_after']) ? (string) $local_apply_result['seo_title_after'] : '',
                        'seo_description_meta_key' => isset($local_apply_result['seo_description_meta_key']) ? (string) $local_apply_result['seo_description_meta_key'] : '',
                        'seo_description_before' => isset($local_apply_result['seo_description_before']) ? (string) $local_apply_result['seo_description_before'] : '',
                        'seo_description_after' => isset($local_apply_result['seo_description_after']) ? (string) $local_apply_result['seo_description_after'] : '',
                        'excerpt_before' => isset($local_apply_result['excerpt_before']) ? (string) $local_apply_result['excerpt_before'] : '',
                        'excerpt_after' => isset($local_apply_result['excerpt_after']) ? (string) $local_apply_result['excerpt_after'] : '',
                    ]
                );
                return;
            }
            if ($status === 'failed') {
                $this->redirect_with_notice('remediation_apply_title_update_failed', 'content-scores', ['content_key' => $content_key]);
                return;
            }
            if ($status === 'no_op') {
                $this->redirect_with_notice(
                    'remediation_apply_noop',
                    'content-scores',
                    [
                        'content_key' => $content_key,
                        'noop_reason' => isset($local_apply_result['reason']) ? (string) $local_apply_result['reason'] : '',
                    ]
                );
                return;
            }
            $this->redirect_with_notice('remediation_apply_queued', 'content-scores', ['content_key' => $content_key]);
            return;
        }

        $error_code = $this->extract_error_code($result);
        if ($error_code === 'validation_error') {
            $this->redirect_with_notice('remediation_validation_error', 'content-scores', ['content_key' => $content_key]);
            return;
        }
        if ($error_code === 'invalid_token' || $error_code === 'forbidden') {
            $this->redirect_with_notice('remediation_auth_error', 'content-scores', ['content_key' => $content_key]);
            return;
        }
        if ($error_code === 'upstream_unavailable' || $error_code === 'network_error') {
            $this->redirect_with_notice('remediation_apply_unavailable', 'content-scores', ['content_key' => $content_key]);
            return;
        }

        $this->redirect_with_notice('remediation_apply_failed', 'content-scores', ['content_key' => $content_key]);
    }

    private function apply_supported_local_remediation(string $content_key, array $approved_issue_codes): array
    {
        $normalized_codes = array_map(
            static fn($value): string => sanitize_key((string) $value),
            $approved_issue_codes
        );
        $apply_title_recommendation = in_array('title_length_out_of_range', $normalized_codes, true);
        $apply_meta_description_recommendation =
            in_array('missing_meta_description', $normalized_codes, true)
            || in_array('meta_description_length_out_of_range', $normalized_codes, true);

        if (!$apply_title_recommendation && !$apply_meta_description_recommendation) {
            return ['status' => 'no_op', 'reason' => 'issue_not_supported_for_local_apply'];
        }

        $post_id = $this->extract_post_id_from_content_key($content_key);
        if ($post_id <= 0) {
            return ['status' => 'failed', 'reason' => 'invalid_post_id'];
        }
        if (!current_user_can('edit_post', $post_id)) {
            return ['status' => 'failed', 'reason' => 'missing_edit_capability'];
        }

        $post = get_post($post_id);
        if (!$post instanceof WP_Post) {
            return ['status' => 'failed', 'reason' => 'post_not_found'];
        }

        $current_title = sanitize_text_field((string) $post->post_title);
        $updated_title = $current_title;
        $title_was_updated = false;
        $seo_title_meta_update = [
            'updated' => false,
            'meta_key' => '',
            'before' => '',
        ];
        $seo_title_meta_was_updated = false;
        if ($apply_title_recommendation) {
            $updated_title = $this->build_seo_title_with_target_length($current_title);
            $title_was_updated = $updated_title !== '' && $updated_title !== $current_title;
            $seo_title_meta_update = $this->resolve_seo_title_meta_update($post_id, $updated_title);
            $seo_title_meta_was_updated = !empty($seo_title_meta_update['updated']);
        }
        $current_excerpt = trim((string) preg_replace('/\s+/', ' ', sanitize_text_field((string) $post->post_excerpt)));
        $updated_excerpt = $current_excerpt;
        $excerpt_was_updated = false;

        $updated_meta_description = '';
        $seo_description_meta_update = [
            'updated' => false,
            'meta_key' => '',
            'before' => '',
        ];
        $seo_description_meta_was_updated = false;
        if ($apply_meta_description_recommendation) {
            $updated_meta_description = $this->build_seo_meta_description($post);
            $updated_excerpt = trim((string) preg_replace('/\s+/', ' ', $updated_meta_description));
            $excerpt_was_updated = $updated_excerpt !== '' && $updated_excerpt !== $current_excerpt;
            $seo_description_meta_update = $this->resolve_seo_description_meta_update($post_id, $updated_meta_description);
            $seo_description_meta_was_updated = !empty($seo_description_meta_update['updated']);
        }
        if (
            !$title_was_updated
            && !$seo_title_meta_was_updated
            && !$excerpt_was_updated
            && !$seo_description_meta_was_updated
        ) {
            $reason = 'no_effective_change_computed';
            if ($apply_meta_description_recommendation) {
                $meta_before = isset($seo_description_meta_update['before']) ? (string) $seo_description_meta_update['before'] : '';
                $meta_length = $this->string_length($meta_before);
                if ($updated_meta_description === '') {
                    $reason = 'meta_description_generation_failed';
                } elseif ($current_excerpt !== '' && $this->string_length($current_excerpt) >= 120 && $this->string_length($current_excerpt) <= 170) {
                    $reason = 'meta_description_already_within_range';
                } elseif ((string) ($seo_description_meta_update['meta_key'] ?? '') === '' && $current_excerpt === '') {
                    $reason = 'meta_description_storage_unavailable';
                } elseif ($meta_before !== '' && $meta_length >= 120 && $meta_length <= 170) {
                    $reason = 'meta_description_already_within_range';
                }
            } elseif ($apply_title_recommendation) {
                $title_length = $this->string_length($current_title);
                if ($title_length >= 20 && $title_length <= 65) {
                    $reason = 'title_already_within_range';
                }
            }

            return [
                'status' => 'no_op',
                'reason' => $reason,
                'title_before' => $current_title,
                'title_after' => $current_title,
                'seo_title_meta_key' => isset($seo_title_meta_update['meta_key']) ? (string) $seo_title_meta_update['meta_key'] : '',
                'seo_title_before' => isset($seo_title_meta_update['before']) ? (string) $seo_title_meta_update['before'] : '',
                'seo_title_after' => isset($seo_title_meta_update['before']) ? (string) $seo_title_meta_update['before'] : '',
                'seo_description_meta_key' => isset($seo_description_meta_update['meta_key']) ? (string) $seo_description_meta_update['meta_key'] : '',
                'seo_description_before' => isset($seo_description_meta_update['before']) ? (string) $seo_description_meta_update['before'] : '',
                'seo_description_after' => isset($seo_description_meta_update['before']) ? (string) $seo_description_meta_update['before'] : '',
                'excerpt_before' => $current_excerpt,
                'excerpt_after' => $current_excerpt,
            ];
        }

        $current_content = (string) $post->post_content;
        $updated_content = $this->wrap_content_with_seo_change_comments($current_content);
        $content_was_updated = $updated_content !== $current_content;
        $update_payload = [
            'ID' => $post_id,
        ];
        if ($title_was_updated) {
            $update_payload['post_title'] = $updated_title;
        }
        if ($excerpt_was_updated) {
            $update_payload['post_excerpt'] = $updated_excerpt;
        }
        if ($content_was_updated) {
            $update_payload['post_content'] = $updated_content;
        }

        $update_result = wp_update_post(
            $update_payload,
            true
        );

        if (is_wp_error($update_result)) {
            return ['status' => 'failed', 'reason' => 'wp_update_post_failed'];
        }

        if ($seo_title_meta_was_updated && !empty($seo_title_meta_update['meta_key'])) {
            update_post_meta($post_id, (string) $seo_title_meta_update['meta_key'], $updated_title);
        }
        if ($seo_description_meta_was_updated && !empty($seo_description_meta_update['meta_key'])) {
            update_post_meta($post_id, (string) $seo_description_meta_update['meta_key'], $updated_meta_description);
        }

        return [
            'status' => 'applied',
            'reason' => 'changes_applied',
            'title_before' => $current_title,
            'title_after' => $title_was_updated ? $updated_title : $current_title,
            'seo_title_meta_key' => isset($seo_title_meta_update['meta_key']) ? (string) $seo_title_meta_update['meta_key'] : '',
            'seo_title_before' => isset($seo_title_meta_update['before']) ? (string) $seo_title_meta_update['before'] : '',
            'seo_title_after' => $seo_title_meta_was_updated ? $updated_title : (isset($seo_title_meta_update['before']) ? (string) $seo_title_meta_update['before'] : ''),
            'seo_description_meta_key' => isset($seo_description_meta_update['meta_key']) ? (string) $seo_description_meta_update['meta_key'] : '',
            'seo_description_before' => isset($seo_description_meta_update['before']) ? (string) $seo_description_meta_update['before'] : '',
            'seo_description_after' => $seo_description_meta_was_updated ? $updated_meta_description : (isset($seo_description_meta_update['before']) ? (string) $seo_description_meta_update['before'] : ''),
            'excerpt_before' => $current_excerpt,
            'excerpt_after' => $excerpt_was_updated ? $updated_excerpt : $current_excerpt,
        ];
    }

    private function wrap_content_with_seo_change_comments(string $content): string
    {
        if (strpos($content, self::SEO_CHANGE_COMMENT_START) !== false) {
            return $content;
        }

        $normalized_content = trim($content);
        if ($normalized_content === '') {
            return self::SEO_CHANGE_COMMENT_START . "\n" . self::SEO_CHANGE_COMMENT_END;
        }

        return self::SEO_CHANGE_COMMENT_START . "\n" . $content . "\n" . self::SEO_CHANGE_COMMENT_END;
    }

    private function extract_post_id_from_content_key(string $content_key): int
    {
        $normalized = sanitize_text_field((string) $content_key);
        if (preg_match('/:(\d+)$/', $normalized, $matches) === 1) {
            return absint($matches[1]);
        }
        if (preg_match('/^post_(\d+)$/', $normalized, $matches) === 1) {
            return absint($matches[1]);
        }

        return 0;
    }

    private function build_seo_title_with_target_length(string $title): string
    {
        $normalized_title = preg_replace('/\s+/', ' ', trim($title));
        if (!is_string($normalized_title)) {
            $normalized_title = '';
        }
        if ($normalized_title === '') {
            return '';
        }

        $candidate = $normalized_title;
        $length = $this->string_length($candidate);
        if ($length > 65) {
            $candidate = $this->trim_title_to_max_length($candidate, 65);
            $length = $this->string_length($candidate);
        }

        if ($length < 20) {
            $site_title_phrase = $this->build_site_title_phrase();
            if ($site_title_phrase !== '') {
                $candidate = trim($candidate . ' ' . $site_title_phrase);
            }
            if ($this->string_length($candidate) < 20) {
                $candidate = trim($candidate . ' About Page');
            }
            if ($this->string_length($candidate) < 20) {
                $candidate = trim($candidate . ' Details');
            }
            if ($this->string_length($candidate) > 65) {
                $candidate = $this->trim_title_to_max_length($candidate, 65);
            }
        }

        return trim((string) $candidate);
    }

    private function build_site_title_phrase(): string
    {
        $site_name = sanitize_text_field((string) get_bloginfo('name'));
        $site_name = (string) preg_replace('/\.[a-z0-9]{2,}$/i', '', $site_name);
        $site_name = str_replace(['|', '-', '_'], ' ', $site_name);
        $site_name = trim((string) preg_replace('/\s+/', ' ', $site_name));
        return $site_name;
    }


    private function resolve_seo_description_meta_update(int $post_id, string $candidate_description): array
    {
        $meta_key = '';
        $meta_before = '';

        foreach (self::SEO_DESCRIPTION_META_KEYS as $candidate_key) {
            if (metadata_exists('post', $post_id, $candidate_key)) {
                $meta_key = $candidate_key;
                $meta_before = sanitize_text_field((string) get_post_meta($post_id, $candidate_key, true));
                break;
            }
        }

        if ($meta_key === '') {
            $meta_key = $this->detect_preferred_seo_description_meta_key();
            if ($meta_key !== '') {
                $meta_before = sanitize_text_field((string) get_post_meta($post_id, $meta_key, true));
            }
        }

        if ($meta_key === '') {
            return [
                'updated' => false,
                'meta_key' => '',
                'before' => '',
            ];
        }

        return [
            'updated' => $candidate_description !== '' && $candidate_description !== $meta_before,
            'meta_key' => $meta_key,
            'before' => $meta_before,
        ];
    }

    private function detect_preferred_seo_description_meta_key(): string
    {
        if (defined('RANK_MATH_VERSION') || class_exists('RankMath')) {
            return 'rank_math_description';
        }
        if (defined('WPSEO_VERSION') || class_exists('WPSEO_Options')) {
            return '_yoast_wpseo_metadesc';
        }
        if (defined('AIOSEO_VERSION') || class_exists('AIOSEO\\Plugin\\Common\\Main')) {
            return '_aioseo_description';
        }

        return '';
    }

    private function build_seo_meta_description(WP_Post $post): string
    {
        $base = '';
        if (has_excerpt($post)) {
            $base = (string) $post->post_excerpt;
        }
        if ($base === '') {
            $base = wp_strip_all_tags((string) $post->post_content, true);
        }
        if ($base === '') {
            $site_name = sanitize_text_field((string) get_bloginfo('name'));
            $base = trim(sanitize_text_field((string) $post->post_title) . ' | ' . $site_name);
        }

        $candidate = preg_replace('/\s+/', ' ', trim((string) $base));
        if (!is_string($candidate)) {
            $candidate = '';
        }

        if ($this->string_length($candidate) > 170) {
            $candidate = $this->trim_text_to_max_length($candidate, 170);
        }

        if ($this->string_length($candidate) < 120) {
            $site_name = sanitize_text_field((string) get_bloginfo('name'));
            if ($site_name !== '') {
                $candidate = trim($candidate . ' Learn more at ' . $site_name . '.');
            }
        }
        if ($this->string_length($candidate) < 120) {
            $candidate = trim($candidate . ' Read more.');
        }
        if ($this->string_length($candidate) > 170) {
            $candidate = $this->trim_text_to_max_length($candidate, 170);
        }

        return trim($candidate);
    }
    private function trim_title_to_max_length(string $title, int $max_length): string
    {
        $normalized = preg_replace('/\s+/', ' ', trim($title));
        if (!is_string($normalized)) {
            $normalized = '';
        }
        if ($normalized === '') {
            return '';
        }
        if ($this->string_length($normalized) <= $max_length) {
            return $normalized;
        }

        $words = preg_split('/\s+/', $normalized);
        if (!is_array($words)) {
            return $this->string_substr($normalized, 0, $max_length);
        }

        $assembled = '';
        foreach ($words as $word) {
            if (!is_string($word) || $word === '') {
                continue;
            }
            $next = $assembled === '' ? $word : ($assembled . ' ' . $word);
            if ($this->string_length($next) > $max_length) {
                break;
            }
            $assembled = $next;
        }

        if ($assembled === '') {
            return $this->string_substr($normalized, 0, $max_length);
        }

        return $assembled;
    }

    private function trim_text_to_max_length(string $text, int $max_length): string
    {
        $normalized = preg_replace('/\s+/', ' ', trim($text));
        if (!is_string($normalized)) {
            $normalized = '';
        }
        if ($normalized === '') {
            return '';
        }
        if ($this->string_length($normalized) <= $max_length) {
            return $normalized;
        }

        $trimmed = trim((string) $this->string_substr($normalized, 0, $max_length));
        $trimmed = preg_replace('/\s+\S*$/', '', $trimmed);
        if (!is_string($trimmed) || trim($trimmed) === '') {
            return trim((string) $this->string_substr($normalized, 0, $max_length));
        }

        return trim($trimmed);
    }

    private function string_length(string $value): int
    {
        if (function_exists('mb_strlen')) {
            return (int) mb_strlen($value);
        }

        return (int) strlen($value);
    }

    private function string_substr(string $value, int $start, int $length): string
    {
        if (function_exists('mb_substr')) {
            return (string) mb_substr($value, $start, $length);
        }

        return (string) substr($value, $start, $length);
    }
    private function build_billing_settings_return_url(string $billing_state): string
    {
        $normalized_state = sanitize_key($billing_state);
        if ($normalized_state === '') {
            $normalized_state = 'portal';
        }

        return add_query_arg(
            [
                'page' => 'icap-seo',
                'tab' => 'settings',
                'billing' => $normalized_state,
            ],
            admin_url('admin.php')
        );
    }

    private function extract_error_code(array $result): string
    {
        if (isset($result['error']['code']) && is_string($result['error']['code'])) {
            return $result['error']['code'];
        }

        return '';
    }
    private function redirect_with_notice(string $notice_code, string $tab, array $extra_query_args = []): void
    {
        $extra_query_args = array_filter(
            $extra_query_args,
            static fn($value): bool => is_string($value) && $value !== ''
        );
        $url = add_query_arg(
            array_merge(
                [
                    'page' => 'icap-seo',
                    'tab' => $tab,
                    self::NOTICE_QUERY_KEY => $notice_code,
                ],
                $extra_query_args
            ),
            admin_url('admin.php')
        );

        wp_safe_redirect($url);
        exit;
    }
}
