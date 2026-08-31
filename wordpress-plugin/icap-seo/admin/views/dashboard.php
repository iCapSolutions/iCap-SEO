<?php
if (!defined('ABSPATH')) {
    exit;
}

$tabs = [
    'overview' => __('Overview', 'icap-seo'),
    'setup-wizard' => __('Setup Wizard', 'icap-seo'),
    'content-scores' => __('Content Scores', 'icap-seo'),
    'settings' => __('Settings', 'icap-seo'),
];

$notice_map = [
    'settings_saved' => ['type' => 'updated', 'message' => __('Connection settings saved.', 'icap-seo')],
    'register_success' => ['type' => 'updated', 'message' => __('Site registration request succeeded.', 'icap-seo')],
    'registration_token_missing' => ['type' => 'error', 'message' => __('Site registration failed. Set Registration Token in Settings or define ICAP_SEO_REGISTRATION_TOKEN in wp-config.php.', 'icap-seo')],
    'api_base_url_missing' => ['type' => 'error', 'message' => __('Site registration failed. API Base URL is required.', 'icap-seo')],
    'register_failed' => ['type' => 'error', 'message' => __('Site registration failed. Confirm API Base URL and Registration Token, then retry.', 'icap-seo')],
    'registration_request_submitted' => ['type' => 'updated', 'message' => __('Registration request submitted. Check your email to verify and complete registration.', 'icap-seo')],
    'registration_request_invalid_email' => ['type' => 'error', 'message' => __('Enter a valid email address to request registration.', 'icap-seo')],
    'registration_request_invalid_tier' => ['type' => 'error', 'message' => __('Select a plan to request registration.', 'icap-seo')],
    'registration_request_failed' => ['type' => 'error', 'message' => __('Registration request failed. Confirm API Base URL and retry.', 'icap-seo')],
    'connection_ok_authenticated' => ['type' => 'updated', 'message' => __('Connection test succeeded. API and saved site credentials are valid.', 'icap-seo')],
    'connection_ok_reachable' => ['type' => 'updated', 'message' => __('Connection test reached the API. Next step: register this site to provision credentials.', 'icap-seo')],
    'connection_api_base_url_missing' => ['type' => 'error', 'message' => __('Connection test failed. API Base URL is required before testing.', 'icap-seo')],
    'connection_invalid_token' => ['type' => 'error', 'message' => __('Connection test reached the API, but Site ID/Site Token were rejected. Re-run registration in Setup Wizard.', 'icap-seo')],
    'connection_endpoint_not_found' => ['type' => 'error', 'message' => __('Connection test reached the host but API route was not found. Confirm API Base URL points to the iCap SEO API root.', 'icap-seo')],
    'connection_unreachable' => ['type' => 'error', 'message' => __('Connection test could not reach the API. Verify network access and API availability.', 'icap-seo')],
    'connection_failed' => ['type' => 'error', 'message' => __('Connection test failed with an unexpected API response. Check logs and retry.', 'icap-seo')],
    'scan_queued' => ['type' => 'updated', 'message' => __('Scan request queued.', 'icap-seo')],
    'content_rescan_complete' => ['type' => 'updated', 'message' => __('Page rescan completed. Recommendations refreshed from the latest scan snapshot.', 'icap-seo')],
    'content_rescan_key_missing' => ['type' => 'error', 'message' => __('Page rescan failed: content key is required.', 'icap-seo')],
    'content_rescan_not_found' => ['type' => 'error', 'message' => __('Page rescan could not find this content item via the WordPress REST API.', 'icap-seo')],
    'content_rescan_validation_error' => ['type' => 'error', 'message' => __('Page rescan failed validation. Refresh this page and retry.', 'icap-seo')],
    'content_rescan_failed' => ['type' => 'error', 'message' => __('Page rescan failed. Confirm API connectivity and retry.', 'icap-seo')],
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
    'billing_status_free_tier' => ['type' => 'updated', 'message' => __('Billing status check: this site is on the free tier (basic scans only).', 'icap-seo')],
    'ai_credit_checkout_not_configured' => ['type' => 'error', 'message' => __('AI credit checkout requires site registration credentials. Register this site first.', 'icap-seo')],
    'ai_credit_checkout_premium_required' => ['type' => 'error', 'message' => __('AI credits can only be purchased on an active premium subscription. Upgrade to premium first.', 'icap-seo')],
    'ai_credit_checkout_misconfigured' => ['type' => 'error', 'message' => __('AI credit checkout is not fully configured yet. Please try again shortly.', 'icap-seo')],
    'ai_credit_checkout_unavailable' => ['type' => 'error', 'message' => __('AI credit checkout is temporarily unavailable. Please retry shortly.', 'icap-seo')],
    'ai_credit_checkout_failed' => ['type' => 'error', 'message' => __('AI credit checkout request failed. Please retry.', 'icap-seo')],
    'ai_credit_checkout_returned' => ['type' => 'updated', 'message' => __('AI credit purchase completed. Your balance has been updated below.', 'icap-seo')],
    'ai_credit_checkout_cancelled' => ['type' => 'error', 'message' => __('AI credit purchase was canceled before completion.', 'icap-seo')],
    'remediation_preview_ready' => ['type' => 'updated', 'message' => __('Remediation preview refreshed for this content item.', 'icap-seo')],
    'remediation_apply_queued' => ['type' => 'updated', 'message' => __('Remediation apply request was accepted and queued.', 'icap-seo')],
    'remediation_apply_title_updated' => ['type' => 'updated', 'message' => __('Remediation applied locally for this item. Re-scan to verify score movement.', 'icap-seo')],
    'remediation_apply_noop' => ['type' => 'error', 'message' => __('No local SEO field changes were applied for this recommendation.', 'icap-seo')],
    'remediation_apply_title_update_failed' => ['type' => 'error', 'message' => __('Remediation request was accepted, but local field updates could not be applied. Verify edit permissions and content key mapping.', 'icap-seo')],
    'remediation_content_key_missing' => ['type' => 'error', 'message' => __('Remediation request failed: content key is required.', 'icap-seo')],
    'remediation_validation_error' => ['type' => 'error', 'message' => __('Remediation request failed validation. Refresh content details and retry.', 'icap-seo')],
    'remediation_auth_error' => ['type' => 'error', 'message' => __('Remediation request failed authentication. Re-register the site credentials and retry.', 'icap-seo')],
    'remediation_preview_unavailable' => ['type' => 'error', 'message' => __('Remediation preview is temporarily unavailable. Please retry shortly.', 'icap-seo')],
    'remediation_apply_unavailable' => ['type' => 'error', 'message' => __('Remediation apply is temporarily unavailable. Please retry shortly.', 'icap-seo')],
    'remediation_preview_failed' => ['type' => 'error', 'message' => __('Remediation preview failed with an unexpected response.', 'icap-seo')],
    'remediation_apply_failed' => ['type' => 'error', 'message' => __('Remediation apply failed with an unexpected response.', 'icap-seo')],
    'content_depth_preview_ready' => ['type' => 'updated', 'message' => __('Content depth draft generated. Review it below before publishing.', 'icap-seo')],
    'content_depth_already_sufficient' => ['type' => 'error', 'message' => __('No-op: this page already meets the content depth word-count target.', 'icap-seo')],
    'content_depth_published' => ['type' => 'updated', 'message' => __('Content depth draft published to this page. Re-scan to verify score movement.', 'icap-seo')],
    'content_depth_discarded' => ['type' => 'updated', 'message' => __('Content depth draft discarded.', 'icap-seo')],
    'content_depth_content_key_missing' => ['type' => 'error', 'message' => __('Content depth request failed: content key is required.', 'icap-seo')],
    'content_depth_preview_failed' => ['type' => 'error', 'message' => __('Could not generate a content depth draft for this page. Verify edit permissions and content key mapping.', 'icap-seo')],
    'content_depth_publish_failed' => ['type' => 'error', 'message' => __('Could not publish the content depth draft. Verify edit permissions and try again.', 'icap-seo')],
    'content_depth_no_draft' => ['type' => 'error', 'message' => __('No content depth draft is pending for this page. Generate a preview first.', 'icap-seo')],
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
    $title_changed = isset($_GET['title_changed'])
        ? sanitize_key((string) wp_unslash($_GET['title_changed'])) === '1'
        : $title_before !== $title_after;
    $excerpt_before = isset($_GET['excerpt_before']) ? sanitize_text_field((string) wp_unslash($_GET['excerpt_before'])) : '';
    $excerpt_after = isset($_GET['excerpt_after']) ? sanitize_text_field((string) wp_unslash($_GET['excerpt_after'])) : '';
    $excerpt_changed = isset($_GET['excerpt_changed'])
        ? sanitize_key((string) wp_unslash($_GET['excerpt_changed'])) === '1'
        : $excerpt_before !== $excerpt_after;

    if ($title_changed) {
        $notice_override_message = sprintf(
            __('Title updated: "%1$s" → "%2$s".', 'icap-seo'),
            $title_before !== '' ? $title_before : __('(empty)', 'icap-seo'),
            $title_after !== '' ? $title_after : __('(empty)', 'icap-seo')
        );
    }
    if ($excerpt_changed) {
        $notice_override_message .= ' ' . sprintf(
            __('Page excerpt/meta description updated: "%1$s" → "%2$s".', 'icap-seo'),
            $excerpt_before !== '' ? $excerpt_before : __('(empty)', 'icap-seo'),
            $excerpt_after !== '' ? $excerpt_after : __('(empty)', 'icap-seo')
        );
    }
    $h1_before = isset($_GET['h1_before']) ? sanitize_text_field((string) wp_unslash($_GET['h1_before'])) : '';
    $h1_after = isset($_GET['h1_after']) ? sanitize_text_field((string) wp_unslash($_GET['h1_after'])) : '';
    $h1_changed = isset($_GET['h1_changed'])
        ? sanitize_key((string) wp_unslash($_GET['h1_changed'])) === '1'
        : $h1_before !== $h1_after;
    if ($h1_changed) {
        $notice_override_message .= ' ' . sprintf(
            __('H1 updated: "%1$s" → "%2$s".', 'icap-seo'),
            $h1_before !== '' ? $h1_before : __('(empty)', 'icap-seo'),
            $h1_after !== '' ? $h1_after : __('(empty)', 'icap-seo')
        );
    }
    $images_alt_before = isset($_GET['images_alt_before']) ? (int) sanitize_text_field((string) wp_unslash($_GET['images_alt_before'])) : 0;
    $images_alt_updated_count = isset($_GET['images_alt_updated_count']) ? (int) sanitize_text_field((string) wp_unslash($_GET['images_alt_updated_count'])) : 0;
    $images_alt_changed = isset($_GET['images_alt_changed'])
        && sanitize_key((string) wp_unslash($_GET['images_alt_changed'])) === '1';
    if ($images_alt_changed) {
        $notice_override_message .= ' ' . sprintf(
            __('Image alt text added/updated for %1$d of %2$d image(s) missing alt text.', 'icap-seo'),
            $images_alt_updated_count,
            $images_alt_before
        );
    }
    $images_dimensions_before = isset($_GET['images_dimensions_before']) ? (int) sanitize_text_field((string) wp_unslash($_GET['images_dimensions_before'])) : 0;
    $images_dimensions_updated_count = isset($_GET['images_dimensions_updated_count']) ? (int) sanitize_text_field((string) wp_unslash($_GET['images_dimensions_updated_count'])) : 0;
    $images_dimensions_changed = isset($_GET['images_dimensions_changed'])
        && sanitize_key((string) wp_unslash($_GET['images_dimensions_changed'])) === '1';
    if ($images_dimensions_changed) {
        $notice_override_message .= ' ' . sprintf(
            __('Width/height added for %1$d of %2$d image(s) missing dimensions.', 'icap-seo'),
            $images_dimensions_updated_count,
            $images_dimensions_before
        );
    }
    $images_lazy_before = isset($_GET['images_lazy_before']) ? (int) sanitize_text_field((string) wp_unslash($_GET['images_lazy_before'])) : 0;
    $images_lazy_updated_count = isset($_GET['images_lazy_updated_count']) ? (int) sanitize_text_field((string) wp_unslash($_GET['images_lazy_updated_count'])) : 0;
    $images_lazy_changed = isset($_GET['images_lazy_changed'])
        && sanitize_key((string) wp_unslash($_GET['images_lazy_changed'])) === '1';
    if ($images_lazy_changed) {
        $notice_override_message .= ' ' . sprintf(
            __('loading="lazy" added to %1$d of %2$d eligible image(s).', 'icap-seo'),
            $images_lazy_updated_count,
            $images_lazy_before
        );
    }
    $canonical_after = isset($_GET['canonical_after']) ? esc_url_raw((string) wp_unslash($_GET['canonical_after'])) : '';
    $canonical_changed = isset($_GET['canonical_changed'])
        && sanitize_key((string) wp_unslash($_GET['canonical_changed'])) === '1';
    if ($canonical_changed) {
        $notice_override_message .= ' ' . sprintf(
            __('Canonical URL set to "%s".', 'icap-seo'),
            $canonical_after !== '' ? $canonical_after : __('(empty)', 'icap-seo')
        );
    }
    $jsonld_schema_after = isset($_GET['jsonld_schema_after']) ? sanitize_text_field((string) wp_unslash($_GET['jsonld_schema_after'])) : '';
    $jsonld_schema_changed = isset($_GET['jsonld_schema_changed'])
        && sanitize_key((string) wp_unslash($_GET['jsonld_schema_changed'])) === '1';
    if ($jsonld_schema_changed) {
        $notice_override_message .= ' ' . sprintf(
            __('JSON-LD schema added (%s).', 'icap-seo'),
            $jsonld_schema_after !== '' ? $jsonld_schema_after : __('unknown type', 'icap-seo')
        );
    }
    $headings_added_count = isset($_GET['headings_added_count']) ? (int) sanitize_text_field((string) wp_unslash($_GET['headings_added_count'])) : 0;
    $heading_structure_changed = isset($_GET['heading_structure_changed'])
        && sanitize_key((string) wp_unslash($_GET['heading_structure_changed'])) === '1';
    if ($heading_structure_changed) {
        $notice_override_message .= ' ' . sprintf(
            __('%d heading(s) added to improve page structure.', 'icap-seo'),
            $headings_added_count
        );
    }
    $internal_links_added_count = isset($_GET['internal_links_added_count']) ? (int) sanitize_text_field((string) wp_unslash($_GET['internal_links_added_count'])) : 0;
    $internal_linking_changed = isset($_GET['internal_linking_changed'])
        && sanitize_key((string) wp_unslash($_GET['internal_linking_changed'])) === '1';
    if ($internal_linking_changed) {
        $notice_override_message .= ' ' . sprintf(
            __('%d internal link(s) added.', 'icap-seo'),
            $internal_links_added_count
        );
    }
    $paragraphs_added_count = isset($_GET['paragraphs_added_count']) ? (int) sanitize_text_field((string) wp_unslash($_GET['paragraphs_added_count'])) : 0;
    $paragraph_structure_changed = isset($_GET['paragraph_structure_changed'])
        && sanitize_key((string) wp_unslash($_GET['paragraph_structure_changed'])) === '1';
    if ($paragraph_structure_changed) {
        $notice_override_message .= ' ' . sprintf(
            __('%d paragraph(s) added to improve readability.', 'icap-seo'),
            $paragraphs_added_count
        );
    }
    if ($notice_override_message !== '') {
        $notice_override_message .= ' ' . __('Re-scan to verify score movement.', 'icap-seo');
    }
}
if ($notice_code === 'content_rescan_complete') {
    $scan_id_notice = isset($_GET['scan_id']) ? sanitize_text_field((string) wp_unslash($_GET['scan_id'])) : '';
    if ($scan_id_notice !== '') {
        $notice_override_message = sprintf(
            __('Page rescan completed (scan %s). Recommendations refreshed from the latest scan snapshot.', 'icap-seo'),
            $scan_id_notice
        );
    }
}
if ($notice_code === 'content_depth_published') {
    $content_depth_words_added_notice = isset($_GET['content_depth_words_added']) ? (int) sanitize_text_field((string) wp_unslash($_GET['content_depth_words_added'])) : 0;
    $notice_override_message = sprintf(
        __('Content depth draft published: %d words added to this page. Re-scan to verify score movement.', 'icap-seo'),
        $content_depth_words_added_notice
    );
}
if ($notice_code === 'remediation_apply_noop') {
    $noop_reason = isset($_GET['noop_reason']) ? sanitize_key((string) wp_unslash($_GET['noop_reason'])) : '';
    if ($noop_reason === 'title_already_within_range') {
        $notice_override_message = __('No-op: title is already within the recommended 20-65 character range.', 'icap-seo');
    } elseif ($noop_reason === 'title_generation_failed') {
        $notice_override_message = __('No-op: a title could not be generated for this page.', 'icap-seo');
    } elseif ($noop_reason === 'meta_description_already_optimized') {
        $notice_override_message = __('No-op: meta description already matches the current page context and SEO target range.', 'icap-seo');
    } elseif ($noop_reason === 'meta_description_already_within_range') {
        $notice_override_message = __('No-op: meta description is already within the recommended 120-170 character range.', 'icap-seo');
    } elseif ($noop_reason === 'meta_description_generation_failed') {
        $notice_override_message = __('No-op: a replacement meta description could not be generated from this page content.', 'icap-seo');
    } elseif ($noop_reason === 'issue_not_supported_for_local_apply') {
        $notice_override_message = __('No-op: this recommendation is not yet supported by local apply logic.', 'icap-seo');
    } elseif ($noop_reason === 'h1_already_present') {
        $notice_override_message = __('No-op: an H1 heading is already present in this page content.', 'icap-seo');
    } elseif ($noop_reason === 'h1_generation_failed') {
        $notice_override_message = __('No-op: an H1 heading could not be generated from this page title.', 'icap-seo');
    } elseif ($noop_reason === 'images_alt_already_present') {
        $notice_override_message = __('No-op: all images in this content already have alt text.', 'icap-seo');
    } elseif ($noop_reason === 'no_images_in_content') {
        $notice_override_message = __('No-op: no images were found in this content to add alt text to.', 'icap-seo');
    } elseif ($noop_reason === 'images_alt_generation_failed') {
        $notice_override_message = __('No-op: alt text could not be generated for the images on this page.', 'icap-seo');
    } elseif ($noop_reason === 'canonical_already_set') {
        $notice_override_message = __('No-op: a canonical URL matching this page is already set.', 'icap-seo');
    } elseif ($noop_reason === 'canonical_generation_failed') {
        $notice_override_message = __('No-op: a canonical URL could not be generated for this page.', 'icap-seo');
    } elseif ($noop_reason === 'jsonld_schema_already_set') {
        $notice_override_message = __('No-op: JSON-LD schema matching this page is already set.', 'icap-seo');
    } elseif ($noop_reason === 'jsonld_schema_generation_failed') {
        $notice_override_message = __('No-op: JSON-LD schema could not be generated for this page.', 'icap-seo');
    } elseif ($noop_reason === 'heading_structure_already_present') {
        $notice_override_message = __('No-op: this page already has enough H2/H3 headings.', 'icap-seo');
    } elseif ($noop_reason === 'internal_linking_already_sufficient') {
        $notice_override_message = __('No-op: this page already has enough internal links.', 'icap-seo');
    } elseif ($noop_reason === 'no_link_candidates_available') {
        $notice_override_message = __('No-op: no other published pages were available to link to.', 'icap-seo');
    } elseif ($noop_reason === 'paragraph_structure_already_sufficient') {
        $notice_override_message = __('No-op: this page already has enough paragraph structure.', 'icap-seo');
    } elseif ($noop_reason === 'insufficient_plain_content_for_paragraph_split') {
        $notice_override_message = __('No-op: this page\'s content could not be safely reformatted into more paragraphs without risking existing formatting (links, emphasis, etc.).', 'icap-seo');
    } elseif ($noop_reason === 'no_effective_change_computed') {
        $notice_override_message = __('No-op: remediation did not produce a different SEO field value to save.', 'icap-seo');
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
            <?php $is_connected = $connection_settings['site_id'] !== '' && $connection_settings['site_token'] !== ''; ?>
            <h2><?php esc_html_e('Setup Wizard', 'icap-seo'); ?></h2>
            <?php if ($is_connected) : ?>
                <p><?php esc_html_e('This site is connected to iCap SEO.', 'icap-seo'); ?></p>
                <p class="description">
                    <?php esc_html_e('API Base URL:', 'icap-seo'); ?>
                    <code><?php echo esc_html($connection_settings['api_base_url'] !== '' ? $connection_settings['api_base_url'] : 'n/a'); ?></code>
                    |
                    <?php esc_html_e('Site ID:', 'icap-seo'); ?>
                    <code><?php echo esc_html($connection_settings['site_id']); ?></code>
                </p>
                <p><?php esc_html_e('Run scans and review results from the Overview and Content Scores tabs.', 'icap-seo'); ?></p>
            <?php else : ?>
                <ol>
                    <li><?php esc_html_e('Enter your API Base URL and Registration Token in Settings.', 'icap-seo'); ?></li>
                    <li><?php esc_html_e('Test the connection to confirm the API is reachable.', 'icap-seo'); ?></li>
                    <li><?php esc_html_e('Register this site to provision your scan credentials.', 'icap-seo'); ?></li>
                    <li><?php esc_html_e('Head to the Overview tab to run your first scan.', 'icap-seo'); ?></li>
                </ol>
            <?php endif; ?>
            <div class="icap-seo-actions">
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <input type="hidden" name="action" value="icap_seo_register_site">
                    <?php wp_nonce_field('icap_seo_register_site'); ?>
                    <button type="submit" class="button button-primary"><?php echo esc_html($is_connected ? __('Re-register Site', 'icap-seo') : __('Request Credentials & Register Site', 'icap-seo')); ?></button>
                </form>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <input type="hidden" name="action" value="icap_seo_test_connection">
                    <?php wp_nonce_field('icap_seo_test_connection'); ?>
                    <button type="submit" class="button"><?php esc_html_e('Test Connection', 'icap-seo'); ?></button>
                </form>
            </div>
            <?php if (!$is_connected) : ?>
                <hr>
                <h3><?php esc_html_e("Don't have a registration token yet?", 'icap-seo'); ?></h3>
                <p class="description"><?php esc_html_e('Request one below. We\'ll email you a verification link, then your registration token.', 'icap-seo'); ?></p>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="icap-seo-registration-request-form">
                    <input type="hidden" name="action" value="icap_seo_request_registration">
                    <?php wp_nonce_field('icap_seo_request_registration'); ?>
                    <p>
                        <label for="icap-seo-registration-request-email"><?php esc_html_e('Email', 'icap-seo'); ?></label><br>
                        <input id="icap-seo-registration-request-email" name="registration_request_email" type="email" class="regular-text" required placeholder="you@example.com">
                    </p>
                    <p>
                        <label for="icap-seo-registration-request-tier"><?php esc_html_e('Plan', 'icap-seo'); ?></label><br>
                        <select id="icap-seo-registration-request-tier" name="registration_request_tier">
                            <option value="baseline"><?php esc_html_e('Baseline (free)', 'icap-seo'); ?></option>
                            <option value="premium"><?php esc_html_e('Premium', 'icap-seo'); ?></option>
                            <option value="ai_scanning"><?php esc_html_e('AI Scanning', 'icap-seo'); ?></option>
                        </select>
                    </p>
                    <button type="submit" class="button button-primary"><?php esc_html_e('Request Registration', 'icap-seo'); ?></button>
                </form>
            <?php endif; ?>
            <p class="description">
                <?php esc_html_e('Connection profile:', 'icap-seo'); ?>
                <?php esc_html_e('API Base URL', 'icap-seo'); ?>
                <code><?php echo esc_html($connection_settings['api_base_url'] !== '' ? 'configured' : 'missing'); ?></code>
                |
                <?php esc_html_e('Site credentials', 'icap-seo'); ?>
                <code><?php echo esc_html($is_connected ? 'present' : 'missing'); ?></code>
            </p>
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
                    $locally_applied_issue_codes = isset($locally_applied_issue_codes) && is_array($locally_applied_issue_codes)
                        ? array_values(array_unique(array_map(static fn($value): string => sanitize_key((string) $value), $locally_applied_issue_codes)))
                        : [];
                    $current_meta_description_value = isset($current_meta_description_value) && is_string($current_meta_description_value)
                        ? sanitize_text_field($current_meta_description_value)
                        : '';
                    $open_detail_issues = [];
                    $applied_detail_issues = [];
                    foreach ($detail_issues as $issue_row) {
                        if (!is_array($issue_row)) {
                            continue;
                        }
                        $code = isset($issue_row['issue_code']) ? sanitize_key((string) $issue_row['issue_code']) : '';
                        if ($code !== '' && in_array($code, $locally_applied_issue_codes, true)) {
                            $applied_detail_issues[] = $issue_row;
                        } else {
                            $open_detail_issues[] = $issue_row;
                        }
                    }
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
                    <?php if ($current_meta_description_value !== '') : ?>
                        <p class="description">
                            <strong><?php esc_html_e('Current meta description:', 'icap-seo'); ?></strong>
                            <?php echo esc_html($current_meta_description_value); ?>
                        </p>
                    <?php endif; ?>
                    <div class="icap-seo-actions" style="margin:12px 0;">
                        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="icap-seo-async-scan-form">
                            <input type="hidden" name="action" value="icap_seo_rescan_content">
                            <input type="hidden" name="content_key" value="<?php echo esc_attr($selected_content_key); ?>">
                            <?php wp_nonce_field('icap_seo_rescan_content'); ?>
                            <button type="submit" class="button button-secondary"><?php esc_html_e('Rescan this page', 'icap-seo'); ?></button>
                            <div class="icap-seo-scan-progress" data-scanning-label="<?php esc_attr_e('Scanning this page…', 'icap-seo'); ?>">
                                <span class="icap-seo-scan-progress-track"></span>
                                <span class="icap-seo-scan-progress-label"></span>
                            </div>
                        </form>
                        <p class="description" style="margin:6px 0 0;">
                            <?php esc_html_e('Runs a focused cloud scan for this page only, then refreshes score/recommendation details without waiting for a full-site scan.', 'icap-seo'); ?>
                        </p>
                    </div>
                    <?php
                    $content_detail_tab = isset($_GET['detail_tab']) ? sanitize_key(wp_unslash($_GET['detail_tab'])) : 'recommendations';
                    if (!in_array($content_detail_tab, ['recommendations', 'ai-drafts', 'history'], true)) {
                        $content_detail_tab = 'recommendations';
                    }
                    $content_detail_tabs = [
                        'recommendations' => __('Recommendations', 'icap-seo'),
                        'ai-drafts' => __('AI Drafts', 'icap-seo'),
                        'history' => __('History', 'icap-seo'),
                    ];
                    ?>
                    <nav class="icap-seo-subnav">
                        <?php foreach ($content_detail_tabs as $detail_tab_key => $detail_tab_label) : ?>
                            <?php
                            $detail_tab_url = add_query_arg(
                                [
                                    'page' => 'icap-seo',
                                    'tab' => 'content-scores',
                                    'content_key' => $selected_content_key,
                                    'detail_tab' => $detail_tab_key,
                                ],
                                admin_url('admin.php')
                            );
                            $detail_tab_active = $content_detail_tab === $detail_tab_key;
                            ?>
                            <a href="<?php echo esc_url($detail_tab_url); ?>" class="icap-seo-subnav-link<?php echo $detail_tab_active ? ' is-active' : ''; ?>">
                                <?php echo esc_html($detail_tab_label); ?>
                            </a>
                        <?php endforeach; ?>
                    </nav>
                    <?php if ($content_detail_tab === 'recommendations') : ?>
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
                    <h4><?php esc_html_e('Full recommendation catalog', 'icap-seo'); ?></h4>
                    <p class="description"><?php esc_html_e('Every check iCap SEO can run against this page: whether it currently passes, which plan it requires, and how it gets fixed.', 'icap-seo'); ?></p>
                    <?php
                    $seo_recommendation_catalog = isset($seo_recommendation_catalog) && is_array($seo_recommendation_catalog) ? $seo_recommendation_catalog : [];
                    $catalog_fired_issue_codes = [];
                    foreach ($detail_issues as $catalog_issue_row) {
                        if (is_array($catalog_issue_row) && isset($catalog_issue_row['issue_code'])) {
                            $catalog_fired_issue_codes[sanitize_key((string) $catalog_issue_row['issue_code'])] = true;
                        }
                    }
                    $catalog_is_premium_scan = $latest_scores_scan_tier === 'premium';
                    $catalog_has_scan_data = $latest_scores_scan_tier !== '';
                    $catalog_apply_labels = [
                        'auto' => __('Auto-fix available', 'icap-seo'),
                        'preview' => __('Preview & publish', 'icap-seo'),
                        'guidance' => __('Guidance only', 'icap-seo'),
                    ];
                    $catalog_status_colors = [
                        'good' => ['bg' => '#eafaf0', 'fg' => '#1e7f4f', 'label' => __('Passing', 'icap-seo')],
                        'warn' => ['bg' => '#fdf0e0', 'fg' => '#9a5b0a', 'label' => __('Needs attention', 'icap-seo')],
                        'applied' => ['bg' => '#eef3fc', 'fg' => '#2455c7', 'label' => __('Applied — pending rescan', 'icap-seo')],
                        'locked_plan' => ['bg' => '#f1f1f1', 'fg' => '#6b7481', 'label' => __('Premium — not included in current plan', 'icap-seo')],
                        'not_scanned' => ['bg' => '#f1f1f1', 'fg' => '#6b7481', 'label' => __('Not yet scanned', 'icap-seo')],
                        'not_evaluated' => ['bg' => '#f1f1f1', 'fg' => '#6b7481', 'label' => __('Not evaluated — blocked by another issue', 'icap-seo')],
                    ];
                    ?>
                    <?php if (empty($seo_recommendation_catalog)) : ?>
                        <p><?php esc_html_e('Recommendation catalog is unavailable right now.', 'icap-seo'); ?></p>
                    <?php else : ?>
                        <div style="overflow-x:auto;">
                            <table class="widefat striped">
                                <thead>
                                    <tr>
                                        <th><?php esc_html_e('Check', 'icap-seo'); ?></th>
                                        <th><?php esc_html_e('Layer', 'icap-seo'); ?></th>
                                        <th><?php esc_html_e('Plan', 'icap-seo'); ?></th>
                                        <th><?php esc_html_e('Status', 'icap-seo'); ?></th>
                                        <th><?php esc_html_e('Fix', 'icap-seo'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($seo_recommendation_catalog as $catalog_item) : ?>
                                        <?php
                                        $catalog_code = isset($catalog_item['issue_code']) ? sanitize_key((string) $catalog_item['issue_code']) : '';
                                        $catalog_label = isset($catalog_item['label']) ? (string) $catalog_item['label'] : $catalog_code;
                                        $catalog_layer = isset($catalog_item['layer']) ? (string) $catalog_item['layer'] : '';
                                        $catalog_premium = !empty($catalog_item['premium']);
                                        $catalog_apply_type = isset($catalog_item['apply_type']) ? (string) $catalog_item['apply_type'] : 'guidance';
                                        $catalog_apply_label = $catalog_apply_labels[$catalog_apply_type] ?? $catalog_apply_labels['guidance'];

                                        $catalog_is_applied = in_array($catalog_code, $locally_applied_issue_codes, true);
                                        $catalog_is_open = isset($catalog_fired_issue_codes[$catalog_code]);
                                        $catalog_preempted_by = isset($catalog_item['preempted_by']) && is_array($catalog_item['preempted_by']) ? $catalog_item['preempted_by'] : [];
                                        $catalog_is_preempted = false;
                                        foreach ($catalog_preempted_by as $catalog_preempt_code) {
                                            if (isset($catalog_fired_issue_codes[sanitize_key((string) $catalog_preempt_code)])) {
                                                $catalog_is_preempted = true;
                                                break;
                                            }
                                        }

                                        if ($catalog_is_applied) {
                                            $catalog_status_key = 'applied';
                                        } elseif ($catalog_is_open) {
                                            $catalog_status_key = 'warn';
                                        } elseif ($catalog_premium && !$catalog_is_premium_scan) {
                                            $catalog_status_key = $catalog_has_scan_data ? 'locked_plan' : 'not_scanned';
                                        } elseif ($catalog_is_preempted) {
                                            // This check is structurally gated behind another (e.g. schema_missing_required_properties
                                            // can't evaluate without a JSON-LD block existing first) - it never ran, so "Passing"
                                            // would be misleading. Resolve the blocking issue first to let this check run.
                                            $catalog_status_key = 'not_evaluated';
                                        } else {
                                            $catalog_status_key = 'good';
                                        }
                                        $catalog_status = $catalog_status_colors[$catalog_status_key];
                                        ?>
                                        <tr>
                                            <td><?php echo esc_html($catalog_label); ?></td>
                                            <td><?php echo esc_html($catalog_layer); ?></td>
                                            <td><?php echo $catalog_premium ? esc_html__('Premium', 'icap-seo') : esc_html__('Free', 'icap-seo'); ?></td>
                                            <td>
                                                <span style="display:inline-block; padding:2px 9px; border-radius:999px; font-size:11px; font-weight:600; white-space:nowrap; background:<?php echo esc_attr($catalog_status['bg']); ?>; color:<?php echo esc_attr($catalog_status['fg']); ?>;">
                                                    <?php echo esc_html($catalog_status['label']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo esc_html($catalog_apply_label); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                    <h4><?php esc_html_e('Open recommendations', 'icap-seo'); ?></h4>
                    <p class="description"><?php esc_html_e('Recommendations still open from the latest scan. Locally applied fixes stay marked applied until the next rescan confirms cloud score updates.', 'icap-seo'); ?></p>
                    <?php if (empty($open_detail_issues)) : ?>
                        <p><?php esc_html_e('No open recommendations remain for this content item.', 'icap-seo'); ?></p>
                    <?php else : ?>
                        <ol>
                            <?php foreach ($open_detail_issues as $issue) : ?>
                                <?php
                                $issue_code = isset($issue['issue_code']) ? sanitize_key((string) $issue['issue_code']) : '';
                                $issue_severity = isset($issue['severity']) ? sanitize_text_field((string) $issue['severity']) : 'medium';
                                $issue_description = isset($issue['description']) ? sanitize_text_field((string) $issue['description']) : '';
                                $issue_recommended_fix = isset($issue['recommended_fix']) ? sanitize_text_field((string) $issue['recommended_fix']) : '';
                                $issue_effort = isset($issue['estimated_effort']) ? sanitize_text_field((string) $issue['estimated_effort']) : '';
                                $is_content_depth_code = in_array($issue_code, ['thin_content', 'no_visible_content', 'insufficient_content_depth', 'content_depth_improvement'], true);
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
                                    <?php if ($is_content_depth_code) : ?>
                                        <div style="margin-top:8px;"><em><?php esc_html_e('Use Content depth expansion below — this recommendation requires reviewing generated content before publishing.', 'icap-seo'); ?></em></div>
                                    <?php elseif ($issue_code !== '') : ?>
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
                    <?php endif; // content_detail_tab === 'recommendations' ?>
                    <?php if ($content_detail_tab === 'ai-drafts') : ?>
                    <?php
                    $content_depth_open_issue_codes = array_values(array_intersect(
                        array_map(
                            static fn($issue_row): string => is_array($issue_row) && isset($issue_row['issue_code']) ? sanitize_key((string) $issue_row['issue_code']) : '',
                            $open_detail_issues
                        ),
                        ['thin_content', 'no_visible_content', 'insufficient_content_depth', 'content_depth_improvement']
                    ));
                    $content_depth_draft = isset($content_depth_draft) && is_array($content_depth_draft) ? $content_depth_draft : ['html' => '', 'word_count' => 0];
                    $content_depth_draft_html = isset($content_depth_draft['html']) && is_string($content_depth_draft['html']) ? $content_depth_draft['html'] : '';
                    $content_depth_draft_word_count = isset($content_depth_draft['word_count']) ? (int) $content_depth_draft['word_count'] : 0;
                    ?>
                    <?php if (!empty($content_depth_open_issue_codes) || $content_depth_draft_html !== '') : ?>
                        <h4><?php esc_html_e('Content depth expansion', 'icap-seo'); ?></h4>
                        <p class="description"><?php esc_html_e('Generates draft paragraphs to review before anything is saved. Nothing publishes to this page until you explicitly accept the draft — unlike the other recommendations above.', 'icap-seo'); ?></p>
                        <?php if (!empty($content_depth_open_issue_codes)) : ?>
                            <p class="description"><?php echo esc_html(sprintf(__('Open codes: %s', 'icap-seo'), implode(', ', $content_depth_open_issue_codes))); ?></p>
                        <?php endif; ?>
                        <?php if ($content_depth_draft_html === '') : ?>
                            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                                <input type="hidden" name="action" value="icap_seo_preview_content_depth">
                                <input type="hidden" name="content_key" value="<?php echo esc_attr($selected_content_key); ?>">
                                <?php wp_nonce_field('icap_seo_preview_content_depth'); ?>
                                <button type="submit" class="button button-secondary"><?php esc_html_e('Preview expanded content', 'icap-seo'); ?></button>
                            </form>
                        <?php else : ?>
                            <div class="icap-seo-content-depth-draft" style="border:1px solid #ccd0d4; padding:12px; margin:8px 0; background:#fff;">
                                <p class="description"><?php echo esc_html(sprintf(__('Draft preview (%d words). Review and edit for accuracy and brand voice before publishing — this is a starting point, not finished copy.', 'icap-seo'), $content_depth_draft_word_count)); ?></p>
                                <div><?php echo wp_kses_post($content_depth_draft_html); ?></div>
                            </div>
                            <div class="icap-seo-actions">
                                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                                    <input type="hidden" name="action" value="icap_seo_publish_content_depth">
                                    <input type="hidden" name="content_key" value="<?php echo esc_attr($selected_content_key); ?>">
                                    <?php wp_nonce_field('icap_seo_publish_content_depth'); ?>
                                    <button type="submit" class="button button-primary"><?php esc_html_e('Accept & publish', 'icap-seo'); ?></button>
                                </form>
                                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                                    <input type="hidden" name="action" value="icap_seo_discard_content_depth">
                                    <input type="hidden" name="content_key" value="<?php echo esc_attr($selected_content_key); ?>">
                                    <?php wp_nonce_field('icap_seo_discard_content_depth'); ?>
                                    <button type="submit" class="button"><?php esc_html_e('Discard', 'icap-seo'); ?></button>
                                </form>
                                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                                    <input type="hidden" name="action" value="icap_seo_preview_content_depth">
                                    <input type="hidden" name="content_key" value="<?php echo esc_attr($selected_content_key); ?>">
                                    <?php wp_nonce_field('icap_seo_preview_content_depth'); ?>
                                    <button type="submit" class="button"><?php esc_html_e('Regenerate draft', 'icap-seo'); ?></button>
                                </form>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php
                    $readability_open = in_array('readability_score_low', array_map(
                        static fn($issue_row): string => is_array($issue_row) && isset($issue_row['issue_code']) ? sanitize_key((string) $issue_row['issue_code']) : '',
                        $open_detail_issues
                    ), true);
                    $readability_draft_paragraphs = isset($readability_draft_paragraphs) && is_array($readability_draft_paragraphs) ? $readability_draft_paragraphs : [];
                    ?>
                    <?php if ($readability_open || !empty($readability_draft_paragraphs)) : ?>
                        <h4><?php esc_html_e('Readability rewrite', 'icap-seo'); ?></h4>
                        <p class="description"><?php esc_html_e('Generates simplified paragraph drafts to review before anything is saved. Nothing publishes to this page until you explicitly accept the draft — unlike the other recommendations above.', 'icap-seo'); ?></p>
                        <?php if (empty($readability_draft_paragraphs)) : ?>
                            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                                <input type="hidden" name="action" value="icap_seo_preview_readability_rewrite">
                                <input type="hidden" name="content_key" value="<?php echo esc_attr($selected_content_key); ?>">
                                <?php wp_nonce_field('icap_seo_preview_readability_rewrite'); ?>
                                <button type="submit" class="button button-secondary"><?php esc_html_e('Preview simplified paragraphs', 'icap-seo'); ?></button>
                            </form>
                        <?php else : ?>
                            <div class="icap-seo-readability-draft" style="border:1px solid #ccd0d4; padding:12px; margin:8px 0; background:#fff;">
                                <p class="description">
                                    <?php
                                    echo esc_html(sprintf(
                                        /* translators: %d: number of paragraphs proposed for simplification */
                                        _n(
                                            '%d paragraph proposed for simplification. Review each before publishing — this is a starting point, not finished copy.',
                                            '%d paragraphs proposed for simplification. Review each before publishing — this is a starting point, not finished copy.',
                                            count($readability_draft_paragraphs),
                                            'icap-seo'
                                        ),
                                        count($readability_draft_paragraphs)
                                    ));
                                    ?>
                                </p>
                                <?php foreach ($readability_draft_paragraphs as $paragraph_entry) : ?>
                                    <?php
                                    $original_text = isset($paragraph_entry['original_text']) ? (string) $paragraph_entry['original_text'] : '';
                                    $rewritten_text = isset($paragraph_entry['rewritten_text']) ? (string) $paragraph_entry['rewritten_text'] : '';
                                    ?>
                                    <div style="margin-bottom:12px; padding-bottom:12px; border-bottom:1px solid #eee;">
                                        <p><strong><?php esc_html_e('Before:', 'icap-seo'); ?></strong> <?php echo esc_html($original_text); ?></p>
                                        <p><strong><?php esc_html_e('After:', 'icap-seo'); ?></strong> <?php echo esc_html($rewritten_text); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="icap-seo-actions">
                                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                                    <input type="hidden" name="action" value="icap_seo_publish_readability_rewrite">
                                    <input type="hidden" name="content_key" value="<?php echo esc_attr($selected_content_key); ?>">
                                    <?php wp_nonce_field('icap_seo_publish_readability_rewrite'); ?>
                                    <button type="submit" class="button button-primary"><?php esc_html_e('Accept & publish', 'icap-seo'); ?></button>
                                </form>
                                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                                    <input type="hidden" name="action" value="icap_seo_discard_readability_rewrite">
                                    <input type="hidden" name="content_key" value="<?php echo esc_attr($selected_content_key); ?>">
                                    <?php wp_nonce_field('icap_seo_discard_readability_rewrite'); ?>
                                    <button type="submit" class="button"><?php esc_html_e('Discard', 'icap-seo'); ?></button>
                                </form>
                                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                                    <input type="hidden" name="action" value="icap_seo_preview_readability_rewrite">
                                    <input type="hidden" name="content_key" value="<?php echo esc_attr($selected_content_key); ?>">
                                    <?php wp_nonce_field('icap_seo_preview_readability_rewrite'); ?>
                                    <button type="submit" class="button"><?php esc_html_e('Regenerate draft', 'icap-seo'); ?></button>
                                </form>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php endif; // content_detail_tab === 'ai-drafts' ?>
                    <?php if ($content_detail_tab === 'recommendations') : ?>
                    <?php if (!empty($applied_detail_issues)) : ?>
                        <h4><?php esc_html_e('Applied recommendations (local)', 'icap-seo'); ?></h4>
                        <p class="description"><?php esc_html_e('These were applied in WordPress already. Use regenerate if you want a new AI draft before the next rescan.', 'icap-seo'); ?></p>
                        <ol>
                            <?php foreach ($applied_detail_issues as $issue) : ?>
                                <?php
                                $issue_code = isset($issue['issue_code']) ? sanitize_key((string) $issue['issue_code']) : '';
                                $issue_severity = isset($issue['severity']) ? sanitize_text_field((string) $issue['severity']) : 'medium';
                                $issue_description = isset($issue['description']) ? sanitize_text_field((string) $issue['description']) : '';
                                $issue_recommended_fix = isset($issue['recommended_fix']) ? sanitize_text_field((string) $issue['recommended_fix']) : '';
                                $can_regenerate = in_array($issue_code, ['missing_meta_description', 'meta_description_length_out_of_range', 'title_length_out_of_range', 'missing_title_tag', 'missing_h1', 'images_missing_alt', 'images_missing_dimensions', 'images_not_lazy_loaded', 'missing_canonical', 'missing_jsonld_schema', 'schema_type_missing', 'schema_missing_required_properties', 'limited_heading_structure', 'low_internal_linking', 'no_links_detected', 'limited_paragraph_structure'], true);
                                ?>
                                <li>
                                    <strong><?php echo esc_html(strtoupper($issue_severity)); ?></strong>
                                    <span class="dashicons dashicons-yes-alt" aria-hidden="true"></span>
                                    <em><?php esc_html_e('Applied locally', 'icap-seo'); ?></em>
                                    <div><?php echo esc_html($issue_description !== '' ? $issue_description : __('No issue description provided.', 'icap-seo')); ?></div>
                                    <?php if ($issue_recommended_fix !== '') : ?>
                                        <div><em><?php echo esc_html($issue_recommended_fix); ?></em></div>
                                    <?php endif; ?>
                                    <?php if ($issue_code !== '' && $can_regenerate) : ?>
                                        <div style="margin-top:8px;">
                                            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                                                <input type="hidden" name="action" value="icap_seo_apply_remediation">
                                                <input type="hidden" name="content_key" value="<?php echo esc_attr($selected_content_key); ?>">
                                                <input type="hidden" name="approved_issue_codes[]" value="<?php echo esc_attr($issue_code); ?>">
                                                <input type="hidden" name="force_regenerate" value="1">
                                                <?php wp_nonce_field('icap_seo_apply_remediation'); ?>
                                                <button type="submit" class="button"><?php esc_html_e('Regenerate & re-apply', 'icap-seo'); ?></button>
                                            </form>
                                        </div>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ol>
                    <?php endif; ?>
                    <h4><?php esc_html_e('Remediation preview and apply', 'icap-seo'); ?></h4>
                    <p class="description"><?php esc_html_e('Preview/apply actions only include open recommendations. Locally applied items are excluded until a rescan refreshes cloud findings.', 'icap-seo'); ?></p>
                    <div class="icap-seo-actions">
                        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                            <input type="hidden" name="action" value="icap_seo_preview_remediation">
                            <input type="hidden" name="content_key" value="<?php echo esc_attr($selected_content_key); ?>">
                            <?php wp_nonce_field('icap_seo_preview_remediation'); ?>
                            <?php foreach ($open_detail_issues as $issue) : ?>
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
                            <?php
                            $apply_all_issue_codes = [];
                            foreach ($open_detail_issues as $issue) {
                                $issue_code = isset($issue['issue_code']) && is_string($issue['issue_code'])
                                    ? sanitize_key($issue['issue_code'])
                                    : '';
                                if (
                                    $issue_code !== ''
                                    && !in_array($issue_code, ['thin_content', 'no_visible_content', 'insufficient_content_depth', 'content_depth_improvement'], true)
                                ) {
                                    $apply_all_issue_codes[] = $issue_code;
                                }
                            }
                            ?>
                            <?php foreach ($apply_all_issue_codes as $issue_code) : ?>
                                <input type="hidden" name="approved_issue_codes[]" value="<?php echo esc_attr($issue_code); ?>">
                            <?php endforeach; ?>
                            <button type="submit" class="button button-primary" <?php disabled(empty($apply_all_issue_codes)); ?>><?php esc_html_e('Apply all open recommendations', 'icap-seo'); ?></button>
                        </form>
                    </div>
                    <p class="description"><?php esc_html_e('Content depth recommendations are excluded from "Apply all" — use the Content depth expansion preview/publish flow above instead.', 'icap-seo'); ?></p>
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
                    <?php endif; // content_detail_tab === 'recommendations' ?>
                    <?php if ($content_detail_tab === 'history') : ?>
                    <h4><?php esc_html_e('Latest remediation changes', 'icap-seo'); ?></h4>
                    <?php
                    $remediation_audit_entries = isset($remediation_audit_entries) && is_array($remediation_audit_entries)
                        ? $remediation_audit_entries
                        : [];
                    ?>
                    <?php if (empty($remediation_audit_entries)) : ?>
                        <p><?php esc_html_e('No local remediation change entries are recorded for this content yet.', 'icap-seo'); ?></p>
                    <?php else : ?>
                        <ul>
                            <?php foreach (array_slice($remediation_audit_entries, 0, 8) as $audit_entry) : ?>
                                <?php
                                $audit_timestamp = isset($audit_entry['timestamp']) ? sanitize_text_field((string) $audit_entry['timestamp']) : '';
                                $audit_issue_codes = isset($audit_entry['issue_codes']) && is_array($audit_entry['issue_codes'])
                                    ? array_map(static fn($value): string => sanitize_key((string) $value), $audit_entry['issue_codes'])
                                    : [];
                                $audit_title_changed = !empty($audit_entry['title_changed']);
                                $audit_excerpt_changed = !empty($audit_entry['excerpt_changed']);
                                $audit_title_before = isset($audit_entry['title_before']) ? sanitize_text_field((string) $audit_entry['title_before']) : '';
                                $audit_title_after = isset($audit_entry['title_after']) ? sanitize_text_field((string) $audit_entry['title_after']) : '';
                                $audit_excerpt_before = isset($audit_entry['excerpt_before']) ? sanitize_text_field((string) $audit_entry['excerpt_before']) : '';
                                $audit_excerpt_after = isset($audit_entry['excerpt_after']) ? sanitize_text_field((string) $audit_entry['excerpt_after']) : '';
                                $audit_h1_changed = !empty($audit_entry['h1_changed']);
                                $audit_h1_before = isset($audit_entry['h1_before']) ? sanitize_text_field((string) $audit_entry['h1_before']) : '';
                                $audit_h1_after = isset($audit_entry['h1_after']) ? sanitize_text_field((string) $audit_entry['h1_after']) : '';
                                $audit_images_alt_changed = !empty($audit_entry['images_alt_changed']);
                                $audit_images_alt_updated_count = isset($audit_entry['images_alt_updated_count']) ? (int) $audit_entry['images_alt_updated_count'] : 0;
                                $audit_images_alt_before = isset($audit_entry['images_alt_before']) ? (int) $audit_entry['images_alt_before'] : 0;
                                $audit_images_dimensions_changed = !empty($audit_entry['images_dimensions_changed']);
                                $audit_images_dimensions_updated_count = isset($audit_entry['images_dimensions_updated_count']) ? (int) $audit_entry['images_dimensions_updated_count'] : 0;
                                $audit_images_dimensions_before = isset($audit_entry['images_dimensions_before']) ? (int) $audit_entry['images_dimensions_before'] : 0;
                                $audit_images_lazy_changed = !empty($audit_entry['images_lazy_changed']);
                                $audit_images_lazy_updated_count = isset($audit_entry['images_lazy_updated_count']) ? (int) $audit_entry['images_lazy_updated_count'] : 0;
                                $audit_images_lazy_before = isset($audit_entry['images_lazy_before']) ? (int) $audit_entry['images_lazy_before'] : 0;
                                $audit_canonical_changed = !empty($audit_entry['canonical_changed']);
                                $audit_canonical_after = isset($audit_entry['canonical_after']) ? esc_url_raw((string) $audit_entry['canonical_after']) : '';
                                $audit_jsonld_schema_changed = !empty($audit_entry['jsonld_schema_changed']);
                                $audit_jsonld_schema_after = isset($audit_entry['jsonld_schema_after']) ? sanitize_text_field((string) $audit_entry['jsonld_schema_after']) : '';
                                $audit_heading_structure_changed = !empty($audit_entry['heading_structure_changed']);
                                $audit_headings_added_count = isset($audit_entry['headings_added_count']) ? (int) $audit_entry['headings_added_count'] : 0;
                                $audit_content_depth_changed = !empty($audit_entry['content_depth_changed']);
                                $audit_content_depth_words_added = isset($audit_entry['content_depth_words_added']) ? (int) $audit_entry['content_depth_words_added'] : 0;
                                $audit_internal_linking_changed = !empty($audit_entry['internal_linking_changed']);
                                $audit_internal_links_added_count = isset($audit_entry['internal_links_added_count']) ? (int) $audit_entry['internal_links_added_count'] : 0;
                                $audit_paragraph_structure_changed = !empty($audit_entry['paragraph_structure_changed']);
                                $audit_paragraphs_added_count = isset($audit_entry['paragraphs_added_count']) ? (int) $audit_entry['paragraphs_added_count'] : 0;
                                ?>
                                <li>
                                    <strong><?php echo esc_html($audit_timestamp !== '' ? $audit_timestamp : __('Unknown time', 'icap-seo')); ?></strong>
                                    <?php if (!empty($audit_issue_codes)) : ?>
                                        <div><code><?php echo esc_html(implode(', ', $audit_issue_codes)); ?></code></div>
                                    <?php endif; ?>
                                    <?php if ($audit_title_changed) : ?>
                                        <div><?php echo esc_html(sprintf(__('Title: "%1$s" → "%2$s"', 'icap-seo'), $audit_title_before, $audit_title_after)); ?></div>
                                    <?php endif; ?>
                                    <?php if ($audit_excerpt_changed) : ?>
                                        <div><?php echo esc_html(sprintf(__('Meta description/excerpt: "%1$s" → "%2$s"', 'icap-seo'), $audit_excerpt_before, $audit_excerpt_after)); ?></div>
                                    <?php endif; ?>
                                    <?php if ($audit_h1_changed) : ?>
                                        <div><?php echo esc_html(sprintf(__('H1: "%1$s" → "%2$s"', 'icap-seo'), $audit_h1_before, $audit_h1_after)); ?></div>
                                    <?php endif; ?>
                                    <?php if ($audit_images_alt_changed) : ?>
                                        <div><?php echo esc_html(sprintf(__('Image alt text: %1$d of %2$d missing image(s) updated', 'icap-seo'), $audit_images_alt_updated_count, $audit_images_alt_before)); ?></div>
                                    <?php endif; ?>
                                    <?php if ($audit_images_dimensions_changed) : ?>
                                        <div><?php echo esc_html(sprintf(__('Image dimensions: %1$d of %2$d missing image(s) updated', 'icap-seo'), $audit_images_dimensions_updated_count, $audit_images_dimensions_before)); ?></div>
                                    <?php endif; ?>
                                    <?php if ($audit_images_lazy_changed) : ?>
                                        <div><?php echo esc_html(sprintf(__('Lazy loading: %1$d of %2$d eligible image(s) updated', 'icap-seo'), $audit_images_lazy_updated_count, $audit_images_lazy_before)); ?></div>
                                    <?php endif; ?>
                                    <?php if ($audit_canonical_changed) : ?>
                                        <div><?php echo esc_html(sprintf(__('Canonical URL set: "%s"', 'icap-seo'), $audit_canonical_after)); ?></div>
                                    <?php endif; ?>
                                    <?php if ($audit_jsonld_schema_changed) : ?>
                                        <div><?php echo esc_html(sprintf(__('JSON-LD schema added: %s', 'icap-seo'), $audit_jsonld_schema_after)); ?></div>
                                    <?php endif; ?>
                                    <?php if ($audit_heading_structure_changed) : ?>
                                        <div><?php echo esc_html(sprintf(__('%d heading(s) added', 'icap-seo'), $audit_headings_added_count)); ?></div>
                                    <?php endif; ?>
                                    <?php if ($audit_content_depth_changed) : ?>
                                        <div><?php echo esc_html(sprintf(__('Content depth draft published (+%d words)', 'icap-seo'), $audit_content_depth_words_added)); ?></div>
                                    <?php endif; ?>
                                    <?php if ($audit_internal_linking_changed) : ?>
                                        <div><?php echo esc_html(sprintf(__('%d internal link(s) added', 'icap-seo'), $audit_internal_links_added_count)); ?></div>
                                    <?php endif; ?>
                                    <?php if ($audit_paragraph_structure_changed) : ?>
                                        <div><?php echo esc_html(sprintf(__('%d paragraph(s) added', 'icap-seo'), $audit_paragraphs_added_count)); ?></div>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
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
                    <?php endif; // content_detail_tab === 'history' ?>
                <?php endif; ?>
            <?php endif; ?>
        <?php elseif ($active_tab === 'settings') : ?>
            <h2><?php esc_html_e('Settings', 'icap-seo'); ?></h2>
            <h3><?php esc_html_e('Connection', 'icap-seo'); ?></h3>
            <p class="description"><?php esc_html_e('API credentials used to register this site and run scans.', 'icap-seo'); ?></p>
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
            <p class="description">
                <?php esc_html_e('Last successful score sync:', 'icap-seo'); ?>
                <code><?php echo esc_html($connection_settings['last_sync_at'] ?: 'n/a'); ?></code>
            </p>

            <hr>
            <h3><?php esc_html_e('Billing', 'icap-seo'); ?></h3>
            <p class="description"><?php esc_html_e('Manage your iCap SEO subscription. Premium unlocks the full 31-check catalog.', 'icap-seo'); ?></p>
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
                <?php esc_html_e('Last known billing state:', 'icap-seo'); ?>
                <code><?php echo esc_html($connection_settings['last_billing_state'] ?: 'unknown'); ?></code>
                |
                <?php esc_html_e('Last billing check:', 'icap-seo'); ?>
                <code><?php echo esc_html($connection_settings['last_billing_checked_at'] ?: 'n/a'); ?></code>
            </p>
        <?php else : ?>
            <?php
            $overview_is_connected = $connection_settings['site_id'] !== '' && $connection_settings['site_token'] !== '';
            $overview_scan_tier_value = '';
            if (isset($scan_status_data['scan_tier']) && is_string($scan_status_data['scan_tier'])) {
                $overview_scan_tier_value = sanitize_text_field($scan_status_data['scan_tier']);
            }
            if ($overview_scan_tier_value === '' && $latest_scores_scan_tier !== '') {
                $overview_scan_tier_value = $latest_scores_scan_tier;
            }
            $overview_scan_layers_data = [];
            if (isset($scan_status_data['scan_layers']) && is_array($scan_status_data['scan_layers'])) {
                $overview_scan_layers_data = $scan_status_data['scan_layers'];
            }
            if (empty($overview_scan_layers_data) && !empty($latest_scores_scan_layers)) {
                $overview_scan_layers_data = $latest_scores_scan_layers;
            }
            $overview_executed_layer_names = [];
            if (isset($overview_scan_layers_data['executed']) && is_array($overview_scan_layers_data['executed'])) {
                foreach ($overview_scan_layers_data['executed'] as $layer_row) {
                    if (is_array($layer_row) && isset($layer_row['name']) && is_string($layer_row['name'])) {
                        $overview_executed_layer_names[] = sanitize_text_field($layer_row['name']);
                    } elseif (is_string($layer_row)) {
                        $overview_executed_layer_names[] = sanitize_text_field($layer_row);
                    }
                }
            }
            $overview_premium_locked_layer_names = [];
            if (isset($overview_scan_layers_data['premium_locked']) && is_array($overview_scan_layers_data['premium_locked'])) {
                foreach ($overview_scan_layers_data['premium_locked'] as $layer_row) {
                    if (is_array($layer_row) && isset($layer_row['name']) && is_string($layer_row['name'])) {
                        $overview_premium_locked_layer_names[] = sanitize_text_field($layer_row['name']);
                    } elseif (is_string($layer_row)) {
                        $overview_premium_locked_layer_names[] = sanitize_text_field($layer_row);
                    }
                }
            }
            $overview_latest_scan_id_display = $connection_settings['last_scan_id'];
            if (isset($scan_status_data['scan_id']) && is_string($scan_status_data['scan_id'])) {
                $overview_latest_scan_id_display = sanitize_text_field($scan_status_data['scan_id']);
            } elseif ($overview_latest_scan_id_display === '' && $latest_scores_scan_id !== '') {
                $overview_latest_scan_id_display = $latest_scores_scan_id;
            }
            $overview_is_premium = $overview_scan_tier_value === 'premium';
            $overview_ai_credits_remaining = isset($connection_settings['ai_credits_remaining'])
                ? (int) $connection_settings['ai_credits_remaining']
                : 0;
            $overview_ai_credits_exhausted = $overview_is_premium && $overview_ai_credits_remaining <= 0;
            ?>
            <h2><?php esc_html_e('Overview', 'icap-seo'); ?></h2>

            <div class="icap-seo-feature-summary">
                <p><?php esc_html_e('iCap SEO runs a cloud-connected scan of your site — 31 checks across 6 categories — and fixes most of what it finds, automatically or with a preview you approve first.', 'icap-seo'); ?></p>
                <ul>
                    <li><?php esc_html_e('Baseline on-page audit — title tags, meta descriptions, headings, content depth', 'icap-seo'); ?> <em>(<?php esc_html_e('Free', 'icap-seo'); ?>)</em></li>
                    <li><?php esc_html_e('Crawlability & security — HTTPS, canonical URLs, robots.txt, security headers', 'icap-seo'); ?> <em>(<?php esc_html_e('Premium', 'icap-seo'); ?>)</em></li>
                    <li><?php esc_html_e('Content quality & readability — depth, structure, plain-language clarity', 'icap-seo'); ?> <em>(<?php esc_html_e('Premium', 'icap-seo'); ?>)</em></li>
                    <li><?php esc_html_e('Structured data — schema.org / JSON-LD for richer search results', 'icap-seo'); ?> <em>(<?php esc_html_e('Premium', 'icap-seo'); ?>)</em></li>
                    <li><?php esc_html_e('Image optimization — alt text, dimensions, lazy loading', 'icap-seo'); ?> <em>(<?php esc_html_e('Premium', 'icap-seo'); ?>)</em></li>
                    <li><?php esc_html_e('Internal & external links — discoverability and broken-link detection', 'icap-seo'); ?> <em>(<?php esc_html_e('Premium', 'icap-seo'); ?>)</em></li>
                </ul>
            </div>

            <div class="icap-seo-cards">
                <div class="icap-seo-card">
                    <h3><?php esc_html_e('Connection Status', 'icap-seo'); ?></h3>
                    <p><?php echo esc_html($score_snapshot['status']); ?></p>
                </div>
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
                <div class="icap-seo-card">
                    <h3><?php esc_html_e('AI Credits Remaining', 'icap-seo'); ?></h3>
                    <p>
                        <?php
                        if ($overview_is_premium) {
                            echo esc_html((string) $overview_ai_credits_remaining);
                        } else {
                            esc_html_e('Requires Premium', 'icap-seo');
                        }
                        ?>
                    </p>
                </div>
            </div>

            <?php if ($overview_is_premium) : ?>
                <?php if ($overview_ai_credits_exhausted) : ?>
                    <p class="notice notice-warning inline" style="padding:8px 12px; margin-top:16px;">
                        <?php esc_html_e('AI content generation is paused — this site is out of AI credits.', 'icap-seo'); ?>
                    </p>
                <?php endif; ?>
                <div class="icap-seo-actions" style="margin-top:8px;">
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                        <input type="hidden" name="action" value="icap_seo_start_ai_credit_checkout">
                        <?php wp_nonce_field('icap_seo_start_ai_credit_checkout'); ?>
                        <button type="submit" class="button<?php echo $overview_ai_credits_exhausted ? ' button-primary' : ''; ?>">
                            <?php esc_html_e('Buy more AI credits', 'icap-seo'); ?>
                        </button>
                    </form>
                </div>
            <?php endif; ?>

            <?php if ($overview_is_connected) : ?>
                <div class="icap-seo-actions" style="margin-top:16px;">
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="icap-seo-async-scan-form">
                        <input type="hidden" name="action" value="icap_seo_trigger_scan">
                        <?php wp_nonce_field('icap_seo_trigger_scan'); ?>
                        <button type="submit" class="button button-primary"><?php esc_html_e('Trigger Full Scan', 'icap-seo'); ?></button>
                        <div class="icap-seo-scan-progress" data-scanning-label="<?php esc_attr_e('Scanning your site…', 'icap-seo'); ?>">
                            <span class="icap-seo-scan-progress-track"></span>
                            <span class="icap-seo-scan-progress-label"></span>
                        </div>
                    </form>
                </div>
            <?php else : ?>
                <?php
                $setup_wizard_url = add_query_arg(['page' => 'icap-seo', 'tab' => 'setup-wizard'], admin_url('admin.php'));
                ?>
                <p class="notice notice-warning inline" style="padding:8px 12px; margin-top:16px;">
                    <?php
                    echo wp_kses(
                        sprintf(
                            /* translators: %s: link to the Setup Wizard tab */
                            __('This site isn\'t connected yet. Visit the %s to register and start scanning.', 'icap-seo'),
                            '<a href="' . esc_url($setup_wizard_url) . '">' . esc_html__('Setup Wizard', 'icap-seo') . '</a>'
                        ),
                        ['a' => ['href' => []]]
                    );
                    ?>
                </p>
            <?php endif; ?>

            <?php if ($overview_latest_scan_id_display !== '') : ?>
                <p class="description">
                    <?php esc_html_e('Latest scan ID:', 'icap-seo'); ?>
                    <code><?php echo esc_html($overview_latest_scan_id_display); ?></code>
                    <?php if ($overview_scan_tier_value !== '') : ?>
                        |
                        <?php esc_html_e('Tier:', 'icap-seo'); ?>
                        <code><?php echo esc_html($overview_scan_tier_value); ?></code>
                    <?php endif; ?>
                </p>
            <?php endif; ?>
            <?php if (!empty($overview_executed_layer_names)) : ?>
                <p class="description">
                    <?php esc_html_e('Executed scan layers:', 'icap-seo'); ?>
                    <code><?php echo esc_html(implode(', ', $overview_executed_layer_names)); ?></code>
                </p>
            <?php endif; ?>
            <?php if (!empty($overview_premium_locked_layer_names)) : ?>
                <p class="description">
                    <?php esc_html_e('Premium-only layers not included in this scan:', 'icap-seo'); ?>
                    <code><?php echo esc_html(implode(', ', $overview_premium_locked_layer_names)); ?></code>
                </p>
            <?php endif; ?>
        <?php endif; ?>
    </section>
</div>
