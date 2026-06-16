<?php

if (!defined('ABSPATH')) {
    exit;
}

class ICap_SEO_Service_Client
{
    private const SETTINGS_OPTION_KEY = 'icap_seo_settings';
    private const CONTENT_SCORES_CACHE_TTL_SECONDS = 120;

    private ?array $content_scores_index_cache = null;

    public function get_connection_settings(): array
    {
        $saved = get_option(self::SETTINGS_OPTION_KEY, []);
        if (!is_array($saved)) {
            $saved = [];
        }

        return array_merge(
            [
                'api_base_url' => '',
                'site_id' => '',
                'site_token' => '',
                'registration_token' => '',
                'last_scan_id' => '',
                'last_sync_at' => '',
                'last_billing_state' => '',
                'last_billing_checked_at' => '',
            ],
            $saved
        );
    }

    public function update_connection_settings(array $partial_settings): void
    {
        $current = $this->get_connection_settings();
        $updated = array_merge($current, $partial_settings);
        update_option(self::SETTINGS_OPTION_KEY, $updated);
    }
    public function get_site_score_snapshot(bool $allow_live_fetch = true): array
    {
        $scores = $this->get_content_scores_overview($allow_live_fetch);

        if (!empty($scores)) {
            $sum = 0;
            $count = 0;

            foreach ($scores as $row) {
                $sum += (int) $row['icap_score_numeric'];
                $count++;
            }

            return [
                'score' => $count > 0 ? sprintf('%d/100', (int) round($sum / $count)) : null,
                'last_scan' => $this->get_connection_settings()['last_sync_at'] ?: 'Unknown',
                'status' => 'Connected',
            ];
        }

        $status_message = $this->is_api_connection_configured() ? 'Connected (awaiting scan data)' : 'Not connected';
        return [
            'score' => null,
            'last_scan' => null,
            'status' => $status_message,
        ];
    }

    public function get_recommendation_preview(): array
    {
        return [
            'items' => [],
            'source' => 'placeholder',
        ];
    }

    public function is_api_connection_configured_public(): bool
    {
        return $this->is_api_connection_configured();
    }

    public function get_content_score_for_post(int $post_id): array
    {
        $scores_index = $this->get_content_scores_index();
        if (isset($scores_index[$post_id])) {
            return $scores_index[$post_id];
        }
        return $this->build_placeholder_score_data($post_id);
    }

    public function get_content_scores_overview(bool $allow_live_fetch = true): array
    {
        $settings = $this->get_connection_settings();
        $site_id = $settings['site_id'];
        $cache_key = $site_id !== '' ? sprintf('icap_seo_scores_%s', md5($site_id)) : '';

        if ($cache_key !== '') {
            $cached = get_transient($cache_key);
            if (is_array($cached)) {
                return $cached;
            }
        }

        if ($allow_live_fetch) {
            $api_rows = $this->fetch_content_scores_from_api();
            if (!empty($api_rows)) {
                $this->update_connection_settings([
                    'last_sync_at' => current_time('mysql'),
                ]);

                if ($cache_key !== '') {
                    set_transient($cache_key, $api_rows, self::CONTENT_SCORES_CACHE_TTL_SECONDS);
                }

                return $api_rows;
            }
        }
        $posts = get_posts([
            'post_type' => ['post', 'page'],
            'post_status' => ['publish', 'draft', 'pending', 'future', 'private'],
            'posts_per_page' => 100,
            'orderby' => 'modified',
            'order' => 'DESC',
        ]);

        $rows = [];

        foreach ($posts as $post) {
            $score_data = $this->build_placeholder_score_data((int) $post->ID);
            $icap_score_numeric = (int) str_replace('/100', '', $score_data['icap_score']);
            $rows[] = [
                'id' => (int) $post->ID,
                'title' => get_the_title($post),
                'type' => (string) $post->post_type,
                'status' => (string) $post->post_status,
                'edit_link' => get_edit_post_link((int) $post->ID, ''),
                'icap_score' => $score_data['icap_score'],
                'icap_score_numeric' => $icap_score_numeric,
                'rank_math_score' => $score_data['rank_math_score'],
                'rank_math_delta' => $score_data['rank_math_delta'],
                'source' => 'placeholder',
            ];
        }

        return $rows;
    }

    public function register_site(array $payload): array
    {
        $settings = $this->get_connection_settings();
        if (empty($settings['api_base_url'])) {
            return [
                'success' => false,
                'error' => [
                    'code' => 'api_base_url_missing',
                    'message' => 'API Base URL is required before requesting registration credentials.',
                ],
            ];
        }
        $registration_token = $this->resolve_registration_token();
        if ($registration_token === '') {
            return [
                'success' => false,
                'error' => [
                    'code' => 'registration_token_missing',
                    'message' => 'Registration token is required. Define ICAP_SEO_REGISTRATION_TOKEN in wp-config.php or save Registration Token in plugin settings.',
                ],
            ];
        }

        $result = $this->api_request(
            'POST',
            '/v1/sites/register',
            $payload,
            [],
            false,
            [
                'X-ICAP-Registration-Token' => $registration_token,
            ]
        );
        if (!$result['success']) {
            return $result;
        }

        $data = $result['data'];
        $updated = [];

        if (!empty($data['api_base_url']) && is_string($data['api_base_url'])) {
            $updated['api_base_url'] = esc_url_raw($data['api_base_url']);
        }
        if (!empty($data['site_id']) && is_string($data['site_id'])) {
            $updated['site_id'] = sanitize_text_field($data['site_id']);
        }
        if (!empty($data['site_token']) && is_string($data['site_token'])) {
            $updated['site_token'] = sanitize_text_field($data['site_token']);
        }

        if (!empty($updated)) {
            $this->update_connection_settings($updated);
        }

        return $result;
    }

    public function trigger_scan(string $scan_type = 'full_site'): array
    {
        $settings = $this->get_connection_settings();
        if (empty($settings['site_id']) || empty($settings['site_token'])) {
            return [
                'success' => false,
                'error' => [
                    'code' => 'site_not_configured',
                    'message' => 'Site registration credentials are not configured.',
                ],
            ];
        }

        $result = $this->api_request(
            'POST',
            sprintf('/v1/sites/%s/scans', rawurlencode($settings['site_id'])),
            [
                'scan_type' => $scan_type,
                'requested_by' => 'manual',
            ]
        );

        if ($result['success'] && !empty($result['data']['scan_id'])) {
            $this->update_connection_settings([
                'last_scan_id' => sanitize_text_field((string) $result['data']['scan_id']),
            ]);
        }

        return $result;
    }

    public function get_subscription_status(bool $allow_live_fetch = true): array
    {
        if (!$allow_live_fetch) {
            return [
                'success' => false,
                'error' => [
                    'code' => 'live_fetch_disabled',
                    'message' => 'Live subscription status fetch is disabled for this request.',
                ],
            ];
        }

        $settings = $this->get_connection_settings();
        if (empty($settings['site_id']) || empty($settings['site_token'])) {
            return [
                'success' => false,
                'error' => [
                    'code' => 'site_not_configured',
                    'message' => 'Site registration credentials are not configured.',
                ],
            ];
        }

        $result = $this->api_request('GET', '/v1/billing/subscription-status');
        if (!$result['success']) {
            return $result;
        }

        $state = 'unknown';
        if (isset($result['data']['entitlement_state']) && is_string($result['data']['entitlement_state'])) {
            $normalized_state = sanitize_key($result['data']['entitlement_state']);
            if ($normalized_state !== '') {
                $state = $normalized_state;
            }
        }

        $result['data']['entitlement_state'] = $state;
        if (isset($result['data']['plan_code']) && is_string($result['data']['plan_code'])) {
            $result['data']['plan_code'] = sanitize_text_field($result['data']['plan_code']);
        }

        $this->update_connection_settings([
            'last_billing_state' => $state,
            'last_billing_checked_at' => current_time('mysql'),
        ]);

        return $result;
    }

    public function get_scan_status(?string $scan_id = null, bool $allow_live_fetch = true): array
    {
        $settings = $this->get_connection_settings();
        $resolved_scan_id = $scan_id ?: $settings['last_scan_id'];
        if (!$allow_live_fetch) {
            return [
                'success' => false,
                'error' => [
                    'code' => 'live_fetch_disabled',
                    'message' => 'Live scan status fetch is disabled for this request.',
                ],
            ];
        }

        if (empty($settings['site_id']) || empty($resolved_scan_id)) {
            return [
                'success' => false,
                'error' => [
                    'code' => 'scan_not_configured',
                    'message' => 'No scan ID available.',
                ],
            ];
        }

        return $this->api_request(
            'GET',
            sprintf(
                '/v1/sites/%s/scans/%s',
                rawurlencode($settings['site_id']),
                rawurlencode($resolved_scan_id)
            )
        );
    }

    private function fetch_content_scores_from_api(): array
    {
        $settings = $this->get_connection_settings();
        if (empty($settings['site_id']) || !$this->is_api_connection_configured()) {
            return [];
        }

        $result = $this->api_request(
            'GET',
            sprintf('/v1/sites/%s/content-scores', rawurlencode($settings['site_id'])),
            [],
            [
                'post_type' => 'all',
                'limit' => 100,
            ]
        );

        if (!$result['success']) {
            return [];
        }

        $items = $result['data']['items'] ?? [];
        if (!is_array($items)) {
            return [];
        }

        $rows = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $post_id = isset($item['wp_post_id']) ? (int) $item['wp_post_id'] : 0;
            $overall_score = isset($item['overall_score']) ? (int) $item['overall_score'] : 0;
            $rank_math_score = isset($item['rank_math_score']) ? (int) $item['rank_math_score'] : null;
            $delta = isset($item['delta_vs_rank_math']) ? (int) $item['delta_vs_rank_math'] : null;

            if ($delta === null && $rank_math_score !== null) {
                $delta = $overall_score - $rank_math_score;
            }

            $delta_display = 'n/a';
            if ($delta !== null) {
                $delta_display = sprintf('%s%d', $delta > 0 ? '+' : '', $delta);
            }

            $rank_math_display = $rank_math_score !== null ? sprintf('%d/100', $rank_math_score) : 'n/a';

            $rows[] = [
                'id' => $post_id,
                'title' => isset($item['title']) ? sanitize_text_field((string) $item['title']) : sprintf('Post %d', $post_id),
                'type' => isset($item['post_type']) ? sanitize_key((string) $item['post_type']) : '',
                'status' => isset($item['status']) ? sanitize_key((string) $item['status']) : '',
                'edit_link' => $post_id > 0 ? get_edit_post_link($post_id, '') : '',
                'icap_score' => sprintf('%d/100', $overall_score),
                'icap_score_numeric' => $overall_score,
                'rank_math_score' => $rank_math_display,
                'rank_math_delta' => $delta_display,
                'source' => 'api',
            ];
        }

        return $rows;
    }

    private function get_content_scores_index(): array
    {
        if ($this->content_scores_index_cache !== null) {
            return $this->content_scores_index_cache;
        }

        $index = [];
        foreach ($this->get_content_scores_overview() as $row) {
            if (!isset($row['id'])) {
                continue;
            }
            $index[(int) $row['id']] = [
                'icap_score' => (string) $row['icap_score'],
                'rank_math_score' => (string) $row['rank_math_score'],
                'rank_math_delta' => (string) $row['rank_math_delta'],
            ];
        }

        $this->content_scores_index_cache = $index;

        return $index;
    }

    private function build_placeholder_score_data(int $post_id): array
    {
        $icap_score_value = 60 + ($post_id % 35);
        $rank_math_value = 55 + ($post_id % 40);
        $delta = $icap_score_value - $rank_math_value;
        $delta_prefix = $delta > 0 ? '+' : '';

        return [
            'icap_score' => sprintf('%d/100', $icap_score_value),
            'rank_math_score' => sprintf('%d/100', $rank_math_value),
            'rank_math_delta' => sprintf('%s%d', $delta_prefix, $delta),
        ];
    }

    private function is_api_connection_configured(): bool
    {
        $settings = $this->get_connection_settings();
        return !empty($settings['api_base_url']) && !empty($settings['site_token']) && !empty($settings['site_id']);
    }
    private function resolve_registration_token(): string
    {
        if (defined('ICAP_SEO_REGISTRATION_TOKEN')) {
            $constant_value = sanitize_text_field((string) ICAP_SEO_REGISTRATION_TOKEN);
            if ($constant_value !== '') {
                return $constant_value;
            }
        }

        $settings = $this->get_connection_settings();
        if (!empty($settings['registration_token']) && is_string($settings['registration_token'])) {
            return sanitize_text_field($settings['registration_token']);
        }

        return '';
    }

    private function api_request(string $method, string $path, array $body = [], array $query = [], bool $requires_auth = true, array $extra_headers = []): array
    {
        $settings = $this->get_connection_settings();

        if (empty($settings['api_base_url'])) {
            return [
                'success' => false,
                'error' => [
                    'code' => 'api_base_url_missing',
                    'message' => 'API Base URL is not configured.',
                ],
            ];
        }
        if ($requires_auth && (empty($settings['site_token']) || empty($settings['site_id']))) {
            return [
                'success' => false,
                'error' => [
                    'code' => 'not_configured',
                    'message' => 'Site ID and Site Token are required.',
                ],
            ];
        }

        $url = rtrim($settings['api_base_url'], '/') . '/' . ltrim($path, '/');
        if (!empty($query)) {
            $url = add_query_arg($query, $url);
        }

        $headers = [
            'X-ICAP-Plugin-Version' => ICAP_SEO_VERSION,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
        if ($requires_auth) {
            $headers['Authorization'] = 'Bearer ' . $settings['site_token'];
            $headers['X-ICAP-Site-Id'] = $settings['site_id'];
        }
        if (!empty($extra_headers)) {
            $headers = array_merge($headers, $extra_headers);
        }

        $args = [
            'method' => strtoupper($method),
            'timeout' => 3,
            'headers' => $headers,
        ];

        if ($args['method'] !== 'GET' && !empty($body)) {
            $args['body'] = wp_json_encode($body);
        }

        $response = wp_remote_request($url, $args);
        if (is_wp_error($response)) {
            return [
                'success' => false,
                'error' => [
                    'code' => 'network_error',
                    'message' => $response->get_error_message(),
                ],
            ];
        }

        $status_code = (int) wp_remote_retrieve_response_code($response);
        $raw_body = wp_remote_retrieve_body($response);
        $decoded_body = json_decode((string) $raw_body, true);

        if ($status_code >= 400) {
            $error_payload = is_array($decoded_body) ? ($decoded_body['error'] ?? []) : [];
            return [
                'success' => false,
                'error' => [
                    'code' => isset($error_payload['code']) ? (string) $error_payload['code'] : 'api_error',
                    'message' => isset($error_payload['message']) ? (string) $error_payload['message'] : sprintf('API request failed with status %d.', $status_code),
                ],
            ];
        }

        if (is_array($decoded_body) && array_key_exists('success', $decoded_body)) {
            return [
                'success' => (bool) $decoded_body['success'],
                'data' => isset($decoded_body['data']) && is_array($decoded_body['data']) ? $decoded_body['data'] : [],
                'error' => isset($decoded_body['error']) && is_array($decoded_body['error']) ? $decoded_body['error'] : [],
            ];
        }

        return [
            'success' => true,
            'data' => is_array($decoded_body) ? $decoded_body : [],
        ];
    }
}
