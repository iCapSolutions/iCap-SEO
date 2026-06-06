<?php
if (!defined('ABSPATH')) {
    exit;
}

$tabs = [
    'home' => __('Home', 'icap-seo'),
    'setup-wizard' => __('Setup Wizard', 'icap-seo'),
    'site-health' => __('Site Health', 'icap-seo'),
];
?>
<div class="wrap icap-seo-wrap">
    <h1 class="icap-seo-header">
        <img src="<?php echo esc_url(ICAP_SEO_PLUGIN_URL . 'assets/images/icap-seo-logo.svg'); ?>" alt="<?php esc_attr_e('iCap SEO', 'icap-seo'); ?>" class="icap-seo-logo">
        <?php esc_html_e('iCap SEO', 'icap-seo'); ?>
    </h1>
    <p><?php esc_html_e('SEO intelligence for WordPress sites by iCapSolutions.', 'icap-seo'); ?></p>

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
                <li><?php esc_html_e('Connect this site to the iCap SEO cloud service.', 'icap-seo'); ?></li>
                <li><?php esc_html_e('Run the first baseline SEO analysis.', 'icap-seo'); ?></li>
                <li><?php esc_html_e('Review prioritized recommendations.', 'icap-seo'); ?></li>
            </ol>
            <p><?php esc_html_e('Wizard actions will be enabled in upcoming releases.', 'icap-seo'); ?></p>
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
