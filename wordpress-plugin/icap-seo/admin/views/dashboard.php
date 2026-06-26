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
            <h2><?php esc_html_e('Setup Wizard', 'icap-seo'); ?></h2>
            <ol>
                <li><?php esc_html_e('Enter API Base URL, then request site credentials from iCap SEO.', 'icap-seo'); ?></li>
                <li><?php esc_html_e('Confirm subscription entitlement is active before triggering scans.', 'icap-seo'); ?></li>
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
                    <input type="hidden" name="action" value="icap_seo_trigger_scan">
                    <?php wp_nonce_field('icap_seo_trigger_scan'); ?>
                    <button type="submit" class="button"><?php esc_html_e('Trigger Full Scan', 'icap-seo'); ?></button>
                </form>
            </div>
            <p class="description">
                <?php esc_html_e('Latest scan ID:', 'icap-seo'); ?>
                <code><?php echo esc_html($connection_settings['last_scan_id'] ?: 'n/a'); ?></code>
                |
                <?php esc_html_e('Status:', 'icap-seo'); ?>
                <code><?php echo esc_html($scan_status_data['status'] ?? 'n/a'); ?></code>
            </p>
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
                    <h3><?php esc_html_e('Recommendations', 'icap-seo'); ?></h3>
                    <p><?php echo esc_html(count($recommendation_preview['items'])); ?> <?php esc_html_e('queued items', 'icap-seo'); ?></p>
                </div>
            </div>
        <?php elseif ($active_tab === 'content-scores') : ?>
            <h2><?php esc_html_e('Content Scores', 'icap-seo'); ?></h2>
            <p><?php esc_html_e('Pages and posts with iCap placeholder scoring and side-by-side Rank Math comparison baseline.', 'icap-seo'); ?></p>
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
                                <td colspan="6"><?php esc_html_e('No pages or posts found.', 'icap-seo'); ?></td>
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
                <?php esc_html_e('Data source: API when configured and reachable; placeholder values otherwise.', 'icap-seo'); ?>
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
