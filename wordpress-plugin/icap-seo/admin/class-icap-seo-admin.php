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
    private const REMEDIATION_HISTORY_META_KEY = '_icap_seo_remediation_history';
    private const REMEDIATION_SUMMARY_META_KEY = 'icap_seo_last_remediation_summary';
    private const META_DESCRIPTION_META_KEY = '_icap_seo_meta_description';
    private const CANONICAL_URL_META_KEY = '_icap_seo_canonical_url';
    private const JSONLD_SCHEMA_TYPE_META_KEY = '_icap_seo_jsonld_schema_type';
    private const JSONLD_SCHEMA_JSON_META_KEY = '_icap_seo_jsonld_schema_json';
    private const APPLIED_ISSUE_CODES_META_KEY = '_icap_seo_applied_issue_codes';
    private const CONTENT_DEPTH_DRAFT_META_KEY = '_icap_seo_content_depth_draft';
    private const CONTENT_DEPTH_DRAFT_WORD_COUNT_META_KEY = '_icap_seo_content_depth_draft_word_count';
    private const CONTENT_DEPTH_TARGET_WORD_COUNT = 600;
    private const CONTENT_DEPTH_ISSUE_CODES = ['thin_content', 'no_visible_content', 'insufficient_content_depth', 'content_depth_improvement'];
    private const REMEDIATION_HISTORY_MAX_ENTRIES = 15;
    private const READABILITY_DRAFT_META_KEY = '_icap_seo_readability_draft';
    private const READABILITY_ISSUE_CODES = ['readability_score_low'];
    private const READABILITY_MAX_PARAGRAPHS = 6;

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
        add_action('admin_post_icap_seo_test_connection', [$this, 'handle_test_connection']);
        add_action('admin_post_icap_seo_trigger_scan', [$this, 'handle_trigger_scan']);
        add_action('admin_post_icap_seo_rescan_content', [$this, 'handle_rescan_content']);
        add_action('admin_post_icap_seo_check_billing_status', [$this, 'handle_check_billing_status']);
        add_action('admin_post_icap_seo_start_billing_checkout', [$this, 'handle_start_billing_checkout']);
        add_action('admin_post_icap_seo_open_billing_portal', [$this, 'handle_open_billing_portal']);
        add_action('admin_post_icap_seo_preview_remediation', [$this, 'handle_preview_remediation']);
        add_action('admin_post_icap_seo_apply_remediation', [$this, 'handle_apply_remediation']);
        add_action('admin_post_icap_seo_preview_content_depth', [$this, 'handle_preview_content_depth']);
        add_action('admin_post_icap_seo_publish_content_depth', [$this, 'handle_publish_content_depth']);
        add_action('admin_post_icap_seo_discard_content_depth', [$this, 'handle_discard_content_depth']);
        add_action('admin_post_icap_seo_preview_readability_rewrite', [$this, 'handle_preview_readability_rewrite']);
        add_action('admin_post_icap_seo_publish_readability_rewrite', [$this, 'handle_publish_readability_rewrite']);
        add_action('admin_post_icap_seo_discard_readability_rewrite', [$this, 'handle_discard_readability_rewrite']);
        add_action('add_meta_boxes', [$this, 'register_remediation_meta_boxes']);
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
        $remediation_audit_entries = [];
        $locally_applied_issue_codes = [];
        $current_meta_description_value = '';
        $content_depth_draft = ['html' => '', 'word_count' => 0];
        $readability_draft_paragraphs = [];
        $seo_recommendation_catalog = $this->get_seo_recommendation_catalog();
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
                            $selected_post_id = $this->extract_post_id_from_content_key($selected_content_key);
                            if ($selected_post_id > 0) {
                                $remediation_audit_entries = $this->get_remediation_history_for_post($selected_post_id);
                                $locally_applied_issue_codes = $this->get_applied_issue_codes_for_post($selected_post_id);
                                $selected_post = get_post($selected_post_id);
                                if ($selected_post instanceof WP_Post) {
                                    $current_meta_description_value = $this->get_current_meta_description_for_post($selected_post);
                                }
                                $content_depth_draft = $this->get_content_depth_draft_for_post($selected_post_id);
                                $readability_draft_paragraphs = $this->get_readability_draft_for_post($selected_post_id);
                            }

                            $open_issue_codes = $this->filter_open_issue_codes(
                                $this->extract_issue_codes_from_detail($content_score_detail),
                                $locally_applied_issue_codes
                            );
                            $preview_result = $this->service_client->get_content_remediation_preview(
                                $selected_content_key,
                                $open_issue_codes,
                                $allow_live_fetch
                            );
                            if (!empty($preview_result['success'])) {
                                $remediation_preview = isset($preview_result['data']) && is_array($preview_result['data']) ? $preview_result['data'] : [];
                                if (isset($remediation_preview['proposed_changes']) && is_array($remediation_preview['proposed_changes'])) {
                                    $remediation_preview['proposed_changes'] = array_values(array_filter(
                                        $remediation_preview['proposed_changes'],
                                        static function ($change_row) use ($locally_applied_issue_codes): bool {
                                            if (!is_array($change_row)) {
                                                return false;
                                            }
                                            $code = isset($change_row['issue_code']) ? sanitize_key((string) $change_row['issue_code']) : '';
                                            return $code === '' || !in_array($code, $locally_applied_issue_codes, true);
                                        }
                                    ));
                                    if (!isset($remediation_preview['summary']) || !is_array($remediation_preview['summary'])) {
                                        $remediation_preview['summary'] = [];
                                    }
                                    $remediation_preview['summary']['proposed_change_count'] = count($remediation_preview['proposed_changes']);
                                }
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
            $remediation_audit_entries = [];
            $locally_applied_issue_codes = [];
            $current_meta_description_value = '';
            $content_depth_draft = ['html' => '', 'word_count' => 0];
            $readability_draft_paragraphs = [];
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

    public function handle_rescan_content(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to do that.', 'icap-seo'));
        }
        check_admin_referer('icap_seo_rescan_content');

        $content_key = isset($_POST['content_key']) ? sanitize_text_field((string) wp_unslash($_POST['content_key'])) : '';
        if ($content_key === '') {
            $this->redirect_with_notice('content_rescan_key_missing', 'content-scores');
            return;
        }

        $post_id = $this->extract_post_id_from_content_key($content_key);
        $options = [
            'scan_mode' => 'content',
            'content_key' => $content_key,
            'requested_by' => 'content_detail',
        ];
        if ($post_id > 0) {
            $options['wp_post_id'] = $post_id;
        }

        $result = $this->service_client->trigger_scan('content', $options);
        if ($result['success']) {
            if ($post_id > 0) {
                // Local applied markers are scan-relative; clear so refreshed issues reappear when still open.
                $this->clear_applied_issue_codes_for_post($post_id);
            }
            $this->redirect_with_notice(
                'content_rescan_complete',
                'content-scores',
                [
                    'content_key' => $content_key,
                    'scan_id' => isset($result['data']['scan_id']) ? (string) $result['data']['scan_id'] : '',
                ]
            );
            return;
        }

        $error_code = $this->extract_error_code($result);
        $notice = 'content_rescan_failed';
        if ($error_code === 'payment_required') {
            $notice = 'payment_required';
        } elseif ($error_code === 'subscription_required') {
            $notice = 'subscription_required';
        } elseif ($error_code === 'account_suspended') {
            $notice = 'account_suspended';
        } elseif ($error_code === 'invalid_token') {
            $notice = 'invalid_token';
        } elseif ($error_code === 'rate_limited') {
            $notice = 'rate_limited';
        } elseif ($error_code === 'content_not_found') {
            $notice = 'content_rescan_not_found';
        } elseif ($error_code === 'validation_error') {
            $notice = 'content_rescan_validation_error';
        }

        $this->redirect_with_notice($notice, 'content-scores', ['content_key' => $content_key]);
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
        $force_regenerate = isset($_POST['force_regenerate'])
            && sanitize_key((string) wp_unslash($_POST['force_regenerate'])) === '1';

        $result = $this->service_client->apply_content_remediation($content_key, $approved_issue_codes, true);
        if ($result['success']) {
            $local_apply_result = $this->apply_supported_local_remediation(
                $content_key,
                $approved_issue_codes,
                $force_regenerate
            );
            $status = isset($local_apply_result['status']) ? sanitize_key((string) $local_apply_result['status']) : '';
            if ($status === 'applied') {
                $this->redirect_with_notice(
                    'remediation_apply_title_updated',
                    'content-scores',
                    [
                        'content_key' => $content_key,
                        'title_before' => isset($local_apply_result['title_before']) ? (string) $local_apply_result['title_before'] : '',
                        'title_after' => isset($local_apply_result['title_after']) ? (string) $local_apply_result['title_after'] : '',
                        'title_changed' => !empty($local_apply_result['title_changed']) ? '1' : '0',
                        'excerpt_before' => isset($local_apply_result['excerpt_before']) ? (string) $local_apply_result['excerpt_before'] : '',
                        'excerpt_after' => isset($local_apply_result['excerpt_after']) ? (string) $local_apply_result['excerpt_after'] : '',
                        'excerpt_changed' => !empty($local_apply_result['excerpt_changed']) ? '1' : '0',
                        'h1_before' => isset($local_apply_result['h1_before']) ? (string) $local_apply_result['h1_before'] : '',
                        'h1_after' => isset($local_apply_result['h1_after']) ? (string) $local_apply_result['h1_after'] : '',
                        'h1_changed' => !empty($local_apply_result['h1_changed']) ? '1' : '0',
                        'images_alt_before' => isset($local_apply_result['images_alt_before']) ? (string) $local_apply_result['images_alt_before'] : '',
                        'images_alt_after' => isset($local_apply_result['images_alt_after']) ? (string) $local_apply_result['images_alt_after'] : '',
                        'images_alt_changed' => !empty($local_apply_result['images_alt_changed']) ? '1' : '0',
                        'images_alt_updated_count' => isset($local_apply_result['images_alt_updated_count']) ? (string) $local_apply_result['images_alt_updated_count'] : '0',
                        'images_dimensions_before' => isset($local_apply_result['images_dimensions_before']) ? (string) $local_apply_result['images_dimensions_before'] : '',
                        'images_dimensions_after' => isset($local_apply_result['images_dimensions_after']) ? (string) $local_apply_result['images_dimensions_after'] : '',
                        'images_dimensions_changed' => !empty($local_apply_result['images_dimensions_changed']) ? '1' : '0',
                        'images_dimensions_updated_count' => isset($local_apply_result['images_dimensions_updated_count']) ? (string) $local_apply_result['images_dimensions_updated_count'] : '0',
                        'images_lazy_before' => isset($local_apply_result['images_lazy_before']) ? (string) $local_apply_result['images_lazy_before'] : '',
                        'images_lazy_after' => isset($local_apply_result['images_lazy_after']) ? (string) $local_apply_result['images_lazy_after'] : '',
                        'images_lazy_changed' => !empty($local_apply_result['images_lazy_changed']) ? '1' : '0',
                        'images_lazy_updated_count' => isset($local_apply_result['images_lazy_updated_count']) ? (string) $local_apply_result['images_lazy_updated_count'] : '0',
                        'canonical_before' => isset($local_apply_result['canonical_before']) ? (string) $local_apply_result['canonical_before'] : '',
                        'canonical_after' => isset($local_apply_result['canonical_after']) ? (string) $local_apply_result['canonical_after'] : '',
                        'canonical_changed' => !empty($local_apply_result['canonical_changed']) ? '1' : '0',
                        'jsonld_schema_before' => isset($local_apply_result['jsonld_schema_before']) ? (string) $local_apply_result['jsonld_schema_before'] : '',
                        'jsonld_schema_after' => isset($local_apply_result['jsonld_schema_after']) ? (string) $local_apply_result['jsonld_schema_after'] : '',
                        'jsonld_schema_changed' => !empty($local_apply_result['jsonld_schema_changed']) ? '1' : '0',
                        'heading_structure_before' => isset($local_apply_result['heading_structure_before']) ? (string) $local_apply_result['heading_structure_before'] : '',
                        'heading_structure_after' => isset($local_apply_result['heading_structure_after']) ? (string) $local_apply_result['heading_structure_after'] : '',
                        'heading_structure_changed' => !empty($local_apply_result['heading_structure_changed']) ? '1' : '0',
                        'headings_added_count' => isset($local_apply_result['headings_added_count']) ? (string) $local_apply_result['headings_added_count'] : '0',
                        'internal_links_before' => isset($local_apply_result['internal_links_before']) ? (string) $local_apply_result['internal_links_before'] : '',
                        'internal_links_after' => isset($local_apply_result['internal_links_after']) ? (string) $local_apply_result['internal_links_after'] : '',
                        'internal_linking_changed' => !empty($local_apply_result['internal_linking_changed']) ? '1' : '0',
                        'internal_links_added_count' => isset($local_apply_result['internal_links_added_count']) ? (string) $local_apply_result['internal_links_added_count'] : '0',
                        'paragraph_structure_before' => isset($local_apply_result['paragraph_structure_before']) ? (string) $local_apply_result['paragraph_structure_before'] : '',
                        'paragraph_structure_after' => isset($local_apply_result['paragraph_structure_after']) ? (string) $local_apply_result['paragraph_structure_after'] : '',
                        'paragraph_structure_changed' => !empty($local_apply_result['paragraph_structure_changed']) ? '1' : '0',
                        'paragraphs_added_count' => isset($local_apply_result['paragraphs_added_count']) ? (string) $local_apply_result['paragraphs_added_count'] : '0',
                        'regenerated' => $force_regenerate ? '1' : '0',
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

    public function handle_preview_content_depth(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to do that.', 'icap-seo'));
        }
        check_admin_referer('icap_seo_preview_content_depth');

        $content_key = isset($_POST['content_key']) ? sanitize_text_field((string) wp_unslash($_POST['content_key'])) : '';
        if ($content_key === '') {
            $this->redirect_with_notice('content_depth_content_key_missing', 'content-scores');
            return;
        }

        $post_id = $this->extract_post_id_from_content_key($content_key);
        if ($post_id <= 0 || !current_user_can('edit_post', $post_id)) {
            $this->redirect_with_notice('content_depth_preview_failed', 'content-scores', ['content_key' => $content_key]);
            return;
        }

        $post = get_post($post_id);
        if (!$post instanceof WP_Post) {
            $this->redirect_with_notice('content_depth_preview_failed', 'content-scores', ['content_key' => $content_key]);
            return;
        }

        $draft = $this->build_content_depth_draft($post, $content_key);
        if ($draft['html'] === '') {
            $this->redirect_with_notice('content_depth_already_sufficient', 'content-scores', ['content_key' => $content_key]);
            return;
        }

        update_post_meta($post_id, self::CONTENT_DEPTH_DRAFT_META_KEY, $draft['html']);
        update_post_meta($post_id, self::CONTENT_DEPTH_DRAFT_WORD_COUNT_META_KEY, $draft['draft_word_count']);

        $this->redirect_with_notice('content_depth_preview_ready', 'content-scores', ['content_key' => $content_key]);
    }

    public function handle_publish_content_depth(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to do that.', 'icap-seo'));
        }
        check_admin_referer('icap_seo_publish_content_depth');

        $content_key = isset($_POST['content_key']) ? sanitize_text_field((string) wp_unslash($_POST['content_key'])) : '';
        if ($content_key === '') {
            $this->redirect_with_notice('content_depth_content_key_missing', 'content-scores');
            return;
        }

        $post_id = $this->extract_post_id_from_content_key($content_key);
        if ($post_id <= 0 || !current_user_can('edit_post', $post_id)) {
            $this->redirect_with_notice('content_depth_publish_failed', 'content-scores', ['content_key' => $content_key]);
            return;
        }

        $draft_html = get_post_meta($post_id, self::CONTENT_DEPTH_DRAFT_META_KEY, true);
        if (!is_string($draft_html) || trim($draft_html) === '') {
            $this->redirect_with_notice('content_depth_no_draft', 'content-scores', ['content_key' => $content_key]);
            return;
        }

        $post = get_post($post_id);
        if (!$post instanceof WP_Post) {
            $this->redirect_with_notice('content_depth_publish_failed', 'content-scores', ['content_key' => $content_key]);
            return;
        }

        $updated_content = rtrim((string) $post->post_content) . "\n\n" . $draft_html;
        $update_result = wp_update_post(
            [
                'ID' => $post_id,
                'post_content' => $updated_content,
            ],
            true
        );

        if (is_wp_error($update_result)) {
            $this->redirect_with_notice('content_depth_publish_failed', 'content-scores', ['content_key' => $content_key]);
            return;
        }

        $words_added = $this->count_words_in_html($draft_html);
        delete_post_meta($post_id, self::CONTENT_DEPTH_DRAFT_META_KEY);
        delete_post_meta($post_id, self::CONTENT_DEPTH_DRAFT_WORD_COUNT_META_KEY);

        $this->mark_issue_codes_applied($post_id, self::CONTENT_DEPTH_ISSUE_CODES);

        $this->store_remediation_history_entry(
            $post_id,
            self::CONTENT_DEPTH_ISSUE_CODES,
            [
                'content_depth_changed' => true,
                'content_depth_words_added' => $words_added,
            ]
        );

        $this->redirect_with_notice(
            'content_depth_published',
            'content-scores',
            ['content_key' => $content_key, 'content_depth_words_added' => (string) $words_added]
        );
    }

    public function handle_discard_content_depth(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to do that.', 'icap-seo'));
        }
        check_admin_referer('icap_seo_discard_content_depth');

        $content_key = isset($_POST['content_key']) ? sanitize_text_field((string) wp_unslash($_POST['content_key'])) : '';
        if ($content_key === '') {
            $this->redirect_with_notice('content_depth_content_key_missing', 'content-scores');
            return;
        }

        $post_id = $this->extract_post_id_from_content_key($content_key);
        if ($post_id > 0 && current_user_can('edit_post', $post_id)) {
            delete_post_meta($post_id, self::CONTENT_DEPTH_DRAFT_META_KEY);
            delete_post_meta($post_id, self::CONTENT_DEPTH_DRAFT_WORD_COUNT_META_KEY);
        }

        $this->redirect_with_notice('content_depth_discarded', 'content-scores', ['content_key' => $content_key]);
    }

    public function handle_preview_readability_rewrite(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to do that.', 'icap-seo'));
        }
        check_admin_referer('icap_seo_preview_readability_rewrite');

        $content_key = isset($_POST['content_key']) ? sanitize_text_field((string) wp_unslash($_POST['content_key'])) : '';
        if ($content_key === '') {
            $this->redirect_with_notice('readability_rewrite_content_key_missing', 'content-scores');
            return;
        }

        $post_id = $this->extract_post_id_from_content_key($content_key);
        if ($post_id <= 0 || !current_user_can('edit_post', $post_id)) {
            $this->redirect_with_notice('readability_rewrite_preview_failed', 'content-scores', ['content_key' => $content_key]);
            return;
        }

        $post = get_post($post_id);
        if (!$post instanceof WP_Post) {
            $this->redirect_with_notice('readability_rewrite_preview_failed', 'content-scores', ['content_key' => $content_key]);
            return;
        }

        $draft = $this->build_readability_rewrite_draft($post, $content_key);
        if (empty($draft['paragraphs'])) {
            $this->redirect_with_notice('readability_rewrite_unavailable', 'content-scores', ['content_key' => $content_key]);
            return;
        }

        update_post_meta($post_id, self::READABILITY_DRAFT_META_KEY, wp_json_encode($draft['paragraphs']));

        $this->redirect_with_notice('readability_rewrite_preview_ready', 'content-scores', ['content_key' => $content_key]);
    }

    public function handle_publish_readability_rewrite(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to do that.', 'icap-seo'));
        }
        check_admin_referer('icap_seo_publish_readability_rewrite');

        $content_key = isset($_POST['content_key']) ? sanitize_text_field((string) wp_unslash($_POST['content_key'])) : '';
        if ($content_key === '') {
            $this->redirect_with_notice('readability_rewrite_content_key_missing', 'content-scores');
            return;
        }

        $post_id = $this->extract_post_id_from_content_key($content_key);
        if ($post_id <= 0 || !current_user_can('edit_post', $post_id)) {
            $this->redirect_with_notice('readability_rewrite_publish_failed', 'content-scores', ['content_key' => $content_key]);
            return;
        }

        $draft_paragraphs = $this->get_readability_draft_for_post($post_id);
        if (empty($draft_paragraphs)) {
            $this->redirect_with_notice('readability_rewrite_no_draft', 'content-scores', ['content_key' => $content_key]);
            return;
        }

        $post = get_post($post_id);
        if (!$post instanceof WP_Post) {
            $this->redirect_with_notice('readability_rewrite_publish_failed', 'content-scores', ['content_key' => $content_key]);
            return;
        }

        $blocks = $this->split_content_into_paragraph_blocks((string) $post->post_content);
        $paragraphs_updated = 0;
        foreach ($draft_paragraphs as $entry) {
            if (!is_array($entry)) {
                continue;
            }
            $block_index = isset($entry['block_index']) ? (int) $entry['block_index'] : -1;
            $original_text = isset($entry['original_text']) ? (string) $entry['original_text'] : '';
            $rewritten_text = isset($entry['rewritten_text']) ? (string) $entry['rewritten_text'] : '';
            if ($block_index < 0 || $rewritten_text === '' || !isset($blocks[$block_index])) {
                continue;
            }

            // Only replace if the page content at this block hasn't changed
            // underneath this draft since it was generated at preview time.
            $current_text = trim(wp_strip_all_tags($this->paragraph_block_inner_html($blocks[$block_index])));
            if ($current_text !== $original_text) {
                continue;
            }

            $updated_block = preg_replace_callback(
                '/^(\s*<p\b[^>]*>).*(<\/p>\s*)$/is',
                static fn(array $matches): string => $matches[1] . esc_html($rewritten_text) . $matches[2],
                $blocks[$block_index],
                1
            );
            if (!is_string($updated_block)) {
                continue;
            }
            $blocks[$block_index] = $updated_block;
            $paragraphs_updated++;
        }

        if ($paragraphs_updated === 0) {
            delete_post_meta($post_id, self::READABILITY_DRAFT_META_KEY);
            $this->redirect_with_notice('readability_rewrite_stale_draft', 'content-scores', ['content_key' => $content_key]);
            return;
        }

        $update_result = wp_update_post(
            [
                'ID' => $post_id,
                'post_content' => implode('', $blocks),
            ],
            true
        );

        if (is_wp_error($update_result)) {
            $this->redirect_with_notice('readability_rewrite_publish_failed', 'content-scores', ['content_key' => $content_key]);
            return;
        }

        delete_post_meta($post_id, self::READABILITY_DRAFT_META_KEY);

        $this->mark_issue_codes_applied($post_id, self::READABILITY_ISSUE_CODES);

        $this->store_remediation_history_entry(
            $post_id,
            self::READABILITY_ISSUE_CODES,
            [
                'readability_rewrite_changed' => true,
                'readability_paragraphs_updated' => $paragraphs_updated,
            ]
        );

        $this->redirect_with_notice(
            'readability_rewrite_published',
            'content-scores',
            ['content_key' => $content_key, 'readability_paragraphs_updated' => (string) $paragraphs_updated]
        );
    }

    public function handle_discard_readability_rewrite(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to do that.', 'icap-seo'));
        }
        check_admin_referer('icap_seo_discard_readability_rewrite');

        $content_key = isset($_POST['content_key']) ? sanitize_text_field((string) wp_unslash($_POST['content_key'])) : '';
        if ($content_key === '') {
            $this->redirect_with_notice('readability_rewrite_content_key_missing', 'content-scores');
            return;
        }

        $post_id = $this->extract_post_id_from_content_key($content_key);
        if ($post_id > 0 && current_user_can('edit_post', $post_id)) {
            delete_post_meta($post_id, self::READABILITY_DRAFT_META_KEY);
        }

        $this->redirect_with_notice('readability_rewrite_discarded', 'content-scores', ['content_key' => $content_key]);
    }

    private function apply_supported_local_remediation(
        string $content_key,
        array $approved_issue_codes,
        bool $force_regenerate = false
    ): array {
        $normalized_codes = array_map(
            static fn($value): string => sanitize_key((string) $value),
            $approved_issue_codes
        );
        $apply_title_recommendation =
            in_array('title_length_out_of_range', $normalized_codes, true)
            || in_array('missing_title_tag', $normalized_codes, true);
        $apply_meta_description_recommendation =
            in_array('missing_meta_description', $normalized_codes, true)
            || in_array('meta_description_length_out_of_range', $normalized_codes, true);
        $apply_h1_recommendation = in_array('missing_h1', $normalized_codes, true);
        $apply_images_alt_recommendation = in_array('images_missing_alt', $normalized_codes, true);
        $apply_images_dimensions_recommendation = in_array('images_missing_dimensions', $normalized_codes, true);
        $apply_images_lazy_loading_recommendation = in_array('images_not_lazy_loaded', $normalized_codes, true);
        $apply_canonical_recommendation = in_array('missing_canonical', $normalized_codes, true);
        $apply_jsonld_schema_recommendation =
            in_array('missing_jsonld_schema', $normalized_codes, true)
            || in_array('schema_type_missing', $normalized_codes, true)
            || in_array('schema_missing_required_properties', $normalized_codes, true);
        $apply_heading_structure_recommendation = in_array('limited_heading_structure', $normalized_codes, true);
        $apply_internal_linking_recommendation =
            in_array('low_internal_linking', $normalized_codes, true)
            || in_array('no_links_detected', $normalized_codes, true);
        $apply_paragraph_structure_recommendation = in_array('limited_paragraph_structure', $normalized_codes, true);

        if (
            !$apply_title_recommendation
            && !$apply_meta_description_recommendation
            && !$apply_h1_recommendation
            && !$apply_images_alt_recommendation
            && !$apply_images_dimensions_recommendation
            && !$apply_images_lazy_loading_recommendation
            && !$apply_canonical_recommendation
            && !$apply_jsonld_schema_recommendation
            && !$apply_heading_structure_recommendation
            && !$apply_internal_linking_recommendation
            && !$apply_paragraph_structure_recommendation
        ) {
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
        $title_no_op_reason = '';
        if ($apply_title_recommendation) {
            if ($current_title === '') {
                $updated_title = $this->build_title_for_empty_post($post, $content_key);
            } else {
                $updated_title = $this->build_seo_title_with_target_length($current_title);
            }
            $title_was_updated = $updated_title !== '' && $updated_title !== $current_title;
            if (!$title_was_updated && $force_regenerate && $updated_title !== '') {
                // Allow explicit re-apply even when length already matches target range.
                $title_was_updated = true;
            }
            if (!$title_was_updated && $updated_title === '') {
                $title_no_op_reason = 'title_generation_failed';
            }
        }
        $current_meta_description = $this->get_current_meta_description_for_post($post);
        $updated_meta_description = $current_meta_description;
        $meta_description_was_updated = false;

        if ($apply_meta_description_recommendation) {
            $generated_meta_description = $this->build_seo_meta_description($post, $content_key, $force_regenerate);
            $updated_meta_description = trim((string) preg_replace('/\s+/', ' ', $generated_meta_description));
            $meta_description_was_updated = $updated_meta_description !== '' && (
                $force_regenerate || $updated_meta_description !== $current_meta_description
            );
        }

        $current_content = (string) $post->post_content;
        $current_h1 = $this->extract_first_h1_text($current_content);
        $working_content = $current_content;
        $updated_h1 = $current_h1;
        $h1_was_updated = false;
        if ($apply_h1_recommendation) {
            $h1_apply = $this->build_content_with_seo_h1(
                $working_content,
                $current_title !== '' ? $current_title : (string) $post->post_title,
                $force_regenerate
            );
            $working_content = isset($h1_apply['content']) ? (string) $h1_apply['content'] : $working_content;
            $updated_h1 = isset($h1_apply['h1_text']) ? sanitize_text_field((string) $h1_apply['h1_text']) : $current_h1;
            $h1_was_updated = !empty($h1_apply['changed']);
            if (!$h1_was_updated && isset($h1_apply['reason']) && is_string($h1_apply['reason'])) {
                $h1_no_op_reason = sanitize_key($h1_apply['reason']);
            } else {
                $h1_no_op_reason = '';
            }
        } else {
            $h1_no_op_reason = '';
        }

        $images_alt_before_count = $this->count_missing_alt_images($current_content);
        $images_alt_after_count = $images_alt_before_count;
        $images_alt_updated_count = 0;
        $images_alt_was_updated = false;
        $images_alt_no_op_reason = '';
        if ($apply_images_alt_recommendation) {
            $images_alt_apply = $this->build_content_with_image_alt_text(
                $working_content,
                (string) $post->post_title,
                $post,
                $content_key,
                $force_regenerate
            );
            $working_content = isset($images_alt_apply['content']) ? (string) $images_alt_apply['content'] : $working_content;
            $images_alt_was_updated = !empty($images_alt_apply['changed']);
            $images_alt_updated_count = isset($images_alt_apply['updated_count']) ? (int) $images_alt_apply['updated_count'] : 0;
            $images_alt_after_count = isset($images_alt_apply['missing_after']) ? (int) $images_alt_apply['missing_after'] : $images_alt_before_count;
            if (!$images_alt_was_updated && isset($images_alt_apply['reason']) && is_string($images_alt_apply['reason'])) {
                $images_alt_no_op_reason = sanitize_key($images_alt_apply['reason']);
            }
        }

        $images_dimensions_before_count = $this->count_images_missing_dimensions($current_content);
        $images_dimensions_after_count = $images_dimensions_before_count;
        $images_dimensions_updated_count = 0;
        $images_dimensions_was_updated = false;
        $images_dimensions_no_op_reason = '';
        if ($apply_images_dimensions_recommendation) {
            $images_dimensions_apply = $this->build_content_with_image_dimensions(
                $working_content,
                $force_regenerate
            );
            $working_content = isset($images_dimensions_apply['content']) ? (string) $images_dimensions_apply['content'] : $working_content;
            $images_dimensions_was_updated = !empty($images_dimensions_apply['changed']);
            $images_dimensions_updated_count = isset($images_dimensions_apply['updated_count']) ? (int) $images_dimensions_apply['updated_count'] : 0;
            $images_dimensions_after_count = isset($images_dimensions_apply['missing_after']) ? (int) $images_dimensions_apply['missing_after'] : $images_dimensions_before_count;
            if (!$images_dimensions_was_updated && isset($images_dimensions_apply['reason']) && is_string($images_dimensions_apply['reason'])) {
                $images_dimensions_no_op_reason = sanitize_key($images_dimensions_apply['reason']);
            }
        }

        $images_lazy_before_count = $this->count_images_not_lazy_loaded($current_content);
        $images_lazy_after_count = $images_lazy_before_count;
        $images_lazy_updated_count = 0;
        $images_lazy_was_updated = false;
        $images_lazy_no_op_reason = '';
        if ($apply_images_lazy_loading_recommendation) {
            $images_lazy_apply = $this->build_content_with_lazy_loading(
                $working_content,
                $force_regenerate
            );
            $working_content = isset($images_lazy_apply['content']) ? (string) $images_lazy_apply['content'] : $working_content;
            $images_lazy_was_updated = !empty($images_lazy_apply['changed']);
            $images_lazy_updated_count = isset($images_lazy_apply['updated_count']) ? (int) $images_lazy_apply['updated_count'] : 0;
            $images_lazy_after_count = isset($images_lazy_apply['missing_after']) ? (int) $images_lazy_apply['missing_after'] : $images_lazy_before_count;
            if (!$images_lazy_was_updated && isset($images_lazy_apply['reason']) && is_string($images_lazy_apply['reason'])) {
                $images_lazy_no_op_reason = sanitize_key($images_lazy_apply['reason']);
            }
        }

        $heading_structure_before_count = $this->count_h2_h3_headings($current_content);
        $heading_structure_after_count = $heading_structure_before_count;
        $headings_added_count = 0;
        $heading_structure_was_updated = false;
        $heading_structure_no_op_reason = '';
        if ($apply_heading_structure_recommendation) {
            $heading_structure_apply = $this->build_content_with_heading_outline(
                $working_content,
                (string) $post->post_title,
                $post,
                $content_key,
                $force_regenerate
            );
            $working_content = isset($heading_structure_apply['content']) ? (string) $heading_structure_apply['content'] : $working_content;
            $heading_structure_was_updated = !empty($heading_structure_apply['changed']);
            $headings_added_count = isset($heading_structure_apply['headings_added']) ? (int) $heading_structure_apply['headings_added'] : 0;
            $heading_structure_after_count = $heading_structure_before_count + $headings_added_count;
            if (!$heading_structure_was_updated && isset($heading_structure_apply['reason']) && is_string($heading_structure_apply['reason'])) {
                $heading_structure_no_op_reason = sanitize_key($heading_structure_apply['reason']);
            }
        }

        $internal_links_before_count = $this->count_internal_links_in_content($current_content);
        $internal_links_after_count = $internal_links_before_count;
        $internal_links_added_count = 0;
        $internal_linking_was_updated = false;
        $internal_linking_no_op_reason = '';
        if ($apply_internal_linking_recommendation) {
            $internal_linking_apply = $this->build_content_with_internal_links(
                $working_content,
                $post,
                $content_key,
                $force_regenerate
            );
            $working_content = isset($internal_linking_apply['content']) ? (string) $internal_linking_apply['content'] : $working_content;
            $internal_linking_was_updated = !empty($internal_linking_apply['changed']);
            $internal_links_added_count = isset($internal_linking_apply['links_added']) ? (int) $internal_linking_apply['links_added'] : 0;
            $internal_links_after_count = $internal_links_before_count + $internal_links_added_count;
            if (!$internal_linking_was_updated && isset($internal_linking_apply['reason']) && is_string($internal_linking_apply['reason'])) {
                $internal_linking_no_op_reason = sanitize_key($internal_linking_apply['reason']);
            }
        }

        $paragraph_structure_before_count = $this->count_paragraph_tags($current_content);
        $paragraph_structure_after_count = $paragraph_structure_before_count;
        $paragraphs_added_count = 0;
        $paragraph_structure_was_updated = false;
        $paragraph_structure_no_op_reason = '';
        if ($apply_paragraph_structure_recommendation) {
            $paragraph_structure_apply = $this->build_content_with_expanded_paragraphs(
                $working_content,
                $force_regenerate
            );
            $working_content = isset($paragraph_structure_apply['content']) ? (string) $paragraph_structure_apply['content'] : $working_content;
            $paragraph_structure_was_updated = !empty($paragraph_structure_apply['changed']);
            $paragraphs_added_count = isset($paragraph_structure_apply['paragraphs_added']) ? (int) $paragraph_structure_apply['paragraphs_added'] : 0;
            $paragraph_structure_after_count = $this->count_paragraph_tags($working_content);
            if (!$paragraph_structure_was_updated && isset($paragraph_structure_apply['reason']) && is_string($paragraph_structure_apply['reason'])) {
                $paragraph_structure_no_op_reason = sanitize_key($paragraph_structure_apply['reason']);
            }
        }

        $updated_content = $working_content;
        $content_was_updated = $h1_was_updated || $images_alt_was_updated || $images_dimensions_was_updated || $images_lazy_was_updated || $heading_structure_was_updated || $internal_linking_was_updated || $paragraph_structure_was_updated;

        $current_canonical_url = $this->get_current_canonical_url_for_post($post);
        $updated_canonical_url = $current_canonical_url;
        $canonical_was_updated = false;
        $canonical_no_op_reason = '';
        if ($apply_canonical_recommendation) {
            $generated_canonical_url = $this->build_canonical_url_for_post($post);
            $canonical_was_updated = $generated_canonical_url !== '' && (
                $force_regenerate || $generated_canonical_url !== $current_canonical_url
            );
            if ($canonical_was_updated) {
                $updated_canonical_url = $generated_canonical_url;
            } elseif ($generated_canonical_url === '') {
                $canonical_no_op_reason = 'canonical_generation_failed';
            } else {
                $canonical_no_op_reason = 'canonical_already_set';
            }
        }

        $current_jsonld_schema = $this->get_current_jsonld_schema_for_post($post);
        $current_jsonld_schema_type = $current_jsonld_schema['type'];
        $updated_jsonld_schema_type = $current_jsonld_schema_type;
        $jsonld_schema_was_updated = false;
        $jsonld_schema_no_op_reason = '';
        $generated_jsonld_schema_json = '';
        if ($apply_jsonld_schema_recommendation) {
            $jsonld_schema_generated = $this->build_jsonld_schema_for_post($post, $content_key);
            $generated_jsonld_schema_json = $jsonld_schema_generated['json'];
            $jsonld_schema_was_updated = $generated_jsonld_schema_json !== '' && (
                $force_regenerate || $generated_jsonld_schema_json !== $current_jsonld_schema['json']
            );
            if ($jsonld_schema_was_updated) {
                $updated_jsonld_schema_type = $jsonld_schema_generated['type'];
            } elseif ($generated_jsonld_schema_json === '') {
                $jsonld_schema_no_op_reason = 'jsonld_schema_generation_failed';
            } else {
                $jsonld_schema_no_op_reason = 'jsonld_schema_already_set';
            }
        }

        if (
            !$title_was_updated
            && !$meta_description_was_updated
            && !$h1_was_updated
            && !$images_alt_was_updated
            && !$images_dimensions_was_updated
            && !$images_lazy_was_updated
            && !$canonical_was_updated
            && !$jsonld_schema_was_updated
            && !$heading_structure_was_updated
            && !$internal_linking_was_updated
            && !$paragraph_structure_was_updated
        ) {
            $reason = 'no_effective_change_computed';
            if ($apply_h1_recommendation && $h1_no_op_reason !== '') {
                $reason = $h1_no_op_reason;
            } elseif ($apply_images_alt_recommendation && $images_alt_no_op_reason !== '') {
                $reason = $images_alt_no_op_reason;
            } elseif ($apply_images_dimensions_recommendation && $images_dimensions_no_op_reason !== '') {
                $reason = $images_dimensions_no_op_reason;
            } elseif ($apply_images_lazy_loading_recommendation && $images_lazy_no_op_reason !== '') {
                $reason = $images_lazy_no_op_reason;
            } elseif ($apply_canonical_recommendation && $canonical_no_op_reason !== '') {
                $reason = $canonical_no_op_reason;
            } elseif ($apply_jsonld_schema_recommendation && $jsonld_schema_no_op_reason !== '') {
                $reason = $jsonld_schema_no_op_reason;
            } elseif ($apply_heading_structure_recommendation && $heading_structure_no_op_reason !== '') {
                $reason = $heading_structure_no_op_reason;
            } elseif ($apply_internal_linking_recommendation && $internal_linking_no_op_reason !== '') {
                $reason = $internal_linking_no_op_reason;
            } elseif ($apply_paragraph_structure_recommendation && $paragraph_structure_no_op_reason !== '') {
                $reason = $paragraph_structure_no_op_reason;
            } elseif ($apply_meta_description_recommendation) {
                if ($updated_meta_description === '') {
                    $reason = 'meta_description_generation_failed';
                } elseif (
                    $current_meta_description !== ''
                    && $updated_meta_description === $current_meta_description
                    && $this->string_length($current_meta_description) >= 120
                    && $this->string_length($current_meta_description) <= 170
                ) {
                    $reason = 'meta_description_already_optimized';
                }
            } elseif ($apply_title_recommendation && $title_no_op_reason !== '') {
                $reason = $title_no_op_reason;
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
                'title_changed' => false,
                'excerpt_before' => $current_meta_description,
                'excerpt_after' => $current_meta_description,
                'excerpt_changed' => false,
                'h1_before' => $current_h1,
                'h1_after' => $current_h1,
                'h1_changed' => false,
                'images_alt_before' => $images_alt_before_count,
                'images_alt_after' => $images_alt_before_count,
                'images_alt_changed' => false,
                'images_alt_updated_count' => 0,
                'images_dimensions_before' => $images_dimensions_before_count,
                'images_dimensions_after' => $images_dimensions_before_count,
                'images_dimensions_changed' => false,
                'images_dimensions_updated_count' => 0,
                'images_lazy_before' => $images_lazy_before_count,
                'images_lazy_after' => $images_lazy_before_count,
                'images_lazy_changed' => false,
                'images_lazy_updated_count' => 0,
                'canonical_before' => $current_canonical_url,
                'canonical_after' => $current_canonical_url,
                'canonical_changed' => false,
                'jsonld_schema_before' => $current_jsonld_schema_type,
                'jsonld_schema_after' => $current_jsonld_schema_type,
                'jsonld_schema_changed' => false,
                'heading_structure_before' => $heading_structure_before_count,
                'heading_structure_after' => $heading_structure_before_count,
                'heading_structure_changed' => false,
                'headings_added_count' => 0,
                'internal_links_before' => $internal_links_before_count,
                'internal_links_after' => $internal_links_before_count,
                'internal_linking_changed' => false,
                'internal_links_added_count' => 0,
                'paragraph_structure_before' => $paragraph_structure_before_count,
                'paragraph_structure_after' => $paragraph_structure_before_count,
                'paragraph_structure_changed' => false,
                'paragraphs_added_count' => 0,
            ];
        }

        $update_payload = [
            'ID' => $post_id,
        ];
        if ($title_was_updated) {
            $update_payload['post_title'] = $updated_title;
        }
        // Keep excerpt aligned for themes/admin UX, but front-end output prefers dedicated meta.
        if ($meta_description_was_updated) {
            $update_payload['post_excerpt'] = $updated_meta_description;
        }
        if ($content_was_updated) {
            $update_payload['post_content'] = $updated_content;
        }

        if (count($update_payload) > 1) {
            $update_result = wp_update_post(
                $update_payload,
                true
            );

            if (is_wp_error($update_result)) {
                return ['status' => 'failed', 'reason' => 'wp_update_post_failed'];
            }
        }

        if ($meta_description_was_updated) {
            update_post_meta($post_id, self::META_DESCRIPTION_META_KEY, $updated_meta_description);
        }
        if ($canonical_was_updated) {
            update_post_meta($post_id, self::CANONICAL_URL_META_KEY, $updated_canonical_url);
        }
        if ($jsonld_schema_was_updated) {
            update_post_meta($post_id, self::JSONLD_SCHEMA_TYPE_META_KEY, $updated_jsonld_schema_type);
            update_post_meta($post_id, self::JSONLD_SCHEMA_JSON_META_KEY, $generated_jsonld_schema_json);
        }

        $this->mark_issue_codes_applied($post_id, $normalized_codes);

        $this->store_remediation_history_entry(
            $post_id,
            $normalized_codes,
            [
                'title_before' => $current_title,
                'title_after' => $title_was_updated ? $updated_title : $current_title,
                'title_changed' => $title_was_updated,
                'excerpt_before' => $current_meta_description,
                'excerpt_after' => $meta_description_was_updated ? $updated_meta_description : $current_meta_description,
                'excerpt_changed' => $meta_description_was_updated,
                'h1_before' => $current_h1,
                'h1_after' => $h1_was_updated ? $updated_h1 : $current_h1,
                'h1_changed' => $h1_was_updated,
                'images_alt_before' => $images_alt_before_count,
                'images_alt_after' => $images_alt_after_count,
                'images_alt_changed' => $images_alt_was_updated,
                'images_alt_updated_count' => $images_alt_updated_count,
                'images_dimensions_before' => $images_dimensions_before_count,
                'images_dimensions_after' => $images_dimensions_after_count,
                'images_dimensions_changed' => $images_dimensions_was_updated,
                'images_dimensions_updated_count' => $images_dimensions_updated_count,
                'images_lazy_before' => $images_lazy_before_count,
                'images_lazy_after' => $images_lazy_after_count,
                'images_lazy_changed' => $images_lazy_was_updated,
                'images_lazy_updated_count' => $images_lazy_updated_count,
                'canonical_before' => $current_canonical_url,
                'canonical_after' => $canonical_was_updated ? $updated_canonical_url : $current_canonical_url,
                'canonical_changed' => $canonical_was_updated,
                'jsonld_schema_before' => $current_jsonld_schema_type,
                'jsonld_schema_after' => $jsonld_schema_was_updated ? $updated_jsonld_schema_type : $current_jsonld_schema_type,
                'jsonld_schema_changed' => $jsonld_schema_was_updated,
                'heading_structure_before' => $heading_structure_before_count,
                'heading_structure_after' => $heading_structure_was_updated ? $heading_structure_after_count : $heading_structure_before_count,
                'heading_structure_changed' => $heading_structure_was_updated,
                'headings_added_count' => $headings_added_count,
                'internal_links_before' => $internal_links_before_count,
                'internal_links_after' => $internal_linking_was_updated ? $internal_links_after_count : $internal_links_before_count,
                'internal_linking_changed' => $internal_linking_was_updated,
                'internal_links_added_count' => $internal_links_added_count,
                'paragraph_structure_before' => $paragraph_structure_before_count,
                'paragraph_structure_after' => $paragraph_structure_was_updated ? $paragraph_structure_after_count : $paragraph_structure_before_count,
                'paragraph_structure_changed' => $paragraph_structure_was_updated,
                'paragraphs_added_count' => $paragraphs_added_count,
            ]
        );

        return [
            'status' => 'applied',
            'reason' => 'changes_applied',
            'title_before' => $current_title,
            'title_after' => $title_was_updated ? $updated_title : $current_title,
            'title_changed' => $title_was_updated,
            'excerpt_before' => $current_meta_description,
            'excerpt_after' => $meta_description_was_updated ? $updated_meta_description : $current_meta_description,
            'excerpt_changed' => $meta_description_was_updated,
            'h1_before' => $current_h1,
            'h1_after' => $h1_was_updated ? $updated_h1 : $current_h1,
            'h1_changed' => $h1_was_updated,
            'images_alt_before' => $images_alt_before_count,
            'images_alt_after' => $images_alt_after_count,
            'images_alt_changed' => $images_alt_was_updated,
            'images_alt_updated_count' => $images_alt_updated_count,
            'images_dimensions_before' => $images_dimensions_before_count,
            'images_dimensions_after' => $images_dimensions_after_count,
            'images_dimensions_changed' => $images_dimensions_was_updated,
            'images_dimensions_updated_count' => $images_dimensions_updated_count,
            'images_lazy_before' => $images_lazy_before_count,
            'images_lazy_after' => $images_lazy_after_count,
            'images_lazy_changed' => $images_lazy_was_updated,
            'images_lazy_updated_count' => $images_lazy_updated_count,
            'canonical_before' => $current_canonical_url,
            'canonical_after' => $canonical_was_updated ? $updated_canonical_url : $current_canonical_url,
            'canonical_changed' => $canonical_was_updated,
            'jsonld_schema_before' => $current_jsonld_schema_type,
            'jsonld_schema_after' => $jsonld_schema_was_updated ? $updated_jsonld_schema_type : $current_jsonld_schema_type,
            'jsonld_schema_changed' => $jsonld_schema_was_updated,
            'heading_structure_before' => $heading_structure_before_count,
            'heading_structure_after' => $heading_structure_was_updated ? $heading_structure_after_count : $heading_structure_before_count,
            'heading_structure_changed' => $heading_structure_was_updated,
            'headings_added_count' => $headings_added_count,
            'internal_links_before' => $internal_links_before_count,
            'internal_links_after' => $internal_linking_was_updated ? $internal_links_after_count : $internal_links_before_count,
            'internal_linking_changed' => $internal_linking_was_updated,
            'internal_links_added_count' => $internal_links_added_count,
            'paragraph_structure_before' => $paragraph_structure_before_count,
            'paragraph_structure_after' => $paragraph_structure_was_updated ? $paragraph_structure_after_count : $paragraph_structure_before_count,
            'paragraph_structure_changed' => $paragraph_structure_was_updated,
            'paragraphs_added_count' => $paragraphs_added_count,
        ];
    }

    private function extract_first_h1_text(string $html): string
    {
        if (preg_match('/<h1\b[^>]*>(.*?)<\/h1>/is', $html, $matches) !== 1) {
            return '';
        }
        $text = wp_strip_all_tags((string) $matches[1], true);
        $text = trim((string) preg_replace('/\s+/', ' ', $text));
        return sanitize_text_field($text);
    }

    private function build_seo_h1_text(string $title): string
    {
        $phrase = $this->clean_title_phrase($title);
        if ($phrase === '') {
            $phrase = __('Page overview', 'icap-seo');
        }
        // Keep H1 concise and readable.
        if ($this->string_length($phrase) > 70) {
            $phrase = $this->trim_title_to_max_length($phrase, 70);
        }
        return trim($phrase);
    }

    private function build_content_with_seo_h1(string $content, string $title, bool $force_regenerate = false): array
    {
        $normalized_content = (string) $content;
        $existing_h1 = $this->extract_first_h1_text($normalized_content);
        $generated_h1 = $this->build_seo_h1_text($title);
        if ($generated_h1 === '') {
            return [
                'changed' => false,
                'reason' => 'h1_generation_failed',
                'content' => $normalized_content,
                'h1_text' => $existing_h1,
            ];
        }

        $h1_html = '<h1 class="icap-seo-h1">' . esc_html($generated_h1) . '</h1>';

        if ($existing_h1 !== '') {
            if (!$force_regenerate && strcasecmp($existing_h1, $generated_h1) === 0) {
                return [
                    'changed' => false,
                    'reason' => 'h1_already_present',
                    'content' => $normalized_content,
                    'h1_text' => $existing_h1,
                ];
            }
            if (!$force_regenerate) {
                return [
                    'changed' => false,
                    'reason' => 'h1_already_present',
                    'content' => $normalized_content,
                    'h1_text' => $existing_h1,
                ];
            }

            $updated = preg_replace(
                '/<h1\b[^>]*>.*?<\/h1>/is',
                $h1_html,
                $normalized_content,
                1
            );
            if (!is_string($updated) || $updated === $normalized_content) {
                return [
                    'changed' => false,
                    'reason' => 'h1_already_present',
                    'content' => $normalized_content,
                    'h1_text' => $existing_h1,
                ];
            }

            return [
                'changed' => true,
                'reason' => 'h1_regenerated',
                'content' => $updated,
                'h1_text' => $generated_h1,
            ];
        }

        $trimmed = ltrim($normalized_content);
        if ($trimmed === '') {
            $updated = $h1_html;
        } else {
            $updated = $h1_html . "

" . $normalized_content;
        }

        return [
            'changed' => true,
            'reason' => 'h1_inserted',
            'content' => $updated,
            'h1_text' => $generated_h1,
        ];
    }

    private function extract_image_tags(string $content): array
    {
        if (preg_match_all('/<img\b[^>]*>/i', $content, $matches) !== false && !empty($matches[0])) {
            return $matches[0];
        }
        return [];
    }

    private function image_tag_alt_value(string $tag): string
    {
        if (
            preg_match('/\balt\s*=\s*"([^"]*)"/i', $tag, $matches) === 1
            || preg_match("/\balt\s*=\s*'([^']*)'/i", $tag, $matches) === 1
        ) {
            return trim((string) preg_replace('/\s+/', ' ', html_entity_decode($matches[1], ENT_QUOTES)));
        }
        return '';
    }

    private function count_missing_alt_images(string $content): int
    {
        $missing = 0;
        foreach ($this->extract_image_tags($content) as $tag) {
            if ($this->image_tag_alt_value($tag) === '') {
                $missing++;
            }
        }
        return $missing;
    }

    private function count_images_missing_dimensions(string $content): int
    {
        $missing = 0;
        foreach ($this->extract_image_tags($content) as $tag) {
            if (!$this->image_tag_has_dimensions($tag)) {
                $missing++;
            }
        }
        return $missing;
    }

    private function count_images_not_lazy_loaded(string $content): int
    {
        $missing = 0;
        foreach (array_slice($this->extract_image_tags($content), 1) as $tag) {
            if (!$this->image_tag_has_lazy_loading($tag)) {
                $missing++;
            }
        }
        return $missing;
    }

    private function build_alt_text_for_image_tag(string $tag, string $fallback_phrase): string
    {
        if (
            preg_match('/\btitle\s*=\s*"([^"]*)"/i', $tag, $matches) === 1
            || preg_match("/\btitle\s*=\s*'([^']*)'/i", $tag, $matches) === 1
        ) {
            $title_attr = trim((string) preg_replace('/\s+/', ' ', html_entity_decode($matches[1], ENT_QUOTES)));
            if ($title_attr !== '') {
                return $this->trim_title_to_max_length($title_attr, 120);
            }
        }

        if (
            preg_match('/\bsrc\s*=\s*"([^"]*)"/i', $tag, $matches) === 1
            || preg_match("/\bsrc\s*=\s*'([^']*)'/i", $tag, $matches) === 1
        ) {
            $path = wp_parse_url((string) $matches[1], PHP_URL_PATH);
            if (is_string($path) && $path !== '') {
                $filename = urldecode(pathinfo($path, PATHINFO_FILENAME));
                // Strip WordPress-generated resize suffixes like "-300x200".
                $filename = (string) preg_replace('/-\d{2,5}x\d{2,5}$/', '', $filename);
                $filename = trim((string) preg_replace('/[-_]+/', ' ', $filename));
                $filename = trim((string) preg_replace('/\s+/', ' ', $filename));
                if ($filename !== '' && preg_match('/^[0-9a-f]{6,}$/i', $filename) !== 1) {
                    return $this->trim_title_to_max_length(ucwords(strtolower($filename)), 120);
                }
            }
        }

        return $this->trim_title_to_max_length($fallback_phrase, 120);
    }

    private function extract_image_src(string $tag): string
    {
        if (
            preg_match('/\bsrc\s*=\s*"([^"]*)"/i', $tag, $matches) === 1
            || preg_match("/\bsrc\s*=\s*'([^']*)'/i", $tag, $matches) === 1
        ) {
            return (string) $matches[1];
        }

        return '';
    }

    private function build_image_alt_texts_via_ai(WP_Post $post, string $content_key, string $site_name, array $image_urls): array
    {
        if ($content_key === '' || empty($image_urls)) {
            return [];
        }

        $result = $this->service_client->request_ai_content_draft(
            $content_key,
            'image_alt_text',
            [
                'title' => (string) $post->post_title,
                'site_name' => $site_name,
                'post_type' => (string) $post->post_type,
                'image_urls' => $image_urls,
            ]
        );

        if (!$result['success'] || empty($result['data']['draft_text'])) {
            return [];
        }

        // Deliberately not trim()-ing the raw response before splitting: a leading
        // blank line here is a real (if malformed) empty slot for image 1, and
        // trimming it away would silently shift every later alt text onto the
        // wrong image. Only the individual lines get trimmed below.
        $lines = preg_split('/\r\n|\r|\n/', (string) $result['data']['draft_text']);
        if (!is_array($lines)) {
            return [];
        }

        $alt_texts = [];
        foreach ($lines as $line) {
            $line = trim((string) $line);
            // Strip numbering/bullet/quote artifacts the model might still include
            // despite the "no numbering" instruction (e.g. "1. ", "- ", "\"").
            $line = preg_replace('/^[\s\-\*\d\.\)]+/', '', $line);
            $line = trim(wp_strip_all_tags((string) $line), " \t\n\r\0\x0B\"'");
            $alt_texts[] = $line === '' ? '' : $this->trim_title_to_max_length($line, 125);
        }

        return $alt_texts;
    }

    private function build_content_with_image_alt_text(
        string $content,
        string $title,
        WP_Post $post,
        string $content_key,
        bool $force_regenerate = false
    ): array {
        $normalized_content = (string) $content;
        $image_tags = $this->extract_image_tags($normalized_content);
        if (empty($image_tags)) {
            return [
                'changed' => false,
                'reason' => 'no_images_in_content',
                'content' => $normalized_content,
                'updated_count' => 0,
                'missing_before' => 0,
                'missing_after' => 0,
            ];
        }

        $missing_before = 0;
        foreach ($image_tags as $tag) {
            if ($this->image_tag_alt_value($tag) === '') {
                $missing_before++;
            }
        }

        if ($missing_before === 0 && !$force_regenerate) {
            return [
                'changed' => false,
                'reason' => 'images_alt_already_present',
                'content' => $normalized_content,
                'updated_count' => 0,
                'missing_before' => 0,
                'missing_after' => 0,
            ];
        }

        $fallback_phrase = $this->clean_title_phrase($title);
        if ($fallback_phrase === '') {
            $fallback_phrase = __('Page image', 'icap-seo');
        }

        // Batch up to 5 eligible images into one AI call (positionally correlated to
        // $ai_alt_texts below) rather than one Lambda call per image.
        $site_name = trim((string) get_bloginfo('name'));
        $eligible_urls = [];
        foreach ($image_tags as $tag) {
            $existing_alt = $this->image_tag_alt_value($tag);
            if ($existing_alt !== '' && !$force_regenerate) {
                continue;
            }
            $src = $this->extract_image_src($tag);
            if ($src !== '' && preg_match('#^https?://#i', $src) === 1) {
                $eligible_urls[] = $src;
            }
            if (count($eligible_urls) >= 5) {
                break;
            }
        }
        $ai_alt_texts = $this->build_image_alt_texts_via_ai($post, $content_key, $site_name, $eligible_urls);

        $updated_count = 0;
        $ai_index = 0;
        $updated_content = preg_replace_callback(
            '/<img\b[^>]*>/i',
            function (array $matches) use (&$updated_count, &$ai_index, $ai_alt_texts, $fallback_phrase, $force_regenerate): string {
                $tag = $matches[0];
                $existing_alt = $this->image_tag_alt_value($tag);
                if ($existing_alt !== '' && !$force_regenerate) {
                    return $tag;
                }

                $ai_alt = $ai_index < count($ai_alt_texts) ? $ai_alt_texts[$ai_index] : '';
                $ai_index++;

                $generated_alt = $ai_alt !== '' ? $ai_alt : $this->build_alt_text_for_image_tag($tag, $fallback_phrase);
                if ($generated_alt === '' || ($existing_alt !== '' && strcasecmp($existing_alt, $generated_alt) === 0)) {
                    return $tag;
                }

                $updated_count++;
                $escaped_alt = esc_attr($generated_alt);
                if (preg_match('/\balt\s*=\s*(["\']).*?\1/is', $tag) === 1) {
                    return (string) preg_replace('/\balt\s*=\s*(["\']).*?\1/is', 'alt="' . $escaped_alt . '"', $tag, 1);
                }

                return (string) preg_replace('/<img\b/i', '<img alt="' . $escaped_alt . '"', $tag, 1);
            },
            $normalized_content
        );

        if (!is_string($updated_content) || $updated_count === 0) {
            return [
                'changed' => false,
                'reason' => 'images_alt_generation_failed',
                'content' => $normalized_content,
                'updated_count' => 0,
                'missing_before' => $missing_before,
                'missing_after' => $missing_before,
            ];
        }

        $missing_after = 0;
        foreach ($this->extract_image_tags($updated_content) as $tag) {
            if ($this->image_tag_alt_value($tag) === '') {
                $missing_after++;
            }
        }

        return [
            'changed' => true,
            'reason' => 'images_alt_updated',
            'content' => $updated_content,
            'updated_count' => $updated_count,
            'missing_before' => $missing_before,
            'missing_after' => $missing_after,
        ];
    }

    private function image_tag_has_dimensions(string $tag): bool
    {
        $has_width = preg_match('/\bwidth\s*=\s*["\']?\d/i', $tag) === 1;
        $has_height = preg_match('/\bheight\s*=\s*["\']?\d/i', $tag) === 1;
        return $has_width && $has_height;
    }

    private function resolve_image_dimensions_from_src(string $src): ?array
    {
        if ($src === '' || !function_exists('attachment_url_to_postid')) {
            return null;
        }
        $attachment_id = attachment_url_to_postid($src);
        if ($attachment_id <= 0) {
            return null;
        }
        $metadata = wp_get_attachment_metadata($attachment_id);
        if (
            is_array($metadata)
            && isset($metadata['width'], $metadata['height'])
            && (int) $metadata['width'] > 0
            && (int) $metadata['height'] > 0
        ) {
            return ['width' => (int) $metadata['width'], 'height' => (int) $metadata['height']];
        }
        return null;
    }

    private function build_content_with_image_dimensions(string $content, bool $force_regenerate = false): array
    {
        $image_tags = $this->extract_image_tags($content);
        if (empty($image_tags)) {
            return [
                'changed' => false,
                'reason' => 'no_images_in_content',
                'content' => $content,
                'updated_count' => 0,
                'missing_before' => 0,
                'missing_after' => 0,
            ];
        }

        $missing_before = 0;
        foreach ($image_tags as $tag) {
            if (!$this->image_tag_has_dimensions($tag)) {
                $missing_before++;
            }
        }

        if ($missing_before === 0 && !$force_regenerate) {
            return [
                'changed' => false,
                'reason' => 'images_dimensions_already_set',
                'content' => $content,
                'updated_count' => 0,
                'missing_before' => 0,
                'missing_after' => 0,
            ];
        }

        $updated_count = 0;
        $updated_content = preg_replace_callback(
            '/<img\b[^>]*>/i',
            function (array $matches) use (&$updated_count, $force_regenerate): string {
                $tag = $matches[0];
                if ($this->image_tag_has_dimensions($tag) && !$force_regenerate) {
                    return $tag;
                }

                if (
                    preg_match('/\bsrc\s*=\s*"([^"]*)"/i', $tag, $src_matches) !== 1
                    && preg_match("/\bsrc\s*=\s*'([^']*)'/i", $tag, $src_matches) !== 1
                ) {
                    return $tag;
                }

                $dimensions = $this->resolve_image_dimensions_from_src((string) $src_matches[1]);
                if ($dimensions === null) {
                    return $tag;
                }

                $new_tag = (string) preg_replace('/\bwidth\s*=\s*(["\']).*?\1/i', '', $tag);
                $new_tag = (string) preg_replace('/\bheight\s*=\s*(["\']).*?\1/i', '', $new_tag);
                $new_tag = (string) preg_replace(
                    '/<img\b/i',
                    '<img width="' . (int) $dimensions['width'] . '" height="' . (int) $dimensions['height'] . '"',
                    $new_tag,
                    1
                );

                $updated_count++;
                return $new_tag;
            },
            $content
        );

        if (!is_string($updated_content) || $updated_count === 0) {
            return [
                'changed' => false,
                'reason' => 'images_dimensions_generation_failed',
                'content' => $content,
                'updated_count' => 0,
                'missing_before' => $missing_before,
                'missing_after' => $missing_before,
            ];
        }

        $missing_after = 0;
        foreach ($this->extract_image_tags($updated_content) as $tag) {
            if (!$this->image_tag_has_dimensions($tag)) {
                $missing_after++;
            }
        }

        return [
            'changed' => true,
            'reason' => 'images_dimensions_added',
            'content' => $updated_content,
            'updated_count' => $updated_count,
            'missing_before' => $missing_before,
            'missing_after' => $missing_after,
        ];
    }

    private function image_tag_has_lazy_loading(string $tag): bool
    {
        return preg_match('/\bloading\s*=\s*(["\'])lazy\1/i', $tag) === 1;
    }

    private function build_content_with_lazy_loading(string $content, bool $force_regenerate = false): array
    {
        // The first image is assumed to be a potential largest-contentful-paint
        // (LCP) candidate - lazy-loading it can hurt page load performance, so
        // it's exempt, matching the backend's detection logic exactly.
        $image_tags = $this->extract_image_tags($content);
        $below_first_tags = array_slice($image_tags, 1);
        if (empty($below_first_tags)) {
            return [
                'changed' => false,
                'reason' => 'no_images_below_first_in_content',
                'content' => $content,
                'updated_count' => 0,
                'missing_before' => 0,
                'missing_after' => 0,
            ];
        }

        $missing_before = 0;
        foreach ($below_first_tags as $tag) {
            if (!$this->image_tag_has_lazy_loading($tag)) {
                $missing_before++;
            }
        }

        if ($missing_before === 0 && !$force_regenerate) {
            return [
                'changed' => false,
                'reason' => 'images_already_lazy_loaded',
                'content' => $content,
                'updated_count' => 0,
                'missing_before' => 0,
                'missing_after' => 0,
            ];
        }

        $image_index = -1;
        $updated_count = 0;
        $updated_content = preg_replace_callback(
            '/<img\b[^>]*>/i',
            function (array $matches) use (&$image_index, &$updated_count, $force_regenerate): string {
                $image_index++;
                $tag = $matches[0];
                if ($image_index === 0) {
                    return $tag;
                }
                if ($this->image_tag_has_lazy_loading($tag) && !$force_regenerate) {
                    return $tag;
                }

                $updated_count++;
                if (preg_match('/\bloading\s*=\s*(["\']).*?\1/i', $tag) === 1) {
                    return (string) preg_replace('/\bloading\s*=\s*(["\']).*?\1/i', 'loading="lazy"', $tag, 1);
                }
                return (string) preg_replace('/<img\b/i', '<img loading="lazy"', $tag, 1);
            },
            $content
        );

        if (!is_string($updated_content) || $updated_count === 0) {
            return [
                'changed' => false,
                'reason' => 'lazy_loading_generation_failed',
                'content' => $content,
                'updated_count' => 0,
                'missing_before' => $missing_before,
                'missing_after' => $missing_before,
            ];
        }

        $missing_after = 0;
        foreach (array_slice($this->extract_image_tags($updated_content), 1) as $tag) {
            if (!$this->image_tag_has_lazy_loading($tag)) {
                $missing_after++;
            }
        }

        return [
            'changed' => true,
            'reason' => 'lazy_loading_added',
            'content' => $updated_content,
            'updated_count' => $updated_count,
            'missing_before' => $missing_before,
            'missing_after' => $missing_after,
        ];
    }

    private function count_h2_h3_headings(string $content): int
    {
        return preg_match_all('/<h2\b/i', $content) + preg_match_all('/<h3\b/i', $content);
    }

    private function split_content_into_paragraph_blocks(string $content): array
    {
        $parts = preg_split('/(<\/p>)/i', $content, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        if (!is_array($parts)) {
            return $content === '' ? [] : [$content];
        }

        $blocks = [];
        $buffer = '';
        foreach ($parts as $part) {
            $buffer .= $part;
            if (strtolower($part) === '</p>') {
                $blocks[] = $buffer;
                $buffer = '';
            }
        }
        if (trim($buffer) !== '') {
            $blocks[] = $buffer;
        }

        return $blocks;
    }

    private function derive_heading_text_from_block(string $block_html, string $fallback_phrase): string
    {
        $text = wp_strip_all_tags($block_html, true);
        $text = trim((string) preg_replace('/\s+/', ' ', $text));
        if ($text === '') {
            return $this->trim_title_to_max_length($fallback_phrase, 60);
        }

        $words = preg_split('/\s+/', $text);
        if (!is_array($words) || empty($words)) {
            return $this->trim_title_to_max_length($fallback_phrase, 60);
        }

        $seed = trim(implode(' ', array_slice($words, 0, 6)));
        $seed = rtrim($seed, " ,.;:!?\t\n\r\0\x0B");
        if ($seed === '') {
            return $this->trim_title_to_max_length($fallback_phrase, 60);
        }

        return $this->trim_title_to_max_length($seed, 60);
    }

    private function build_heading_texts_via_ai(WP_Post $post, string $content_key, string $site_name, int $needed): array
    {
        if ($content_key === '' || $needed <= 0) {
            return [];
        }

        $result = $this->service_client->request_ai_content_draft(
            $content_key,
            'heading_outline',
            [
                'title' => (string) $post->post_title,
                'existing_content_text' => mb_substr(wp_strip_all_tags((string) $post->post_content), 0, 4000),
                'site_name' => $site_name,
                'post_type' => (string) $post->post_type,
                'heading_count' => $needed,
            ]
        );

        if (!$result['success'] || empty($result['data']['draft_text'])) {
            return [];
        }

        $lines = preg_split('/\r\n|\r|\n/', trim((string) $result['data']['draft_text']));
        if (!is_array($lines)) {
            return [];
        }

        $headings = [];
        foreach ($lines as $line) {
            $line = trim((string) $line);
            // Strip numbering/bullet artifacts the model might still include
            // despite the "no numbering" instruction (e.g. "1. ", "- ", "* ").
            $line = preg_replace('/^[\s\-\*\d\.\)]+/', '', $line);
            $line = trim((string) $line, " \t\n\r\0\x0B\"'");
            if ($line === '') {
                continue;
            }
            $headings[] = $this->trim_title_to_max_length($line, 60);
            if (count($headings) >= $needed) {
                break;
            }
        }

        return $headings;
    }

    private function build_content_with_heading_outline(
        string $content,
        string $title,
        WP_Post $post,
        string $content_key,
        bool $force_regenerate = false
    ): array {
        $normalized_content = (string) $content;
        $existing_heading_count = $this->count_h2_h3_headings($normalized_content);

        if ($existing_heading_count >= 2 && !$force_regenerate) {
            return [
                'changed' => false,
                'reason' => 'heading_structure_already_present',
                'content' => $normalized_content,
                'headings_added' => 0,
            ];
        }

        $needed = max(1, 2 - $existing_heading_count);
        $fallback_phrase = $this->clean_title_phrase($title);
        if ($fallback_phrase === '') {
            $fallback_phrase = __('Overview', 'icap-seo');
        }

        $blocks = $this->split_content_into_paragraph_blocks($normalized_content);
        $paragraph_indexes = [];
        foreach ($blocks as $index => $block) {
            if (preg_match('/^\s*<p\b/i', $block) === 1) {
                $paragraph_indexes[] = $index;
            }
        }

        // Never break before the opening paragraph; space remaining insertions
        // evenly across the rest so we don't wreck the page's existing flow.
        $insert_before = [];
        if (count($paragraph_indexes) >= 2) {
            $eligible = array_slice($paragraph_indexes, 1);
            $count_eligible = count($eligible);
            $slots = min($needed, $count_eligible);
            for ($i = 1; $i <= $slots; $i++) {
                $position = (int) floor(($i * $count_eligible) / ($slots + 1));
                $position = max(0, min($count_eligible - 1, $position));
                $insert_before[$eligible[$position]] = true;
            }
        }

        $site_name = trim((string) get_bloginfo('name'));

        if (empty($insert_before)) {
            $ai_headings = $this->build_heading_texts_via_ai($post, $content_key, $site_name, 1);
            $heading_text = !empty($ai_headings)
                ? array_shift($ai_headings)
                : $this->trim_title_to_max_length($fallback_phrase, 60);
            $heading_html = '<h2>' . esc_html($heading_text) . '</h2>';
            $trimmed = ltrim($normalized_content);
            $updated = $trimmed === '' ? $heading_html : $heading_html . "\n\n" . $normalized_content;

            return [
                'changed' => true,
                'reason' => 'heading_inserted_top_fallback',
                'content' => $updated,
                'headings_added' => 1,
            ];
        }

        $ai_headings = $this->build_heading_texts_via_ai($post, $content_key, $site_name, count($insert_before));

        $headings_added = 0;
        $updated_blocks = [];
        foreach ($blocks as $index => $block) {
            if (isset($insert_before[$index])) {
                $heading_text = !empty($ai_headings)
                    ? array_shift($ai_headings)
                    : $this->derive_heading_text_from_block($block, $fallback_phrase);
                $updated_blocks[] = '<h2>' . esc_html($heading_text) . "</h2>\n";
                $headings_added++;
            }
            $updated_blocks[] = $block;
        }

        return [
            'changed' => true,
            'reason' => 'heading_structure_improved',
            'content' => implode('', $updated_blocks),
            'headings_added' => $headings_added,
        ];
    }

    private function extract_anchor_hrefs(string $content): array
    {
        $hrefs = [];
        if (preg_match_all('/<a\b[^>]*\bhref\s*=\s*"([^"]*)"/i', $content, $matches_double) > 0) {
            $hrefs = array_merge($hrefs, $matches_double[1]);
        }
        if (preg_match_all("/<a\\b[^>]*\\bhref\\s*=\\s*'([^']*)'/i", $content, $matches_single) > 0) {
            $hrefs = array_merge($hrefs, $matches_single[1]);
        }

        $normalized = [];
        foreach ($hrefs as $href) {
            $href = trim((string) $href);
            if (
                $href === ''
                || strpos($href, '#') === 0
                || stripos($href, 'javascript:') === 0
                || stripos($href, 'mailto:') === 0
                || stripos($href, 'tel:') === 0
            ) {
                continue;
            }
            $normalized[] = $href;
        }

        return $normalized;
    }

    private function is_internal_href(string $href, string $site_host): bool
    {
        if ($href === '') {
            return false;
        }
        if ($href[0] === '/') {
            return true;
        }
        $href_host = wp_parse_url($href, PHP_URL_HOST);
        if (!is_string($href_host) || $href_host === '') {
            return true;
        }

        return $site_host !== '' && strcasecmp($href_host, $site_host) === 0;
    }

    private function count_internal_links_in_content(string $content): int
    {
        $site_host = (string) wp_parse_url(home_url('/'), PHP_URL_HOST);
        $count = 0;
        foreach ($this->extract_anchor_hrefs($content) as $href) {
            if ($this->is_internal_href($href, $site_host)) {
                $count++;
            }
        }

        return $count;
    }

    private function get_internal_link_candidates(int $exclude_post_id, int $limit = 8): array
    {
        $posts = get_posts([
            'post_type' => ['post', 'page'],
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            'orderby' => 'modified',
            'order' => 'DESC',
            'exclude' => [$exclude_post_id],
            'no_found_rows' => true,
        ]);

        $candidates = [];
        foreach ($posts as $candidate_post) {
            $title = sanitize_text_field(get_the_title($candidate_post));
            $url = get_permalink($candidate_post);
            if ($title === '' || !is_string($url) || $url === '') {
                continue;
            }
            $candidates[] = ['title' => $title, 'url' => $url];
        }

        return $candidates;
    }

    private function join_link_list_with_and(array $parts): string
    {
        $count = count($parts);
        if ($count === 0) {
            return '';
        }
        if ($count === 1) {
            return $parts[0];
        }
        if ($count === 2) {
            return $parts[0] . ' ' . __('and', 'icap-seo') . ' ' . $parts[1];
        }
        $last = array_pop($parts);

        return implode(', ', $parts) . ', ' . __('and', 'icap-seo') . ' ' . $last;
    }

    private function build_internal_links_via_ai(WP_Post $post, string $content_key, string $site_name, array $candidates): ?array
    {
        if ($content_key === '' || empty($candidates)) {
            return null;
        }

        $candidate_titles = array_map(static function (array $candidate): string {
            return (string) $candidate['title'];
        }, $candidates);

        $result = $this->service_client->request_ai_content_draft(
            $content_key,
            'internal_links',
            [
                'title' => (string) $post->post_title,
                'existing_content_text' => mb_substr(wp_strip_all_tags((string) $post->post_content), 0, 4000),
                'site_name' => $site_name,
                'post_type' => (string) $post->post_type,
                'link_candidates' => $candidate_titles,
            ]
        );

        if (!$result['success'] || empty($result['data']['draft_text'])) {
            return null;
        }

        return $this->parse_internal_links_draft((string) $result['data']['draft_text'], count($candidates));
    }

    /**
     * Parses and validates the model's two-line response, rejecting anything that
     * doesn't cleanly resolve to a subset of the known candidate indexes. The model
     * is never given a real URL, so the only way a bad/hallucinated link could reach
     * the page is via a malformed or out-of-range index here - this is the one place
     * that has to catch that before `{{N}}` is ever substituted into real HTML.
     */
    private function parse_internal_links_draft(string $draft_text, int $candidate_count): ?array
    {
        $lines = preg_split('/\r\n|\r|\n/', trim($draft_text));
        if (!is_array($lines)) {
            return null;
        }

        $selected_indexes = null;
        $sentence = null;
        foreach ($lines as $line) {
            $line = trim((string) $line);
            if ($selected_indexes === null && stripos($line, 'SELECTED:') === 0) {
                $raw = trim(substr($line, strlen('SELECTED:')));
                $parts = array_filter(array_map('trim', explode(',', $raw)), static fn(string $part): bool => $part !== '');
                $selected_indexes = array_values(array_unique(array_map('intval', $parts)));
            } elseif ($sentence === null && stripos($line, 'SENTENCE:') === 0) {
                $sentence = trim(substr($line, strlen('SENTENCE:')));
            }
        }

        if ($selected_indexes === null || $sentence === null || $sentence === '') {
            return null;
        }
        if (count($selected_indexes) < 1 || count($selected_indexes) > 3) {
            return null;
        }
        foreach ($selected_indexes as $index) {
            if ($index < 1 || $index > $candidate_count) {
                return null;
            }
        }

        // No HTML/markdown, and no literal URL/domain the model wasn't given.
        if ($sentence !== wp_strip_all_tags($sentence)) {
            return null;
        }
        if (preg_match('#https?://|www\.#i', $sentence) === 1) {
            return null;
        }
        if (mb_strlen($sentence) > 300) {
            return null;
        }

        preg_match_all('/\{\{\s*(\d+)\s*\}\}/', $sentence, $placeholder_matches);
        $placeholder_indexes = array_values(array_unique(array_map('intval', $placeholder_matches[1])));
        if (empty($placeholder_indexes)) {
            return null;
        }
        sort($placeholder_indexes);
        $sorted_selected = $selected_indexes;
        sort($sorted_selected);
        if ($placeholder_indexes !== $sorted_selected) {
            return null;
        }

        return [
            'sentence_template' => $sentence,
            'selected_indexes' => $selected_indexes,
        ];
    }

    private function build_content_with_internal_links(
        string $content,
        WP_Post $post,
        string $content_key,
        bool $force_regenerate = false
    ): array {
        $normalized_content = (string) $content;
        $existing_internal_count = $this->count_internal_links_in_content($normalized_content);

        if ($existing_internal_count >= 2 && !$force_regenerate) {
            return [
                'changed' => false,
                'reason' => 'internal_linking_already_sufficient',
                'content' => $normalized_content,
                'links_added' => 0,
            ];
        }

        $existing_urls = [];
        foreach ($this->extract_anchor_hrefs($normalized_content) as $href) {
            $existing_urls[trailingslashit($href)] = true;
        }

        $candidates = $this->get_internal_link_candidates($post->ID, 8);
        $selected = [];
        foreach ($candidates as $candidate) {
            if (isset($existing_urls[trailingslashit($candidate['url'])])) {
                continue;
            }
            $selected[] = $candidate;
        }

        if (empty($selected)) {
            return [
                'changed' => false,
                'reason' => 'no_link_candidates_available',
                'content' => $normalized_content,
                'links_added' => 0,
            ];
        }

        $site_name = trim((string) get_bloginfo('name'));
        // Pass the full filtered pool (not just top 3) so the model can choose the
        // most topically relevant candidates instead of the template's recency order.
        $ai_result = $this->build_internal_links_via_ai($post, $content_key, $site_name, $selected);

        if ($ai_result !== null) {
            $block_text = preg_replace_callback(
                '/\{\{\s*(\d+)\s*\}\}/',
                function (array $matches) use ($selected): string {
                    $candidate = $selected[((int) $matches[1]) - 1];

                    return '<a href="' . esc_url($candidate['url']) . '">' . esc_html($candidate['title']) . '</a>';
                },
                $ai_result['sentence_template']
            );

            $block = '<p>' . $block_text . '</p>';
            $trimmed = rtrim($normalized_content);
            $updated = $trimmed === '' ? $block : $trimmed . "\n\n" . $block;

            return [
                'changed' => true,
                'reason' => 'internal_links_added',
                'content' => $updated,
                'links_added' => count($ai_result['selected_indexes']),
            ];
        }

        $template_selected = array_slice($selected, 0, 3);
        $link_html_parts = [];
        foreach ($template_selected as $candidate) {
            $link_html_parts[] = '<a href="' . esc_url($candidate['url']) . '">' . esc_html($candidate['title']) . '</a>';
        }

        $block = '<p>' . sprintf(
            /* translators: %s: comma/and-joined list of linked page titles */
            __('Related: %s.', 'icap-seo'),
            $this->join_link_list_with_and($link_html_parts)
        ) . '</p>';

        $trimmed = rtrim($normalized_content);
        $updated = $trimmed === '' ? $block : $trimmed . "\n\n" . $block;

        return [
            'changed' => true,
            'reason' => 'internal_links_added',
            'content' => $updated,
            'links_added' => count($template_selected),
        ];
    }

    private function count_paragraph_tags(string $content): int
    {
        return preg_match_all('/<p\b/i', $content);
    }

    private function split_text_into_sentences(string $text): array
    {
        $sentences = preg_split('/(?<=[.!?])\s+/', trim($text));
        if (!is_array($sentences)) {
            return [];
        }

        return array_values(array_filter(
            array_map('trim', $sentences),
            static fn($sentence): bool => $sentence !== ''
        ));
    }

    private function paragraph_block_inner_html(string $block): string
    {
        if (preg_match('/^\s*<p\b[^>]*>(.*)<\/p>\s*$/is', $block, $matches) === 1) {
            return $matches[1];
        }

        return $block;
    }

    private function is_plain_text_html(string $html): bool
    {
        return preg_match('/<[a-z][^>]*>/i', $html) !== 1;
    }

    private function build_readability_paragraphs_via_ai(string $content_key, array $paragraph_texts): array
    {
        if ($content_key === '' || empty($paragraph_texts)) {
            return [];
        }

        $result = $this->service_client->request_ai_content_draft(
            $content_key,
            'readability_rewrite',
            [
                'paragraphs' => $paragraph_texts,
            ]
        );

        if (!$result['success'] || empty($result['data']['draft_text'])) {
            return [];
        }

        // Not trim()-ing the raw response before splitting, same reasoning as
        // image_alt_text: a leading blank line is a real empty slot, not noise to
        // discard, and trimming it away would shift every later rewrite by one.
        $lines = preg_split('/\r\n|\r|\n/', (string) $result['data']['draft_text']);
        if (!is_array($lines)) {
            return [];
        }

        $rewrites = [];
        foreach ($lines as $line) {
            $line = trim((string) $line);
            $line = preg_replace('/^[\s\-\*\d\.\)]+/', '', $line);
            $rewrites[] = trim((string) $line, " \t\n\r\0\x0B\"'");
        }

        return $rewrites;
    }

    /**
     * Selects up to READABILITY_MAX_PARAGRAPHS eligible plain-text paragraphs
     * (never touching headings, images, lists, or any paragraph containing links/
     * emphasis - same is_plain_text_html() boundary the paragraph-expansion feature
     * already uses), prioritizing the longest ones since they weigh most heavily on
     * the page's whole-page Flesch reading-ease score. Each AI rewrite is validated
     * before being included in the draft: any HTML/markdown, empty result, or a
     * suspicious length ratio (the model truncating or wildly padding the original)
     * causes that specific paragraph to be silently left out of the draft rather
     * than risk losing real content.
     */
    private function build_readability_rewrite_draft(WP_Post $post, string $content_key): array
    {
        $blocks = $this->split_content_into_paragraph_blocks((string) $post->post_content);

        $eligible = [];
        foreach ($blocks as $block_index => $block) {
            if (preg_match('/^\s*<p\b/i', $block) !== 1) {
                continue;
            }
            $inner_html = $this->paragraph_block_inner_html($block);
            if (!$this->is_plain_text_html($inner_html)) {
                continue;
            }
            $text = trim(wp_strip_all_tags($inner_html));
            if ($text === '') {
                continue;
            }
            $eligible[$block_index] = $text;
        }

        if (empty($eligible)) {
            return ['paragraphs' => []];
        }

        $ranked = $eligible;
        uasort($ranked, fn(string $a, string $b): int => $this->count_words_in_html($b) <=> $this->count_words_in_html($a));
        $selected_indexes = array_slice(array_keys($ranked), 0, self::READABILITY_MAX_PARAGRAPHS);

        $selected_texts = [];
        foreach ($selected_indexes as $block_index) {
            $selected_texts[] = $eligible[$block_index];
        }

        $rewrites = $this->build_readability_paragraphs_via_ai($content_key, $selected_texts);
        if (empty($rewrites)) {
            return ['paragraphs' => []];
        }

        $draft_paragraphs = [];
        foreach ($selected_indexes as $position => $block_index) {
            $original_text = $selected_texts[$position];
            $rewritten_text = $rewrites[$position] ?? '';

            if ($rewritten_text === '' || $rewritten_text !== wp_strip_all_tags($rewritten_text)) {
                continue;
            }
            $original_word_count = $this->count_words_in_html($original_text);
            if ($original_word_count > 0) {
                $ratio = $this->count_words_in_html($rewritten_text) / $original_word_count;
                if ($ratio < 0.4 || $ratio > 2.5) {
                    continue;
                }
            }
            if (strcasecmp($original_text, $rewritten_text) === 0) {
                continue;
            }

            $draft_paragraphs[] = [
                'block_index' => $block_index,
                'original_text' => $original_text,
                'rewritten_text' => $rewritten_text,
            ];
        }

        return ['paragraphs' => $draft_paragraphs];
    }

    private function get_readability_draft_for_post(int $post_id): array
    {
        $raw = get_post_meta($post_id, self::READABILITY_DRAFT_META_KEY, true);
        if (!is_string($raw) || trim($raw) === '') {
            return [];
        }
        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function build_content_with_expanded_paragraphs(string $content, bool $force_regenerate = false): array
    {
        $normalized_content = (string) $content;
        $existing_count = $this->count_paragraph_tags($normalized_content);

        if ($existing_count >= 3 && !$force_regenerate) {
            return [
                'changed' => false,
                'reason' => 'paragraph_structure_already_sufficient',
                'content' => $normalized_content,
                'paragraphs_added' => 0,
            ];
        }

        $blocks = $this->split_content_into_paragraph_blocks($normalized_content);
        $has_paragraph_blocks = false;
        foreach ($blocks as $block) {
            if (preg_match('/^\s*<p\b/i', $block) === 1) {
                $has_paragraph_blocks = true;
                break;
            }
        }

        if (!$has_paragraph_blocks) {
            // No <p> tags at all. Only safe to restructure if the content has no other
            // HTML markup either — otherwise stripping tags to re-wrap risks destroying
            // existing formatting (links, emphasis, etc.).
            if (!$this->is_plain_text_html($normalized_content)) {
                return [
                    'changed' => false,
                    'reason' => 'insufficient_plain_content_for_paragraph_split',
                    'content' => $normalized_content,
                    'paragraphs_added' => 0,
                ];
            }

            $visible_text = trim((string) preg_replace('/\s+/', ' ', $normalized_content));
            $sentences = $this->split_text_into_sentences($visible_text);
            if (count($sentences) < 3) {
                return [
                    'changed' => false,
                    'reason' => 'insufficient_plain_content_for_paragraph_split',
                    'content' => $normalized_content,
                    'paragraphs_added' => 0,
                ];
            }

            $groups = [$sentences];
            $safety_counter = 0;
            while (count($groups) < 3 && $safety_counter < 10) {
                $safety_counter++;
                $longest_group_index = null;
                $longest_group_size = 0;
                foreach ($groups as $index => $group) {
                    if (count($group) > $longest_group_size && count($group) >= 2) {
                        $longest_group_size = count($group);
                        $longest_group_index = $index;
                    }
                }
                if ($longest_group_index === null) {
                    break;
                }
                $group = $groups[$longest_group_index];
                $midpoint = (int) ceil(count($group) / 2);
                $first_half = array_slice($group, 0, $midpoint);
                $second_half = array_slice($group, $midpoint);
                if (empty($first_half) || empty($second_half)) {
                    break;
                }
                array_splice($groups, $longest_group_index, 1, [$first_half, $second_half]);
            }

            if (count($groups) < 3) {
                return [
                    'changed' => false,
                    'reason' => 'insufficient_plain_content_for_paragraph_split',
                    'content' => $normalized_content,
                    'paragraphs_added' => 0,
                ];
            }

            $new_blocks = [];
            foreach ($groups as $group) {
                $new_blocks[] = '<p>' . esc_html(implode(' ', $group)) . '</p>';
            }

            return [
                'changed' => true,
                'reason' => 'paragraph_structure_wrapped',
                'content' => implode("\n", $new_blocks),
                'paragraphs_added' => max(0, count($new_blocks) - 1),
            ];
        }

        // Has some <p> blocks but too few. Split the longest plain-text (no nested
        // markup) paragraph at a sentence boundary; skip any paragraph containing
        // links/emphasis/etc. so we never lose existing formatting.
        $needed = 3 - $existing_count;
        $updated_blocks = $blocks;
        $paragraphs_added = 0;
        $safety_counter = 0;

        while ($paragraphs_added < $needed && $safety_counter < 10) {
            $safety_counter++;
            $longest_index = null;
            $longest_sentence_count = 0;

            foreach ($updated_blocks as $index => $block) {
                if (preg_match('/^\s*<p\b/i', $block) !== 1) {
                    continue;
                }
                $inner_html = $this->paragraph_block_inner_html($block);
                if (!$this->is_plain_text_html($inner_html)) {
                    continue;
                }
                $text = trim(wp_strip_all_tags($inner_html, true));
                $sentence_count = count($this->split_text_into_sentences($text));
                if ($sentence_count > $longest_sentence_count && $sentence_count >= 2) {
                    $longest_sentence_count = $sentence_count;
                    $longest_index = $index;
                }
            }

            if ($longest_index === null) {
                break;
            }

            $text = trim(wp_strip_all_tags($this->paragraph_block_inner_html($updated_blocks[$longest_index]), true));
            $sentences = $this->split_text_into_sentences($text);
            $midpoint = (int) ceil(count($sentences) / 2);
            $first_half = array_slice($sentences, 0, $midpoint);
            $second_half = array_slice($sentences, $midpoint);
            if (empty($first_half) || empty($second_half)) {
                break;
            }

            $split_blocks = [
                '<p>' . esc_html(implode(' ', $first_half)) . '</p>',
                '<p>' . esc_html(implode(' ', $second_half)) . '</p>',
            ];
            array_splice($updated_blocks, $longest_index, 1, $split_blocks);
            $paragraphs_added++;
        }

        if ($paragraphs_added === 0) {
            return [
                'changed' => false,
                'reason' => 'insufficient_plain_content_for_paragraph_split',
                'content' => $normalized_content,
                'paragraphs_added' => 0,
            ];
        }

        return [
            'changed' => true,
            'reason' => 'paragraph_structure_expanded',
            'content' => implode('', $updated_blocks),
            'paragraphs_added' => $paragraphs_added,
        ];
    }

    private function get_current_canonical_url_for_post(WP_Post $post): string
    {
        $stored = get_post_meta((int) $post->ID, self::CANONICAL_URL_META_KEY, true);
        if (is_string($stored) && trim($stored) !== '') {
            return esc_url_raw(trim($stored));
        }
        return '';
    }

    private function build_canonical_url_for_post(WP_Post $post): string
    {
        $permalink = get_permalink($post);
        if (!is_string($permalink) || trim($permalink) === '') {
            return '';
        }
        return esc_url_raw(trim($permalink));
    }

    private function count_words_in_html(string $html): int
    {
        $text = wp_strip_all_tags($html, true);
        $text = trim((string) preg_replace('/\s+/', ' ', $text));
        if ($text === '') {
            return 0;
        }
        $words = preg_split('/\s+/', $text);
        return is_array($words) ? count($words) : 0;
    }

    private function get_content_depth_draft_for_post(int $post_id): array
    {
        $html = get_post_meta($post_id, self::CONTENT_DEPTH_DRAFT_META_KEY, true);
        $word_count = get_post_meta($post_id, self::CONTENT_DEPTH_DRAFT_WORD_COUNT_META_KEY, true);

        return [
            'html' => is_string($html) ? $html : '',
            'word_count' => is_numeric($word_count) ? (int) $word_count : 0,
        ];
    }

    private function build_content_depth_paragraph_templates(string $title_phrase, string $site_name, string $post_type): array
    {
        $brand = $site_name !== '' ? $site_name : __('this site', 'icap-seo');
        $kind = $post_type === 'post' ? __('article', 'icap-seo') : __('page', 'icap-seo');

        return [
            sprintf(
                __('This %1$s covers %2$s in more depth, walking through the details visitors most often look for before deciding what to do next. %3$s puts an emphasis on clear, practical information rather than vague marketing language, so the goal here is to answer real questions plainly.', 'icap-seo'),
                $kind,
                $title_phrase,
                $brand
            ),
            sprintf(
                __('A closer look at %1$s shows why it matters: the specifics, the context, and the reasons someone would care are all worth spelling out rather than assuming they are obvious. Filling in that context here helps both readers and search engines understand what this %2$s is actually about.', 'icap-seo'),
                $title_phrase,
                $kind
            ),
            sprintf(
                __('Visitors coming to this %1$s from %2$s can expect grounded, relevant detail tailored to %3$s specifically, not generic filler that could apply to any page on the internet. That focus is what makes the difference between a thin page and one that genuinely helps people.', 'icap-seo'),
                $kind,
                $brand,
                $title_phrase
            ),
            sprintf(
                __('This draft paragraph is a starting point, not a finished version — replace it with real detail about %1$s, add specifics only %2$s would know, and remove anything that still reads as generic before publishing.', 'icap-seo'),
                $title_phrase,
                $brand
            ),
        ];
    }

    private function build_content_depth_draft(WP_Post $post, string $content_key = ''): array
    {
        $current_word_count = $this->count_words_in_html((string) $post->post_content);
        if ($current_word_count >= self::CONTENT_DEPTH_TARGET_WORD_COUNT) {
            return ['html' => '', 'draft_word_count' => 0, 'current_word_count' => $current_word_count];
        }

        $site_name = trim((string) get_bloginfo('name'));

        // AI generation (Phase A, 2026-08-28) is tried first and falls back to the
        // template generator below on any failure - not configured, site isn't premium,
        // network error, empty model response. Keeps content-depth remediation working
        // even before/without the backend AI endpoint being live for a given environment.
        $ai_html = $this->build_content_depth_draft_via_ai($post, $content_key, $site_name);
        if ($ai_html !== null) {
            return [
                'html' => $ai_html,
                'draft_word_count' => $this->count_words_in_html($ai_html),
                'current_word_count' => $current_word_count,
            ];
        }

        $title_phrase = $this->clean_title_phrase((string) $post->post_title);
        if ($title_phrase === '') {
            $title_phrase = __('this page', 'icap-seo');
        }

        $paragraphs = $this->build_content_depth_paragraph_templates($title_phrase, $site_name, (string) $post->post_type);
        $html_paragraphs = array_map(
            static fn(string $text): string => '<p>' . esc_html($text) . '</p>',
            $paragraphs
        );
        $html = implode("\n", $html_paragraphs);

        return [
            'html' => $html,
            'draft_word_count' => $this->count_words_in_html($html),
            'current_word_count' => $current_word_count,
        ];
    }

    private function build_content_depth_draft_via_ai(WP_Post $post, string $content_key, string $site_name): ?string
    {
        if ($content_key === '') {
            return null;
        }

        $target_word_count = max(
            self::CONTENT_DEPTH_TARGET_WORD_COUNT - $this->count_words_in_html((string) $post->post_content),
            150
        );

        $result = $this->service_client->request_ai_content_draft(
            $content_key,
            'content_depth',
            [
                'title' => (string) $post->post_title,
                'existing_content_text' => mb_substr(wp_strip_all_tags((string) $post->post_content), 0, 4000),
                'site_name' => $site_name,
                'post_type' => (string) $post->post_type,
                'target_word_count' => $target_word_count,
            ]
        );

        if (!$result['success'] || empty($result['data']['draft_text'])) {
            return null;
        }

        $paragraphs = preg_split('/\n{2,}/', trim((string) $result['data']['draft_text']));
        if (!is_array($paragraphs)) {
            return null;
        }
        $paragraphs = array_filter($paragraphs, static fn(string $text): bool => trim($text) !== '');
        if (empty($paragraphs)) {
            return null;
        }

        $html_paragraphs = array_map(
            static fn(string $text): string => '<p>' . esc_html(trim($text)) . '</p>',
            $paragraphs
        );

        return implode("\n", $html_paragraphs);
    }

    private function get_current_jsonld_schema_for_post(WP_Post $post): array
    {
        $type = get_post_meta((int) $post->ID, self::JSONLD_SCHEMA_TYPE_META_KEY, true);
        $json = get_post_meta((int) $post->ID, self::JSONLD_SCHEMA_JSON_META_KEY, true);
        return [
            'type' => is_string($type) ? $type : '',
            'json' => is_string($json) ? $json : '',
        ];
    }

    /**
     * Mechanically detects Q&A pairs already visible on the page: an h2-h4 heading
     * whose text ends in "?", followed by the paragraph text before the next
     * heading. No AI involved - this is unambiguous from the markup alone, and the
     * extracted question/answer text is used verbatim, so there's no fabrication
     * risk. Requires at least 2 pairs to qualify as genuinely FAQ-shaped content.
     */
    private function detect_faq_pairs_in_content(string $content): array
    {
        if (preg_match_all('/<(h[2-4])\b[^>]*>(.*?)<\/\1>|<p\b[^>]*>(.*?)<\/p>/is', $content, $matches, PREG_SET_ORDER) === false) {
            return [];
        }

        $pairs = [];
        $pending_question = '';
        $pending_answer_parts = [];
        foreach ($matches as $match) {
            $is_heading = $match[1] !== '';
            if ($is_heading) {
                if ($pending_question !== '' && !empty($pending_answer_parts)) {
                    $pairs[] = ['question' => $pending_question, 'answer' => trim(implode(' ', $pending_answer_parts))];
                }
                $pending_answer_parts = [];
                $heading_text = trim(wp_strip_all_tags($match[2]));
                $pending_question = (substr($heading_text, -1) === '?') ? $heading_text : '';
            } elseif ($pending_question !== '') {
                $paragraph_text = trim(wp_strip_all_tags($match[3]));
                if ($paragraph_text !== '') {
                    $pending_answer_parts[] = $paragraph_text;
                }
            }
        }
        if ($pending_question !== '' && !empty($pending_answer_parts)) {
            $pairs[] = ['question' => $pending_question, 'answer' => trim(implode(' ', $pending_answer_parts))];
        }

        return count($pairs) >= 2 ? $pairs : [];
    }

    /**
     * Mechanically extracts <li> text (verbatim) from the first <ol> with at least
     * 2 items. A numbered list isn't always sequential how-to steps (it could be a
     * ranked list, examples, etc.), so finding a candidate list here doesn't by
     * itself mean HowTo applies - see classify_howto_steps_via_ai().
     */
    private function detect_howto_candidate_steps_in_content(string $content): array
    {
        if (preg_match('/<ol\b[^>]*>(.*?)<\/ol>/is', $content, $list_match) !== 1) {
            return [];
        }
        if (preg_match_all('/<li\b[^>]*>(.*?)<\/li>/is', $list_match[1], $item_matches) === false) {
            return [];
        }

        $steps = [];
        foreach ($item_matches[1] as $item_html) {
            $text = trim(wp_strip_all_tags($item_html));
            if ($text !== '') {
                $steps[] = $text;
            }
        }

        return count($steps) >= 2 ? $steps : [];
    }

    /**
     * The only AI call in the schema-type decision, and its role is strictly
     * classification - it never generates or alters step text, only answers
     * whether the already-real, verbatim-extracted list items are genuinely
     * sequential steps. Defaults to false (no HowTo) on any failure or ambiguity,
     * since a false negative here just misses an opportunity while a false
     * positive would be inaccurate structured data.
     */
    private function classify_howto_steps_via_ai(string $content_key, string $title, array $steps): bool
    {
        if ($content_key === '' || empty($steps)) {
            return false;
        }

        $result = $this->service_client->request_ai_content_draft(
            $content_key,
            'howto_step_classification',
            [
                'title' => $title,
                'paragraphs' => $steps,
            ]
        );

        if (!$result['success'] || empty($result['data']['draft_text'])) {
            return false;
        }

        $answer = strtoupper(trim(wp_strip_all_tags((string) $result['data']['draft_text'])));

        return strpos($answer, 'YES') === 0;
    }

    private function build_jsonld_schema_for_post(WP_Post $post, string $content_key = ''): array
    {
        $title = sanitize_text_field((string) $post->post_title);
        if ($title === '') {
            return ['type' => '', 'json' => ''];
        }

        $permalink = get_permalink($post);
        $permalink = is_string($permalink) ? $permalink : '';
        $site_name = trim((string) get_bloginfo('name'));
        $content = (string) $post->post_content;

        $faq_pairs = $this->detect_faq_pairs_in_content($content);
        if (!empty($faq_pairs)) {
            $schema = [
                '@context' => 'https://schema.org',
                '@type' => 'FAQPage',
                'url' => $permalink,
                'mainEntity' => array_map(
                    static fn(array $pair): array => [
                        '@type' => 'Question',
                        'name' => $pair['question'],
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => $pair['answer'],
                        ],
                    ],
                    $faq_pairs
                ),
            ];

            $json = wp_json_encode($schema);

            return is_string($json) && $json !== '' ? ['type' => 'FAQPage', 'json' => $json] : ['type' => '', 'json' => ''];
        }

        $howto_steps = $this->detect_howto_candidate_steps_in_content($content);
        if (!empty($howto_steps) && $this->classify_howto_steps_via_ai($content_key, $title, $howto_steps)) {
            $schema = [
                '@context' => 'https://schema.org',
                '@type' => 'HowTo',
                'url' => $permalink,
                'name' => $title,
                'step' => array_map(
                    static fn(string $step_text): array => [
                        '@type' => 'HowToStep',
                        'text' => $step_text,
                    ],
                    $howto_steps
                ),
            ];

            $json = wp_json_encode($schema);

            return is_string($json) && $json !== '' ? ['type' => 'HowTo', 'json' => $json] : ['type' => '', 'json' => ''];
        }

        $schema_type = $post->post_type === 'post' ? 'Article' : 'WebPage';
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => $schema_type,
            'url' => $permalink,
        ];

        if ($schema_type === 'Article') {
            $schema['headline'] = $this->trim_title_to_max_length($title, 110);
            $schema['datePublished'] = get_the_date('c', $post);
            $schema['dateModified'] = get_the_modified_date('c', $post);
            $author_name = trim((string) get_the_author_meta('display_name', (int) $post->post_author));
            if ($author_name === '') {
                $author_name = $site_name;
            }
            if ($author_name !== '') {
                $schema['author'] = [
                    '@type' => 'Person',
                    'name' => $author_name,
                ];
            }
        } else {
            $schema['name'] = $title;
        }

        $description = $this->get_current_meta_description_for_post($post);
        if ($description !== '') {
            $schema['description'] = $description;
        }

        $thumbnail_id = get_post_thumbnail_id($post);
        if ($thumbnail_id) {
            $image_url = wp_get_attachment_image_url($thumbnail_id, 'full');
            if (is_string($image_url) && $image_url !== '') {
                $schema['image'] = $image_url;
            }
        }

        if ($site_name !== '') {
            $schema['publisher'] = [
                '@type' => 'Organization',
                'name' => $site_name,
            ];
        }

        $json = wp_json_encode($schema);
        if (!is_string($json) || $json === '') {
            return ['type' => '', 'json' => ''];
        }

        return ['type' => $schema_type, 'json' => $json];
    }

    private function get_current_meta_description_for_post(WP_Post $post): string
    {
        $stored = get_post_meta((int) $post->ID, self::META_DESCRIPTION_META_KEY, true);
        if (is_string($stored) && trim($stored) !== '') {
            return trim((string) preg_replace('/\s+/', ' ', sanitize_text_field($stored)));
        }

        $excerpt = trim((string) preg_replace('/\s+/', ' ', sanitize_text_field((string) $post->post_excerpt)));
        return $excerpt;
    }

    private function get_applied_issue_codes_for_post(int $post_id): array
    {
        $stored = get_post_meta($post_id, self::APPLIED_ISSUE_CODES_META_KEY, true);
        if (!is_array($stored)) {
            // Fallback: derive from successful history entries for installs that applied before this meta existed.
            $history = $this->get_remediation_history_for_post($post_id);
            $derived = [];
            foreach ($history as $entry) {
                if (
                    empty($entry['title_changed'])
                    && empty($entry['excerpt_changed'])
                    && empty($entry['h1_changed'])
                ) {
                    continue;
                }
                if (!isset($entry['issue_codes']) || !is_array($entry['issue_codes'])) {
                    continue;
                }
                foreach ($entry['issue_codes'] as $code) {
                    $normalized = sanitize_key((string) $code);
                    if ($normalized !== '') {
                        $derived[$normalized] = true;
                    }
                }
            }
            return array_keys($derived);
        }

        $normalized = [];
        foreach ($stored as $code) {
            $value = sanitize_key((string) $code);
            if ($value !== '') {
                $normalized[$value] = true;
            }
        }

        return array_keys($normalized);
    }

    private function mark_issue_codes_applied(int $post_id, array $issue_codes): void
    {
        $existing = $this->get_applied_issue_codes_for_post($post_id);
        $merged = [];
        foreach (array_merge($existing, $issue_codes) as $code) {
            $normalized = sanitize_key((string) $code);
            if ($normalized !== '') {
                $merged[$normalized] = true;
            }
        }
        update_post_meta($post_id, self::APPLIED_ISSUE_CODES_META_KEY, array_keys($merged));
    }

    private function clear_applied_issue_codes_for_post(int $post_id): void
    {
        if ($post_id <= 0) {
            return;
        }
        delete_post_meta($post_id, self::APPLIED_ISSUE_CODES_META_KEY);
    }

    private function extract_issue_codes_from_detail(array $detail): array
    {
        $issues = isset($detail['issues']) && is_array($detail['issues']) ? $detail['issues'] : [];
        $codes = [];
        foreach ($issues as $issue) {
            if (!is_array($issue)) {
                continue;
            }
            $code = isset($issue['issue_code']) ? sanitize_key((string) $issue['issue_code']) : '';
            if ($code !== '') {
                $codes[] = $code;
            }
        }

        return array_values(array_unique($codes));
    }

    private function filter_open_issue_codes(array $issue_codes, array $applied_issue_codes): array
    {
        $applied_lookup = [];
        foreach ($applied_issue_codes as $code) {
            $normalized = sanitize_key((string) $code);
            if ($normalized !== '') {
                $applied_lookup[$normalized] = true;
            }
        }

        $open = [];
        foreach ($issue_codes as $code) {
            $normalized = sanitize_key((string) $code);
            if ($normalized === '' || isset($applied_lookup[$normalized])) {
                continue;
            }
            $open[] = $normalized;
        }

        return array_values(array_unique($open));
    }

    /**
     * Canonical catalog of every issue_code the backend can emit, kept in sync with
     * docs/seo-checks-catalog.md. `premium` mirrors the backend's tier gating: only the
     * on-page audit runs on the basic tier, so every other layer is premium-only as a whole.
     */
    private function get_seo_recommendation_catalog(): array
    {
        return [
            // Baseline on-page audit (seo-page) — free tier
            ['issue_code' => 'missing_title_tag', 'label' => __('Page has a title tag', 'icap-seo'), 'layer' => __('Baseline on-page audit', 'icap-seo'), 'premium' => false, 'apply_type' => 'auto'],
            ['issue_code' => 'title_length_out_of_range', 'label' => __('Title length is optimized (20–65 characters)', 'icap-seo'), 'layer' => __('Baseline on-page audit', 'icap-seo'), 'premium' => false, 'apply_type' => 'auto', 'preempted_by' => ['missing_title_tag']],
            ['issue_code' => 'missing_meta_description', 'label' => __('Page has a meta description', 'icap-seo'), 'layer' => __('Baseline on-page audit', 'icap-seo'), 'premium' => false, 'apply_type' => 'auto'],
            ['issue_code' => 'meta_description_length_out_of_range', 'label' => __('Meta description length is optimized (120–170 characters)', 'icap-seo'), 'layer' => __('Baseline on-page audit', 'icap-seo'), 'premium' => false, 'apply_type' => 'auto', 'preempted_by' => ['missing_meta_description']],
            ['issue_code' => 'missing_h1', 'label' => __('Page has an H1 heading', 'icap-seo'), 'layer' => __('Baseline on-page audit', 'icap-seo'), 'premium' => false, 'apply_type' => 'auto'],
            ['issue_code' => 'thin_content', 'label' => __('Page has enough visible content (250+ words)', 'icap-seo'), 'layer' => __('Baseline on-page audit', 'icap-seo'), 'premium' => false, 'apply_type' => 'preview'],
            // Technical (seo-technical) — premium
            ['issue_code' => 'non_https_url', 'label' => __('Page is served over HTTPS', 'icap-seo'), 'layer' => __('Robots and crawler policy / security headers', 'icap-seo'), 'premium' => true, 'apply_type' => 'guidance'],
            ['issue_code' => 'content_fetch_unavailable', 'label' => __('Page is reachable for scanning', 'icap-seo'), 'layer' => __('Robots and crawler policy / security headers', 'icap-seo'), 'premium' => true, 'apply_type' => 'guidance'],
            ['issue_code' => 'missing_canonical', 'label' => __('Page has a canonical URL', 'icap-seo'), 'layer' => __('Robots and crawler policy / security headers', 'icap-seo'), 'premium' => true, 'apply_type' => 'auto', 'preempted_by' => ['content_fetch_unavailable']],
            ['issue_code' => 'noindex_detected', 'label' => __('Page is not accidentally excluded from search (noindex)', 'icap-seo'), 'layer' => __('Robots and crawler policy / security headers', 'icap-seo'), 'premium' => true, 'apply_type' => 'guidance', 'preempted_by' => ['content_fetch_unavailable']],
            ['issue_code' => 'missing_security_headers', 'label' => __('Server sends recommended security headers', 'icap-seo'), 'layer' => __('Robots and crawler policy / security headers', 'icap-seo'), 'premium' => true, 'apply_type' => 'guidance', 'preempted_by' => ['content_fetch_unavailable']],
            ['issue_code' => 'robots_txt_missing', 'label' => __('Site has a robots.txt file', 'icap-seo'), 'layer' => __('Robots and crawler policy / security headers', 'icap-seo'), 'premium' => true, 'apply_type' => 'guidance'],
            ['issue_code' => 'robots_txt_blocks_page', 'label' => __('robots.txt does not block this page', 'icap-seo'), 'layer' => __('Robots and crawler policy / security headers', 'icap-seo'), 'premium' => true, 'apply_type' => 'guidance', 'preempted_by' => ['robots_txt_missing']],
            // Content quality (seo-content) — premium
            ['issue_code' => 'no_visible_content', 'label' => __('Page has visible content', 'icap-seo'), 'layer' => __('Content quality and readability', 'icap-seo'), 'premium' => true, 'apply_type' => 'preview'],
            ['issue_code' => 'insufficient_content_depth', 'label' => __('Content depth meets baseline (300+ words)', 'icap-seo'), 'layer' => __('Content quality and readability', 'icap-seo'), 'premium' => true, 'apply_type' => 'preview', 'preempted_by' => ['no_visible_content']],
            ['issue_code' => 'content_depth_improvement', 'label' => __('Content depth is competitive (600+ words)', 'icap-seo'), 'layer' => __('Content quality and readability', 'icap-seo'), 'premium' => true, 'apply_type' => 'preview', 'preempted_by' => ['no_visible_content', 'insufficient_content_depth']],
            ['issue_code' => 'limited_heading_structure', 'label' => __('Page has enough secondary headings (H2/H3)', 'icap-seo'), 'layer' => __('Content quality and readability', 'icap-seo'), 'premium' => true, 'apply_type' => 'auto'],
            ['issue_code' => 'limited_paragraph_structure', 'label' => __('Page has enough paragraph structure', 'icap-seo'), 'layer' => __('Content quality and readability', 'icap-seo'), 'premium' => true, 'apply_type' => 'auto'],
            ['issue_code' => 'readability_score_low', 'label' => __('Content is written in plain, easy-to-read language', 'icap-seo'), 'layer' => __('Content quality and readability', 'icap-seo'), 'premium' => true, 'apply_type' => 'preview', 'preempted_by' => ['no_visible_content', 'insufficient_content_depth']],
            // Structured data (seo-schema) — premium
            ['issue_code' => 'missing_jsonld_schema', 'label' => __('Page has JSON-LD structured data', 'icap-seo'), 'layer' => __('Structured data schema', 'icap-seo'), 'premium' => true, 'apply_type' => 'auto'],
            ['issue_code' => 'schema_type_missing', 'label' => __('Structured data includes a valid @type', 'icap-seo'), 'layer' => __('Structured data schema', 'icap-seo'), 'premium' => true, 'apply_type' => 'auto', 'preempted_by' => ['missing_jsonld_schema']],
            ['issue_code' => 'schema_missing_required_properties', 'label' => __('Structured data includes required properties for its type', 'icap-seo'), 'layer' => __('Structured data schema', 'icap-seo'), 'premium' => true, 'apply_type' => 'auto', 'preempted_by' => ['missing_jsonld_schema', 'schema_type_missing']],
            // Images (seo-images) — premium
            ['issue_code' => 'no_images_detected', 'label' => __('Page includes relevant images', 'icap-seo'), 'layer' => __('Image optimization', 'icap-seo'), 'premium' => true, 'apply_type' => 'guidance'],
            ['issue_code' => 'images_missing_alt', 'label' => __('Images have descriptive alt text', 'icap-seo'), 'layer' => __('Image optimization', 'icap-seo'), 'premium' => true, 'apply_type' => 'auto', 'preempted_by' => ['no_images_detected']],
            ['issue_code' => 'images_missing_dimensions', 'label' => __('Images have explicit width/height attributes', 'icap-seo'), 'layer' => __('Image optimization', 'icap-seo'), 'premium' => true, 'apply_type' => 'auto', 'preempted_by' => ['no_images_detected']],
            ['issue_code' => 'images_not_lazy_loaded', 'label' => __('Below-the-fold images use lazy loading', 'icap-seo'), 'layer' => __('Image optimization', 'icap-seo'), 'premium' => true, 'apply_type' => 'auto', 'preempted_by' => ['no_images_detected']],
            // Links (seo-links) — premium
            ['issue_code' => 'no_links_detected', 'label' => __('Page includes crawlable links', 'icap-seo'), 'layer' => __('Internal and broken links', 'icap-seo'), 'premium' => true, 'apply_type' => 'auto'],
            ['issue_code' => 'low_internal_linking', 'label' => __('Page links to enough related content', 'icap-seo'), 'layer' => __('Internal and broken links', 'icap-seo'), 'premium' => true, 'apply_type' => 'auto', 'preempted_by' => ['no_links_detected']],
            ['issue_code' => 'no_external_references', 'label' => __('Page cites at least one external source', 'icap-seo'), 'layer' => __('Internal and broken links', 'icap-seo'), 'premium' => true, 'apply_type' => 'guidance', 'preempted_by' => ['no_links_detected']],
            ['issue_code' => 'broken_internal_link_detected', 'label' => __('Internal links resolve without errors', 'icap-seo'), 'layer' => __('Internal and broken links', 'icap-seo'), 'premium' => true, 'apply_type' => 'preview', 'preempted_by' => ['no_links_detected']],
            ['issue_code' => 'broken_external_link_detected', 'label' => __('External references resolve without errors', 'icap-seo'), 'layer' => __('Internal and broken links', 'icap-seo'), 'premium' => true, 'apply_type' => 'guidance', 'preempted_by' => ['no_links_detected']],
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

    private function build_page_title_via_ai(WP_Post $post, string $content_key, string $site_name): ?string
    {
        if ($content_key === '') {
            return null;
        }

        $result = $this->service_client->request_ai_content_draft(
            $content_key,
            'page_title',
            [
                'existing_content_text' => mb_substr(wp_strip_all_tags((string) $post->post_content), 0, 4000),
                'site_name' => $site_name,
                'post_type' => (string) $post->post_type,
            ]
        );

        if (!$result['success'] || empty($result['data']['draft_text'])) {
            return null;
        }

        $candidate = trim((string) $result['data']['draft_text'], " \t\n\r\0\x0B\"'");
        $candidate = trim((string) preg_replace('/\s+/', ' ', $candidate));

        return $candidate !== '' ? $candidate : null;
    }

    private function build_title_for_empty_post(WP_Post $post, string $content_key): string
    {
        $site_name = trim((string) get_bloginfo('name'));
        $ai_seed = $this->build_page_title_via_ai($post, $content_key, $site_name);
        if ($ai_seed !== null) {
            $ai_candidate = $this->build_seo_title_with_target_length($ai_seed);
            if ($ai_candidate !== '') {
                return $ai_candidate;
            }
        }

        $content_text = trim(wp_strip_all_tags((string) $post->post_content, true));
        $content_text = trim((string) preg_replace('/\s+/', ' ', $content_text));
        $seed = '';
        if ($content_text !== '') {
            $words = preg_split('/\s+/', $content_text);
            if (is_array($words) && !empty($words)) {
                $seed = trim(implode(' ', array_slice($words, 0, 8)));
                $seed = rtrim($seed, " ,.;:!?\t\n\r\0\x0B");
            }
        }

        if ($seed === '') {
            $site_title_phrase = $this->build_site_title_phrase();
            $post_type_label = (string) $post->post_type === 'post' ? __('New Post', 'icap-seo') : __('New Page', 'icap-seo');
            $seed = $site_title_phrase !== '' ? ($post_type_label . ' on ' . $site_title_phrase) : $post_type_label;
        }

        $candidate = $this->build_seo_title_with_target_length($seed);

        return $candidate !== '' ? $candidate : $this->trim_title_to_max_length($seed, 65);
    }

    private function build_seo_meta_summary_via_ai(WP_Post $post, string $content_key, string $site_name): ?string
    {
        if ($content_key === '') {
            return null;
        }

        $result = $this->service_client->request_ai_content_draft(
            $content_key,
            'meta_description',
            [
                'title' => (string) $post->post_title,
                'existing_content_text' => mb_substr(wp_strip_all_tags((string) $post->post_content), 0, 4000),
                'site_name' => $site_name,
                'post_type' => (string) $post->post_type,
            ]
        );

        if (!$result['success'] || empty($result['data']['draft_text'])) {
            return null;
        }

        $candidate = trim((string) $result['data']['draft_text'], " \t\n\r\0\x0B\"'");
        $candidate = trim((string) preg_replace('/\s+/', ' ', $candidate));

        return $candidate !== '' ? $candidate : null;
    }

    private function build_seo_meta_description(WP_Post $post, string $content_key, bool $force_regenerate = false): string
    {
        $site_name = sanitize_text_field((string) get_bloginfo('name'));
        $title_phrase = $this->clean_title_phrase((string) $post->post_title);

        $summary = $this->build_seo_meta_summary_via_ai($post, $content_key, $site_name);
        if ($summary === null) {
            $summary = $this->extract_meta_summary_sentence($post, $force_regenerate);

            if (
                $force_regenerate
                || $summary === ''
                || $this->is_generic_wordpress_description($summary)
                || $this->is_low_quality_meta_description($summary)
            ) {
                $summary = $this->build_on_brand_meta_summary($post, $site_name);
            }
        }

        $candidate = trim((string) $summary);
        $candidate = rtrim($candidate, " .\t\n\r\0\x0B");

        // Prefer a natural page-intent sentence over token stuffing.
        if ($title_phrase !== '' && stripos($candidate, $title_phrase) === false) {
            $candidate = trim($title_phrase . ' — ' . $candidate);
        }
        if ($site_name !== '' && stripos($candidate, $site_name) === false) {
            $candidate = trim($candidate . ' from ' . $site_name);
        }

        $candidate = preg_replace('/\s+/', ' ', (string) $candidate);
        if (!is_string($candidate)) {
            $candidate = '';
        }
        $candidate = trim($candidate);
        if ($candidate !== '' && !preg_match('/[.!?]$/', $candidate)) {
            $candidate .= '.';
        }

        if ($this->string_length($candidate) > 160) {
            $candidate = $this->trim_text_to_max_length($candidate, 160);
            $candidate = rtrim($candidate, " .\t\n\r\0\x0B");
            $candidate = $this->strip_dangling_site_name_suffix($candidate, $site_name);
            if ($candidate !== '') {
                $candidate .= '.';
            }
        }

        if ($this->string_length($candidate) < 120) {
            $fillers = [
                ' Learn what makes this page useful and what visitors can expect next.',
                ' Discover key highlights and practical details at a glance.',
                ' Find clear context and next steps for this page.',
            ];
            foreach ($fillers as $filler) {
                if ($this->string_length($candidate) >= 120) {
                    break;
                }
                $candidate = trim($candidate . $filler);
            }
        }

        if ($this->string_length($candidate) > 160) {
            $candidate = $this->trim_text_to_max_length($candidate, 160);
            $candidate = rtrim($candidate, " .\t\n\r\0\x0B");
            $candidate = $this->strip_dangling_site_name_suffix($candidate, $site_name);
            if ($candidate !== '') {
                $candidate .= '.';
            }
        }

        if ($this->is_low_quality_meta_description($candidate)) {
            $candidate = $this->build_on_brand_meta_summary($post, $site_name);
            if ($this->string_length($candidate) < 120) {
                $candidate = trim($candidate . ' Learn what makes this page useful and what visitors can expect next.');
            }
            if ($this->string_length($candidate) > 160) {
                $candidate = $this->trim_text_to_max_length($candidate, 160);
                $candidate = rtrim($candidate, " .\t\n\r\0\x0B");
                $candidate = $this->strip_dangling_site_name_suffix($candidate, $site_name);
                $candidate .= '.';
            }
        }

        return trim($candidate);
    }

    private function strip_dangling_site_name_suffix(string $candidate, string $site_name): string
    {
        if ($site_name === '' || stripos($candidate, $site_name) !== false) {
            return $candidate;
        }

        // Length-clamping can cut the appended " from {site_name}" suffix mid-word,
        // leaving a dangling "from" connector with nothing after it (e.g. "...website from.").
        $stripped = preg_replace('/\s+from\.?$/i', '', $candidate);

        return is_string($stripped) ? trim($stripped) : $candidate;
    }

    private function is_generic_wordpress_description(string $value): bool
    {
        $normalized = strtolower((string) preg_replace('/\s+/', ' ', trim($value)));
        if ($normalized === '') {
            return false;
        }
        $generic_fragments = [
            'this is an example of a wordpress page',
            'you could edit this to put information about yourself',
            'so readers know where you are coming from',
            'you can create as',
            'wordpress page',
            'seo by icap',
        ];
        foreach ($generic_fragments as $fragment) {
            if (strpos($normalized, $fragment) !== false) {
                return true;
            }
        }

        return false;
    }

    private function is_low_quality_meta_description(string $value): bool
    {
        $normalized = strtolower((string) preg_replace('/\s+/', ' ', trim($value)));
        if ($normalized === '') {
            return true;
        }
        if (preg_match('/\bwith [a-z0-9]+ and [a-z0-9]+\.?$/', $normalized) === 1) {
            return true;
        }
        if (strpos($normalized, 'discover ') !== false && strpos($normalized, ' insights') !== false) {
            return true;
        }

        return false;
    }

    private function clean_title_phrase(string $title): string
    {
        $title_phrase = sanitize_text_field($title);
        $title_phrase = preg_replace('/\s*[\|\-–—]\s*.*/u', '', $title_phrase);
        if (!is_string($title_phrase)) {
            $title_phrase = '';
        }
        $title_phrase = trim((string) preg_replace('/\s+/', ' ', $title_phrase));

        return $title_phrase;
    }

    private function build_on_brand_meta_summary(WP_Post $post, string $site_name): string
    {
        $title_phrase = $this->clean_title_phrase((string) $post->post_title);
        if ($title_phrase === '') {
            $title_phrase = __('this page', 'icap-seo');
        }

        $post_type = sanitize_key((string) $post->post_type);
        if ($post_type === 'page' && strcasecmp($title_phrase, 'About') === 0 && $site_name !== '') {
            return sprintf(
                __('Meet the story behind %1$s. Learn who we are, what we share, and why visitors keep coming back.', 'icap-seo'),
                $site_name
            );
        }

        if ($site_name !== '') {
            return sprintf(
                __('Explore %1$s on %2$s for clear highlights, useful details, and what to do next.', 'icap-seo'),
                $title_phrase,
                $site_name
            );
        }

        return sprintf(
            __('Explore %s for clear highlights, useful details, and what to do next.', 'icap-seo'),
            $title_phrase
        );
    }

    private function extract_meta_summary_sentence(WP_Post $post, bool $force_regenerate = false): string
    {
        $candidates = [];
        if (!$force_regenerate) {
            $stored_meta = get_post_meta((int) $post->ID, self::META_DESCRIPTION_META_KEY, true);
            if (is_string($stored_meta) && trim($stored_meta) !== '') {
                $candidates[] = $stored_meta;
            }
            if (has_excerpt($post)) {
                $candidates[] = (string) $post->post_excerpt;
            }
        }

        $content = (string) $post->post_content;
        $content = str_replace(
            [self::SEO_CHANGE_COMMENT_START, self::SEO_CHANGE_COMMENT_END],
            ' ',
            $content
        );
        $candidates[] = wp_strip_all_tags($content, true);
        $candidates[] = sanitize_text_field((string) $post->post_title);

        foreach ($candidates as $candidate) {
            $normalized = preg_replace('/\s+/', ' ', trim((string) $candidate));
            if (!is_string($normalized) || $normalized === '') {
                continue;
            }
            if ($this->is_generic_wordpress_description($normalized) || $this->is_low_quality_meta_description($normalized)) {
                continue;
            }
            $sentences = preg_split('/(?<=[.!?])\s+/', $normalized);
            if (is_array($sentences) && !empty($sentences)) {
                $first_sentence = trim((string) $sentences[0]);
                if ($first_sentence !== '' && !$this->is_generic_wordpress_description($first_sentence)) {
                    return $this->trim_text_to_max_length($first_sentence, 140);
                }
            }
            return $this->trim_text_to_max_length($normalized, 140);
        }

        return '';
    }

    private function get_remediation_history_for_post(int $post_id): array
    {
        $history = get_post_meta($post_id, self::REMEDIATION_HISTORY_META_KEY, true);
        if (!is_array($history)) {
            return [];
        }

        $normalized_entries = [];
        foreach ($history as $entry) {
            if (!is_array($entry)) {
                continue;
            }
            $normalized_entries[] = [
                'timestamp' => isset($entry['timestamp']) ? sanitize_text_field((string) $entry['timestamp']) : '',
                'issue_codes' => isset($entry['issue_codes']) && is_array($entry['issue_codes']) ? array_map('sanitize_key', $entry['issue_codes']) : [],
                'title_before' => isset($entry['title_before']) ? sanitize_text_field((string) $entry['title_before']) : '',
                'title_after' => isset($entry['title_after']) ? sanitize_text_field((string) $entry['title_after']) : '',
                'title_changed' => !empty($entry['title_changed']),
                'excerpt_before' => isset($entry['excerpt_before']) ? sanitize_text_field((string) $entry['excerpt_before']) : '',
                'excerpt_after' => isset($entry['excerpt_after']) ? sanitize_text_field((string) $entry['excerpt_after']) : '',
                'excerpt_changed' => !empty($entry['excerpt_changed']),
                'h1_before' => isset($entry['h1_before']) ? sanitize_text_field((string) $entry['h1_before']) : '',
                'h1_after' => isset($entry['h1_after']) ? sanitize_text_field((string) $entry['h1_after']) : '',
                'h1_changed' => !empty($entry['h1_changed']),
                'images_alt_before' => isset($entry['images_alt_before']) ? (int) $entry['images_alt_before'] : 0,
                'images_alt_after' => isset($entry['images_alt_after']) ? (int) $entry['images_alt_after'] : 0,
                'images_alt_changed' => !empty($entry['images_alt_changed']),
                'images_alt_updated_count' => isset($entry['images_alt_updated_count']) ? (int) $entry['images_alt_updated_count'] : 0,
                'images_dimensions_before' => isset($entry['images_dimensions_before']) ? (int) $entry['images_dimensions_before'] : 0,
                'images_dimensions_after' => isset($entry['images_dimensions_after']) ? (int) $entry['images_dimensions_after'] : 0,
                'images_dimensions_changed' => !empty($entry['images_dimensions_changed']),
                'images_dimensions_updated_count' => isset($entry['images_dimensions_updated_count']) ? (int) $entry['images_dimensions_updated_count'] : 0,
                'images_lazy_before' => isset($entry['images_lazy_before']) ? (int) $entry['images_lazy_before'] : 0,
                'images_lazy_after' => isset($entry['images_lazy_after']) ? (int) $entry['images_lazy_after'] : 0,
                'images_lazy_changed' => !empty($entry['images_lazy_changed']),
                'images_lazy_updated_count' => isset($entry['images_lazy_updated_count']) ? (int) $entry['images_lazy_updated_count'] : 0,
                'canonical_before' => isset($entry['canonical_before']) ? esc_url_raw((string) $entry['canonical_before']) : '',
                'canonical_after' => isset($entry['canonical_after']) ? esc_url_raw((string) $entry['canonical_after']) : '',
                'canonical_changed' => !empty($entry['canonical_changed']),
                'jsonld_schema_before' => isset($entry['jsonld_schema_before']) ? sanitize_text_field((string) $entry['jsonld_schema_before']) : '',
                'jsonld_schema_after' => isset($entry['jsonld_schema_after']) ? sanitize_text_field((string) $entry['jsonld_schema_after']) : '',
                'jsonld_schema_changed' => !empty($entry['jsonld_schema_changed']),
                'heading_structure_before' => isset($entry['heading_structure_before']) ? (int) $entry['heading_structure_before'] : 0,
                'heading_structure_after' => isset($entry['heading_structure_after']) ? (int) $entry['heading_structure_after'] : 0,
                'heading_structure_changed' => !empty($entry['heading_structure_changed']),
                'headings_added_count' => isset($entry['headings_added_count']) ? (int) $entry['headings_added_count'] : 0,
                'content_depth_changed' => !empty($entry['content_depth_changed']),
                'content_depth_words_added' => isset($entry['content_depth_words_added']) ? (int) $entry['content_depth_words_added'] : 0,
                'internal_links_before' => isset($entry['internal_links_before']) ? (int) $entry['internal_links_before'] : 0,
                'internal_links_after' => isset($entry['internal_links_after']) ? (int) $entry['internal_links_after'] : 0,
                'internal_linking_changed' => !empty($entry['internal_linking_changed']),
                'internal_links_added_count' => isset($entry['internal_links_added_count']) ? (int) $entry['internal_links_added_count'] : 0,
                'paragraph_structure_before' => isset($entry['paragraph_structure_before']) ? (int) $entry['paragraph_structure_before'] : 0,
                'paragraph_structure_after' => isset($entry['paragraph_structure_after']) ? (int) $entry['paragraph_structure_after'] : 0,
                'paragraph_structure_changed' => !empty($entry['paragraph_structure_changed']),
                'paragraphs_added_count' => isset($entry['paragraphs_added_count']) ? (int) $entry['paragraphs_added_count'] : 0,
            ];
        }

        return $normalized_entries;
    }

    private function store_remediation_history_entry(int $post_id, array $issue_codes, array $result): void
    {
        $history = $this->get_remediation_history_for_post($post_id);
        array_unshift($history, [
            'timestamp' => current_time('mysql'),
            'issue_codes' => array_values(array_unique(array_map(static fn($value): string => sanitize_key((string) $value), $issue_codes))),
            'title_before' => isset($result['title_before']) ? sanitize_text_field((string) $result['title_before']) : '',
            'title_after' => isset($result['title_after']) ? sanitize_text_field((string) $result['title_after']) : '',
            'title_changed' => !empty($result['title_changed']),
            'excerpt_before' => isset($result['excerpt_before']) ? sanitize_text_field((string) $result['excerpt_before']) : '',
            'excerpt_after' => isset($result['excerpt_after']) ? sanitize_text_field((string) $result['excerpt_after']) : '',
            'excerpt_changed' => !empty($result['excerpt_changed']),
            'h1_before' => isset($result['h1_before']) ? sanitize_text_field((string) $result['h1_before']) : '',
            'h1_after' => isset($result['h1_after']) ? sanitize_text_field((string) $result['h1_after']) : '',
            'h1_changed' => !empty($result['h1_changed']),
            'images_alt_before' => isset($result['images_alt_before']) ? (int) $result['images_alt_before'] : 0,
            'images_alt_after' => isset($result['images_alt_after']) ? (int) $result['images_alt_after'] : 0,
            'images_alt_changed' => !empty($result['images_alt_changed']),
            'images_alt_updated_count' => isset($result['images_alt_updated_count']) ? (int) $result['images_alt_updated_count'] : 0,
            'images_dimensions_before' => isset($result['images_dimensions_before']) ? (int) $result['images_dimensions_before'] : 0,
            'images_dimensions_after' => isset($result['images_dimensions_after']) ? (int) $result['images_dimensions_after'] : 0,
            'images_dimensions_changed' => !empty($result['images_dimensions_changed']),
            'images_dimensions_updated_count' => isset($result['images_dimensions_updated_count']) ? (int) $result['images_dimensions_updated_count'] : 0,
            'images_lazy_before' => isset($result['images_lazy_before']) ? (int) $result['images_lazy_before'] : 0,
            'images_lazy_after' => isset($result['images_lazy_after']) ? (int) $result['images_lazy_after'] : 0,
            'images_lazy_changed' => !empty($result['images_lazy_changed']),
            'images_lazy_updated_count' => isset($result['images_lazy_updated_count']) ? (int) $result['images_lazy_updated_count'] : 0,
            'canonical_before' => isset($result['canonical_before']) ? esc_url_raw((string) $result['canonical_before']) : '',
            'canonical_after' => isset($result['canonical_after']) ? esc_url_raw((string) $result['canonical_after']) : '',
            'canonical_changed' => !empty($result['canonical_changed']),
            'jsonld_schema_before' => isset($result['jsonld_schema_before']) ? sanitize_text_field((string) $result['jsonld_schema_before']) : '',
            'jsonld_schema_after' => isset($result['jsonld_schema_after']) ? sanitize_text_field((string) $result['jsonld_schema_after']) : '',
            'jsonld_schema_changed' => !empty($result['jsonld_schema_changed']),
            'heading_structure_before' => isset($result['heading_structure_before']) ? (int) $result['heading_structure_before'] : 0,
            'heading_structure_after' => isset($result['heading_structure_after']) ? (int) $result['heading_structure_after'] : 0,
            'heading_structure_changed' => !empty($result['heading_structure_changed']),
            'headings_added_count' => isset($result['headings_added_count']) ? (int) $result['headings_added_count'] : 0,
            'content_depth_changed' => !empty($result['content_depth_changed']),
            'content_depth_words_added' => isset($result['content_depth_words_added']) ? (int) $result['content_depth_words_added'] : 0,
            'internal_links_before' => isset($result['internal_links_before']) ? (int) $result['internal_links_before'] : 0,
            'internal_links_after' => isset($result['internal_links_after']) ? (int) $result['internal_links_after'] : 0,
            'internal_linking_changed' => !empty($result['internal_linking_changed']),
            'internal_links_added_count' => isset($result['internal_links_added_count']) ? (int) $result['internal_links_added_count'] : 0,
            'paragraph_structure_before' => isset($result['paragraph_structure_before']) ? (int) $result['paragraph_structure_before'] : 0,
            'paragraph_structure_after' => isset($result['paragraph_structure_after']) ? (int) $result['paragraph_structure_after'] : 0,
            'paragraph_structure_changed' => !empty($result['paragraph_structure_changed']),
            'paragraphs_added_count' => isset($result['paragraphs_added_count']) ? (int) $result['paragraphs_added_count'] : 0,
        ]);

        if (count($history) > self::REMEDIATION_HISTORY_MAX_ENTRIES) {
            $history = array_slice($history, 0, self::REMEDIATION_HISTORY_MAX_ENTRIES);
        }

        update_post_meta($post_id, self::REMEDIATION_HISTORY_META_KEY, $history);

        $latest = $history[0];
        $parts = [];
        if (!empty($latest['title_changed'])) {
            $parts[] = __('Title updated', 'icap-seo');
        }
        if (!empty($latest['excerpt_changed'])) {
            $parts[] = __('Meta description/excerpt updated', 'icap-seo');
        }
        if (!empty($latest['h1_changed'])) {
            $parts[] = __('H1 updated', 'icap-seo');
        }
        if (!empty($latest['images_alt_changed'])) {
            $parts[] = sprintf(
                __('%d image alt attribute(s) updated', 'icap-seo'),
                isset($latest['images_alt_updated_count']) ? (int) $latest['images_alt_updated_count'] : 0
            );
        }
        if (!empty($latest['canonical_changed'])) {
            $parts[] = __('Canonical URL set', 'icap-seo');
        }
        if (!empty($latest['jsonld_schema_changed'])) {
            $parts[] = sprintf(
                __('JSON-LD schema added (%s)', 'icap-seo'),
                isset($latest['jsonld_schema_after']) ? (string) $latest['jsonld_schema_after'] : ''
            );
        }
        if (!empty($latest['heading_structure_changed'])) {
            $parts[] = sprintf(
                __('%d heading(s) added', 'icap-seo'),
                isset($latest['headings_added_count']) ? (int) $latest['headings_added_count'] : 0
            );
        }
        if (!empty($latest['content_depth_changed'])) {
            $parts[] = sprintf(
                __('Content depth draft published (+%d words)', 'icap-seo'),
                isset($latest['content_depth_words_added']) ? (int) $latest['content_depth_words_added'] : 0
            );
        }
        if (!empty($latest['internal_linking_changed'])) {
            $parts[] = sprintf(
                __('%d internal link(s) added', 'icap-seo'),
                isset($latest['internal_links_added_count']) ? (int) $latest['internal_links_added_count'] : 0
            );
        }
        if (!empty($latest['paragraph_structure_changed'])) {
            $parts[] = sprintf(
                __('%d paragraph(s) added', 'icap-seo'),
                isset($latest['paragraphs_added_count']) ? (int) $latest['paragraphs_added_count'] : 0
            );
        }
        $summary = empty($parts) ? __('No field changes applied.', 'icap-seo') : implode('; ', $parts) . '.';
        if (!empty($latest['issue_codes']) && is_array($latest['issue_codes'])) {
            $summary .= ' ' . sprintf(__('Issues: %s', 'icap-seo'), implode(', ', array_map('sanitize_key', $latest['issue_codes'])));
        }
        update_post_meta($post_id, self::REMEDIATION_SUMMARY_META_KEY, $summary);
    }

    public function register_remediation_meta_boxes(): void
    {
        add_meta_box(
            'icap-seo-remediation-log',
            __('iCap SEO Remediation Log', 'icap-seo'),
            [$this, 'render_remediation_meta_box'],
            'page',
            'side',
            'default'
        );
        add_meta_box(
            'icap-seo-remediation-log',
            __('iCap SEO Remediation Log', 'icap-seo'),
            [$this, 'render_remediation_meta_box'],
            'post',
            'side',
            'default'
        );
    }

    public function render_remediation_meta_box(WP_Post $post): void
    {
        if (!current_user_can('edit_post', $post->ID)) {
            echo '<p>' . esc_html__('No permission to view remediation history.', 'icap-seo') . '</p>';
            return;
        }

        $history = $this->get_remediation_history_for_post((int) $post->ID);
        if (empty($history)) {
            echo '<p>' . esc_html__('No remediation changes recorded yet for this content.', 'icap-seo') . '</p>';
            return;
        }

        echo '<div class="icap-seo-remediation-meta-box">';
        foreach (array_slice($history, 0, 5) as $entry) {
            $when = isset($entry['timestamp']) ? (string) $entry['timestamp'] : '';
            $issues = isset($entry['issue_codes']) && is_array($entry['issue_codes']) ? $entry['issue_codes'] : [];
            echo '<p><strong>' . esc_html($when !== '' ? $when : __('Unknown time', 'icap-seo')) . '</strong><br>';
            if (!empty($issues)) {
                echo esc_html(sprintf(__('Issues: %s', 'icap-seo'), implode(', ', array_map('sanitize_key', $issues)))) . '<br>';
            }
            if (!empty($entry['title_changed'])) {
                echo esc_html__('Title changed', 'icap-seo') . '<br>';
            }
            if (!empty($entry['excerpt_changed'])) {
                echo esc_html__('Meta description/excerpt changed', 'icap-seo') . '<br>';
            }
            if (!empty($entry['h1_changed'])) {
                echo esc_html__('H1 changed', 'icap-seo') . '<br>';
            }
            if (!empty($entry['images_alt_changed'])) {
                echo esc_html(sprintf(
                    __('%d image alt attribute(s) updated', 'icap-seo'),
                    isset($entry['images_alt_updated_count']) ? (int) $entry['images_alt_updated_count'] : 0
                )) . '<br>';
            }
            if (!empty($entry['canonical_changed'])) {
                echo esc_html__('Canonical URL set', 'icap-seo') . '<br>';
            }
            if (!empty($entry['jsonld_schema_changed'])) {
                echo esc_html(sprintf(
                    __('JSON-LD schema added (%s)', 'icap-seo'),
                    isset($entry['jsonld_schema_after']) ? (string) $entry['jsonld_schema_after'] : ''
                )) . '<br>';
            }
            if (!empty($entry['heading_structure_changed'])) {
                echo esc_html(sprintf(
                    __('%d heading(s) added', 'icap-seo'),
                    isset($entry['headings_added_count']) ? (int) $entry['headings_added_count'] : 0
                )) . '<br>';
            }
            if (!empty($entry['content_depth_changed'])) {
                echo esc_html(sprintf(
                    __('Content depth draft published (+%d words)', 'icap-seo'),
                    isset($entry['content_depth_words_added']) ? (int) $entry['content_depth_words_added'] : 0
                )) . '<br>';
            }
            if (!empty($entry['internal_linking_changed'])) {
                echo esc_html(sprintf(
                    __('%d internal link(s) added', 'icap-seo'),
                    isset($entry['internal_links_added_count']) ? (int) $entry['internal_links_added_count'] : 0
                )) . '<br>';
            }
            if (!empty($entry['paragraph_structure_changed'])) {
                echo esc_html(sprintf(
                    __('%d paragraph(s) added', 'icap-seo'),
                    isset($entry['paragraphs_added_count']) ? (int) $entry['paragraphs_added_count'] : 0
                )) . '<br>';
            }
            echo '</p><hr>';
        }
        echo '</div>';
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
