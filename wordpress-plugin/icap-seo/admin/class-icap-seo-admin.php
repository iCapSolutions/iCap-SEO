<?php

if (!defined('ABSPATH')) {
    exit;
}

class ICap_SEO_Admin
{
    private ICap_SEO_Service_Client $service_client;
    private const SCORE_COLUMN_KEY = 'icap_seo_score';
    private const DELTA_COLUMN_KEY = 'icap_seo_delta';

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
        $score_snapshot = $this->service_client->get_site_score_snapshot();
        $recommendation_preview = $this->service_client->get_recommendation_preview();
        $content_scores = $this->service_client->get_content_scores_overview();

        include ICAP_SEO_PLUGIN_DIR . 'admin/views/dashboard.php';
    }
}
