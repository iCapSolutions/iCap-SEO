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
}
