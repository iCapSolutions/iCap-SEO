<?php

if (!defined('ABSPATH')) {
    exit;
}

class ICap_SEO_Service_Client
{
    public function get_site_score_snapshot(): array
    {
        return [
            'score' => null,
            'last_scan' => null,
            'status' => 'Not connected',
        ];
    }

    public function get_recommendation_preview(): array
    {
        return [
            'items' => [],
            'source' => 'placeholder',
        ];
    }

    public function get_content_score_for_post(int $post_id): array
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

    public function get_content_scores_overview(): array
    {
        $posts = get_posts([
            'post_type' => ['post', 'page'],
            'post_status' => ['publish', 'draft', 'pending', 'future', 'private'],
            'posts_per_page' => 100,
            'orderby' => 'modified',
            'order' => 'DESC',
        ]);

        $rows = [];

        foreach ($posts as $post) {
            $score_data = $this->get_content_score_for_post((int) $post->ID);
            $rows[] = [
                'id' => (int) $post->ID,
                'title' => get_the_title($post),
                'type' => (string) $post->post_type,
                'status' => (string) $post->post_status,
                'edit_link' => get_edit_post_link((int) $post->ID, ''),
                'icap_score' => $score_data['icap_score'],
                'rank_math_score' => $score_data['rank_math_score'],
                'rank_math_delta' => $score_data['rank_math_delta'],
            ];
        }

        return $rows;
    }
}
