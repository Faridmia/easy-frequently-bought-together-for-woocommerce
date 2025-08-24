<?php

namespace Zamzamcoders\Easybundlefbt\Admin\Ajax;

class FbtAjax
{
    /**
     * efbtw_search_products function
     *
     * @return void
     */
    function efbtw_get_products_callback()
    {
        $product_args = array(
            'post_type'      => 'product',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'order'          => 'DESC',
        );

        $product_query = new \WP_Query($product_args);

        $output = '<option value="">Select Product</option>'; // Default option

        if ($product_query->have_posts()) :
            while ($product_query->have_posts()) : $product_query->the_post();
                $product_id  = get_the_ID();
                $output .= '<option data-item="' . $product_id . '" value="' . get_the_ID() . '">' . get_the_title() . '</option>';
            endwhile;
            wp_reset_postdata();
        endif;

        echo wp_kses($output, 'efbtw_kses');
        wp_die();
    }

    function efbtw_tab_bundle_get_products_callback()
    {
        $product_args = array(
            'post_type'      => 'easyfbt_bundle',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'order'          => 'DESC',
        );

        $product_query = new \WP_Query($product_args);

        $output = '<option value="">Select Bundle Product</option>'; // Default option

        if ($product_query->have_posts()) :
            while ($product_query->have_posts()) : $product_query->the_post();
                $product_id  = get_the_ID();

                // error_log(print_r(get_the_title(), true));
                $output .= '<option  data-item="' . $product_id . '" value="' . get_the_ID() . '">' . get_the_title() . '</option>';
            endwhile;
            wp_reset_postdata();
        endif;

        echo wp_kses($output, 'efbtw_kses');
        wp_die();
    }
}
