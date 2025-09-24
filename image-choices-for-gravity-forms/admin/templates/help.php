<?php

$faqs = [
    [
        'question' => __('Is image picker pro single product field supported?', 'image-choices-for-gravity-forms'),
        'answer' => __('Yes, Image Picker Pro supports all types of product fields — including single product fields, product options (such as select dropdown), and shipping fields.', 'image-choices-for-gravity-forms'),
    ],
    [
        'question' => __('Is image picker pro send image in the notification?', 'image-choices-for-gravity-forms'),
        'answer' => __('Yes, Image Picker Pro can include images in notifications. It provides six options for configuring how data is sent in the notification, based on requirements.', 'image-choices-for-gravity-forms'),
    ],
    [
        'question' => __('Is image picker pro dropdown select field supported?', 'image-choices-for-gravity-forms'),
        'answer' => __('Yes, You can use images in dropdown select field with image picker pro.', 'image-choices-for-gravity-forms'),
    ]
];

?>


<div id="help" class="help_introduction tab_item">
    <div class="content_heading">
        <h2><?php esc_html_e('Frequently Asked Questions', 'image-choices-for-gravity-forms'); ?></h2>
    </div>

    <section class="section_faq">
        <?php foreach ($faqs as $key => $faq) : ?>
            <div class="faq_item">
                <input type="checkbox" name="accordion-1" id="faq<?php echo esc_attr($key); ?>">
                <label for="faq<?php echo esc_attr($key); ?>" class="faq__header">
                    <?php echo esc_html($faq['question']); ?>
                    <i class="dashicons dashicons-arrow-down-alt2"></i>
                </label>
                <div class="faq__body">
                    <p><?php echo esc_html($faq['answer']); ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </section>

    <div class="content_heading">
        <h2><?php esc_html_e('Need Help?', 'image-choices-for-gravity-forms'); ?></h2>
        <p><?php esc_html_e('If you have any questions or need help, please feel free to contact us.', 'image-choices-for-gravity-forms'); ?></p>
    </div>

    <div class="help_docs">
        <section class="help_box section_half">
            <div class="help_box__img">
                <img src="<?php echo esc_url(GFIMP_ASSET_URL . 'admin/assets/images/docs.svg'); ?>">
            </div>
            <div class="help_box__content">
                <h3><?php esc_html_e('Documentation', 'image-choices-for-gravity-forms'); ?></h3>
                <p><?php esc_html_e('Check out our detailed online documentation and video tutorials to find out more about what you can do.', 'image-choices-for-gravity-forms'); ?></p>
                <a target="_blank" href="https://pluginscafe.com/docs/image-picker-for-gravity-forms-pro/" class="pcafe_btn"><?php esc_html_e('Documentation', 'image-choices-for-gravity-forms'); ?></a>
            </div>
        </section>
        <section class="help_box section_half">
            <div class="help_box__img">
                <img src="<?php echo esc_url(GFIMP_ASSET_URL . 'admin/assets/images/service247.svg'); ?>">
            </div>
            <div class="help_box__content">
                <h3><?php esc_html_e('Support', 'image-choices-for-gravity-forms'); ?></h3>
                <p><?php esc_html_e('We have dedicated support team to provide you fast, friendly & top-notch customer support.', 'image-choices-for-gravity-forms'); ?></p>
                <a target="_blank" href="https://wordpress.org/support/plugin/image-choices-for-gravity-forms/" class="pcafe_btn"><?php esc_html_e('Get Support', 'image-choices-for-gravity-forms'); ?></a>
            </div>
        </section>
    </div>
</div>