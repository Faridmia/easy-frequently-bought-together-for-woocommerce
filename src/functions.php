<?php

/**
 * Filter the allowed HTML tags for a specific context.
 *
 * This function extends the list of allowed HTML tags and attributes for specific contexts
 * using the `wp_kses` function. The contexts can be 'efbtw_kses' for general HTML content
 * or 'efbtw_img' for image-specific tags.
 *
 * @param array  $efbtw_tags    The default allowed HTML tags and attributes.
 * @param string $efbtw_context The context in which the HTML is being filtered.
 * @return array The modified list of allowed HTML tags and attributes.
 *
 * @since 1.0.0
 */
function efbtw_kses_allowed_html($efbtw_tags, $efbtw_context)
{
    switch ($efbtw_context) {
        case 'efbtw_kses':
            $efbtw_tags = array(
                'div'    => array(
                    'class' => array(),
                ),
                'ul'     => array(
                    'class' => array(),
                ),
                'li'     => array(),
                'span'   => array(
                    'class' => array(),
                ),
                'a'      => array(
                    'href'  => array(),
                    'class' => array(),
                ),
                'i'      => array(
                    'class' => array(),
                ),
                'p'      => array(),
                'em'     => array(),
                'br'     => array(),
                'strong' => array(),
                'h1'     => array(),
                'h2'     => array(),
                'h3'     => array(),
                'h4'     => array(),
                'h5'     => array(),
                'h6'     => array(),
                'del'    => array(),
                'ins'    => array(),
                'option' => array(
                    'value' => array(),
                    'data-item' => array(),
                ),
            );
            return $efbtw_tags;
        case 'efbtw_img':
            $efbtw_tags = array(
                'img' => array(
                    'class'  => array(),
                    'height' => array(),
                    'width'  => array(),
                    'src'    => array(),
                    'alt'    => array(),
                ),
            );
            return $efbtw_tags;
        default:
            return $efbtw_tags;
    }
}

add_filter('wp_kses_allowed_html', 'efbtw_kses_allowed_html', 10, 2);

/**
 * Sanitizes the custom field items data.
 *
 * This function takes input data, which can be either an array or a string,
 * and sanitizes it by ensuring that keys are safe and values are properly 
 * sanitized as text fields. It returns an associative array of sanitized 
 * values or a single sanitized string.
 *
 * @param mixed $data The input data to be sanitized (array or string).
 * @return array|string The sanitized data.
 *
 * @since 1.0.0
 */
function efbtw_sanitize_custom_field_items_data($data)
{
    $sanitized_data = array();

    if (is_array($data)) {
        foreach ($data as $key => $value) {
            $sanitized_key = sanitize_key($key);

            // Check if $value is an array before using array_map
            if (is_array($value)) {
                $sanitized_value = array_map('sanitize_text_field', $value);
            } else {
                $sanitized_value = sanitize_text_field($value);
            }

            $sanitized_data[$sanitized_key] = $sanitized_value;
        }
    } else {
        // Sanitize non-array data
        $sanitized_data = sanitize_text_field($data);
    }

    return $sanitized_data;
}

function efbtw_sanitize_input($input)
{
    return wp_strip_all_tags($input);
}


// product per page
function efbtw_product_per_page($products)
{

    if (!empty(get_option('efbtw_product_per_page'))) {
        $products = get_option('efbtw_product_per_page');
    }

    return $products;
}

add_filter('loop_shop_per_page', "efbtw_product_per_page", 30);

/** search panel initial output and after widget save this output will show  */
function efbtw_all_product_panel_output($select_all_product = [])
{

    $product_args = array(
        'post_type' => 'product',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'order' => 'desc',
        'ignore_sticky_posts' => 'true'
    );

    $product_output = '';
    $product_query = new WP_Query($product_args);
    $product_output .=  '<option  data-item="" value="empty">Select Product</option>';
    if ($product_query->have_posts()) :
        $search_item_name = 'album';
        $count = 0;
        while ($product_query->have_posts()) : $product_query->the_post();
            global $post;
            $product_id         = $post->ID;
            $select_product = "";
            if (is_array($select_all_product) || is_object($select_all_product)) {
                foreach ($select_all_product as $key => $value) {
                    $get_product =  $select_all_product[$key];
                    if ($get_product == $product_id) {
                        $select_product = "selected='selected'";
                    }
                }
            }

            $product_output .= '<option ' . $select_product . ' data-item="' . $product_id . '" value="' . $product_id . '">' . get_the_title() . '</option>';

            $count++;
        endwhile;

        wp_reset_postdata();
    endif;

    return $product_output;
}

function efbtw_single_product_panel_output($select_all_product = '')
{

    $product_args = array(
        'post_type' => 'product',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'order' => 'desc',
        'ignore_sticky_posts' => 'true'
    );

    $product_output = '';
    $product_query = new WP_Query($product_args);
    $product_output .=  '<option  data-item="" value="empty">Select Product</option>';
    if ($product_query->have_posts()) :
        $search_item_name = 'album';
        $count = 0;
        while ($product_query->have_posts()) : $product_query->the_post();
            global $post;
            $product_id         = $post->ID;
            $select_product = "";
            if ($select_all_product) {
                if ($select_all_product == $product_id) {
                    $select_product = "selected='selected'";
                }
            }

            $product_output .= '<option ' . $select_product . ' data-item="' . $product_id . '" value="' . $product_id . '">' . get_the_title() . '</option>';

            $count++;
        endwhile;

        wp_reset_postdata();
    endif;

    return $product_output;
}

/**
 * All exclude product functions
 */
function efbtw_exclude_product_panel_output($efbtw_exclude_product = [])
{

    $product_args = array(
        'post_type' => 'product',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'order' => 'desc',
        'ignore_sticky_posts' => 'true'
    );


    $product_output = '';
    $product_query = new WP_Query($product_args);
    $product_output .=  '<option  data-item="" value="empty">Select Product</option>';

    if ($product_query->have_posts()) :

        $search_item_name = 'album';
        $count = 0;

        while ($product_query->have_posts()) : $product_query->the_post();
            global $post;
            $product_id         = $post->ID;
            $select_product = " ";
            if (is_array($efbtw_exclude_product) || is_object($efbtw_exclude_product)) {
                foreach ($efbtw_exclude_product as $key => $value) {
                    $get_product =  $efbtw_exclude_product[$key];
                    if ($get_product == $product_id) {
                        $select_product = "selected='selected'";
                    }
                }
            }

            $product_output .= '<option ' . $select_product . ' data-item="' . $product_id . '" value="' . $product_id . '">' . get_the_title() . '</option>';

            $count++;
        endwhile;

        wp_reset_postdata();
    endif;

    return $product_output;
}

// select all bundle easyfbt_bundle post type product
function efbtw_all_fbt_bundle_panel_output($select_all_product = [])
{

    $product_args = array(
        'post_type' => 'easyfbt_bundle',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'order' => 'desc',
        'ignore_sticky_posts' => 'true'
    );

    $product_output = '';
    $product_query = new WP_Query($product_args);
    $product_output .=  '<option  data-item="" value="empty">Select Bundle Product</option>';
    if ($product_query->have_posts()) :
        $search_item_name = 'bundle product';
        $count = 0;
        while ($product_query->have_posts()) : $product_query->the_post();
            global $post;
            $product_id         = $post->ID;
            $select_product = "";
            if (is_array($select_all_product) || is_object($select_all_product)) {
                foreach ($select_all_product as $key => $value) {
                    $get_product =  $select_all_product[$key];
                    if ($get_product == $product_id) {
                        $select_product = "selected='selected'";
                    }
                }
            }

            $product_output .= '<option ' . $select_product . ' data-item="' . $product_id . '" value="' . $product_id . '">' . get_the_title() . '</option>';

            $count++;
        endwhile;

        wp_reset_postdata();
    endif;

    return $product_output;
}

/**
 * get all product categories
 */

function efbtw_get_product_categories($product_ids = [])
{

    $options = array();
    $taxonomy = 'product_cat';
    $category_output = '';

    if (!empty($taxonomy)) {
        $terms = get_terms(
            array(
                'parent' => 0,
                'taxonomy' => $taxonomy,
                'hide_empty' => false,
            )
        );

        $category_output .=  '<option  data-item="" value="empty">Select Categories</option>';

        if (!empty($terms)) {
            foreach ($terms as $index => $term) {
                if (isset($term)) {
                    $options[''] = 'Select';
                    $select_product = ' ';
                    // Get the option and check if it is an array or object

                    if ($product_ids) {
                        if (is_array($product_ids) || is_object($product_ids)) {
                            foreach ($product_ids as $key => $value) {
                                $get_category = $value; // Retrieve the value correctly
                                if ($get_category == $term->term_id) {
                                    $select_product = "selected='selected'";
                                    break; // Exit the loop once a match is found
                                }
                            }
                        }
                    }
                    if (isset($term->slug) && isset($term->name)) {
                        $category_output .= '<option  ' . $select_product . ' data-item="' . $term->term_id . '" value="' . $term->term_id . '">' . $term->name . '</option>';
                    }
                }
            }
        }
    }

    printf("%s", do_shortcode($category_output));
}

/**
 * Custom function to retrieve an option with a default value.
 *
 * @param string $option_name The name of the option to retrieve.
 * @param mixed $default The default value to return if the option does not exist.
 * @return mixed The value of the option, or the default value if the option does not exist.
 */
function efbtw_get_option($option_name, $default = false)
{
    // Check if the option exists
    $option_value = get_option($option_name, $default);

    // If the option does not exist, return the default value
    if ($option_value === false) {
        return $default;
    }

    return $option_value;
}

/**
 * efbtw_do_shortcode function
 *
 * @param [type] $shortcode
 * @param array $atts
 * @return void
 */
function efbtw_do_shortcode($shortcode, $atts = [])
{
    $atts_string = '';
    foreach ($atts as $key => $value) {
        $atts_string .= $key . '="' . esc_attr($value) . '" ';
    }
    $atts_string = trim($atts_string);

    return do_shortcode("[$shortcode $atts_string]");
}


function efbtw_apply_discount($price, $discount_value, $discount_type)
{
    switch ($discount_type) {
        case 'percentage_discount':
            return max($price - ($price * ($discount_value / 100)), 0);

        case 'fixed_discount':
            return max($price - $discount_value, 0);

        case 'fixed_price':
            return max($discount_value, 0);

        default:
            return $price;
    }
}


function efbtw_get_product_prices($product)
{
    if (! is_a($product, 'WC_Product')) {
        return ['regular_price' => 0, 'current_price' => 0];
    }

    if ($product->is_type('variation')) {
        $regular_price = (float) $product->get_regular_price();
        $current_price = (float) $product->get_price();
    } elseif ($product->is_type('variable')) {
        $regular_price = (float) $product->get_variation_regular_price('max');
        $current_price = (float) $product->get_variation_price('max');
    } else {
        $regular_price = (float) $product->get_regular_price();
        $current_price = (float) $product->get_price();
    }

    return [
        'regular_price' => $regular_price,
        'current_price' => $current_price,
    ];
}


function efbtw_custom_price_html($product, $discount_value, $discount_type)
{

    $regular_price = (float) $product->get_regular_price();
    $sale_price    = (float) $product->get_sale_price();
    $final_price   = $product->is_on_sale() ? $sale_price : $regular_price;

    if (!empty($discount_value) && $discount_value > 0) {
        $discount_price = efbtw_apply_discount($final_price, $discount_value, $discount_type);

        if ($discount_price < $regular_price) {
            return '<del>' . wc_price($regular_price) . '</del> <ins>' . wc_price($discount_price) . '</ins>';
        } else {
            return wc_price($final_price);
        }
    }

    if ($product->is_on_sale()) {
        return '<del>' . wc_price($regular_price) . '</del> <ins>' . wc_price($sale_price) . '</ins>';
    } else {
        return wc_price($regular_price);
    }
}


/**
 * Get default variation product id.
 *
 * @param object $product Product data.
 *
 * @return false|mixed
 */
function efbtw_get_default_variation_product_id($product)
{
    if ($product->get_default_attributes()) {
        $is_default_variation = false;

        foreach ($product->get_available_variations() as $variation_values) {
            foreach ($variation_values['attributes'] as $key => $attribute_value) {
                $attribute_name = str_replace('attribute_', '', $key);
                $default_value  = $product->get_variation_default_attribute($attribute_name);

                if ($default_value === $attribute_value) {
                    $is_default_variation = true;
                } else {
                    $is_default_variation = false;
                }
            }

            if ($is_default_variation) {
                return $variation_values['variation_id'];
            }
        }
    }

    return current($product->get_visible_children());
}

function efbtw_get_selected_total_price($product_ids = [], $discount_value = 0, $discount_type = '')
{
    $total = 0;

    if (!empty($product_ids) && is_array($product_ids)) {
        foreach ($product_ids as $pid) {
            $product = wc_get_product($pid);
            if ($product) {
                $price = (float) $product->get_price();
                // Apply discount
                $discounted_price = efbtw_apply_discount($price, $discount_value, $discount_type);
                $total += $discounted_price;
            }
        }
    }

    return wc_price($total);
}


if (! function_exists('efbtw_clean')) {
    /**
     * Recursively clean user input using sanitize_text_field.
     *
     * This function accepts both strings and arrays. For arrays,
     * it applies cleaning to each item recursively. Non-scalar
     * values (e.g., objects, resources) are returned as-is.
     *
     * @param string|array $var Data to sanitize.
     * @return string|array Cleaned data.
     */
    function efbtw_clean($var)
    {
        if (is_array($var)) {
            return array_map('efbtw_clean', $var);
        }

        return is_scalar($var) ? sanitize_text_field($var) : $var;
    }
}


add_action('rest_api_init',  'efbtw_easy_settings_function');


function efbtw_easy_settings_function()
{
    register_rest_route('efbtw/v1', '/settings', [
        'methods'  => 'GET',
        'callback' => 'efbtw_get_settings',
        'permission_callback' => '__return_true'
    ]);

    register_rest_route('efbtw/v1', '/settings', [
        'methods'  => 'POST',
        'callback' => 'efbtw_save_settings',
        'permission_callback' => '__return_true'
    ]);
}

function efbtw_get_settings()
{
    $settings = get_option('efbtw_global_settings', []);
    return rest_ensure_response($settings);
}

function efbtw_save_settings(WP_REST_Request $request)
{

    $nonce = $request->get_header('X-WP-Nonce');
    if (!wp_verify_nonce($nonce, 'wp_rest')) {
        return new WP_Error('forbidden', 'Invalid nonce', ['status' => 403]);
    }

    $data = $request->get_json_params();
    if (!is_array($data)) {
        return new WP_Error('invalid_data', 'Invalid settings data', ['status' => 400]);
    }

    $data['enableProductPrice'] = isset($data['enableProductPrice']) ? (bool)$data['enableProductPrice'] : true;
    $data['enableCartPrice'] = isset($data['enableCartPrice']) ? (bool)$data['enableCartPrice'] : true;
    $data['enableDiscountPercentage'] = isset($data['enableDiscountPercentage']) ? (bool)$data['enableDiscountPercentage'] : true;

    update_option('efbtw_global_settings', $data);

    return rest_ensure_response([
        'success'  => true,
        'settings' => $data,
    ]);
}
