<?php
/*
Plugin Name: Image Picker For Gravity Forms
Plugin Url: https://pluginscafe.com/plugin/image-picker-for-gravity-forms-pro/
Version: 1.1.3
Description: A simple and nice plugin to add images easily on gravity forms radio and checkbox field.
Author: PluginsCafe
Author URI: https://pluginscafe.com
License: GPLv2 or later
Text Domain: image-choices-for-gravity-forms
*/
if (!defined('ABSPATH')) {
    exit;
}

if (function_exists('ipfgf_fs')) {
    ipfgf_fs()->set_basename(false, __FILE__);
} else {
    if (! function_exists('ipfgf_fs')) {
        // Create a helper function for easy SDK access.
        function ipfgf_fs() {
            global $ipfgf_fs;

            if (! isset($ipfgf_fs)) {
                // Include Freemius SDK.
                require_once dirname(__FILE__) . '/vendor/freemius/start.php';
                $ipfgf_fs = fs_dynamic_init(array(
                    'id'                  => '20542',
                    'slug'                => 'image-choices-for-gravity-forms',
                    'premium_slug'        => 'image-picker-for-gravity-forms-pro',
                    'type'                => 'plugin',
                    'public_key'          => 'pk_9650f7415ef562f7e8b524b9082a4',
                    'is_premium'          => false,
                    'premium_suffix'      => 'Pro',
                    'has_addons'          => false,
                    'has_paid_plans'      => true,
                    'menu'                => array(
                        'slug'           => 'image-picker-for-gravity-forms-pro',
                        'support'        => false,
                        'contact'       => false,
                        'account'       => false,
                        'parent'         => array(
                            'slug' => 'options-general.php',
                        ),
                    ),
                    'is_live'        => true,
                ));
            }

            return $ipfgf_fs;
        }

        // Init Freemius.
        ipfgf_fs();
        // Signal that SDK was initiated.
        do_action('ipfgf_fs_loaded');
    }
}

if (is_admin()) {
    require_once 'admin/class-dashboard.php';
}

define('GFIMP_ADDON_VERSION', '1.1.3');
define('GFIMP_ASSET_URL', plugin_dir_url(__FILE__));

add_action('gform_loaded', array('GFIMP_AddOn_Bootstrap', 'load'), 5);
class GFIMP_AddOn_Bootstrap {
    public static function load() {
        if (!method_exists('GFForms', 'include_addon_framework')) {
            return;
        }

        require_once 'class-gfImgChoice.php';
        GFAddOn::register('GFImgChoiceAddon');
    }
}
function GF_Image_Picker_Field() {
    return GFImgChoiceAddon::get_instance();
}
