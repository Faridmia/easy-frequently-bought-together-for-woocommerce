<?php

namespace Zamzamcoders\Easybundlefbt\Admin\Settings;

use Zamzamcoders\Easybundlefbt\Traitval\Traitval;

/**
 * Class class Enqueue
 * 
 * This class uses the Traitval trait to implement singleton functionality and
 * provides methods for adding custom submenus to the WordPress admin Enqueue
 * within the Ultimate Product Options For WooCommerce plugin.
 */
class Enqueue
{
    use Traitval;

    public function __construct()
    {
        add_action('admin_enqueue_scripts', array($this, 'easy_settings_admin_enqueue'));
    }

    function easy_settings_admin_enqueue($screen)
    {
        // if ('settings_page_easy-bought-together' !== $screen) {
        //     return;
        // }



        $asset_file = EFBTW_BUILD_PATH . 'admin/index.asset.php';

        if (! file_exists($asset_file)) {
            return;
        }

        $asset = include $asset_file;

        $deps = array_filter(
            $asset['dependencies'],
            function ($dep) {
                return $dep !== 'wp-blocks' && $dep !== 'wp-editor';
            }
        );

        wp_enqueue_style(
            'efbtw-admin-style-settingsnew',
            EFBTW_BUILD_URL . 'admin/index.css',
            null,
            $asset['version']
        );

        wp_enqueue_script(
            'efbtw-admin-script-settings',
            EFBTW_BUILD_URL . 'admin/index.js',
            $deps,
            $asset['version'],
            true
        );

        wp_enqueue_style('wp-components');

        wp_localize_script('efbtw-admin-script-settings', 'efbtwSettings_object', [
            'rest_url' => esc_url_raw(rest_url()),
            'nonce'    => wp_create_nonce('wp_rest'),
        ]);
    }
}
