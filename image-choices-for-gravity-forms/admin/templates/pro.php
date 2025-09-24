<?php

$features = [
    [
        'feature'   => __('Supported Field Type  -  Radio, Checkbox', 'image-choices-for-gravity-forms'),
        'pro'      => 0,
    ],
    [
        'feature'   => __('Supported Field Type - Single product and options', 'image-choices-for-gravity-forms'),
        'pro'       => true
    ],
    [
        'feature'   => __('Supported Field Type - Dropdown field', 'image-choices-for-gravity-forms'),
        'pro'       => true
    ],
    [
        'feature'   => __('Supported Field Type - Survey, Polls, shipping, custom post field', 'image-choices-for-gravity-forms'),
        'pro'       => true
    ],
    [
        'feature'   => __('Responsive options', 'image-choices-for-gravity-forms'),
        'pro'       => 0
    ],
    [
        'feature'   => __('Global settings', 'image-choices-for-gravity-forms'),
        'pro'       => 0
    ],
    [
        'feature'   => __('Form settings', 'image-choices-for-gravity-forms'),
        'pro'       => 0
    ],
    [
        'feature'   => __('Color settings', 'image-choices-for-gravity-forms'),
        'pro'       => 0
    ],
    [
        'feature'   => __('Image Options', 'image-choices-for-gravity-forms'),
        'pro'       => true
    ],
    [
        'feature'   => __('Additional theme for product field', 'image-choices-for-gravity-forms'),
        'pro'       => true
    ],
    [
        'feature'   => __('Show images in Entry / Notification', 'image-choices-for-gravity-forms'),
        'pro'       => true
    ],
    [
        'feature'   => __('6 Ready Themes', 'image-choices-for-gravity-forms'),
        'pro'       => true
    ]
];

?>
<div id="pro" class="pro_introduction tab_item">

    <div class="content_heading">
        <h2><?php esc_html_e('Unlock the full power of Image Picker For Gravity Forms', 'image-choices-for-gravity-forms'); ?></h2>
        <p><?php esc_html_e('The amazing PRO features will make your Image Picker even more efficient.', 'image-choices-for-gravity-forms'); ?></p>
    </div>

    <div class="content_heading free_vs_pro">
        <h2>
            <span><?php esc_html_e('Free', 'image-choices-for-gravity-forms'); ?></span>
            <?php esc_html_e('vs', 'image-choices-for-gravity-forms'); ?>
            <span><?php esc_html_e('Pro', 'image-choices-for-gravity-forms'); ?></span>
        </h2>
    </div>

    <div class="features_list">
        <div class="list_header">
            <div class="feature_title"><?php esc_html_e('Feature List', 'image-choices-for-gravity-forms'); ?></div>
            <div class="feature_free"><?php esc_html_e('Free', 'image-choices-for-gravity-forms'); ?></div>
            <div class="feature_pro"><?php esc_html_e('Pro', 'image-choices-for-gravity-forms'); ?></div>
        </div>
        <?php foreach ($features as $feature) : ?>
            <div class="feature">
                <div class="feature_title"><?php echo esc_html($feature['feature']); ?></div>
                <div class="feature_free">
                    <?php if ($feature['pro']) : ?>
                        <i class="dashicons dashicons-no-alt"></i>
                    <?php else : ?>
                        <i class="dashicons dashicons-saved"></i>
                    <?php endif; ?>
                </div>
                <div class="feature_pro">
                    <i class="dashicons dashicons-saved"></i>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if (! ipfgf_fs()->is_plan('pro', true)) : ?>
        <div class="pro-cta background_pro">
            <div class="cta-content">
                <h2><?php esc_html_e('Don\'t waste time, get the PRO version now!', 'image-choices-for-gravity-forms'); ?></h2>
                <p><?php esc_html_e('Upgrade to the PRO version of the plugin and unlock all the amazing Image Picker features for
                your website.', 'image-choices-for-gravity-forms'); ?></p>
            </div>
            <div class="cta-btn">
                <a href="<?php echo esc_url(ipfgf_fs()->get_upgrade_url()); ?>" class="pcafe_btn"><?php esc_html_e('Upgrade Now', 'image-choices-for-gravity-forms'); ?></a>
            </div>
        </div>
    <?php endif; ?>

    <div class="pro-cta background_free">
        <div class="cta-content">
            <h2><?php esc_html_e('Want to try live demo, before purchase?', 'image-choices-for-gravity-forms'); ?></h2>
            <p><?php esc_html_e('Try our instant ready-made demo with form submission! If you use an active email address, you\'ll also receive a notification.', 'image-choices-for-gravity-forms'); ?></p>
        </div>
        <div class="cta-btn">
            <a href="https://pluginscafe.com/plugin/image-picker-for-gravity-forms-pro/" target="_blank" class="pcafe_btn"><?php esc_html_e('Try Live Demo', 'image-choices-for-gravity-forms'); ?></a>
        </div>
    </div>
</div>