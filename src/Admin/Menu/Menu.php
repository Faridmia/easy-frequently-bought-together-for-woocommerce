<?php

namespace Zamzamcoders\Easybundlefbt\Admin\Menu;

use Zamzamcoders\Easybundlefbt\Traitval\Traitval;

/**
 * Class Menu
 * 
 * This class uses the Traitval trait to implement singleton functionality and
 * provides methods for adding custom submenus to the WordPress admin menu
 * within the Ultimate Product Options For WooCommerce plugin.
 */
class Menu
{
    use Traitval;

    public function __construct()
    {
        add_action('admin_menu', array($this, 'easy_bought_together_settings_page'));
        add_action('admin_menu', array($this, 'fbt_bundle_products_submenu'));
    }

    /**
     * Main Menu - Settings Page
     */
    function easy_bought_together_settings_page()
    {
        add_menu_page(
            __('Bought Together', 'easy-frequently-bought-together-for-woocommerce'),
            __('Bought Together', 'easy-frequently-bought-together-for-woocommerce'),
            'manage_options',
            'easy-bought-together',
            array($this, 'easy_bought_together_settings_page_html'),
            EFBTW_PLUGIN_URL . 'assets/admin/images/bundle-icon.png',
            20
        );
    }

    /**
     * Submenus under Bought Together
     */
    function fbt_bundle_products_submenu()
    {

        add_submenu_page(
            'easy-bought-together',
            __('All Bundles', 'easy-frequently-bought-together-for-woocommerce'),
            __('All Bundles', 'easy-frequently-bought-together-for-woocommerce'),
            'manage_options',
            'edit.php?post_type=easyfbt_bundle'
        );


        add_submenu_page(
            'easy-bought-together',
            __('Add New Bundle', 'easy-frequently-bought-together-for-woocommerce'),
            __('Add New Bundle', 'easy-frequently-bought-together-for-woocommerce'),
            'manage_options',
            'post-new.php?post_type=easyfbt_bundle'
        );
    }

    /**
     * Settings Page HTML
     */
    function easy_bought_together_settings_page_html()
    {
?>
        <div class="wrap">
            <div id="easy-bought-together-settings-app"></div>
        </div>
<?php
    }
}
