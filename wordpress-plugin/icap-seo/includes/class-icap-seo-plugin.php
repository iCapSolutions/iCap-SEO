<?php

if (!defined('ABSPATH')) {
    exit;
}

require_once ICAP_SEO_PLUGIN_DIR . 'includes/class-icap-seo-service-client.php';
require_once ICAP_SEO_PLUGIN_DIR . 'includes/class-icap-seo-output.php';
require_once ICAP_SEO_PLUGIN_DIR . 'includes/class-icap-seo-editor-panel.php';
require_once ICAP_SEO_PLUGIN_DIR . 'admin/class-icap-seo-admin.php';

class ICap_SEO_Plugin
{
    private ICap_SEO_Admin $admin;
    private ICap_SEO_Output $output;
    private ICap_SEO_Editor_Panel $editor_panel;

    public function __construct()
    {
        $this->admin = new ICap_SEO_Admin(new ICap_SEO_Service_Client());
        $this->output = new ICap_SEO_Output();
        $this->editor_panel = new ICap_SEO_Editor_Panel($this->output);
    }
    public function run(): void
    {
        add_action('admin_menu', [$this, 'register_admin']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_dashboard_setup', [$this->admin, 'register_dashboard_widget']);
        $this->output->register();
        $this->editor_panel->register();
        add_action('template_redirect', [$this, 'maybe_apply_redirect'], 1);
        add_action('template_redirect', [$this, 'serve_indexnow_key_file'], 1);
        add_action('template_redirect', [$this, 'serve_llms_txt'], 1);
        add_action('template_redirect', [$this, 'maybe_log_404'], 5);
        add_action('save_post', [$this, 'ping_indexnow_on_publish'], 20, 3);
        add_action('before_delete_post', [$this, 'ping_indexnow_on_delete']);
        add_action('wp_trash_post', [$this, 'ping_indexnow_on_delete']);
        $this->admin->register_admin_actions();
        $this->admin->register_list_table_columns();
    }

    public function register_admin(): void
    {
        $this->admin->register_menu();
    }

    public function enqueue_assets(string $hook): void
    {
        if (strpos($hook, 'icap-seo') === false) {
            return;
        }

        wp_enqueue_style(
            'icap-seo-admin',
            ICAP_SEO_PLUGIN_URL . 'assets/css/admin.css',
            [],
            ICAP_SEO_VERSION
        );

        wp_enqueue_script(
            'icap-seo-admin',
            ICAP_SEO_PLUGIN_URL . 'assets/js/admin.js',
            [],
            ICAP_SEO_VERSION,
            true
        );
    }

    /**
     * Redirects are admin-configured (manage_options capability, validated with
     * esc_url_raw() at save time in ICap_SEO_Admin::handle_add_redirect()), not
     * request-controlled input being reflected back - unlike this plugin's OAuth/
     * Stripe redirects, an external destination here is a deliberate, trusted
     * setting, so wp_redirect() (not wp_safe_redirect()) is the correct choice:
     * redirect managers in every competitor plugin support sending visitors to
     * another domain, and wp_safe_redirect() would silently rewrite that to the
     * homepage instead.
     */
    public function maybe_apply_redirect(): void
    {
        $redirects = get_option('icap_seo_redirects', []);
        if (!is_array($redirects) || empty($redirects)) {
            return;
        }

        $request_path = $this->get_current_request_path();

        foreach ($redirects as $row) {
            if (!is_array($row) || ($row['source'] ?? '') !== $request_path) {
                continue;
            }
            $target = (string) ($row['target'] ?? '');
            if ($target === '') {
                continue;
            }
            $status = ((string) ($row['type'] ?? '301')) === '302' ? 302 : 301;
            wp_redirect($target, $status);
            exit;
        }
    }

    /**
     * IndexNow (api.indexnow.org) requires a per-site key served as a plain-text
     * file at the site root before it will accept submissions from that key -
     * this serves it without needing a rewrite-rule flush, matching how simple
     * key-verification files are commonly handled from a plugin.
     */
    private function get_or_create_indexnow_key(): string
    {
        $key = get_option('icap_seo_indexnow_key');
        if (is_string($key) && $key !== '') {
            return $key;
        }

        $key = bin2hex(random_bytes(16));
        update_option('icap_seo_indexnow_key', $key, false);

        return $key;
    }

    public function serve_indexnow_key_file(): void
    {
        $key = $this->get_or_create_indexnow_key();

        if ($this->get_current_request_path() !== '/' . $key . '.txt') {
            return;
        }

        header('Content-Type: text/plain; charset=utf-8');
        echo esc_html($key);
        exit;
    }

    /**
     * Shared by every feature that dispatches on the current request path
     * (redirects, the IndexNow key file, llms.txt) - a single normalized
     * form (leading slash, no trailing slash except root) so all of them
     * compare against the same shape.
     */
    private function get_current_request_path(): string
    {
        $request_uri = isset($_SERVER['REQUEST_URI']) ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])) : '';
        $path = '/' . ltrim((string) wp_parse_url($request_uri, PHP_URL_PATH), '/');
        if ($path !== '/') {
            $path = rtrim($path, '/');
        }

        return $path;
    }

    /**
     * Fire-and-forget: IndexNow has no response we act on, and a slow/unreachable
     * endpoint must never delay the actual publish/update/delete action.
     */
    private function ping_indexnow_for_url(string $url): void
    {
        if ($url === '' || $this->output->is_another_seo_plugin_active()) {
            return;
        }

        $key = $this->get_or_create_indexnow_key();
        $endpoint = 'https://api.indexnow.org/indexnow'
            . '?url=' . rawurlencode($url)
            . '&key=' . rawurlencode($key)
            . '&keyLocation=' . rawurlencode(home_url('/' . $key . '.txt'));

        wp_remote_get($endpoint, [
            'timeout' => 3,
            'blocking' => false,
        ]);
    }

    public function ping_indexnow_on_publish(int $post_id, WP_Post $post, bool $update): void
    {
        unset($update);
        if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
            return;
        }
        if ($post->post_status !== 'publish' || !in_array($post->post_type, ['post', 'page'], true)) {
            return;
        }

        $url = get_permalink($post);
        if (is_string($url)) {
            $this->ping_indexnow_for_url($url);
        }
    }

    public function ping_indexnow_on_delete(int $post_id): void
    {
        $post = get_post($post_id);
        if (!$post instanceof WP_Post || !in_array($post->post_type, ['post', 'page'], true)) {
            return;
        }

        $url = get_permalink($post);
        if (is_string($url)) {
            $this->ping_indexnow_for_url($url);
        }
    }

    /**
     * llms.txt (emerging convention, not an official standard) - a plain-text
     * map of a site's key content for AI assistants/crawlers, analogous to
     * robots.txt. Generated on request rather than cached, so it always
     * reflects current published content; reuses the same request-path
     * dispatch as the IndexNow key file and the same description-resolution
     * helper as the meta/social tags.
     */
    public function serve_llms_txt(): void
    {
        if ($this->get_current_request_path() !== '/llms.txt' || $this->output->is_another_seo_plugin_active()) {
            return;
        }

        $site_name = get_bloginfo('name');
        $tagline = get_bloginfo('description');

        $lines = ['# ' . $site_name];
        if ($tagline !== '') {
            $lines[] = '';
            $lines[] = '> ' . $tagline;
        }
        $lines[] = '';
        $lines[] = '## Pages';
        $lines[] = '';

        $posts = get_posts([
            'post_type' => ['page', 'post'],
            'post_status' => 'publish',
            'posts_per_page' => 50,
            'orderby' => 'modified',
            'order' => 'DESC',
            'no_found_rows' => true,
        ]);
        foreach ($posts as $listed_post) {
            $listed_title = trim((string) $listed_post->post_title);
            if ($listed_title === '') {
                continue;
            }
            $listed_url = get_permalink($listed_post);
            $listed_description = $this->output->get_effective_meta_description($listed_post, 120);
            $line = '- [' . $listed_title . '](' . $listed_url . ')';
            if ($listed_description !== '') {
                $line .= ': ' . $listed_description;
            }
            $lines[] = $line;
        }

        // Plain-text response, not HTML - esc_html() would corrupt the file's own
        // markdown-style "> " blockquote marker (into "&gt; ") and double-encode any
        // "&"/quotes already present in real titles/descriptions. No injection risk
        // to escape against: Content-Type is already sent, and post titles can't
        // contain newlines to fake extra lines.
        header('Content-Type: text/plain; charset=utf-8');
        echo implode("\n", $lines);
        exit;
    }

    /**
     * A lightweight 404 log (icap_seo_404_log option, capped at 200 rows,
     * evicting the oldest by last-seen when full) so the Redirects tab can
     * surface real broken links to fix instead of requiring the user to
     * already know what's missing. Only counts genuine WordPress 404s that
     * reach this point - a request this plugin's own redirect/IndexNow/
     * llms.txt handlers already served above never reaches here, since each
     * of those exits immediately on a match.
     */
    public function maybe_log_404(): void
    {
        if (!is_404() || is_admin() || wp_doing_ajax() || (defined('REST_REQUEST') && REST_REQUEST)) {
            return;
        }

        $path = $this->get_current_request_path();
        if ($path === '/') {
            return;
        }

        $log = get_option('icap_seo_404_log', []);
        if (!is_array($log)) {
            $log = [];
        }

        $now = gmdate('c');
        $found = false;
        foreach ($log as &$row) {
            if (is_array($row) && ($row['path'] ?? '') === $path) {
                $row['hits'] = (int) ($row['hits'] ?? 0) + 1;
                $row['last_seen'] = $now;
                $found = true;
                break;
            }
        }
        unset($row);

        if (!$found) {
            $referrer = wp_get_referer();
            $log[] = [
                'path' => $path,
                'hits' => 1,
                'last_seen' => $now,
                'referrer' => is_string($referrer) ? $referrer : '',
            ];
        }

        if (count($log) > 200) {
            usort($log, static fn($a, $b): int => strcmp((string) ($a['last_seen'] ?? ''), (string) ($b['last_seen'] ?? '')));
            $log = array_slice($log, count($log) - 200);
        }

        update_option('icap_seo_404_log', $log, false);
    }
}
