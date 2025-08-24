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

    /**
     * Constructor
     * 
     * The constructor adds an action to the 'admin_menu' hook to add custom submenus
     * to the WordPress admin menu.
     */
    public function __construct()
    {
        add_action('admin_menu', array($this, 'fbt_bundle_products_submenu'));
    }

    /**
     * Add a Product Extra Data submenu under the WooCommerce menu.
     *
     * This function adds a submenu page for 'Product Extra Data' under the main WooCommerce menu in the WordPress admin dashboard.
     *
     * @since 1.0.0
     *
     * @return void
     */
    function fbt_bundle_products_submenu()
    {
        add_submenu_page(
            'woocommerce',
            __('Freequently Bought Together', 'easy-frequently-bought-together-for-woocommerce'),
            __('Freequently Bought Together', 'easy-frequently-bought-together-for-woocommerce'),
            'manage_options',
            'edit.php?post_type=easyfbt_bundle'
        );
    }
}
