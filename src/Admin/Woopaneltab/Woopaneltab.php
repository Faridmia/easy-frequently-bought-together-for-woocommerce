<?php

namespace Zamzamcoders\Easybundlefbt\Admin\Woopaneltab;

use Zamzamcoders\Easybundlefbt\Traitval\Traitval;

/**
 * Class Menu
 * 
 * This class uses the Traitval trait to implement singleton functionality and
 * provides methods for adding custom submenus to the WordPress admin menu
 * within the Ultimate Product Options For WooCommerce plugin.
 */
class Woopaneltab
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
        add_filter('woocommerce_product_data_tabs', array($this, 'product_data_tabs'));
        add_action('woocommerce_product_data_panels', array($this, 'product_data_panels'));
        add_action('woocommerce_process_product_meta', array($this, 'save_efbtw_product_options_field'));
    }

    /**
     * Add custom tab in WC tabs.
     *
     * @param array $tabs WooCommerce tabs.
     * @return array
     */
    public function product_data_tabs($tabs)
    {
        $tabs['efbtw_bought_together'] = array(
            'label'    => esc_html__('Frequently Bought Together', 'easy-frequently-bought-together-for-woocommerce'),
            'target'   => 'efbtw_bought_together',
            'priority' => 80,
        );

        return $tabs;
    }


    public function product_data_panels()
    {
        $product_id = get_the_ID();
        $selected_products = get_post_meta($product_id, '_efbtw_select_bundle_product', true);

        if (empty($selected_products)) {
            $selected_products = array();
        } elseif (is_string($selected_products)) {
            $selected_products = array_map('intval', explode(',', $selected_products));
        } elseif (is_array($selected_products)) {
            $selected_products = array_map('intval', $selected_products);
        }

        $selected_products = array_filter($selected_products, 'is_int');

        $product_args = array(
            'post_type' => 'easyfbt_bundle',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'order' => 'desc',
            'ignore_sticky_posts' => true
        );

        $product_query = new \WP_Query($product_args);
        $nonce = wp_create_nonce('efbtw-woobundle-nonce');
?>

        <div id="efbtw_bought_together" class="widget-content panel woocommerce_options_panel efbtw-bought-together" style="display:none">
            <input type="hidden" name="efbtw-woobundle-nonce" value="<?php echo esc_attr($nonce); ?>" />
            <div class="efbtw-bought-together-controls efbtw-active-section">
                <div class="options_group">
                    <p class="form-field">
                        <label><?php esc_html_e('Add bundles', 'easy-frequently-bought-together-for-woocommerce'); ?></label>
                        <select class="wc-enhanced-select" name="efbtw_select_bundle_product[]" multiple="multiple" style="width: 50%;">
                            <?php
                            if ($product_query->have_posts()) :
                                while ($product_query->have_posts()) : $product_query->the_post();
                                    $bundle_id = get_the_ID();
                                    $selected = in_array($bundle_id, $selected_products) ? 'selected="selected"' : '';
                                    echo '<option value="' . esc_attr($bundle_id) . '" ' . esc_attr($selected) . '>' . esc_html(get_the_title()) . '</option>';
                                endwhile;
                                wp_reset_postdata();
                            endif;
                            ?>
                        </select>
                    </p>
                </div>
                <a href="<?php echo esc_url(admin_url('edit.php?post_type=easyfbt_bundle')); ?>" class="efbtw-open-bundle-manager">
                    <?php esc_html_e('Open bundles manager', 'easy-frequently-bought-together-for-woocommerce'); ?>
                </a>
            </div>
        </div>
<?php
    }

    public function save_efbtw_product_options_field($post_id)
    {

        if (
            !isset($_POST['efbtw-woobundle-nonce']) ||
            !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['efbtw-woobundle-nonce'])), 'efbtw-woobundle-nonce')
        ) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!current_user_can('edit_product', $post_id)) return;

        if (isset($_POST['efbtw_select_bundle_product'])) {
            $bundle_products = array_map('absint', $_POST['efbtw_select_bundle_product']);
            $bundle_products = array_unique(array_filter($bundle_products));

            update_post_meta($post_id, '_efbtw_select_bundle_product', $bundle_products);
        } else {
            delete_post_meta($post_id, '_efbtw_select_bundle_product');
        }
    }
}
