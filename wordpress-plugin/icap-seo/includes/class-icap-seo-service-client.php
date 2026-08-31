<?php

if (!defined('ABSPATH')) {
    exit;
}

class ICap_SEO_Service_Client
{
    private const SETTINGS_OPTION_KEY = 'icap_seo_settings';
    private const CONTENT_SCORES_CACHE_TTL_SECONDS = 120;

    private ?array $content_scores_index_cache = null;
    private array $latest_content_scores_meta = [
        'scan_id' => '',
        'scan_tier' => '',
        'scan_layers' => [
            'executed' => [],
            'premium_locked' => [],
        ],
        'item_count' => 0,
        'source' => 'unknown',
    ];

    public function get_connection_settings(): array
    {
        $saved = get_option(self::SETTINGS_OPTION_KEY, []);
        if (!is_array($saved)) {
            $saved = [];
        }
        $default_api_base_url = '';
        if (defined('ICAP_SEO_DEFAULT_API_BASE_URL')) {
            $default_api_base_url = esc_url_raw((string) ICAP_SEO_DEFAULT_API_BASE_URL);
        }
        if ($default_api_base_url === '') {
            $default_api_base_url = 'https://de1mbls2mfy7q.cloudfront.net';
        }

        return array_merge(
            [
                'api_base_url' => $default_api_base_url,
                'site_id' => '',
                'site_token' => '',
                'registration_token' => '',
                'last_scan_id' => '',
                'last_sync_at' => '',
                'last_billing_state' => '',
                'last_billing_checked_at' => '',
                'ai_credits_remaining' => 0,
                'ai_credits_checked_at' => '',
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

        if ($cache_key !== '' && !$allow_live_fetch) {
            $cached = get_transient($cache_key);
            if (is_array($cached)) {
                if (array_key_exists('rows', $cached) && is_array($cached['rows'])) {
                    if (isset($cached['meta']) && is_array($cached['meta'])) {
                        $this->latest_content_scores_meta = $this->normalize_content_scores_meta($cached['meta']);
                    } else {
                        $this->latest_content_scores_meta = $this->build_content_scores_meta(
                            '',
                            '',
                            [],
                            count($cached['rows']),
                            'cache'
                        );
                    }
                    return $cached['rows'];
                }

                $this->latest_content_scores_meta = $this->build_content_scores_meta(
                    '',
                    '',
                    [],
                    count($cached),
                    'cache_legacy'
                );
                return $cached;
            }
        }

        if ($allow_live_fetch) {
            $api_rows = $this->fetch_content_scores_from_api();
            if (($this->latest_content_scores_meta['source'] ?? '') === 'api') {
                $this->update_connection_settings([
                    'last_sync_at' => current_time('mysql'),
                ]);

                if ($cache_key !== '') {
                    set_transient(
                        $cache_key,
                        [
                            'rows' => $api_rows,
                            'meta' => $this->latest_content_scores_meta,
                        ],
                        self::CONTENT_SCORES_CACHE_TTL_SECONDS
                    );
                }

                return $api_rows;
            }
        }
        if ($cache_key !== '' && $allow_live_fetch) {
            $cached = get_transient($cache_key);
            if (is_array($cached)) {
                if (array_key_exists('rows', $cached) && is_array($cached['rows'])) {
                    if (isset($cached['meta']) && is_array($cached['meta'])) {
                        $this->latest_content_scores_meta = $this->normalize_content_scores_meta($cached['meta']);
                    } else {
                        $this->latest_content_scores_meta = $this->build_content_scores_meta(
                            '',
                            '',
                            [],
                            count($cached['rows']),
                            'cache_fallback'
                        );
                    }
                    return $cached['rows'];
                }

                $this->latest_content_scores_meta = $this->build_content_scores_meta(
                    '',
                    '',
                    [],
                    count($cached),
                    'cache_legacy'
                );
                return $cached;
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
                'content_key' => sprintf('post_%d', (int) $post->ID),
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
        $this->latest_content_scores_meta = $this->build_content_scores_meta(
            '',
            '',
            [],
            count($rows),
            'placeholder'
        );

        return $rows;
    }

    public function get_content_score_detail(string $content_key, bool $allow_live_fetch = true): array
    {
        if (!$allow_live_fetch) {
            return [
                'success' => false,
                'error' => [
                    'code' => 'live_fetch_disabled',
                    'message' => 'Live content detail fetch is disabled for this request.',
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

        $resolved_content_key = sanitize_text_field(trim($content_key));
        if ($resolved_content_key === '') {
            return [
                'success' => false,
                'error' => [
                    'code' => 'validation_error',
                    'message' => 'Content key is required.',
                ],
            ];
        }

        $result = $this->api_request(
            'GET',
            sprintf(
                '/v1/sites/%s/content-scores/%s',
                rawurlencode((string) $settings['site_id']),
                rawurlencode($resolved_content_key)
            )
        );
        if (!$result['success']) {
            return $result;
        }

        $data = isset($result['data']) && is_array($result['data']) ? $result['data'] : [];
        $category_scores = [];
        if (isset($data['category_scores']) && is_array($data['category_scores'])) {
            foreach ($data['category_scores'] as $category => $value) {
                $category_key = sanitize_key((string) $category);
                if ($category_key === '') {
                    continue;
                }
                $category_scores[$category_key] = (int) $value;
            }
        }

        $issues = [];
        if (isset($data['issues']) && is_array($data['issues'])) {
            foreach ($data['issues'] as $issue) {
                if (!is_array($issue)) {
                    continue;
                }
                $issues[] = [
                    'issue_code' => isset($issue['issue_code']) ? sanitize_key((string) $issue['issue_code']) : '',
                    'severity' => isset($issue['severity']) ? sanitize_key((string) $issue['severity']) : 'medium',
                    'description' => isset($issue['description']) ? sanitize_text_field((string) $issue['description']) : '',
                    'recommended_fix' => isset($issue['recommended_fix']) ? sanitize_text_field((string) $issue['recommended_fix']) : '',
                    'estimated_effort' => isset($issue['estimated_effort']) ? sanitize_key((string) $issue['estimated_effort']) : '',
                ];
            }
        }

        $history = [];
        if (isset($data['history']) && is_array($data['history'])) {
            foreach ($data['history'] as $history_row) {
                if (!is_array($history_row)) {
                    continue;
                }
                $history[] = [
                    'scan_id' => isset($history_row['scan_id']) ? sanitize_text_field((string) $history_row['scan_id']) : '',
                    'scored_at' => isset($history_row['scored_at']) ? sanitize_text_field((string) $history_row['scored_at']) : '',
                    'overall_score' => isset($history_row['overall_score']) ? (int) $history_row['overall_score'] : 0,
                ];
            }
        }

        $result['data'] = [
            'content_key' => isset($data['content_key']) ? sanitize_text_field((string) $data['content_key']) : $resolved_content_key,
            'wp_post_id' => isset($data['wp_post_id']) ? (int) $data['wp_post_id'] : 0,
            'title' => isset($data['title']) ? sanitize_text_field((string) $data['title']) : '',
            'post_type' => isset($data['post_type']) ? sanitize_key((string) $data['post_type']) : '',
            'status' => isset($data['status']) ? sanitize_key((string) $data['status']) : '',
            'permalink' => isset($data['permalink']) ? esc_url_raw((string) $data['permalink']) : '',
            'overall_score' => isset($data['overall_score']) ? (int) $data['overall_score'] : 0,
            'rank_math_score' => isset($data['rank_math_score']) && $data['rank_math_score'] !== null ? (int) $data['rank_math_score'] : null,
            'delta_vs_rank_math' => isset($data['delta_vs_rank_math']) && $data['delta_vs_rank_math'] !== null ? (int) $data['delta_vs_rank_math'] : null,
            'last_scored_at' => isset($data['last_scored_at']) ? sanitize_text_field((string) $data['last_scored_at']) : '',
            'category_scores' => $category_scores,
            'issues' => $issues,
            'history' => $history,
        ];

        return $result;
    }

    public function get_content_remediation_preview(string $content_key, array $approved_issue_codes = [], bool $allow_live_fetch = true): array
    {
        if (!$allow_live_fetch) {
            return [
                'success' => false,
                'error' => [
                    'code' => 'live_fetch_disabled',
                    'message' => 'Live remediation preview fetch is disabled for this request.',
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

        $resolved_content_key = sanitize_text_field(trim($content_key));
        if ($resolved_content_key === '') {
            return [
                'success' => false,
                'error' => [
                    'code' => 'validation_error',
                    'message' => 'Content key is required.',
                ],
            ];
        }

        $payload = [];
        $normalized_issue_codes = $this->normalize_approved_issue_codes($approved_issue_codes);
        if (!empty($normalized_issue_codes)) {
            $payload['approved_issue_codes'] = $normalized_issue_codes;
        }

        $result = $this->api_request(
            'POST',
            sprintf(
                '/v1/sites/%s/content-scores/%s/remediation-preview',
                rawurlencode((string) $settings['site_id']),
                rawurlencode($resolved_content_key)
            ),
            $payload
        );
        if (!$result['success']) {
            return $result;
        }

        $data = isset($result['data']) && is_array($result['data']) ? $result['data'] : [];
        $proposed_changes = [];
        if (isset($data['proposed_changes']) && is_array($data['proposed_changes'])) {
            foreach ($data['proposed_changes'] as $change_row) {
                if (!is_array($change_row)) {
                    continue;
                }
                $target = isset($change_row['target']) && is_array($change_row['target']) ? $change_row['target'] : [];
                $proposed_changes[] = [
                    'change_id' => isset($change_row['change_id']) ? sanitize_text_field((string) $change_row['change_id']) : '',
                    'issue_code' => isset($change_row['issue_code']) ? sanitize_key((string) $change_row['issue_code']) : '',
                    'severity' => isset($change_row['severity']) ? sanitize_key((string) $change_row['severity']) : 'medium',
                    'summary' => isset($change_row['summary']) ? sanitize_text_field((string) $change_row['summary']) : '',
                    'estimated_effort' => isset($change_row['estimated_effort']) ? sanitize_key((string) $change_row['estimated_effort']) : '',
                    'requires_editor_review' => !empty($change_row['requires_editor_review']),
                    'target' => [
                        'content_key' => isset($target['content_key']) ? sanitize_text_field((string) $target['content_key']) : '',
                        'wp_post_id' => isset($target['wp_post_id']) ? (int) $target['wp_post_id'] : 0,
                    ],
                ];
            }
        }

        $result['data'] = [
            'site_id' => isset($data['site_id']) ? sanitize_text_field((string) $data['site_id']) : '',
            'content_key' => isset($data['content_key']) ? sanitize_text_field((string) $data['content_key']) : $resolved_content_key,
            'scan_id' => isset($data['scan_id']) ? sanitize_text_field((string) $data['scan_id']) : '',
            'overall_score' => isset($data['overall_score']) ? (int) $data['overall_score'] : 0,
            'proposed_changes' => $proposed_changes,
            'summary' => isset($data['summary']) && is_array($data['summary']) ? $data['summary'] : [],
        ];

        return $result;
    }

    public function apply_content_remediation(string $content_key, array $approved_issue_codes = [], bool $allow_live_fetch = true): array
    {
        if (!$allow_live_fetch) {
            return [
                'success' => false,
                'error' => [
                    'code' => 'live_fetch_disabled',
                    'message' => 'Live remediation apply is disabled for this request.',
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

        $resolved_content_key = sanitize_text_field(trim($content_key));
        if ($resolved_content_key === '') {
            return [
                'success' => false,
                'error' => [
                    'code' => 'validation_error',
                    'message' => 'Content key is required.',
                ],
            ];
        }

        $payload = [];
        $normalized_issue_codes = $this->normalize_approved_issue_codes($approved_issue_codes);
        if (!empty($normalized_issue_codes)) {
            $payload['approved_issue_codes'] = $normalized_issue_codes;
        }

        $result = $this->api_request(
            'POST',
            sprintf(
                '/v1/sites/%s/content-scores/%s/apply-remediation',
                rawurlencode((string) $settings['site_id']),
                rawurlencode($resolved_content_key)
            ),
            $payload
        );
        if (!$result['success']) {
            return $result;
        }

        $data = isset($result['data']) && is_array($result['data']) ? $result['data'] : [];
        $result['data'] = [
            'remediation_job_id' => isset($data['remediation_job_id']) ? sanitize_text_field((string) $data['remediation_job_id']) : '',
            'status' => isset($data['status']) ? sanitize_key((string) $data['status']) : '',
            'queued_changes_count' => isset($data['queued_changes_count']) ? (int) $data['queued_changes_count'] : 0,
            'requires_confirmation' => !empty($data['requires_confirmation']),
        ];

        return $result;
    }

    public function request_ai_content_draft(string $content_key, string $generator, array $context): array
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

        $resolved_content_key = sanitize_text_field(trim($content_key));
        if ($resolved_content_key === '') {
            return [
                'success' => false,
                'error' => [
                    'code' => 'validation_error',
                    'message' => 'Content key is required.',
                ],
            ];
        }

        $payload = [
            'generator' => sanitize_key($generator),
            'context' => [
                'title' => isset($context['title']) ? (string) $context['title'] : '',
                'existing_content_text' => isset($context['existing_content_text']) ? (string) $context['existing_content_text'] : '',
                'site_name' => isset($context['site_name']) ? (string) $context['site_name'] : '',
                'post_type' => isset($context['post_type']) ? (string) $context['post_type'] : '',
                'target_word_count' => isset($context['target_word_count']) ? (int) $context['target_word_count'] : 0,
                'heading_count' => isset($context['heading_count']) ? (int) $context['heading_count'] : 0,
                'link_candidates' => isset($context['link_candidates']) && is_array($context['link_candidates'])
                    ? array_values(array_map(static fn($title): string => sanitize_text_field((string) $title), $context['link_candidates']))
                    : [],
                'image_urls' => isset($context['image_urls']) && is_array($context['image_urls'])
                    ? array_values(array_filter(array_map(static fn($url): string => esc_url_raw((string) $url), $context['image_urls'])))
                    : [],
                'paragraphs' => isset($context['paragraphs']) && is_array($context['paragraphs'])
                    ? array_values(array_map(static fn($text): string => sanitize_text_field((string) $text), $context['paragraphs']))
                    : [],
            ],
        ];

        // 28s: above the Lambda's 25s function timeout so a slow-but-completing AI call
        // returns the Lambda's own response/error rather than a client-side abort racing it.
        $result = $this->api_request(
            'POST',
            sprintf(
                '/v1/sites/%s/content-scores/%s/ai-draft',
                rawurlencode((string) $settings['site_id']),
                rawurlencode($resolved_content_key)
            ),
            $payload,
            [],
            true,
            [],
            28
        );
        if (!$result['success']) {
            return $result;
        }

        $data = isset($result['data']) && is_array($result['data']) ? $result['data'] : [];
        $result['data'] = [
            'generator' => isset($data['generator']) ? sanitize_key((string) $data['generator']) : '',
            'draft_text' => isset($data['draft_text']) ? (string) $data['draft_text'] : '',
            'draft_word_count' => isset($data['draft_word_count']) ? (int) $data['draft_word_count'] : 0,
            'model' => isset($data['model']) ? sanitize_text_field((string) $data['model']) : '',
        ];

        return $result;
    }

    private function normalize_layer_rows($raw_layers): array
    {
        if (!is_array($raw_layers)) {
            return [];
        }
        $normalized = [];
        foreach ($raw_layers as $layer) {
            if (is_array($layer)) {
                $row = [];
                if (isset($layer['layer_id']) && is_string($layer['layer_id'])) {
                    $row['layer_id'] = sanitize_key($layer['layer_id']);
                }
                if (isset($layer['name']) && is_string($layer['name'])) {
                    $row['name'] = sanitize_text_field($layer['name']);
                }
                if (isset($layer['sub_skill']) && is_string($layer['sub_skill'])) {
                    $row['sub_skill'] = sanitize_key($layer['sub_skill']);
                }
                if (isset($layer['premium_only'])) {
                    $row['premium_only'] = (bool) $layer['premium_only'];
                }
                if (!empty($row)) {
                    $normalized[] = $row;
                }
                continue;
            }

            if (is_string($layer)) {
                $normalized[] = [
                    'name' => sanitize_text_field($layer),
                ];
            }
        }
        return $normalized;
    }

    private function normalize_scan_layers_meta($raw_scan_layers): array
    {
        $scan_layers = is_array($raw_scan_layers) ? $raw_scan_layers : [];
        return [
            'executed' => $this->normalize_layer_rows($scan_layers['executed'] ?? []),
            'premium_locked' => $this->normalize_layer_rows($scan_layers['premium_locked'] ?? []),
        ];
    }

    private function build_content_scores_meta(string $scan_id, string $scan_tier, array $scan_layers, int $item_count, string $source): array
    {
        return [
            'scan_id' => sanitize_text_field($scan_id),
            'scan_tier' => sanitize_key($scan_tier),
            'scan_layers' => $this->normalize_scan_layers_meta($scan_layers),
            'item_count' => max(0, (int) $item_count),
            'source' => sanitize_key($source),
        ];
    }

    private function normalize_content_scores_meta(array $meta): array
    {
        return $this->build_content_scores_meta(
            isset($meta['scan_id']) && is_string($meta['scan_id']) ? $meta['scan_id'] : '',
            isset($meta['scan_tier']) && is_string($meta['scan_tier']) ? $meta['scan_tier'] : '',
            isset($meta['scan_layers']) && is_array($meta['scan_layers']) ? $meta['scan_layers'] : [],
            isset($meta['item_count']) ? (int) $meta['item_count'] : 0,
            isset($meta['source']) && is_string($meta['source']) ? $meta['source'] : 'cache'
        );
    }

    public function get_latest_content_scores_meta(): array
    {
        return $this->latest_content_scores_meta;
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

    public function request_registration(array $payload): array
    {
        $settings = $this->get_connection_settings();
        if (empty($settings['api_base_url'])) {
            return [
                'success' => false,
                'error' => [
                    'code' => 'api_base_url_missing',
                    'message' => 'API Base URL is required before requesting registration.',
                ],
            ];
        }

        return $this->api_request(
            'POST',
            '/v1/registration-requests',
            $payload,
            [],
            false
        );
    }

    public function trigger_scan(string $scan_type = 'full_site', array $options = []): array
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

        $scan_mode = sanitize_key((string) ($options['scan_mode'] ?? ''));
        if ($scan_mode === '') {
            $normalized_type = sanitize_key($scan_type);
            $scan_mode = in_array($normalized_type, ['content', 'single_content', 'page', 'single_page'], true)
                ? 'content'
                : 'full_site';
        }
        if (!in_array($scan_mode, ['full_site', 'content'], true)) {
            $scan_mode = 'full_site';
        }

        $payload = [
            'scan_type' => sanitize_key($scan_type) !== '' ? sanitize_key($scan_type) : ($scan_mode === 'content' ? 'content' : 'full_site'),
            'scan_mode' => $scan_mode,
            'requested_by' => isset($options['requested_by']) ? sanitize_key((string) $options['requested_by']) : 'manual',
        ];
        if ($payload['requested_by'] === '') {
            $payload['requested_by'] = 'manual';
        }

        $content_keys = [];
        if (isset($options['content_keys']) && is_array($options['content_keys'])) {
            foreach ($options['content_keys'] as $key) {
                $normalized = sanitize_text_field((string) $key);
                if ($normalized !== '') {
                    $content_keys[] = $normalized;
                }
            }
        }
        if (isset($options['content_key'])) {
            $single = sanitize_text_field((string) $options['content_key']);
            if ($single !== '') {
                $content_keys[] = $single;
            }
        }
        $content_keys = array_values(array_unique($content_keys));
        if (!empty($content_keys)) {
            $payload['content_keys'] = $content_keys;
        }

        $wp_post_ids = [];
        if (isset($options['wp_post_ids']) && is_array($options['wp_post_ids'])) {
            foreach ($options['wp_post_ids'] as $post_id) {
                $id = absint($post_id);
                if ($id > 0) {
                    $wp_post_ids[] = $id;
                }
            }
        }
        if (isset($options['wp_post_id'])) {
            $id = absint($options['wp_post_id']);
            if ($id > 0) {
                $wp_post_ids[] = $id;
            }
        }
        $wp_post_ids = array_values(array_unique($wp_post_ids));
        if (!empty($wp_post_ids)) {
            $payload['wp_post_ids'] = $wp_post_ids;
        }

        if ($scan_mode === 'content' && empty($content_keys) && empty($wp_post_ids)) {
            return [
                'success' => false,
                'error' => [
                    'code' => 'validation_error',
                    'message' => 'content scan requires content_key/content_keys and/or wp_post_id(s).',
                ],
            ];
        }

        // Scan execution runs synchronously on the backend within a single request -
        // for a full-site scan that can legitimately take several seconds (page
        // fetches, plus phases like broken-link checking with their own budget),
        // right up against the backend Lambda's own function timeout. The default
        // 3s client timeout used for fast metadata calls is too short here and was
        // causing this call to time out client-side while the scan kept running and
        // completed successfully server-side - use a longer timeout that comfortably
        // covers the backend's own worst case instead.
        $result = $this->api_request(
            'POST',
            sprintf('/v1/sites/%s/scans', rawurlencode($settings['site_id'])),
            $payload,
            [],
            true,
            [],
            15
        );

        if ($result['success'] && !empty($result['data']['scan_id'])) {
            $this->update_connection_settings([
                'last_scan_id' => sanitize_text_field((string) $result['data']['scan_id']),
            ]);
            // Bust content score cache so detail/list reflect the fresh page scan.
            $this->clear_content_scores_cache();
        }

        return $result;
    }

    public function clear_content_scores_cache(): void
    {
        $settings = $this->get_connection_settings();
        $site_id = isset($settings['site_id']) ? sanitize_text_field((string) $settings['site_id']) : '';
        if ($site_id === '') {
            return;
        }
        delete_transient(sprintf('icap_seo_scores_%s', md5($site_id)));
        $this->content_scores_index_cache = null;
        $this->latest_content_scores_meta = $this->build_content_scores_meta('', '', [], 0, 'cache_cleared');
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

        $ai_credits_remaining = 0;
        if (isset($result['data']['ai_credits_remaining']) && is_numeric($result['data']['ai_credits_remaining'])) {
            $ai_credits_remaining = (int) $result['data']['ai_credits_remaining'];
        }
        $result['data']['ai_credits_remaining'] = $ai_credits_remaining;

        $this->update_connection_settings([
            'last_billing_state' => $state,
            'last_billing_checked_at' => current_time('mysql'),
            'ai_credits_remaining' => $ai_credits_remaining,
            'ai_credits_checked_at' => current_time('mysql'),
        ]);

        return $result;
    }

    public function create_billing_checkout_session(array $payload = []): array
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

        return $this->api_request('POST', '/v1/billing/checkout-session', $payload);
    }

    public function create_ai_credit_checkout_session(array $payload = []): array
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

        return $this->api_request('POST', '/v1/billing/ai-credit-checkout-session', $payload);
    }

    public function create_billing_portal_session(array $payload = []): array
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

        return $this->api_request('POST', '/v1/billing/portal-session', $payload);
    }

    public function test_connection(): array
    {
        $settings = $this->get_connection_settings();
        if (empty($settings['api_base_url'])) {
            return [
                'success' => false,
                'error' => [
                    'code' => 'api_base_url_missing',
                    'message' => 'API Base URL is required before testing the connection.',
                ],
            ];
        }

        if (!empty($settings['site_id']) && !empty($settings['site_token'])) {
            $status_result = $this->get_subscription_status(true);
            if ($status_result['success']) {
                return [
                    'success' => true,
                    'mode' => 'authenticated',
                    'data' => $status_result['data'] ?? [],
                ];
            }

            $error_code = $this->extract_error_code($status_result);
            if ($error_code === 'invalid_token' || $error_code === 'forbidden') {
                return [
                    'success' => false,
                    'error' => [
                        'code' => 'invalid_token',
                        'message' => 'API is reachable, but Site ID/Site Token were rejected.',
                    ],
                ];
            }
            if ($error_code === 'network_error' || $error_code === 'upstream_unavailable') {
                return [
                    'success' => false,
                    'error' => [
                        'code' => 'network_error',
                        'message' => 'Could not reach the API endpoint.',
                    ],
                ];
            }
            if ($this->is_endpoint_missing_error($status_result)) {
                return [
                    'success' => false,
                    'error' => [
                        'code' => 'endpoint_not_found',
                        'message' => 'API endpoint path was not found. Check API Base URL.',
                    ],
                ];
            }

            return $status_result;
        }

        $probe_result = $this->api_request('GET', '/v1/billing/subscription-status', [], [], false);
        if ($probe_result['success']) {
            return [
                'success' => true,
                'mode' => 'reachable_unregistered',
                'data' => [],
            ];
        }

        $probe_error_code = $this->extract_error_code($probe_result);
        if ($probe_error_code === 'network_error' || $probe_error_code === 'upstream_unavailable') {
            return [
                'success' => false,
                'error' => [
                    'code' => 'network_error',
                    'message' => 'Could not reach the API endpoint.',
                ],
            ];
        }
        if ($this->is_endpoint_missing_error($probe_result)) {
            return [
                'success' => false,
                'error' => [
                    'code' => 'endpoint_not_found',
                    'message' => 'API endpoint path was not found. Check API Base URL.',
                ],
            ];
        }
        if ($this->is_server_error_response($probe_result)) {
            return [
                'success' => false,
                'error' => [
                    'code' => 'upstream_unavailable',
                    'message' => 'API responded with a server-side error.',
                ],
            ];
        }

        return [
            'success' => true,
            'mode' => 'reachable_unregistered',
            'data' => [],
        ];
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

        if (empty($settings['site_id'])) {
            return [
                'success' => false,
                'error' => [
                    'code' => 'scan_not_configured',
                    'message' => 'Site ID is not configured.',
                ],
            ];
        }
        if (empty($resolved_scan_id) && $allow_live_fetch) {
            $this->fetch_content_scores_from_api();
            $latest_scan_id = '';
            if (isset($this->latest_content_scores_meta['scan_id']) && is_string($this->latest_content_scores_meta['scan_id'])) {
                $latest_scan_id = sanitize_text_field($this->latest_content_scores_meta['scan_id']);
            }
            if ($latest_scan_id !== '') {
                $resolved_scan_id = $latest_scan_id;
                $this->update_connection_settings([
                    'last_scan_id' => $latest_scan_id,
                ]);
            }
        }
        if (empty($resolved_scan_id)) {
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
            $this->latest_content_scores_meta = $this->build_content_scores_meta(
                '',
                '',
                [],
                0,
                'not_configured'
            );
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
            $this->latest_content_scores_meta = $this->build_content_scores_meta(
                '',
                '',
                [],
                0,
                'api_error'
            );
            return [];
        }

        $items = $result['data']['items'] ?? [];
        if (!is_array($items)) {
            $this->latest_content_scores_meta = $this->build_content_scores_meta(
                '',
                '',
                [],
                0,
                'api_invalid'
            );
            return [];
        }
        $scan_id = '';
        if (isset($result['data']['scan_id']) && is_string($result['data']['scan_id'])) {
            $scan_id = sanitize_text_field($result['data']['scan_id']);
        }
        $scan_tier = '';
        if (isset($result['data']['scan_tier']) && is_string($result['data']['scan_tier'])) {
            $scan_tier = sanitize_key($result['data']['scan_tier']);
        }
        $scan_layers = [];
        if (isset($result['data']['scan_layers']) && is_array($result['data']['scan_layers'])) {
            $scan_layers = $result['data']['scan_layers'];
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
                'content_key' => isset($item['content_key']) && is_string($item['content_key']) && $item['content_key'] !== ''
                    ? sanitize_text_field((string) $item['content_key'])
                    : sprintf('post_%d', $post_id),
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
        $this->latest_content_scores_meta = $this->build_content_scores_meta(
            $scan_id,
            $scan_tier,
            $scan_layers,
            count($rows),
            'api'
        );

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

    private function normalize_approved_issue_codes(array $approved_issue_codes): array
    {
        $normalized = [];
        foreach ($approved_issue_codes as $issue_code) {
            $candidate = sanitize_key((string) $issue_code);
            if ($candidate !== '') {
                $normalized[] = $candidate;
            }
        }

        return array_values(array_unique($normalized));
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

    private function extract_error_code(array $result): string
    {
        if (isset($result['error']['code']) && is_string($result['error']['code'])) {
            return sanitize_key($result['error']['code']);
        }

        return '';
    }

    private function extract_http_status(array $result): int
    {
        if (isset($result['error']['http_status']) && is_numeric($result['error']['http_status'])) {
            return (int) $result['error']['http_status'];
        }

        if (isset($result['http_status']) && is_numeric($result['http_status'])) {
            return (int) $result['http_status'];
        }

        return 0;
    }

    private function is_endpoint_missing_error(array $result): bool
    {
        return $this->extract_http_status($result) === 404;
    }

    private function is_server_error_response(array $result): bool
    {
        $status_code = $this->extract_http_status($result);

        return $status_code >= 500;
    }

    private function api_request(string $method, string $path, array $body = [], array $query = [], bool $requires_auth = true, array $extra_headers = [], int $timeout_seconds = 3): array
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
            'timeout' => $timeout_seconds,
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
                    'http_status' => $status_code,
                ],
            ];
        }

        if (is_array($decoded_body) && array_key_exists('success', $decoded_body)) {
            return [
                'success' => (bool) $decoded_body['success'],
                'data' => isset($decoded_body['data']) && is_array($decoded_body['data']) ? $decoded_body['data'] : [],
                'error' => isset($decoded_body['error']) && is_array($decoded_body['error']) ? $decoded_body['error'] : [],
                'http_status' => $status_code,
            ];
        }

        return [
            'success' => true,
            'data' => is_array($decoded_body) ? $decoded_body : [],
            'http_status' => $status_code,
        ];
    }
}
