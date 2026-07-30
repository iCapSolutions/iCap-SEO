<?php
if (!defined('ABSPATH')) {
    exit;
}

$tabs = [
    'home' => __('Home', 'icap-seo'),
    'setup-wizard' => __('Setup Wizard', 'icap-seo'),
    'site-health' => __('Site Health', 'icap-seo'),
    'content-scores' => __('Content Scores', 'icap-seo'),
    'settings' => __('Settings', 'icap-seo'),
];

$notice_map = [
    'settings_saved' => ['type' => 'updated', 'message' => __('Connection settings saved.', 'icap-seo')],
    'register_success' => ['type' => 'updated', 'message' => __('Site registration request succeeded.', 'icap-seo')],
    'registration_token_missing' => ['type' => 'error', 'message' => __('Site registration failed. Set Registration Token in Settings or define ICAP_SEO_REGISTRATION_TOKEN in wp-config.php.', 'icap-seo')],
    'api_base_url_missing' => ['type' => 'error', 'message' => __('Site registration failed. API Base URL is required.', 'icap-seo')],
    'register_failed' => ['type' => 'error', 'message' => __('Site registration failed. Confirm API Base URL and Registration Token, then retry.', 'icap-seo')],
    'connection_ok_authenticated' => ['type' => 'updated', 'message' => __('Connection test succeeded. API and saved site credentials are valid.', 'icap-seo')],
    'connection_ok_reachable' => ['type' => 'updated', 'message' => __('Connection test reached the API. Next step: register this site to provision credentials.', 'icap-seo')],
    'connection_api_base_url_missing' => ['type' => 'error', 'message' => __('Connection test failed. API Base URL is required before testing.', 'icap-seo')],
    'connection_invalid_token' => ['type' => 'error', 'message' => __('Connection test reached the API, but Site ID/Site Token were rejected. Re-run registration in Setup Wizard.', 'icap-seo')],
    'connection_endpoint_not_found' => ['type' => 'error', 'message' => __('Connection test reached the host but API route was not found. Confirm API Base URL points to the iCap SEO API root.', 'icap-seo')],
    'connection_unreachable' => ['type' => 'error', 'message' => __('Connection test could not reach the API. Verify network access and API availability.', 'icap-seo')],
    'connection_failed' => ['type' => 'error', 'message' => __('Connection test failed with an unexpected API response. Check logs and retry.', 'icap-seo')],
    'scan_queued' => ['type' => 'updated', 'message' => __('Scan request queued.', 'icap-seo')],
    'payment_required' => ['type' => 'error', 'message' => __('Scan request blocked: payment is required for this site subscription. Resolve billing and retry.', 'icap-seo')],
    'subscription_required' => ['type' => 'error', 'message' => __('Scan request blocked: no active subscription is associated with this site. Activate a plan and retry.', 'icap-seo')],
    'account_suspended' => ['type' => 'error', 'message' => __('Scan request blocked: account is suspended. Contact iCap SEO support to restore access.', 'icap-seo')],
    'invalid_token' => ['type' => 'error', 'message' => __('Scan request failed: site credentials are invalid. Re-run registration from Setup Wizard.', 'icap-seo')],
    'rate_limited' => ['type' => 'error', 'message' => __('Scan request was rate-limited. Wait and retry.', 'icap-seo')],
    'scan_failed' => ['type' => 'error', 'message' => __('Scan request failed. Confirm site is registered and billing/auth are active.', 'icap-seo')],
    'billing_status_active' => ['type' => 'updated', 'message' => __('Billing status check: site entitlement is active.', 'icap-seo')],
    'billing_status_attention' => ['type' => 'error', 'message' => __('Billing status check: account needs billing attention (past due or grace period).', 'icap-seo')],
    'billing_status_blocked' => ['type' => 'error', 'message' => __('Billing status check: account is blocked (canceled or suspended).', 'icap-seo')],
    'billing_status_not_configured' => ['type' => 'error', 'message' => __('Billing status check requires site registration credentials. Register this site first.', 'icap-seo')],
    'billing_status_unknown' => ['type' => 'error', 'message' => __('Billing status check returned an unknown entitlement state.', 'icap-seo')],
    'billing_status_unavailable' => ['type' => 'error', 'message' => __('Billing status check failed. Confirm API availability and retry.', 'icap-seo')],
    'billing_checkout_not_configured' => ['type' => 'error', 'message' => __('Billing checkout requires site registration credentials. Register this site first.', 'icap-seo')],
    'billing_checkout_misconfigured' => ['type' => 'error', 'message' => __('Billing checkout is not fully configured yet. Confirm price/URLs and retry.', 'icap-seo')],
    'billing_checkout_unavailable' => ['type' => 'error', 'message' => __('Billing checkout is temporarily unavailable. Please retry shortly.', 'icap-seo')],
    'billing_checkout_failed' => ['type' => 'error', 'message' => __('Billing checkout request failed. Confirm API and billing settings, then retry.', 'icap-seo')],
    'billing_checkout_returned' => ['type' => 'updated', 'message' => __('Checkout completed. Run Check Billing Status to confirm entitlement update.', 'icap-seo')],
    'billing_checkout_cancelled' => ['type' => 'error', 'message' => __('Checkout was canceled before completion.', 'icap-seo')],
    'billing_portal_not_configured' => ['type' => 'error', 'message' => __('Billing portal requires site registration credentials. Register this site first.', 'icap-seo')],
    'billing_portal_subscription_required' => ['type' => 'error', 'message' => __('Billing portal is unavailable until a billing customer/subscription exists for this site.', 'icap-seo')],
    'billing_portal_misconfigured' => ['type' => 'error', 'message' => __('Billing portal is not fully configured yet. Confirm return URL and retry.', 'icap-seo')],
    'billing_portal_unavailable' => ['type' => 'error', 'message' => __('Billing portal is temporarily unavailable. Please retry shortly.', 'icap-seo')],
    'billing_portal_failed' => ['type' => 'error', 'message' => __('Billing portal request failed. Confirm API and billing settings, then retry.', 'icap-seo')],
    'billing_portal_returned' => ['type' => 'updated', 'message' => __('Returned from billing portal.', 'icap-seo')],
    'remediation_preview_ready' => ['type' => 'updated', 'message' => __('Remediation preview refreshed for this content item.', 'icap-seo')],
    'remediation_apply_queued' => ['type' => 'updated', 'message' => __('Remediation apply request was accepted and queued.', 'icap-seo')],
    'remediation_apply_title_updated' => ['type' => 'updated', 'message' => __('Title remediation applied locally for this item. Re-scan to verify score movement.', 'icap-seo')],
    'remediation_apply_noop' => ['type' => 'error', 'message' => __('No title changes were applied for this recommendation.', 'icap-seo')],
    'remediation_apply_title_update_failed' => ['type' => 'error', 'message' => __('Remediation request was accepted, but local title update could not be applied. Verify edit permissions and content key mapping.', 'icap-seo')],
    'remediation_content_key_missing' => ['type' => 'error', 'message' => __('Remediation request failed: content key is required.', 'icap-seo')],
    'remediation_validation_error' => ['type' => 'error', 'message' => __('Remediation request failed validation. Refresh content details and retry.', 'icap-seo')],
    'remediation_auth_error' => ['type' => 'error', 'message' => __('Remediation request failed authentication. Re-register the site credentials and retry.', 'icap-seo')],
    'remediation_preview_unavailable' => ['type' => 'error', 'message' => __('Remediation preview is temporarily unavailable. Please retry shortly.', 'icap-seo')],
    'remediation_apply_unavailable' => ['type' => 'error', 'message' => __('Remediation apply is temporarily unavailable. Please retry shortly.', 'icap-seo')],
    'remediation_preview_failed' => ['type' => 'error', 'message' => __('Remediation preview failed with an unexpected response.', 'icap-seo')],
    'remediation_apply_failed' => ['type' => 'error', 'message' => __('Remediation apply failed with an unexpected response.', 'icap-seo')],
    'render_fallback' => ['type' => 'error', 'message' => __('Dashboard loaded in fallback mode after an internal error. Please retry and check logs.', 'icap-seo')],
];

if (!isset($latest_content_scores_meta) || !is_array($latest_content_scores_meta)) {
    $latest_content_scores_meta = [];
}
$selected_content_key = isset($selected_content_key) && is_string($selected_content_key)
    ? sanitize_text_field($selected_content_key)
    : '';
$content_score_detail = isset($content_score_detail) && is_array($content_score_detail)
    ? $content_score_detail
    : [];
$content_score_detail_error = isset($content_score_detail_error) && is_string($content_score_detail_error)
    ? sanitize_text_field($content_score_detail_error)
    : '';
$remediation_preview = isset($remediation_preview) && is_array($remediation_preview)
    ? $remediation_preview
    : [];
$remediation_preview_error = isset($remediation_preview_error) && is_string($remediation_preview_error)
    ? sanitize_text_field($remediation_preview_error)
    : '';
$latest_scores_scan_id = (isset($latest_content_scores_meta['scan_id']) && is_string($latest_content_scores_meta['scan_id']))
    ? sanitize_text_field($latest_content_scores_meta['scan_id'])
    : '';
$latest_scores_scan_tier = (isset($latest_content_scores_meta['scan_tier']) && is_string($latest_content_scores_meta['scan_tier']))
    ? sanitize_key($latest_content_scores_meta['scan_tier'])
    : '';
$latest_scores_source = (isset($latest_content_scores_meta['source']) && is_string($latest_content_scores_meta['source']))
    ? sanitize_key($latest_content_scores_meta['source'])
    : 'unknown';
$latest_scores_item_count = isset($latest_content_scores_meta['item_count']) ? (int) $latest_content_scores_meta['item_count'] : count($content_scores ?? []);
$latest_scores_scan_layers = (isset($latest_content_scores_meta['scan_layers']) && is_array($latest_content_scores_meta['scan_layers']))
    ? $latest_content_scores_meta['scan_layers']
    : [];
$latest_scores_executed_layer_names = [];
if (isset($latest_scores_scan_layers['executed']) && is_array($latest_scores_scan_layers['executed'])) {
    foreach ($latest_scores_scan_layers['executed'] as $layer_row) {
        if (is_array($layer_row) && isset($layer_row['name']) && is_string($layer_row['name'])) {
            $latest_scores_executed_layer_names[] = sanitize_text_field($layer_row['name']);
        } elseif (is_string($layer_row)) {
            $latest_scores_executed_layer_names[] = sanitize_text_field($layer_row);
        }
    }
}
$notice_override_message = '';
if ($notice_code === 'remediation_apply_title_updated') {
    $title_before = isset($_GET['title_before']) ? sanitize_text_field((string) wp_unslash($_GET['title_before'])) : '';
    $title_after = isset($_GET['title_after']) ? sanitize_text_field((string) wp_unslash($_GET['title_after'])) : '';
    $excerpt_before = isset($_GET['excerpt_before']) ? sanitize_text_field((string) wp_unslash($_GET['excerpt_before'])) : '';
    $excerpt_after = isset($_GET['excerpt_after']) ? sanitize_text_field((string) wp_unslash($_GET['excerpt_after'])) : '';

    if ($title_before !== '' || $title_after !== '') {
        $notice_override_message = sprintf(
            __('Title updated: "%1$s" → "%2$s".', 'icap-seo'),
            $title_before !== '' ? $title_before : __('(empty)', 'icap-seo'),
            $title_after !== '' ? $title_after : __('(empty)', 'icap-seo')
        );
    }
    if ($excerpt_before !== '' || $excerpt_after !== '') {
        $notice_override_message .= ' ' . sprintf(
            __('Page excerpt/meta description updated: "%1$s" → "%2$s".', 'icap-seo'),
            $excerpt_before !== '' ? $excerpt_before : __('(empty)', 'icap-seo'),
            $excerpt_after !== '' ? $excerpt_after : __('(empty)', 'icap-seo')
        );
    }
    if ($notice_override_message !== '') {
        $notice_override_message .= ' ' . __('Re-scan to verify score movement.', 'icap-seo');
    }
}
if ($notice_code === 'remediation_apply_noop') {
    $noop_reason = isset($_GET['noop_reason']) ? sanitize_key((string) wp_unslash($_GET['noop_reason'])) : '';
    if ($noop_reason === 'title_already_within_range') {
        $notice_override_message = __('No-op: title is already within the recommended 20-65 character range.', 'icap-seo');
    } elseif ($noop_reason === 'meta_description_already_optimized') {
        $notice_override_message = __('No-op: meta description already matches the current page context and SEO target range.', 'icap-seo');
    } elseif ($noop_reason === 'meta_description_already_within_range') {
        $notice_override_message = __('No-op: meta description is already within the recommended 120-170 character range.', 'icap-seo');
    } elseif ($noop_reason === 'meta_description_generation_failed') {
        $notice_override_message = __('No-op: a replacement meta description could not be generated from this page content.', 'icap-seo');
    } elseif ($noop_reason === 'issue_not_supported_for_local_apply') {
        $notice_override_message = __('No-op: this recommendation is not yet supported by local apply logic.', 'icap-seo');
    } elseif ($noop_reason === 'no_effective_change_computed') {
        $notice_override_message = __('No-op: remediation did not produce a different title value to save.', 'icap-seo');
    } else {
        $notice_override_message = __('No-op: no changes were required for this recommendation.', 'icap-seo');
    }
}
?>
<div class="wrap icap-seo-wrap">
    <h1 class="icap-seo-header">
        <img src="<?php echo esc_url(ICAP_SEO_PLUGIN_URL . 'assets/images/icap-seo-logo.svg'); ?>" alt="<?php esc_attr_e('iCap SEO', 'icap-seo'); ?>" class="icap-seo-logo">
        <?php esc_html_e('iCap SEO', 'icap-seo'); ?>
    </h1>
    <p><?php esc_html_e('SEO intelligence for WordPress sites by iCapSolutions.', 'icap-seo'); ?></p>
    <?php if ($notice_code !== '' && isset($notice_map[$notice_code])) : ?>
        <div class="notice <?php echo esc_attr($notice_map[$notice_code]['type'] === 'error' ? 'notice-error' : 'notice-success'); ?> is-dismissible">
            <p><?php echo esc_html($notice_override_message !== '' ? $notice_override_message : $notice_map[$notice_code]['message']); ?></p>
        </div>
    <?php endif; ?>

    <nav class="nav-tab-wrapper">
        <?php foreach ($tabs as $tab_key => $label) : ?>
            <?php
            $tab_url = add_query_arg(
                [
                    'page' => 'icap-seo',
                    'tab' => $tab_key,
                ],
                admin_url('admin.php')
            );
            $active_class = $active_tab === $tab_key ? ' nav-tab-active' : '';
            ?>
            <a href="<?php echo esc_url($tab_url); ?>" class="nav-tab<?php echo esc_attr($active_class); ?>">
                <?php echo esc_html($label); ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <section class="icap-seo-content">
        <?php if ($active_tab === 'setup-wizard') : ?>
            <?php
            $scan_tier_value = '';
            if (isset($scan_status_data['scan_tier']) && is_string($scan_status_data['scan_tier'])) {
                $scan_tier_value = sanitize_text_field($scan_status_data['scan_tier']);
            }
            if ($scan_tier_value === '' && $latest_scores_scan_tier !== '') {
                $scan_tier_value = $latest_scores_scan_tier;
            }
            $scan_layers_data = [];
            if (isset($scan_status_data['scan_layers']) && is_array($scan_status_data['scan_layers'])) {
                $scan_layers_data = $scan_status_data['scan_layers'];
            }
            if (empty($scan_layers_data) && !empty($latest_scores_scan_layers)) {
                $scan_layers_data = $latest_scores_scan_layers;
            }
            $scan_status_value = 'n/a';
            if (isset($scan_status_data['status']) && is_string($scan_status_data['status'])) {
                $scan_status_value = sanitize_key($scan_status_data['status']);
            } elseif ($latest_scores_scan_id !== '') {
                $scan_status_value = 'completed';
            }
            $latest_scan_id_display = $connection_settings['last_scan_id'];
            if (isset($scan_status_data['scan_id']) && is_string($scan_status_data['scan_id'])) {
                $latest_scan_id_display = sanitize_text_field($scan_status_data['scan_id']);
            } elseif ($latest_scan_id_display === '' && $latest_scores_scan_id !== '') {
                $latest_scan_id_display = $latest_scores_scan_id;
            }
            $executed_layer_names = [];
            if (isset($scan_layers_data['executed']) && is_array($scan_layers_data['executed'])) {
                foreach ($scan_layers_data['executed'] as $layer_row) {
                    if (is_array($layer_row) && isset($layer_row['name']) && is_string($layer_row['name'])) {
                        $executed_layer_names[] = sanitize_text_field($layer_row['name']);
                    } elseif (is_string($layer_row)) {
                        $executed_layer_names[] = sanitize_text_field($layer_row);
                    }
                }
            }
            $premium_locked_layer_names = [];
            if (isset($scan_layers_data['premium_locked']) && is_array($scan_layers_data['premium_locked'])) {
                foreach ($scan_layers_data['premium_locked'] as $layer_row) {
                    if (is_array($layer_row) && isset($layer_row['name']) && is_string($layer_row['name'])) {
                        $premium_locked_layer_names[] = sanitize_text_field($layer_row['name']);
                    } elseif (is_string($layer_row)) {
                        $premium_locked_layer_names[] = sanitize_text_field($layer_row);
                    }
                }
            }
            ?>
            <h2><?php esc_html_e('Setup Wizard', 'icap-seo'); ?></h2>
            <ol>
                <li><?php esc_html_e('Enter API Base URL, then request site credentials from iCap SEO.', 'icap-seo'); ?></li>
                <li><?php esc_html_e('Run baseline scans anytime, then activate premium subscription for full layered checks.', 'icap-seo'); ?></li>
                <li><?php esc_html_e('Run the first baseline SEO analysis.', 'icap-seo'); ?></li>
                <li><?php esc_html_e('Review prioritized recommendations.', 'icap-seo'); ?></li>
            </ol>
            <div class="icap-seo-actions">
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <input type="hidden" name="action" value="icap_seo_register_site">
                    <?php wp_nonce_field('icap_seo_register_site'); ?>
                    <button type="submit" class="button button-primary"><?php esc_html_e('Request Credentials & Register Site', 'icap-seo'); ?></button>
                </form>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <input type="hidden" name="action" value="icap_seo_test_connection">
                    <?php wp_nonce_field('icap_seo_test_connection'); ?>
                    <button type="submit" class="button"><?php esc_html_e('Test Connection', 'icap-seo'); ?></button>
                </form>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <input type="hidden" name="action" value="icap_seo_trigger_scan">
                    <?php wp_nonce_field('icap_seo_trigger_scan'); ?>
                    <button type="submit" class="button"><?php esc_html_e('Trigger Full Scan', 'icap-seo'); ?></button>
                </form>
            </div>
            <p class="description">
                <?php esc_html_e('Connection profile:', 'icap-seo'); ?>
                <?php esc_html_e('API Base URL', 'icap-seo'); ?>
                <code><?php echo esc_html($connection_settings['api_base_url'] !== '' ? 'configured' : 'missing'); ?></code>
                |
                <?php esc_html_e('Site credentials', 'icap-seo'); ?>
                <code><?php echo esc_html(($connection_settings['site_id'] !== '' && $connection_settings['site_token'] !== '') ? 'present' : 'missing'); ?></code>
            </p>
            <p class="description">
                <?php esc_html_e('Latest scan ID:', 'icap-seo'); ?>
                <code><?php echo esc_html($latest_scan_id_display !== '' ? $latest_scan_id_display : 'n/a'); ?></code>
                |
                <?php esc_html_e('Status:', 'icap-seo'); ?>
                <code><?php echo esc_html($scan_status_value); ?></code>
            </p>
            <p class="description">
                <?php esc_html_e('Scan tier:', 'icap-seo'); ?>
                <code><?php echo esc_html($scan_tier_value !== '' ? $scan_tier_value : 'n/a'); ?></code>
            </p>
            <?php if (!empty($executed_layer_names)) : ?>
                <p class="description">
                    <?php esc_html_e('Executed scan layers:', 'icap-seo'); ?>
                    <code><?php echo esc_html(implode(', ', $executed_layer_names)); ?></code>
                </p>
            <?php endif; ?>
            <?php if (!empty($premium_locked_layer_names)) : ?>
                <p class="description">
                    <?php esc_html_e('Premium-only layers not included in this scan:', 'icap-seo'); ?>
                    <code><?php echo esc_html(implode(', ', $premium_locked_layer_names)); ?></code>
                </p>
            <?php endif; ?>
        <?php elseif ($active_tab === 'site-health') : ?>
            <h2><?php esc_html_e('Site Health', 'icap-seo'); ?></h2>
            <div class="icap-seo-cards">
                <div class="icap-seo-card">
                    <h3><?php esc_html_e('Overall SEO Score', 'icap-seo'); ?></h3>
                    <p><?php echo esc_html($score_snapshot['score'] ?? 'Pending'); ?></p>
                </div>
                <div class="icap-seo-card">
                    <h3><?php esc_html_e('Last Scan', 'icap-seo'); ?></h3>
                    <p><?php echo esc_html($score_snapshot['last_scan'] ?? 'Not available'); ?></p>
                </div>
                <div class="icap-seo-card">
                    <h3><?php esc_html_e('Scored Content Items', 'icap-seo'); ?></h3>
                    <p><?php echo esc_html((string) max(count($content_scores), $latest_scores_item_count)); ?></p>
                </div>
            </div>
            <?php if ($latest_scores_scan_id !== '') : ?>
                <p class="description">
                    <?php esc_html_e('Latest completed scan:', 'icap-seo'); ?>
                    <code><?php echo esc_html($latest_scores_scan_id); ?></code>
                    <?php if ($latest_scores_scan_tier !== '') : ?>
                        |
                        <?php esc_html_e('Tier:', 'icap-seo'); ?>
                        <code><?php echo esc_html($latest_scores_scan_tier); ?></code>
                    <?php endif; ?>
                </p>
            <?php endif; ?>
            <?php if (!empty($latest_scores_executed_layer_names)) : ?>
                <p class="description">
                    <?php esc_html_e('Executed layers:', 'icap-seo'); ?>
                    <code><?php echo esc_html(implode(', ', $latest_scores_executed_layer_names)); ?></code>
                </p>
            <?php endif; ?>
        <?php elseif ($active_tab === 'content-scores') : ?>
            <h2><?php esc_html_e('Content Scores', 'icap-seo'); ?></h2>
            <?php if ($latest_scores_scan_id !== '') : ?>
                <p class="description">
                    <?php esc_html_e('Latest scan:', 'icap-seo'); ?>
                    <code><?php echo esc_html($latest_scores_scan_id); ?></code>
                    <?php if ($latest_scores_scan_tier !== '') : ?>
                        |
                        <?php esc_html_e('Tier:', 'icap-seo'); ?>
                        <code><?php echo esc_html($latest_scores_scan_tier); ?></code>
                    <?php endif; ?>
                    |
                    <?php esc_html_e('Scored items:', 'icap-seo'); ?>
                    <code><?php echo esc_html((string) $latest_scores_item_count); ?></code>
                </p>
            <?php endif; ?>
            <?php if (!empty($latest_scores_executed_layer_names)) : ?>
                <p class="description">
                    <?php esc_html_e('Executed layers:', 'icap-seo'); ?>
                    <code><?php echo esc_html(implode(', ', $latest_scores_executed_layer_names)); ?></code>
                </p>
            <?php endif; ?>
            <div class="icap-seo-table-wrap">
                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Title', 'icap-seo'); ?></th>
                            <th><?php esc_html_e('Type', 'icap-seo'); ?></th>
                            <th><?php esc_html_e('Status', 'icap-seo'); ?></th>
                            <th><?php esc_html_e('iCap Score', 'icap-seo'); ?></th>
                            <th><?php esc_html_e('Rank Math (baseline)', 'icap-seo'); ?></th>
                            <th><?php esc_html_e('Delta', 'icap-seo'); ?></th>
                            <th><?php esc_html_e('Details', 'icap-seo'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($content_scores)) : ?>
                            <tr>
                                <td colspan="7">
                                    <?php if ($latest_scores_source === 'api') : ?>
                                        <?php esc_html_e('No scored content rows were returned for the latest scan yet.', 'icap-seo'); ?>
                                    <?php else : ?>
                                        <?php esc_html_e('No pages or posts found.', 'icap-seo'); ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($content_scores as $row) : ?>
                                <?php
                                $row_content_key = (isset($row['content_key']) && is_string($row['content_key']))
                                    ? sanitize_text_field($row['content_key'])
                                    : '';
                                $row_is_selected = $row_content_key !== '' && $selected_content_key !== '' && $row_content_key === $selected_content_key;
                                ?>
                                <tr<?php echo $row_is_selected ? ' style="background-color:#eef6ff;"' : ''; ?>>
                                    <td>
                                        <a href="<?php echo esc_url($row['edit_link']); ?>">
                                            <?php echo esc_html($row['title']); ?>
                                        </a>
                                    </td>
                                    <td><?php echo esc_html($row['type']); ?></td>
                                    <td><?php echo esc_html($row['status']); ?></td>
                                    <td><?php echo esc_html($row['icap_score']); ?></td>
                                    <td><?php echo esc_html($row['rank_math_score']); ?></td>
                                    <td><?php echo esc_html($row['rank_math_delta']); ?></td>
                                    <td>
                                        <?php if ($row_content_key !== '') : ?>
                                            <?php
                                            $detail_link = add_query_arg(
                                                [
                                                    'page' => 'icap-seo',
                                                    'tab' => 'content-scores',
                                                    'content_key' => $row_content_key,
                                                ],
                                                admin_url('admin.php')
                                            );
                                            ?>
                                            <a href="<?php echo esc_url($detail_link); ?>">
                                                <?php echo esc_html($row_is_selected ? __('Viewing details', 'icap-seo') : __('View details', 'icap-seo')); ?>
                                            </a>
                                        <?php else : ?>
                                            <span>&mdash;</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <p class="description">
                <?php if ($latest_scores_source === 'api') : ?>
                    <?php esc_html_e('Data source: live iCap SEO API scan results.', 'icap-seo'); ?>
                <?php else : ?>
                    <?php esc_html_e('Data source: placeholder fallback (API results unavailable for this view).', 'icap-seo'); ?>
                <?php endif; ?>
            </p>
            <?php if ($selected_content_key !== '') : ?>
                <hr>
                <h3><?php esc_html_e('Content Detail', 'icap-seo'); ?></h3>
                <?php if ($content_score_detail_error !== '') : ?>
                    <div class="notice notice-error inline">
                        <p><?php echo esc_html($content_score_detail_error); ?></p>
                    </div>
                <?php elseif (empty($content_score_detail)) : ?>
                    <p><?php esc_html_e('No detail data available for this content key yet.', 'icap-seo'); ?></p>
                <?php else : ?>
                    <?php
                    $detail_title = isset($content_score_detail['title']) && is_string($content_score_detail['title']) && $content_score_detail['title'] !== ''
                        ? sanitize_text_field($content_score_detail['title'])
                        : __('Untitled content', 'icap-seo');
                    $detail_status = isset($content_score_detail['status']) && is_string($content_score_detail['status'])
                        ? sanitize_text_field($content_score_detail['status'])
                        : '';
                    $detail_type = isset($content_score_detail['post_type']) && is_string($content_score_detail['post_type'])
                        ? sanitize_text_field($content_score_detail['post_type'])
                        : '';
                    $detail_permalink = isset($content_score_detail['permalink']) && is_string($content_score_detail['permalink'])
                        ? esc_url_raw($content_score_detail['permalink'])
                        : '';
                    $detail_score = isset($content_score_detail['overall_score']) ? (int) $content_score_detail['overall_score'] : 0;
                    $detail_rank_math = (isset($content_score_detail['rank_math_score']) && $content_score_detail['rank_math_score'] !== null)
                        ? (int) $content_score_detail['rank_math_score']
                        : null;
                    $detail_delta = (isset($content_score_detail['delta_vs_rank_math']) && $content_score_detail['delta_vs_rank_math'] !== null)
                        ? (int) $content_score_detail['delta_vs_rank_math']
                        : null;
                    $detail_category_scores = isset($content_score_detail['category_scores']) && is_array($content_score_detail['category_scores'])
                        ? $content_score_detail['category_scores']
                        : [];
                    if (!empty($detail_category_scores)) {
                        arsort($detail_category_scores);
                    }
                    $detail_issues = isset($content_score_detail['issues']) && is_array($content_score_detail['issues'])
                        ? $content_score_detail['issues']
                        : [];
                    $severity_order = ['critical' => 0, 'high' => 1, 'medium' => 2, 'low' => 3];
                    usort($detail_issues, static function ($left, $right) use ($severity_order): int {
                        $left_severity = isset($left['severity']) && is_string($left['severity']) ? sanitize_key($left['severity']) : 'medium';
                        $right_severity = isset($right['severity']) && is_string($right['severity']) ? sanitize_key($right['severity']) : 'medium';
                        $left_rank = $severity_order[$left_severity] ?? 4;
                        $right_rank = $severity_order[$right_severity] ?? 4;
                        return $left_rank <=> $right_rank;
                    });
                    $detail_history = isset($content_score_detail['history']) && is_array($content_score_detail['history'])
                        ? $content_score_detail['history']
                        : [];
                    $trend_summary = __('Insufficient history for trend.', 'icap-seo');
                    if (count($detail_history) >= 2) {
                        $latest_history_score = isset($detail_history[0]['overall_score']) ? (int) $detail_history[0]['overall_score'] : 0;
                        $oldest_history_score = isset($detail_history[count($detail_history) - 1]['overall_score']) ? (int) $detail_history[count($detail_history) - 1]['overall_score'] : 0;
                        $trend_delta = $latest_history_score - $oldest_history_score;
                        if ($trend_delta > 0) {
                            $trend_summary = sprintf(__('Improving (%+d over %d scans).', 'icap-seo'), $trend_delta, count($detail_history));
                        } elseif ($trend_delta < 0) {
                            $trend_summary = sprintf(__('Declining (%+d over %d scans).', 'icap-seo'), $trend_delta, count($detail_history));
                        } else {
                            $trend_summary = sprintf(__('Flat trend (0 over %d scans).', 'icap-seo'), count($detail_history));
                        }
                    }
                    ?>
                    <h4><?php echo esc_html($detail_title); ?></h4>
                    <p class="description">
                        <?php esc_html_e('Type:', 'icap-seo'); ?> <code><?php echo esc_html($detail_type !== '' ? $detail_type : 'n/a'); ?></code>
                        |
                        <?php esc_html_e('Status:', 'icap-seo'); ?> <code><?php echo esc_html($detail_status !== '' ? $detail_status : 'n/a'); ?></code>
                        |
                        <?php esc_html_e('Overall score:', 'icap-seo'); ?> <code><?php echo esc_html(sprintf('%d/100', $detail_score)); ?></code>
                        |
                        <?php esc_html_e('Rank Math:', 'icap-seo'); ?> <code><?php echo esc_html($detail_rank_math === null ? 'n/a' : sprintf('%d/100', $detail_rank_math)); ?></code>
                        |
                        <?php esc_html_e('Delta:', 'icap-seo'); ?> <code><?php echo esc_html($detail_delta === null ? 'n/a' : sprintf('%+d', $detail_delta)); ?></code>
                    </p>
                    <?php if ($detail_permalink !== '') : ?>
                        <p><a href="<?php echo esc_url($detail_permalink); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('View published URL', 'icap-seo'); ?></a></p>
                    <?php endif; ?>
                    <h4><?php esc_html_e('Category score breakdown', 'icap-seo'); ?></h4>
                    <?php if (empty($detail_category_scores)) : ?>
                        <p><?php esc_html_e('No category scores returned for this scan.', 'icap-seo'); ?></p>
                    <?php else : ?>
                        <ul>
                            <?php foreach ($detail_category_scores as $category_name => $category_score) : ?>
                                <li>
                                    <strong><?php echo esc_html(ucwords(str_replace('_', ' ', sanitize_text_field((string) $category_name)))); ?>:</strong>
                                    <?php echo esc_html(sprintf('%d/100', (int) $category_score)); ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                    <h4><?php esc_html_e('Prioritized recommendations', 'icap-seo'); ?></h4>
                    <?php if (empty($detail_issues)) : ?>
                        <p><?php esc_html_e('No recommendation issues returned for this content item.', 'icap-seo'); ?></p>
                    <?php else : ?>
                        <ol>
                            <?php foreach ($detail_issues as $issue) : ?>
                                <?php
                                $issue_code = isset($issue['issue_code']) ? sanitize_key((string) $issue['issue_code']) : '';
                                $issue_severity = isset($issue['severity']) ? sanitize_text_field((string) $issue['severity']) : 'medium';
                                $issue_description = isset($issue['description']) ? sanitize_text_field((string) $issue['description']) : '';
                                $issue_recommended_fix = isset($issue['recommended_fix']) ? sanitize_text_field((string) $issue['recommended_fix']) : '';
                                $issue_effort = isset($issue['estimated_effort']) ? sanitize_text_field((string) $issue['estimated_effort']) : '';
                                ?>
                                <li>
                                    <strong><?php echo esc_html(strtoupper($issue_severity)); ?></strong>
                                    <?php if ($issue_effort !== '') : ?>
                                        <span>(<?php echo esc_html(sprintf(__('effort: %s', 'icap-seo'), $issue_effort)); ?>)</span>
                                    <?php endif; ?>
                                    <div><?php echo esc_html($issue_description !== '' ? $issue_description : __('No issue description provided.', 'icap-seo')); ?></div>
                                    <?php if ($issue_recommended_fix !== '') : ?>
                                        <div><em><?php echo esc_html($issue_recommended_fix); ?></em></div>
                                    <?php endif; ?>
                                    <?php if ($issue_code !== '') : ?>
                                        <div style="margin-top:8px;">
                                            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                                                <input type="hidden" name="action" value="icap_seo_apply_remediation">
                                                <input type="hidden" name="content_key" value="<?php echo esc_attr($selected_content_key); ?>">
                                                <input type="hidden" name="approved_issue_codes[]" value="<?php echo esc_attr($issue_code); ?>">
                                                <?php wp_nonce_field('icap_seo_apply_remediation'); ?>
                                                <button type="submit" class="button button-secondary"><?php esc_html_e('Apply this recommendation', 'icap-seo'); ?></button>
                                            </form>
                                        </div>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ol>
                    <?php endif; ?>
                    <h4><?php esc_html_e('Remediation preview and apply', 'icap-seo'); ?></h4>
                    <p class="description"><?php esc_html_e('Use the action below to submit all listed recommendations, or apply a single recommendation directly from its row above.', 'icap-seo'); ?></p>
                    <div class="icap-seo-actions">
                        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                            <input type="hidden" name="action" value="icap_seo_preview_remediation">
                            <input type="hidden" name="content_key" value="<?php echo esc_attr($selected_content_key); ?>">
                            <?php wp_nonce_field('icap_seo_preview_remediation'); ?>
                            <?php foreach ($detail_issues as $issue) : ?>
                                <?php
                                $issue_code = isset($issue['issue_code']) && is_string($issue['issue_code'])
                                    ? sanitize_key($issue['issue_code'])
                                    : '';
                                ?>
                                <?php if ($issue_code !== '') : ?>
                                    <input type="hidden" name="approved_issue_codes[]" value="<?php echo esc_attr($issue_code); ?>">
                                <?php endif; ?>
                            <?php endforeach; ?>
                            <button type="submit" class="button"><?php esc_html_e('Refresh remediation preview', 'icap-seo'); ?></button>
                        </form>
                        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                            <input type="hidden" name="action" value="icap_seo_apply_remediation">
                            <input type="hidden" name="content_key" value="<?php echo esc_attr($selected_content_key); ?>">
                            <?php wp_nonce_field('icap_seo_apply_remediation'); ?>
                            <?php foreach ($detail_issues as $issue) : ?>
                                <?php
                                $issue_code = isset($issue['issue_code']) && is_string($issue['issue_code'])
                                    ? sanitize_key($issue['issue_code'])
                                    : '';
                                ?>
                                <?php if ($issue_code !== '') : ?>
                                    <input type="hidden" name="approved_issue_codes[]" value="<?php echo esc_attr($issue_code); ?>">
                                <?php endif; ?>
                            <?php endforeach; ?>
                            <button type="submit" class="button button-primary"><?php esc_html_e('Apply all recommendations', 'icap-seo'); ?></button>
                        </form>
                    </div>
                    <?php if ($remediation_preview_error !== '') : ?>
                        <div class="notice notice-error inline">
                            <p><?php echo esc_html($remediation_preview_error); ?></p>
                        </div>
                    <?php else : ?>
                        <?php
                        $preview_changes = (isset($remediation_preview['proposed_changes']) && is_array($remediation_preview['proposed_changes']))
                            ? $remediation_preview['proposed_changes']
                            : [];
                        $preview_summary = (isset($remediation_preview['summary']) && is_array($remediation_preview['summary']))
                            ? $remediation_preview['summary']
                            : [];
                        ?>
                        <?php if (empty($preview_changes)) : ?>
                            <p><?php esc_html_e('No proposed remediation changes are currently available for this content item.', 'icap-seo'); ?></p>
                        <?php else : ?>
                            <p class="description">
                                <?php
                                $queued_estimate = isset($preview_summary['proposed_change_count']) ? (int) $preview_summary['proposed_change_count'] : count($preview_changes);
                                echo esc_html(sprintf(__('Proposed changes: %d', 'icap-seo'), $queued_estimate));
                                ?>
                            </p>
                            <ul>
                                <?php foreach ($preview_changes as $change_row) : ?>
                                    <?php
                                    $change_issue_code = isset($change_row['issue_code']) ? sanitize_text_field((string) $change_row['issue_code']) : '';
                                    $change_summary = isset($change_row['summary']) ? sanitize_text_field((string) $change_row['summary']) : '';
                                    $change_severity = isset($change_row['severity']) ? sanitize_text_field((string) $change_row['severity']) : 'medium';
                                    $change_effort = isset($change_row['estimated_effort']) ? sanitize_text_field((string) $change_row['estimated_effort']) : '';
                                    $change_review = !empty($change_row['requires_editor_review']);
                                    ?>
                                    <li>
                                        <strong><?php echo esc_html(strtoupper($change_severity)); ?></strong>
                                        <?php if ($change_issue_code !== '') : ?>
                                            <code><?php echo esc_html($change_issue_code); ?></code>
                                        <?php endif; ?>
                                        <?php if ($change_effort !== '') : ?>
                                            <span>(<?php echo esc_html(sprintf(__('effort: %s', 'icap-seo'), $change_effort)); ?>)</span>
                                        <?php endif; ?>
                                        <div><?php echo esc_html($change_summary !== '' ? $change_summary : __('No summary provided.', 'icap-seo')); ?></div>
                                        <?php if ($change_review) : ?>
                                            <div><em><?php esc_html_e('Requires editor review before publish.', 'icap-seo'); ?></em></div>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    <?php endif; ?>
                    <h4><?php esc_html_e('Score trend history', 'icap-seo'); ?></h4>
                    <p class="description"><?php echo esc_html($trend_summary); ?></p>
                    <?php if (empty($detail_history)) : ?>
                        <p><?php esc_html_e('No historical scan points available yet.', 'icap-seo'); ?></p>
                    <?php else : ?>
                        <table class="widefat striped">
                            <thead>
                                <tr>
                                    <th><?php esc_html_e('Scored At', 'icap-seo'); ?></th>
                                    <th><?php esc_html_e('Score', 'icap-seo'); ?></th>
                                    <th><?php esc_html_e('Scan ID', 'icap-seo'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($detail_history as $history_row) : ?>
                                    <?php
                                    $history_scored_at = isset($history_row['scored_at']) ? sanitize_text_field((string) $history_row['scored_at']) : '';
                                    $history_score = isset($history_row['overall_score']) ? (int) $history_row['overall_score'] : 0;
                                    $history_scan_id = isset($history_row['scan_id']) ? sanitize_text_field((string) $history_row['scan_id']) : '';
                                    ?>
                                    <tr>
                                        <td><?php echo esc_html($history_scored_at !== '' ? $history_scored_at : 'n/a'); ?></td>
                                        <td><?php echo esc_html(sprintf('%d/100', $history_score)); ?></td>
                                        <td><code><?php echo esc_html($history_scan_id !== '' ? $history_scan_id : 'n/a'); ?></code></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                <?php endif; ?>
            <?php endif; ?>
        <?php elseif ($active_tab === 'settings') : ?>
            <h2><?php esc_html_e('API Connection Settings', 'icap-seo'); ?></h2>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="icap-seo-settings-form">
                <input type="hidden" name="action" value="icap_seo_save_settings">
                <?php wp_nonce_field('icap_seo_save_settings'); ?>
                <table class="form-table" role="presentation">
                    <tbody>
                        <tr>
                            <th scope="row"><label for="icap-seo-api-base-url"><?php esc_html_e('API Base URL', 'icap-seo'); ?></label></th>
                            <td>
                                <input id="icap-seo-api-base-url" name="api_base_url" type="url" class="regular-text" value="<?php echo esc_attr($connection_settings['api_base_url']); ?>" placeholder="https://api.example.com">
                                <p class="description"><?php esc_html_e('Required for self-serve registration. Example: https://api.icapseo.com', 'icap-seo'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="icap-seo-registration-token"><?php esc_html_e('Registration Token', 'icap-seo'); ?></label></th>
                            <td>
                                <input id="icap-seo-registration-token" name="registration_token" type="password" class="regular-text" value="<?php echo esc_attr($connection_settings['registration_token']); ?>" autocomplete="off">
                                <p class="description"><?php esc_html_e('Required for registration requests. If ICAP_SEO_REGISTRATION_TOKEN is defined in wp-config.php, that constant takes precedence over this saved value.', 'icap-seo'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="icap-seo-site-id"><?php esc_html_e('Site ID', 'icap-seo'); ?></label></th>
                            <td>
                                <input id="icap-seo-site-id" name="site_id" type="text" class="regular-text" value="<?php echo esc_attr($connection_settings['site_id']); ?>">
                                <p class="description"><?php esc_html_e('Usually auto-filled after registration.', 'icap-seo'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="icap-seo-site-token"><?php esc_html_e('Site Token', 'icap-seo'); ?></label></th>
                            <td>
                                <input id="icap-seo-site-token" name="site_token" type="password" class="regular-text" value="<?php echo esc_attr($connection_settings['site_token']); ?>" autocomplete="off">
                                <p class="description"><?php esc_html_e('Usually auto-filled after registration. Stored in WordPress options; rotate from customer portal when needed.', 'icap-seo'); ?></p>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <p>
                    <button type="submit" class="button button-primary"><?php esc_html_e('Save Settings', 'icap-seo'); ?></button>
                </p>
            </form>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="icap-seo-settings-form">
                <input type="hidden" name="action" value="icap_seo_check_billing_status">
                <?php wp_nonce_field('icap_seo_check_billing_status'); ?>
                <p>
                    <button type="submit" class="button"><?php esc_html_e('Check Billing Status', 'icap-seo'); ?></button>
                </p>
            </form>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="icap-seo-settings-form">
                <input type="hidden" name="action" value="icap_seo_start_billing_checkout">
                <?php wp_nonce_field('icap_seo_start_billing_checkout'); ?>
                <p>
                    <button type="submit" class="button button-primary"><?php esc_html_e('Start Billing Checkout', 'icap-seo'); ?></button>
                </p>
            </form>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="icap-seo-settings-form">
                <input type="hidden" name="action" value="icap_seo_open_billing_portal">
                <?php wp_nonce_field('icap_seo_open_billing_portal'); ?>
                <p>
                    <button type="submit" class="button"><?php esc_html_e('Open Billing Portal', 'icap-seo'); ?></button>
                </p>
            </form>
            <p class="description">
                <?php esc_html_e('Last successful score sync:', 'icap-seo'); ?>
                <code><?php echo esc_html($connection_settings['last_sync_at'] ?: 'n/a'); ?></code>
            </p>
            <p class="description">
                <?php esc_html_e('Last known billing state:', 'icap-seo'); ?>
                <code><?php echo esc_html($connection_settings['last_billing_state'] ?: 'unknown'); ?></code>
                |
                <?php esc_html_e('Last billing check:', 'icap-seo'); ?>
                <code><?php echo esc_html($connection_settings['last_billing_checked_at'] ?: 'n/a'); ?></code>
            </p>
        <?php else : ?>
            <h2><?php esc_html_e('Home', 'icap-seo'); ?></h2>
            <p><?php esc_html_e('Welcome to the iCap SEO service dashboard. This plugin will provide site scoring, setup automation, and cloud-powered SEO recommendations.', 'icap-seo'); ?></p>
            <div class="icap-seo-cards">
                <div class="icap-seo-card">
                    <h3><?php esc_html_e('Connection Status', 'icap-seo'); ?></h3>
                    <p><?php echo esc_html($score_snapshot['status']); ?></p>
                </div>
                <div class="icap-seo-card">
                    <h3><?php esc_html_e('SEO Score', 'icap-seo'); ?></h3>
                    <p><?php echo esc_html($score_snapshot['score'] ?? 'Coming soon'); ?></p>
                </div>
                <div class="icap-seo-card">
                    <h3><?php esc_html_e('What is next?', 'icap-seo'); ?></h3>
                    <p><?php esc_html_e('Complete setup to unlock baseline scans and recommendation workflows.', 'icap-seo'); ?></p>
                </div>
            </div>
        <?php endif; ?>
    </section>
</div>
