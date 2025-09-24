<?php
if (!defined('ABSPATH')) {
    exit;
}

class GFIMP_Dashboard {
    public function __construct() {
        add_filter('admin_footer_text', [$this, 'admin_footer'], 1, 2);
        add_action('admin_menu', [$this, 'add_menu_under_options']);
        add_action('admin_enqueue_scripts', [$this, 'admin_scripts']);

        add_action('admin_notices', [$this, 'upgrade_notice']);
        add_action('admin_notices', [$this, 'offer_admin_notice']);
        add_action('wp_ajax_gfimp_offer_notice_dismiss', [$this, 'gfimp_offer_notice_dismiss']);
        add_action('wp_ajax_gfimp_upgrade_notice_dismiss', [$this, 'gfimp_upgrade_notice_dismiss']);
    }

    public function admin_scripts() {
        $current_screen = get_current_screen();
        if (strpos($current_screen->base, 'image-picker-for-gravity-forms-pro') === false) {
            return;
        }

        wp_enqueue_style('gfimp_dashboard_style', GFIMP_ASSET_URL . 'admin/assets/css/gfimp_dashboard_style.css', array(), GFIMP_ADDON_VERSION);
        wp_enqueue_script('gfimp_dashboard_script', GFIMP_ASSET_URL . 'admin/assets/js/gfimp_dashboard_script.js', array('jquery'), GFIMP_ADDON_VERSION, true);
    }
    public function add_menu_under_options() {
        add_submenu_page(
            'options-general.php',
            'Image Picker For Gravity Forms',
            'GF Image Picker',
            'administrator',
            'image-picker-for-gravity-forms-pro',
            [$this, 'gfimp_admin_page']
        );
    }

    public function gfimp_admin_page() {
        echo '<div class="pcafe_imp_dashboard">';
        include_once __DIR__ . '/templates/header.php';

        echo '<div id="pcafe_tab_box" class="pcafe_container">';
        include_once __DIR__ . '/templates/introduction.php';
        include_once __DIR__ . '/templates/usage.php';
        include_once __DIR__ . '/templates/help.php';
        include_once __DIR__ . '/templates/pro.php';
        include_once __DIR__ . '/templates/other-plugins.php';
        echo '</div>';
        echo '</div>';
    }


    public function admin_footer($text) {
        global $current_screen;

        if (! empty($current_screen->id) && strpos($current_screen->id, 'image-picker-for-gravity-forms-pro') !== false) {
            $url  = 'https://wordpress.org/support/plugin/image-choices-for-gravity-forms/reviews/?filter=5#new-post';
            $text = sprintf(
                wp_kses(
                    /* translators: $1$s - WPForms plugin name; $2$s - WP.org review link; $3$s - WP.org review link. */
                    __('Thank you for using %1$s. Please rate us <a href="%2$s" target="_blank" rel="noopener noreferrer">&#9733;&#9733;&#9733;&#9733;&#9733;</a> on <a href="%3$s" target="_blank" rel="noopener">WordPress.org</a> to boost our motivation.', 'image-choices-for-gravity-forms'),
                    array(
                        'a' => array(
                            'href'   => array(),
                            'target' => array(),
                            'rel'    => array(),
                        ),
                    )
                ),
                '<strong>Image Picker For Gravity Forms</strong>',
                $url,
                $url
            );
        }

        return $text;
    }

    public function offer_admin_notice() {
        $nonce = wp_create_nonce('gfimp_offer_dismiss_nonce');
        $ajax_url = admin_url('admin-ajax.php');

        $api_offer_notice = 'gfimp_offer_notice';
        $notice_array = get_transient($api_offer_notice);
        $is_offer_checked = get_transient('gfimp_offer_notice_arrived');

        $allowed_tags = [
            'strong' => ['style' => []],
            'code' => [],
            'a'      => [
                'href'   => [],
                'title'  => [],
                'target' => [],
                'rel'    => [],
            ],
            'span'   => ['style' => []],
        ];


        if ($notice_array === false) {
            // Fetch from remote only if cache expired
            $endpoint  = 'https://api.pluginscafe.com/wp-json/pcafe/v1/offers?id=4';
            $response  = wp_remote_get($endpoint, array('timeout' => 10));

            if (!is_wp_error($response) && $response['response']['code'] === 200) {
                $notice_array = json_decode($response['body'], true);

                // Save in cache for 3 hours (change as needed)
                set_transient($api_offer_notice, $notice_array, 3 * HOUR_IN_SECONDS);
            }
        }

        if (!empty($notice_array) && isset($notice_array['notice']) && $notice_array['live'] === true && $is_offer_checked === false) {
            $notice_type = $notice_array['notice']['notice_type'] ? $notice_array['notice']['notice_type'] : 'info';
            $notice_class = "notice-{$notice_type}";
?>
            <div class="notice <?php echo esc_attr($notice_class); ?> is-dismissible gfimp_offer_notice" data-ajax-url="<?php echo esc_url($ajax_url); ?>"
                data-nonce="<?php echo esc_attr($nonce); ?>">
                <div class="gfimp_notice_container" style="display: flex;align-items:center;padding:10px 0;justify-content:space-between;gap:15px;">
                    <div class="gfimp_notice_content" style="display: flex;align-items:center;gap:15px;">
                        <?php if ($notice_array['notice']['image']) : ?>
                            <div class="gfimp_notice_img">
                                <img width="90px" src="<?php echo esc_url($notice_array['notice']['image']); ?>" />
                            </div>
                        <?php endif; ?>
                        <div class="gfimp_notice_text">
                            <h3 style="margin:0 0 6px;"><?php echo esc_html($notice_array['notice']['title']); ?></h3>
                            <p><?php echo wp_kses($notice_array['notice']['content'], $allowed_tags); ?></p>
                            <div class="gfimp_notice_buttons" style="display: flex; gap:15px;align-items:center;">
                                <?php if ($notice_array['notice']['show_demo_url'] === true) : ?>
                                    <a href="https://pluginscafe.com/plugin/image-picker-for-gravity-forms-pro/" class="button-primary" target="__blank"><?php esc_html_e('Check Demo', 'image-choices-for-gravity-forms'); ?></a>
                                <?php endif; ?>
                                <a href="#" class="dismis_api__notice">
                                    <?php esc_html_e('Dismiss', 'image-choices-for-gravity-forms'); ?>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php if ($notice_array['notice']['upgrade_btn'] === true) : ?>
                        <div class="gfimp_upgrade_btn">
                            <a href="<?php echo esc_url(ipfgf_fs()->get_upgrade_url()); ?>" style="text-decoration: none;font-size: 15px;background: #7BBD02;color: #fff;display: inline-block;padding: 10px 20px;border-radius: 3px;">
                                <?php echo esc_html($notice_array['notice']['upgrade_btn_text']); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <script>
                jQuery(document).ready(function($) {
                    $(document).on('click', '.dismis_api__notice, .gfimp_offer_notice .notice-dismiss', function(event) {
                        event.preventDefault();
                        const $notice = jQuery(this).closest('.gfimp_offer_notice');
                        const ajaxUrl = $notice.data('ajax-url');
                        const nonce = $notice.data('nonce');

                        $.ajax({
                            url: ajaxUrl,
                            type: 'post',
                            data: {
                                action: 'gfimp_offer_notice_dismiss',
                                nonce: nonce
                            },
                            success: function(response) {
                                $('.gfimp_offer_notice').remove();
                            },
                            error: function(data) {}
                        });
                    });
                });
            </script>
        <?php

        }
    }

    public function gfimp_offer_notice_dismiss() {
        check_ajax_referer('gfimp_offer_dismiss_nonce', 'nonce');
        set_transient('gfimp_offer_notice_arrived', true, 3 * DAY_IN_SECONDS);
        wp_send_json_success();
    }

    public function upgrade_notice() {
        $nonce = wp_create_nonce('gfimp_upgrade_dismiss_nonce');
        $ajax_url = admin_url('admin-ajax.php');
        $show = false;

        if (ipfgf_fs()->is_not_paying()) {
            $show = true;
        }

        if (! $this->is_active_gravityforms()) { ?>
            <div id="gfimp_notice-error" class="gfimp_notice-error notice notice-error">
                <div class="notice-container" style="padding:10px">
                    <span> <?php esc_html_e("Image picker needs to active gravity forms.", "image-choices-for-gravity-forms"); ?></span>
                </div>
            </div>
            <?php
        } else {
            if ($show && false == get_transient('gfimp_upgrade_notice') && current_user_can('install_plugins')) {
            ?>
                <div id="gfimp_upgrade_notice" class="gfimp_upgrade_notice notice notice-info is-dismissible" data-ajax-url="<?php echo esc_url($ajax_url); ?>"
                    data-nonce="<?php echo esc_attr($nonce); ?>">
                    <div class="notice_container">
                        <div class="notice_wrap">
                            <div class="gfimp_img">
                                <img width="100px" src="<?php echo esc_url(GFIMP_ASSET_URL . 'admin/assets/images/gf-image-picker.svg'); ?>" class="gfimp_logo">
                            </div>
                            <div class="notice-content">
                                <div class="notice-heading">
                                    <?php esc_html_e("Hi there, Thanks for using Image picker for Gravity Forms", "image-choices-for-gravity-forms"); ?>
                                </div>
                                <?php esc_html_e("Did you know our PRO version includes the ability to use single product, dropdown fields and more features? Check it out!", "image-choices-for-gravity-forms"); ?>
                                <div class="gfimp_review-notice-container">
                                    <a href="https://demo.pluginscafe.com/image-picker-for-gravity-forms-pro/" class="gfimp_upgrade_notice_dismiss button-primary" target="_blank">
                                        <?php esc_html_e("See The Demo", "image-choices-for-gravity-forms"); ?>
                                    </a>
                                    <span class="dashicons dashicons-smiley"></span>
                                    <a href="#" class="gfimp_upgrade_notice_close">
                                        <?php esc_html_e("Dismiss", "image-choices-for-gravity-forms"); ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="gfimp_upgrade_btn">
                            <a href="<?php echo esc_url(ipfgf_fs()->get_upgrade_url()); ?>">
                                <?php esc_html_e('Upgrade Now!', 'image-choices-for-gravity-forms'); ?>
                            </a>
                        </div>
                    </div>
                    <style>
                        .notice_container {
                            display: flex;
                            align-items: center;
                            padding: 10px 0;
                            gap: 15px;
                            justify-content: space-between;
                        }

                        img.gfimp_logo {
                            max-width: 90px;
                        }

                        .notice-heading {
                            font-size: 16px;
                            font-weight: 500;
                            margin-bottom: 5px;
                        }

                        .gfimp_review-notice-container {
                            margin-top: 11px;
                            display: flex;
                            align-items: center;
                        }

                        .gfimp_notice-close {
                            padding-left: 5px;
                        }

                        span.dashicons.dashicons-smiley {
                            padding-left: 15px;
                        }

                        .notice_wrap {
                            display: flex;
                            align-items: center;
                            gap: 15px;
                        }

                        .gfimp_upgrade_btn a {
                            text-decoration: none;
                            font-size: 15px;
                            background: #7BBD02;
                            color: #fff;
                            display: inline-block;
                            padding: 10px 20px;
                            border-radius: 3px;
                            transition: 0.3s;
                        }

                        .gfimp_upgrade_btn a:hover {
                            background: #69a103;
                        }
                    </style>
                    <script>
                        jQuery(document).ready(function($) {
                            $(document).on('click', '.gfimp_upgrade_notice_close, .gfimp_upgrade_notice .notice-dismiss', function(event) {
                                event.preventDefault();
                                const $notice = jQuery(this).closest('.gfimp_upgrade_notice');
                                const ajaxUrl = $notice.data('ajax-url');
                                const nonce = $notice.data('nonce');

                                $.ajax({
                                    url: ajaxUrl,
                                    type: 'post',
                                    data: {
                                        action: 'gfimp_upgrade_notice_dismiss',
                                        nonce: nonce
                                    },
                                    success: function(response) {
                                        $('.gfimp_upgrade_notice').remove();
                                    },
                                    error: function(data) {}
                                });
                            });
                        });
                    </script>
                </div>
<?php
            }
        }
    }

    public function gfimp_upgrade_notice_dismiss() {
        check_ajax_referer('gfimp_upgrade_dismiss_nonce', 'nonce');
        set_transient('gfimp_upgrade_notice', true, 15 * DAY_IN_SECONDS);
        wp_send_json_success();
    }

    public function is_active_gravityforms() {
        if (!method_exists('GFForms', 'include_payment_addon_framework')) {
            return false;
        }
        return true;
    }
}


new GFIMP_Dashboard;
