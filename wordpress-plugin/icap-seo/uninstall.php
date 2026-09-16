<?php
/**
 * Uninstall handler. WordPress only loads this file on a real "Delete" from
 * the Plugins screen (never on mere deactivation), and only after confirming
 * WP_UNINSTALL_PLUGIN itself - the guard below is a defense-in-depth check
 * against direct access, not the primary safeguard.
 *
 * Scope: this clears connection/operational state (the registration token,
 * site token, and site ID that let this install talk to the iCap SEO
 * service, plus the scan-results cache derived from them) - state that is
 * useless without the plugin and would otherwise sit in wp_options forever.
 *
 * It intentionally does NOT delete per-post SEO postmeta (meta descriptions,
 * canonical URLs, JSON-LD schema, content/readability drafts, remediation
 * history) - that's user-approved content applied to their pages, not
 * plugin-internal state, and deleting it site-wide on uninstall would be a
 * bigger, less reversible action than removing a settings row.
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

$settings = get_option('icap_seo_settings', []);
$site_id = is_array($settings) ? (string) ($settings['site_id'] ?? '') : '';

if ($site_id !== '') {
    delete_transient(sprintf('icap_seo_scores_%s', md5($site_id)));
}

delete_option('icap_seo_settings');
delete_option('icap_seo_indexnow_key');
delete_option('icap_seo_redirects');
delete_option('icap_seo_404_log');
