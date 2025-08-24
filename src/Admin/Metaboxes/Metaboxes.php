<?php

namespace Zamzamcoders\Easybundlefbt\Admin\Metaboxes;

use Zamzamcoders\Easybundlefbt\Traitval\Traitval;

/**
 * Class Metaboxes
 * 
 * This class uses the Traitval trait to implement singleton functionality and
 * provides methods for creating custom post types within the Ultimate Product Options For WooCommerce plugin.
 */
class Metaboxes
{
    use Traitval;

    /**
     * Constructor
     * 
     * The constructor adds actions to the WordPress hooks for saving post data
     * and adding meta boxes to the post edit screen. These actions ensure that
     * custom metadata is handled properly within the Ultimate Product Options For WooCommerce plugin.
     */
    public function __construct()
    {
        // Add an action to the 'add_meta_boxes' hook to add custom meta boxes to the post edit screen
        add_action('add_meta_boxes', array($this, 'efbtw_product_meta_boxes'));
        add_action('save_post', array($this, 'efbtw_product_meta_save'));
    }

    /**
     * Add a custom meta box for extra item text details.
     *
     * This function hooks into the 'add_meta_boxes' action to add a custom meta box 
     * for entering extra item text details in the product field screen.
     *
     * @since 1.0.0
     *
     * @return void
     */
    function efbtw_product_meta_boxes()
    {
        add_meta_box(
            'efbtw_product_meta_box',
            __('Freequently Bought Together', 'easy-frequently-bought-together-for-woocommerce'),
            array($this, 'efbtw_show_efbtw_product_meta_box'),
            array('easyfbt_bundle'),
            'normal',
            'high'
        );
    }

    /**
     * Display the Extra Product Data meta box.
     *
     * This function is the callback used by `add_meta_box` to render the content of the
     * extra item text meta box on the product field screen. It retrieves the current value
     * of the 'efbtw_product' meta field for the current post and displays an input field
     * for editing it.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function efbtw_show_efbtw_product_meta_box()
    {
        global $post;
        $efbtw_bundle_item_group   = get_post_meta($post->ID, 'efbtw_bundle_item_group', true);
        $efbtw_primary_discount_type   = get_post_meta($post->ID, 'efbtw_primary_discount_type', true);
        $efbtw_bundle_primary_item_price   = get_post_meta($post->ID, 'efbtw_bundle_primary_item_price', true);
        $efbtw_allow_customization   = get_post_meta($post->ID, '_efbtw_allow_customization', true);
        $efbtw_hide_outof_stock   = get_post_meta($post->ID, '_efbtw_hide_outof_stock', true);
        $efbtw_fbtcheckboxstate   = get_post_meta($post->ID, '_efbtw_fbtcheckboxstate', true);

        $checkstate = ($efbtw_fbtcheckboxstate == 'check') ? 'efbtw-btn-primary' : 'efbtw-btn-default';
        $uncheckstate = ($efbtw_fbtcheckboxstate == 'uncheck') ? 'efbtw-btn-primary' : 'efbtw-btn-default';


        $nonce = wp_create_nonce('efbtw-metaboxes-nonce');

?>
        <div class="efbtw-main-settings-wrapper">
            <input type="hidden" name="efbtw-metaboxes-nonce" value="<?php echo esc_attr($nonce); ?>" />
            <table id="fbt-repeatable-fieldset-one" class="fbt-repeatable-fieldset-one">
                <thead>
                    <h4>Primary product discount</h4>
                    <tr class="fbt-bought-together-field-item">
                        <td class="fbt-bought-together-item">
                            <label class="fbt-label">Discount Type</label>
                            <select name="efbtw_primary_discount_type" class="fbt-discount-type">
                                <option value=""><?php echo esc_html__('Discount type', 'easy-frequently-bought-together-for-woocommerce') ?></option>
                                <option value="percentage_discount" <?php selected($efbtw_primary_discount_type, 'percentage_discount'); ?>>Percentage Discount</option>
                                <option value="fixed_discount" <?php selected($efbtw_primary_discount_type, 'fixed_discount'); ?>>Fixed Discount</option>
                                <option value="fixed_price" <?php selected($efbtw_primary_discount_type, 'fixed_price'); ?>>Fixed Price</option>
                            </select>
                        </td>
                        <td class="fbt-bought-together-item">
                            <label class="fbt-label">Discount</label>
                            <input type="number" placeholder="Price" name="efbtw_bundle_primary_item_price" value="<?php if ($efbtw_bundle_primary_item_price != '') echo esc_attr($efbtw_bundle_primary_item_price); ?>" />
                        </td>
                    </tr>
                </thead>
                <hr />
                <tbody>
                    <?php
                    if ($efbtw_bundle_item_group) :
                        foreach ($efbtw_bundle_item_group as $field) {

                    ?>
                            <tr class="fbt-bought-together-field-item">
                                <td class="fbt-bought-together-item">
                                    <label class="fbt-label">Select Product</label>
                                    <select name="efbtw_select_product[]" class="fbt-select-product">
                                        <?php
                                        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                        echo efbtw_single_product_panel_output($field['efbtw_select_product']);
                                        ?>
                                    </select>
                                </td>
                                <td class="fbt-bought-together-item">
                                    <label class="fbt-label">Discount Type</label>
                                    <select name="efbtw_discount_type[]" class="fbt-discount-type">
                                        <option value=""><?php echo esc_html__('Discount type', 'easy-frequently-bought-together-for-woocommerce') ?></option>
                                        <option value="percentage_discount" <?php selected($field['efbtw_discount_type'], 'percentage_discount'); ?>>Percentage Discount</option>
                                        <option value="fixed_discount" <?php selected($field['efbtw_discount_type'], 'fixed_discount'); ?>>Fixed Discount</option>
                                        <option value="fixed_price" <?php selected($field['efbtw_discount_type'], 'fixed_price'); ?>>Fixed Price</option>
                                    </select>
                                </td>
                                <td class="fbt-bought-together-item">
                                    <label class="fbt-label">Discount</label>
                                    <input type="number" placeholder="Price" name="efbtw_bundle_item_price[]" value="<?php if ($field['efbtw_bundle_item_price'] != '') echo esc_attr($field['efbtw_bundle_item_price']); ?>" />
                                </td>
                                <td class="fbt-bought-together-remove"><a class="button fbt-bought-together-remove-row" href="#1"><?php echo esc_html__("Remove", "easy-frequently-bought-together-for-woocommerce"); ?></a></td>
                            </tr>
                        <?php
                        }
                    else :
                        // show a blank one
                        ?>
                        <tr class="fbt-bought-together-field-item">
                            <td class="fbt-bought-together-item">
                                <label class="fbt-label">Select Product</label>
                                <select name="efbtw_select_product[]" class="fbt-select-product">
                                    <?php
                                    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                    echo efbtw_single_product_panel_output();
                                    ?>
                                </select>
                            </td>
                            <td class="fbt-bought-together-item">
                                <label class="fbt-label">Discount Type</label>
                                <select name="efbtw_discount_type[]" class="fbt-discount-type">
                                    <option value=""><?php echo esc_html__('Discount type', 'easy-frequently-bought-together-for-woocommerce') ?></option>
                                    <option value="percentage_discount">Percentage Discount</option>
                                    <option value="fixed_discount">Fixed Discount</option>
                                    <option value="fixed_price">Fixed Price</option>
                                </select>
                            </td>
                            <td class="fbt-bought-together-item">
                                <label class="fbt-label">Discount</label>
                                <input type="number" placeholder="Price" title="Price" name="efbtw_bundle_item_price[]" />
                            </td>
                            <td class="fbt-bought-together-remove"><a class="button fbt-bought-together-remove-row button-disabled" href="#"><?php echo esc_html__("Remove", "easy-frequently-bought-together-for-woocommerce"); ?></a></td>
                        </tr>
                    <?php endif; ?>
                    <!-- empty hidden one for jQuery -->
                    <tr class="empty-row screen-reader-text fbt-bought-together-field-item">
                        <td class="fbt-bought-together-item">
                            <label class="fbt-label">Select Product</label>
                            <select name="efbtw_select_product[]" class="fbt-select-product2">

                            </select>
                        </td>
                        <td class="fbt-bought-together-item">
                            <label class="fbt-label">Discount Type</label>
                            <select name="efbtw_discount_type[]" class="fbt-discount-type">
                                <option value=""><?php echo esc_html__('Discount type', 'easy-frequently-bought-together-for-woocommerce') ?></option>
                                <option value="percentage_discount">Percentage Discount</option>
                                <option value="fixed_discount">Fixed Discount</option>
                                <option value="fixed_price">Fixed Price</option>
                            </select>
                        </td>
                        <td class="fbt-bought-together-item">
                            <label class="fbt-label">Discount</label>
                            <input type="number" placeholder="Price" title="Price" name="efbtw_bundle_item_price[]" />
                        </td>
                        <td class="fbt-bought-together-remove"><a class="button fbt-bought-together-remove-row button-disabled" href="#"><?php echo esc_html__("Remove", "easy-frequently-bought-together-for-woocommerce"); ?></a></td>
                    </tr>
                </tbody>
            </table>
            <p><a id="fbt-bought-together-add-row" class="button" href="#"><?php echo esc_html__("Add New Item", "easy-frequently-bought-together-for-woocommerce"); ?> </a></p>
        </div>

        <div class="efbtw-setting-wrapper">
            <div class="efbtw-settings-section">
                <div class="efbtw-setting-row">
                    <h3>Allow customize</h3>
                    <div class="efbtw-toggle-wrapper">
                        <label class="efbtw-toggle efbtw-toggle-button">
                            <input type="checkbox" name="efbtw_allow_customization" <?php echo ($efbtw_allow_customization == 'on') ? 'checked' : ''; ?> />
                            <span class="efbtw-toggle-track"></span>
                            <span class="efbtw-toggle-thumb">
                                <?php
                                echo ($efbtw_allow_customization == 'on') ? 'YES' : 'NO';
                                ?>
                            </span>
                        </label>
                    </div>
                </div>
                <p class="setting-description">Enable this option to allow users customize the bundle and check/uncheck some products.</p>
            </div>

            <div class="efbtw-settings-section efbtw-settings-show-hidden">
                <div class="efbtw-setting-row">
                    <h3>Default checkbox state</h3>
                    <div class="efbtw-button-group efbtw-checked-unchecked">
                        <button class="efbtw-btn <?php echo esc_attr($checkstate); ?> efbtw-check-button">Check</button>
                        <button class="efbtw-btn <?php echo esc_attr($uncheckstate); ?> efbtw-uncheck-button">Uncheck</button>
                        <input type="hidden" name="efbtw_fbtcheckboxstate" value="<?php echo esc_attr($efbtw_fbtcheckboxstate); ?>" />
                    </div>
                </div>
            </div>

            <div class="efbtw-settings-section efbtw-settings-show-hidden">
                <div class="efbtw-setting-row">
                    <h3>Hide out of stock product</h3>
                    <div class="efbtw-toggle-wrapper">
                        <label class="efbtw-toggle efbtw-toggle-button">
                            <input type="checkbox" name="efbtw_hide_outof_stock" <?php echo ($efbtw_hide_outof_stock == 'on') ? 'checked' : ''; ?> />
                            <span class="efbtw-toggle-track"></span>
                            <span class="efbtw-toggle-thumb">
                                <?php
                                echo ($efbtw_hide_outof_stock == 'on') ? 'YES' : 'NO';
                                ?>
                            </span>
                        </label>
                    </div>
                </div>
            </div>

        </div>
<?php
    }

    /**
     * Save the Extra Product Data meta field.
     *
     * This function saves the value of the 'efbtw_product' meta field when a post is saved.
     * It verifies a nonce to ensure the request is legitimate, then updates the meta field
     * with the value from the form input.
     *
     * @since 1.0.0
     *
     * @param int $post_id The ID of the post being saved.
     * @return int The post ID if the nonce is invalid.
     */



    function efbtw_product_meta_save($post_id)
    {
        if (
            !isset($_POST['efbtw-metaboxes-nonce']) ||
            !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['efbtw-metaboxes-nonce'])), 'efbtw-metaboxes-nonce')
        ) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $old_meta = get_post_meta($post_id, 'efbtw_bundle_item_group', true);
        $new_meta = [];

        $efbtw_select_product = isset($_POST['efbtw_select_product']) && is_array($_POST['efbtw_select_product'])
            ? array_map('sanitize_text_field', wp_unslash($_POST['efbtw_select_product']))
            : [];

        $prices = isset($_POST['efbtw_bundle_item_price']) && is_array($_POST['efbtw_bundle_item_price'])
            ? array_map('intval', wp_unslash($_POST['efbtw_bundle_item_price']))
            : [];

        $efbtw_discount_type = isset($_POST['efbtw_discount_type']) && is_array($_POST['efbtw_discount_type'])
            ? array_map('sanitize_text_field', wp_unslash($_POST['efbtw_discount_type']))
            : [];

        $efbtw_primary_discount_type = isset($_POST['efbtw_primary_discount_type'])
            ? sanitize_text_field(wp_unslash($_POST['efbtw_primary_discount_type']))
            : '';

        $efbtw_bundle_primary_item_price = isset($_POST['efbtw_bundle_primary_item_price'])
            ? sanitize_text_field(wp_unslash($_POST['efbtw_bundle_primary_item_price']))
            : '';

        $efbtw_allow_customization = isset($_POST['efbtw_allow_customization'])
            ? sanitize_text_field(wp_unslash($_POST['efbtw_allow_customization']))
            : 'off';

        $efbtw_hide_outof_stock = isset($_POST['efbtw_hide_outof_stock'])
            ? sanitize_text_field(wp_unslash($_POST['efbtw_hide_outof_stock']))
            : 'off';

        $efbtw_fbtcheckboxstate = isset($_POST['efbtw_fbtcheckboxstate'])
            ? sanitize_text_field(wp_unslash($_POST['efbtw_fbtcheckboxstate']))
            : '';

        $count = count($efbtw_discount_type);

        for ($i = 0; $i < $count; $i++) {
            if (!empty($efbtw_select_product[$i])) {
                $new_meta[$i] = [
                    'efbtw_select_product'  => $efbtw_select_product[$i],
                    'efbtw_bundle_item_price'    => $prices[$i],
                    'efbtw_discount_type'   => $efbtw_discount_type[$i],
                ];
            }
        }

        if (!empty($new_meta) && $new_meta !== $old_meta) {
            update_post_meta($post_id, 'efbtw_bundle_item_group', $new_meta);
        } elseif (empty($new_meta) && $old_meta) {
            delete_post_meta($post_id, 'efbtw_bundle_item_group');
        }

        update_post_meta($post_id, 'efbtw_primary_discount_type', $efbtw_primary_discount_type);
        update_post_meta($post_id, 'efbtw_bundle_primary_item_price', $efbtw_bundle_primary_item_price);
        update_post_meta($post_id, '_efbtw_allow_customization', $efbtw_allow_customization);
        update_post_meta($post_id, '_efbtw_hide_outof_stock', $efbtw_hide_outof_stock);
        update_post_meta($post_id, '_efbtw_fbtcheckboxstate', $efbtw_fbtcheckboxstate);
    }
}
