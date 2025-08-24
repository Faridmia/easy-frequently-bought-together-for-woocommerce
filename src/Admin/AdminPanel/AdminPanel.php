<?php

namespace Zamzamcoders\Easybundlefbt\Admin\AdminPanel;

use Zamzamcoders\Easybundlefbt\Admin\Ajax\FbtAjax;

/**
 * Class Admin
 *
 * This class uses the Traitval trait to implement singleton functionality and
 * provides methods for initializing the admin menu and other admin-related features
 * within the Frequently Bought Together plugin.
 */
class AdminPanel
{

    protected $efbtw_ajax;

    /**
     * Initialize the class
     *
     * This method overrides the initialize method from the Traitval trait.
     * It sets up the necessary classes and features for the admin area.
     */

    public function __construct()
    {

        $this->efbtw_ajax = new FbtAjax();
        $this->initialize_hooks();
    }

    protected function initialize_hooks()
    {

        // fbt get products

        add_action('wp_ajax_efbtw_get_products', array($this->efbtw_ajax, 'efbtw_get_products_callback'));
        add_action('wp_ajax_nopriv_efbtw_get_products', array($this->efbtw_ajax, 'efbtw_get_products_callback'));

        add_action('wp_ajax_tab_bundle_get_products_callback', array($this->efbtw_ajax, 'efbtw_tab_bundle_get_products_callback'));
        add_action('wp_ajax_nopriv_tab_bundle_get_products_callback', array($this->efbtw_ajax, 'efbtw_tab_bundle_get_products_callback'));
    }
}
