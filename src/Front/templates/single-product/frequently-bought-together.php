<?php
defined('ABSPATH') || exit;

global $product;

$product_id = $product->get_id();
$bundle_ids_raw = get_post_meta($product_id, '_efbtw_select_bundle_product', true);
if (empty($bundle_ids_raw)) return;

$bundle_ids = is_array($bundle_ids_raw) ? $bundle_ids_raw : (!empty($bundle_ids_raw) ? explode(',', $bundle_ids_raw) : []);
$settings = get_option('efbtw_global_settings', []);

$frequenty_box_title     = isset($settings['boxTitle']) ? $settings['boxTitle'] : '';
$frequenty_totalLabel    = isset($settings['totalLabel']) ? $settings['totalLabel'] : '';
$frequenty_buttonLabel   = isset($settings['buttonLabel']) ? $settings['buttonLabel'] : '';
$frequenty_enableDiscount = isset($settings['enableDiscountPercentage']) ? $settings['enableDiscountPercentage'] : '';


?>
<div class="efbtw-product-bundle-wrapper">
    <h1>
        <?php
        echo esc_html(
            apply_filters(
                'efbtw_product_bought_together_title',
                $frequenty_box_title
            )
        );
        ?>
    </h1>
    <?php
    $repeater_count = 1;
    foreach ($bundle_ids as $bundle_id) :

        $products_datas = [];
        $instance->reset_subtotal();
        $instance->set_bundle_id($bundle_id);

        $bundle_group = get_post_meta($bundle_id, 'efbtw_bundle_item_group', true);

        if (is_array($bundle_group)) {
            foreach ($bundle_group as $item) {
                $pid = absint($item['efbtw_select_product'] ?? 0);
                if ($pid && !in_array($pid, array_column($products_datas, 'efbtw_select_product'))) {
                    $products_datas[] = $item;
                }
            }
        }

        $efbtw_discount_type   = get_post_meta($bundle_id, 'efbtw_primary_discount_type', true);
        $efbtw_bundle_item_price = get_post_meta($bundle_id, 'efbtw_bundle_primary_item_price', true);

        $prod_main = wc_get_product($product_id);
        $main_product_price_html = efbtw_custom_price_html($prod_main, $efbtw_bundle_item_price, $efbtw_discount_type);

        $prices_main = efbtw_get_product_prices($prod_main);
        $regular_price_main = $prices_main['regular_price'];
        $current_price_main = $prices_main['current_price'];
        $main_discounted_price = efbtw_apply_discount($current_price_main, $efbtw_bundle_item_price, $efbtw_discount_type);

        $efbtw_fbtcheckboxstate   = get_post_meta($bundle_id, '_efbtw_fbtcheckboxstate', true);

        $woofbt_checkbox = '';
        $button_class = '';
        $woofbt_checked = '';

        $efbtw_allow_customization   = get_post_meta($bundle_id, '_efbtw_allow_customization', true);

        if ($efbtw_fbtcheckboxstate == 'uncheck' && $efbtw_allow_customization != 'off') {
            $woofbt_checkbox .= ' efbtw-checkbox-uncheck efbtw-checkbox-on';
            $button_class .= ' buttonwoofbt-disabled';
        }
        if ($efbtw_fbtcheckboxstate === 'check') {
            $woofbt_checked = 'checked';
        }

        $primary_product = [
            'efbtw_select_product' => $product_id,
            'efbtw_bundle_item_price' => $efbtw_bundle_item_price,
            'efbtw_discount_type' => $efbtw_discount_type,
        ];

        array_unshift($products_datas, $primary_product);

        $efbtw_hide_outof_stock = get_post_meta($bundle_id, '_efbtw_hide_outof_stock', true);


        $products_data = [];
        $total_regular_price = 0;
        $total_discounted_price = 0;


        foreach ($products_datas as $product_data) {
            $pid = (int) ($product_data['efbtw_select_product'] ?? 0);
            $discount_value = (float) ($product_data['efbtw_bundle_item_price'] ?? 0);
            $discount_type = $product_data['efbtw_discount_type'] ?? '';

            $prod = wc_get_product($pid);
            if (!$prod) continue;

            $pid = apply_filters('wpml_object_id', $pid, 'product');

            if (
                $prod->is_type('grouped') ||
                $prod->is_type('external') ||
                ($prod->get_stock_status() === 'outofstock' && $efbtw_hide_outof_stock === 'on')
            ) {
                continue;
            }

            $prices = efbtw_get_product_prices($prod);
            $regular_price = $prices['regular_price'];
            $current_price = $prices['current_price'];

            $discounted_price = efbtw_apply_discount($current_price, $discount_value, $discount_type);

            $total_regular_price += $regular_price;
            $total_discounted_price += $discounted_price;

            $products_data[] = [
                'id'    => $pid,
                'title' => $prod->get_name(),
                'type'  => $prod->get_type(),
                'permalink' => get_permalink($pid),
                'image' => wp_get_attachment_image_url($prod->get_image_id(), 'medium'),
                'price_html' => efbtw_custom_price_html($prod, $discount_value, $discount_type),
                'discount_value' => $discount_value,
                'discount_type'  => $discount_type,
                'current_class_li' => ($prod->get_id() === $product->get_id()) ? 'product-primary' : '',
                'regular_price' => $regular_price,
                'sale_price'    => $current_price,
            ];
        }

        $formatted_price_html = '';
        if ($total_regular_price > $total_discounted_price) {

            $formatted_price_html .= '<del>' . wc_price($total_regular_price) . '</del> ';
            $formatted_price_html .= '<ins>' . wc_price($total_discounted_price) . '</ins>';
        } else {
            $formatted_price_html .= '<ins>' . wc_price($total_regular_price) . '</ins>';
        }

        if ($efbtw_fbtcheckboxstate === 'check') {
            $savings = $total_regular_price - $total_discounted_price;
            $total_items = count($products_data);
        } else {
            $savings = $regular_price_main - $main_discounted_price;
            $total_items = 1;
        }

        $allow_customization_class = ($efbtw_allow_customization == 'off') ? 'efbtw-allow-customization-off' : '';
    ?>

        <div class="efbtw-product-bundle-container <?php echo esc_attr($allow_customization_class); ?>">

            <form class="easyefbtw-fbt-form <?php echo esc_attr($woofbt_checkbox); ?>" method="post">
                <input type="hidden" name="efbtw-fbt-bundle-id" value="<?php echo esc_attr($bundle_id); ?>">
                <input type="hidden" name="easyefbtw-fbt-main-product" value="<?php echo esc_attr($product_id); ?>">

                <div class="efbtw-product-bundle-wrap">

                    <div class="efbtw-product-image-summery">
                        <div class="efbtw-product-images">
                            <?php foreach ($products_data as $i => $p) : ?>
                                <div class="efbtw-product-image <?php echo esc_attr($p['current_class_li']); ?>" data-productId="woofbt-product<?php echo esc_attr($p['id']); ?>">
                                    <img src="<?php echo esc_url($p['image']); ?>" alt="<?php echo esc_attr($p['title']); ?>" />
                                </div>
                                <?php if ($i < count($products_data) - 1) : ?>
                                    <div class="plus">+</div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>

                        <div class="efbtw-price-summary">
                            <div class="efbtw-price-info efbtw-price-info<?php echo esc_attr($repeater_count); ?>">
                                <span class="efbtw-price-label">
                                    <?php
                                    // Translators:  %s total product items count
                                    echo esc_html(sprintf($frequenty_totalLabel, $total_items));
                                    ?>
                                </span>
                                <span class="efbtw-total-price">
                                    <?php echo wp_kses_post($formatted_price_html);
                                    ?>
                                </span>
                                <?php
                                if ($frequenty_enableDiscount == '1') {
                                ?>
                                    <span class="efbtw-savings">
                                        <?php
                                        if ($savings > 0) {

                                            echo wp_kses_post(

                                                sprintf(
                                                    // Translators: %s is the discount amount (formatted price).
                                                    __('Save %s', 'easy-frequently-bought-together-for-woocommerce'),
                                                    wc_price($savings)
                                                )
                                            );
                                        }
                                        ?>
                                    </span>
                                <?php } ?>
                            </div>
                            <button type="submit" class="single_add_to_cart_button easyefbtw-add-to-cart-btn button<?php echo esc_attr($button_class); ?>"><?php echo esc_html($frequenty_buttonLabel); ?></button>
                        </div>
                    </div>

                    <div class="efbtw-product-list">
                        <?php
                        foreach ($products_data as $i => $p) :
                            $current_product = wc_get_product($p['id']);
                            $check_id = $current_product->get_id();
                        ?>
                            <div class="efbtw-product-item <?php echo esc_attr($p['current_class_li']); ?> product-<?php echo esc_attr($p['id']); ?>" data-productid="<?php echo esc_attr($p['id']); ?>">
                                <?php
                                if ($efbtw_allow_customization == 'on') {
                                ?>
                                    <div class="checkbox checked">

                                        <input type="checkbox"
                                            name="efbtw_bundle_products[]"
                                            id="checkbox_<?php echo esc_attr($i); ?>"
                                            class="active"
                                            value="<?php echo esc_attr($check_id); ?>"
                                            <?php echo esc_attr($woofbt_checked); ?>
                                            discount_value="<?php echo esc_attr($p['discount_value']); ?>"
                                            discount_type="<?php echo esc_attr($p['discount_type']); ?>"
                                            data-regular-price="<?php echo esc_attr($p['regular_price']); ?>"
                                            data-sale-price="<?php echo esc_attr($p['sale_price']); ?>">

                                    </div>
                                <?php } else { ?>
                                    <input type="hidden"
                                        name="efbtw_bundle_products[]"
                                        id="checkbox_<?php echo esc_attr($i); ?>"
                                        class="active"
                                        value="<?php echo esc_attr($check_id); ?>"
                                        <?php echo esc_attr($woofbt_checked); ?>
                                        discount_value="<?php echo esc_attr($p['discount_value']); ?>"
                                        discount_type="<?php echo esc_attr($p['discount_type']); ?>"
                                        data-regular-price="<?php echo esc_attr($p['regular_price']); ?>"
                                        data-sale-price="<?php echo esc_attr($p['sale_price']); ?>">
                                <?php } ?>
                                <div class="efbtw-product-details">
                                    <span class="efbtw-product-name">
                                        <a href="<?php echo esc_url($p['permalink']); ?>"><?php echo esc_html($p['title']); ?></a>
                                    </span>
                                    <span class="efbtw-product-price">
                                        <?php echo wp_kses_post($p['price_html']); ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                </div>
            </form>
        </div>

    <?php
        $repeater_count++;
    endforeach; ?>
</div>