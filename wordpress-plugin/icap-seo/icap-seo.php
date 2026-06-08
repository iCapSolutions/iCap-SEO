<?php
/**
 * Plugin Name: iCap SEO
 * Plugin URI: https://www.icapsolutions.com
 * Description: iCap SEO service dashboard and setup foundation for WordPress.
 * Version: 0.1.6
 * Author: iCapSolutions
 * Author URI: https://www.icapsolutions.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: icap-seo
 */

if (!defined('ABSPATH')) {
    exit;
}

define('ICAP_SEO_VERSION', '0.1.6');
define('ICAP_SEO_PLUGIN_FILE', __FILE__);
define('ICAP_SEO_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ICAP_SEO_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once ICAP_SEO_PLUGIN_DIR . 'includes/class-icap-seo-plugin.php';

function icap_seo_bootstrap(): void
{
    $plugin = new ICap_SEO_Plugin();
    $plugin->run();
}

icap_seo_bootstrap();
