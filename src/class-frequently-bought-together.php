<?php
// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use Zamzamcoders\Easybundlefbt\Admin\Admin;
use Zamzamcoders\Easybundlefbt\Traitval\Traitval;
use Zamzamcoders\Easybundlefbt\Common\Common;
use Zamzamcoders\Easybundlefbt\Front\Front;

final class EASY_BUNDLE_FBT_Manager
{

    use Traitval;
    /**
     * Plugin Version
     *
     * @since 1.0.0
     * @var string The plugin version.
     */

    private static $instance;
    public $admin;
    public $front;
    public $common;
    public $hookwoo;

    private function __construct()
    {

        $this->define_constants();
        add_action('plugins_loaded', array($this, 'init_plugin'));

        add_action('wp_enqueue_scripts', array($this, 'efbtw_enqueue_frontend_assets'));
        add_action('admin_enqueue_scripts', array($this, 'efbtw_enqueue_admin_assets'));
        add_filter('plugin_action_links_' . EFBTW_PLUGIN_BASE,  array($this, 'efbtw_setting_page_link_func'));
    }

    /**
     * Define the required plugin constants
     *
     * @return void
     */
    public function define_constants()
    {
        // general constants
        define('EFBTW_PLUGIN_URL', plugins_url('/', EFBTW_PLUGIN_ROOT));
        define('EFBTW_PLUGIN_BASE', plugin_basename(EFBTW_PLUGIN_ROOT));
        define('EFBTW_CORE_ASSETS', EFBTW_PLUGIN_URL);
    }
    /**
     * Enqueues frontend CSS and JavaScript for the Frequently Bought Together plugin.
     *
     * This function hooks into 'wp_enqueue_scripts' to load the necessary frontend assets (CSS and JS)
     * for the plugin. It ensures the assets are loaded on the front end of the site.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function efbtw_enqueue_frontend_assets()
    {

        // Enqueue frontend CSS
        wp_enqueue_style('easy-front-css', EFBTW_CORE_ASSETS . 'assets/frontend/css/easy-front.css', array(), EFBTW_VERSION);
        // Enqueue frontend JS
        wp_enqueue_script('efbtw-frontend-script', EFBTW_CORE_ASSETS . 'assets/frontend/js/front-script.js', array('jquery'), EFBTW_VERSION, true);

        wp_localize_script('efbtw-frontend-script', 'efbtw_fbt_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'currency_symbol'   => get_woocommerce_currency_symbol(),
            'currency_position' => get_option('woocommerce_currency_pos'),
            'ajax_scroll_offset' => apply_filters('wofbt_ajax_scroll_offset', 100),
            'cart_price_nonce' => wp_create_nonce('efbtw_update_total_price_nonce')
        ]);

        wp_localize_script('efbtw-frontend-script', 'efbtw_add_to_cart_params', array(
            'ajax_url' => WC()->ajax_url(),
        ));

        wp_localize_script(
            'efbtw-frontend-script',
            'efbtw_rest_data',
            array(
                'nonce' => wp_create_nonce('wp_rest'),
                'rest_url'  => esc_url_raw(rest_url('wc/store/cart/add-item')),
            )
        );

        wp_enqueue_script('wc-blocks-interactivity');

        if (function_exists('wc_store_api_register_endpoint')) {
            wp_localize_script('woofbt-frontend-script', 'efbtw_store_api', [
                'nonce' => wp_create_nonce('efbtw_store_api')
            ]);
        }
    }

    /**
     * Enqueues admin CSS and JavaScript for the Frequently Bought Together plugin.
     *
     * This function hooks into 'admin_enqueue_scripts' to load the necessary admin assets (CSS and JS)
     * for the plugin. It ensures the assets are loaded on the admin side of the site.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function efbtw_enqueue_admin_assets()
    {

        if (!is_admin()) {
            return;
        }

        // CSS and JS files to enqueue
        $enqueue_styles = array(
            array(
                'handle' => 'efbtw-admin-css',
                'src' => EFBTW_CORE_ASSETS . 'assets/admin/css/back-admin.css',
                'deps' => array(),
                'ver' => EFBTW_VERSION,
                'media' => 'all'
            ),
            array(
                'handle' => 'efbtw-select2-min-css',
                'src' => EFBTW_CORE_ASSETS . 'assets/admin/css/select2.min.css',
                'deps' => array(),
                'ver' => EFBTW_VERSION,
                'media' => 'all'
            ),
        );

        $enqueue_scripts = array(
            array(
                'handle' => 'wp-color-picker',
                'src' => '',
                'deps' => array(),
                'ver' => '',
                'in_footer' => true
            ),
            array(
                'handle' => 'efbtw-select2-min-js',
                'src' => EFBTW_CORE_ASSETS . 'assets/admin/js/select2.min.js',
                'deps' => array('jquery'),
                'ver' => EFBTW_VERSION,
                'in_footer' => true
            ),
            array(
                'handle' => 'efbtw-select2-full-js',
                'src' => EFBTW_CORE_ASSETS . 'assets/admin/js/select2.full.js',
                'deps' => array('jquery'),
                'ver' => EFBTW_VERSION,
                'in_footer' => true
            ),
            array(
                'handle' => 'jquery-ui-sortable',
                'src' => '',
                'deps' => array(),
                'ver' => '',
                'in_footer' => true
            ),
            array(
                'handle' => 'efbtw-admin-js',
                'src' => EFBTW_CORE_ASSETS . 'assets/admin/js/back-admin.js',
                'deps' => array('jquery', 'jquery-ui-sortable'),
                'ver' => time(),
                'in_footer' => true
            ),
            array(
                'handle' => 'efbtw-admin-ajax-script',
                'src' => EFBTW_CORE_ASSETS . 'assets/admin/js/efbtw-admin-ajax-script.js',
                'deps' => array('jquery'),
                'ver' => time(),
                'in_footer' => true
            ),
        );

        // Enqueue styles
        foreach ($enqueue_styles as $style) {
            wp_enqueue_style($style['handle'], $style['src'], $style['deps'], $style['ver'], $style['media']);
        }

        // Enqueue scripts
        foreach ($enqueue_scripts as $script) {
            if (!empty($script['src'])) {
                wp_enqueue_script($script['handle'], $script['src'], $script['deps'], $script['ver'], $script['in_footer']);
            } else {
                wp_enqueue_script($script['handle']);
            }
        }

        // Localize script
        $data_to_pass = array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'homeUrl' => home_url('/'),
            "select_placeholder" => esc_html__('Select Product', 'easy-frequently-bought-together-for-woocommerce'),
            "exclude_placeholder" => esc_html__('Exclude Product', 'easy-frequently-bought-together-for-woocommerce'),
            "select_categories" => esc_html__('Select Categories', 'easy-frequently-bought-together-for-woocommerce'),
            'nonce' => wp_create_nonce('efbtw_save_size_chart')
        );

        wp_localize_script('efbtw-admin-js', 'efbtw_localize_obj', $data_to_pass);
        wp_localize_script('efbtw-admin-ajax-script', 'efbtw_localize_obj', $data_to_pass);
    }
    /**
     * Add a settings link to the plugin action links.
     *
     * This function adds a link to the settings page in the plugin's action links on the Plugins page.
     * It uses the 'plugin_action_links_' filter to append the settings link to the existing array of links.
     *
     * @since 1.0.0
     *
     * @param array $links An array of the plugin's action links.
     * @return array The modified array of action links with the settings page link appended.
     */

    function efbtw_setting_page_link_func($links)
    {
        $action_link = sprintf("<a href='%s'>%s</a>", admin_url('edit.php?post_type=easyfbt_bundle'), __('Settings', 'easy-frequently-bought-together-for-woocommerce'));
        array_push($links, $action_link);
        return $links;
    }

    /**
     * Check if a plugin is installed
     *
     * @since v1.0.0
     */
    public function is_plugin_installed($basename)
    {
        if (!function_exists('get_plugins')) {
            include_once ABSPATH . '/wp-admin/includes/plugin.php';
        }
        $installed_plugins = get_plugins();

        return isset($installed_plugins[$basename]);
    }

    /**
     * Initialize the plugin
     *
     * @return void
     */

    public function init_plugin()
    {
        if (is_null(self::$instance)) {
            self::$instance = self::getInstance();

            if (class_exists('WooCommerce')) {
                self::$instance->common  = Common::getInstance();
                self::$instance->front   = Front::getInstance();

                if (is_admin()) {
                    self::$instance->admin = Admin::getInstance();
                }
            }
        }
    }
}

/**
 * Initializes the main plugin
 *
 * This function returns the singleton instance of the EASY_BUNDLE_FBT_Manager  class,
 * ensuring that there is only one instance of the plugin running at any time.
 *
 * @return \EASY_BUNDLE_FBT_Manager  The singleton instance of the EASY_BUNDLE_FBT_Manager  class.
 */
function EFBTW_WPO()
{
    return EASY_BUNDLE_FBT_Manager::getInstance();
}

// Kick-off the plugin by calling the EFBTW_WPO function to initialize the plugin.
EFBTW_WPO();
