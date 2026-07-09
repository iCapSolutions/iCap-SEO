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
    'render_fallback' => ['type' => 'error', 'message' => __('Dashboard loaded in fallback mode after an internal error. Please retry and check logs.', 'icap-seo')],
];

if (!isset($latest_content_scores_meta) || !is_array($latest_content_scores_meta)) {
    $latest_content_scores_meta = [];
}
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
?>
<div class="wrap icap-seo-wrap">
    <h1 class="icap-seo-header">
        <img src="<?php echo esc_url(ICAP_SEO_PLUGIN_URL . 'assets/images/icap-seo-logo.svg'); ?>" alt="<?php esc_attr_e('iCap SEO', 'icap-seo'); ?>" class="icap-seo-logo">
        <?php esc_html_e('iCap SEO', 'icap-seo'); ?>
    </h1>
    <p><?php esc_html_e('SEO intelligence for WordPress sites by iCapSolutions.', 'icap-seo'); ?></p>
    <?php if ($notice_code !== '' && isset($notice_map[$notice_code])) : ?>
        <div class="notice <?php echo esc_attr($notice_map[$notice_code]['type'] === 'error' ? 'notice-error' : 'notice-success'); ?> is-dismissible">
            <p><?php echo esc_html($notice_map[$notice_code]['message']); ?></p>
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
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($content_scores)) : ?>
                            <tr>
                                <td colspan="6">
                                    <?php if ($latest_scores_source === 'api') : ?>
                                        <?php esc_html_e('No scored content rows were returned for the latest scan yet.', 'icap-seo'); ?>
                                    <?php else : ?>
                                        <?php esc_html_e('No pages or posts found.', 'icap-seo'); ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($content_scores as $row) : ?>
                                <tr>
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
