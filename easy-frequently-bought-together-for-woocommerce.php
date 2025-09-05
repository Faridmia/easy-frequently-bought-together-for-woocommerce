<?php

/**
 * Plugin Name:       Easy Frequently Bought Together for WooCommerce
 * Plugin URI:        https://github.com/faridmia/easy-frequently-bought-together-for-woocommerce
 * Description:       Enhance WooCommerce by offering smart product bundle suggestions with discounts to increase sales.
 * Version:           1.0.2
 * Requires at least: 6.4
 * Requires PHP: 7.4
 * Author: zamzamcoders
 * Author URI: https://profiles.wordpress.org/zamzamcoders/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires Plugins: woocommerce
 * Text Domain: easy-frequently-bought-together-for-woocommerce
 */


// If this file is called directly, abort.

if (! defined('ABSPATH') || ! function_exists('add_action')) {
    echo 'You are not allowed to access this file directly.';
    exit;
}

if (!defined('WPINC')) {
    die;
}


define('EFBTW_VERSION', '1.0.2');
define('EFBTW_PLUGIN_ROOT', __FILE__);
define('EFBTW_PLUGIN_PATH', plugin_dir_path(EFBTW_PLUGIN_ROOT));
define('EFBTW_BUILD_PATH', EFBTW_PLUGIN_PATH . 'build/');
define('EFBTW_PLUGIN_URL', plugin_dir_url(EFBTW_PLUGIN_ROOT));
define('EFBTW_BUILD_URL', EFBTW_PLUGIN_URL . 'build/');
define('EFBTW_PLUGIN_TITLE', 'Easy Frequently Bought Together for WooCommerce');

add_action('init', 'efbtw_load_textdomain');
if (!version_compare(PHP_VERSION, '7.4', '>=')) {
    add_action('admin_notices', 'efbtw_fail_php_version');
} elseif (!version_compare(get_bloginfo('version'), '6.4', '>=')) {
    add_action('admin_notices', 'efbtw_fail_wp_version');
}

require_once __DIR__ . '/vendor/autoload.php';

/**
 * Display an admin notice if the PHP version is not sufficient for the plugin.
 *
 * This function checks the current PHP version and displays a warning notice in the WordPress admin
 * if the current PHP version is less than the required version.
 *
 * @since 1.0.0
 */
function efbtw_fail_php_version()
{

    $message = sprintf(
        // Translators: %1$s is the plugin title, %2$s is the required PHP version.
        __('%1$s requires PHP version %2$s+, plugin is currently NOT RUNNING.', 'easy-frequently-bought-together-for-woocommerce'),
        '<strong>' . EFBTW_PLUGIN_TITLE . '</strong>',
        '7.4'
    );

    printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', wp_kses_post($message));
}
/**
 * Display an admin notice if the WordPress version is not sufficient for the plugin.
 *
 * This function checks the current WordPress version and displays a warning notice in the WordPress admin
 * if the current version is less than the required version.
 *
 * @since 1.0.0
 */
function efbtw_fail_wp_version()
{

    $message      = sprintf(
        // Translators: %1$s is the plugin title, %2$s is the WordPress version.
        esc_html__('To function, %1$s needs WordPress version %2$s or higher. The plugin is currently NOT RUNNING due to an outdated version.', 'easy-frequently-bought-together-for-woocommerce'),
        EFBTW_PLUGIN_TITLE,
        '6.4'
    );
    $error_message = sprintf('<div class="error">%s</div>', wpautop($message));
    echo wp_kses_post($error_message);
}

/**
 * efbtw_load_easy-frequently-bought-together-for-woocommerce loads easy-frequently-bought-together-for-woocommerce easy-frequently-bought-together-for-woocommerce.
 *
 * Load gettext translate for the easy-frequently-bought-together-for-woocommerce text domain.
 *
 * @since 1.0.0
 *
 * @return void
 */
function efbtw_load_textdomain()
{
    // woocommerce  plugin dependency
    if (!function_exists('WC')) {
        add_action('admin_notices', 'efbtw_admin_notices');
    }
}

/**
 * The code that runs during plugin activation.
 * This action is documented in src/class-woobundle-fbt-activator.php
 */
function efbtw_activate_func()
{
    require_once EFBTW_PLUGIN_PATH . 'src/class-woobundle-fbt-activator.php';
    EFBTW_fbt_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in src/class-woobundle-fbt-deactivator.php
 */
function efbtw__deactivate_func()
{
    require_once EFBTW_PLUGIN_PATH . 'src/class-woobundle-fbt-deactivator.php';
    EFBTW_Woo_Deactivator::deactivate();
}

register_activation_hook(EFBTW_PLUGIN_ROOT, 'efbtw_activate_func');
register_deactivation_hook(EFBTW_PLUGIN_ROOT, 'efbtw__deactivate_func');


/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */

function Efbtw_run_func()
{
    require_once __DIR__ . '/src/class-frequently-bought-together.php';
}

function efbtw_admin_notices()
{
    $woocommerce_plugin = 'woocommerce/woocommerce.php';
    $plugin_name = esc_html__('Easy Frequently Bought Together for WooCommerce', 'easy-frequently-bought-together-for-woocommerce');

    // Check if WooCommerce is installed
    if (file_exists(WP_PLUGIN_DIR . '/' . $woocommerce_plugin)) {
        // WooCommerce is installed but may not be active
        if (!is_plugin_active($woocommerce_plugin)) {
            $activation_url = wp_nonce_url(
                'plugins.php?action=activate&amp;plugin=' . $woocommerce_plugin . '&amp;plugin_status=all&amp;paged=1&amp;s',
                'activate-plugin_' . $woocommerce_plugin
            );
            $message = sprintf(
                '<strong>%1$s requires WooCommerce to be active. You can <a href="%2$s" class="message" target="_blank">%3$s</a> here.</strong>',
                $plugin_name,
                esc_url($activation_url),
                __("Activate WooCommerce", "easy-frequently-bought-together-for-woocommerce"),
            );
        }
    } else {
        // WooCommerce is not installed
        $plugin_name = 'WooCommerce';
        $action = 'install-plugin';
        $slug = 'woocommerce';
        $install_link = wp_nonce_url(
            add_query_arg(
                array(
                    'action' => $action,
                    'plugin' => $slug
                ),
                admin_url('update.php')
            ),
            $action . '_' . $slug
        );
        $message = sprintf(
            '<strong>%1$s requires WooCommerce to be installed. You can download <a href="%2$s" class="message" target="_blank">%3$s</a> here.</strong>',
            $plugin_name,
            esc_url($install_link),
            __("WooCommerce Install", "easy-frequently-bought-together-for-woocommerce"),
        );
    }
?>
    <div class="error">
        <p><?php echo wp_kses($message, 'efbtw_kses'); ?></p>
    </div>
<?php
}

Efbtw_run_func();
