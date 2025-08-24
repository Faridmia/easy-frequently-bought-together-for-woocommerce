<?php

namespace Zamzamcoders\Easybundlefbt\Common\PostType;

use Zamzamcoders\Easybundlefbt\Traitval\Traitval;

/**
 * Class PostType
 * 
 * Handles the creation of a custom post type for the Frequently Bought Together plugin.
 */
class PostType
{
    use Traitval;

    /**
     * @var string $post_type The name of the custom post type
     */
    private $post_type = 'easyfbt_bundle';

    /**
     * @var array $labels The labels for the post type
     */
    private $labels = [];

    /**
     * @var array $args The arguments for the post type
     */
    private $args = [];

    /**
     * Initializes the class and creates the custom post type.
     */
    protected function initialize()
    {
        $this->set_labels();
        $this->set_args();

        add_action('init', [$this, 'register_custom_post_type']);

        // disable gutenberg for this post type
        add_filter('gutenberg_can_edit_post_type', [$this, 'efbtw_gutenberg_can_edit_post_type'], 10, 2);
        add_filter('use_block_editor_for_post_type', [$this, 'efbtw_gutenberg_can_edit_post_type'], 10, 2);
    }

    /**
     * Sets the labels for the custom post type.
     */
    public function set_labels()
    {
        $this->labels = [
            'name'                  => esc_html__('Fbt Bought Together', 'easy-frequently-bought-together-for-woocommerce'),
            'singular_name'         => esc_html__('Fbt Bought Together', 'easy-frequently-bought-together-for-woocommerce'),
            'menu_name'             => esc_html__('Fbt Bought Together', 'easy-frequently-bought-together-for-woocommerce'),
            'name_admin_bar'        => esc_html__('Fbt Bought Together', 'easy-frequently-bought-together-for-woocommerce'),
            'archives'              => esc_html__('Fbt Bought Together Archives', 'easy-frequently-bought-together-for-woocommerce'),
            'attributes'            => esc_html__('Fbt Bought Together Attributes', 'easy-frequently-bought-together-for-woocommerce'),
            'parent_item_colon'     => esc_html__('Parent Item:', 'easy-frequently-bought-together-for-woocommerce'),
            'all_items'             => esc_html__('Fbt Bought Togethers', 'easy-frequently-bought-together-for-woocommerce'),
            'add_new_item'          => esc_html__('Add New Fbt Bought Together', 'easy-frequently-bought-together-for-woocommerce'),
            'add_new'               => esc_html__('Add New', 'easy-frequently-bought-together-for-woocommerce'),
            'new_item'              => esc_html__('New Fbt Bought Together', 'easy-frequently-bought-together-for-woocommerce'),
            'edit_item'             => esc_html__('Edit Fbt Bought Together', 'easy-frequently-bought-together-for-woocommerce'),
            'update_item'           => esc_html__('Update Fbt Bought Together', 'easy-frequently-bought-together-for-woocommerce'),
            'view_item'             => esc_html__('View Fbt Bought Together', 'easy-frequently-bought-together-for-woocommerce'),
            'view_items'            => esc_html__('View Fbt Bought Together', 'easy-frequently-bought-together-for-woocommerce'),
            'search_items'          => esc_html__('Search Fbt Bought Together', 'easy-frequently-bought-together-for-woocommerce'),
            'not_found'             => esc_html__('Not found', 'easy-frequently-bought-together-for-woocommerce'),
            'not_found_in_trash'    => esc_html__('Not found in Trash', 'easy-frequently-bought-together-for-woocommerce'),
            'insert_into_item'      => esc_html__('Insert into Fbt Bought Together', 'easy-frequently-bought-together-for-woocommerce'),
            'uploaded_to_this_item' => esc_html__('Uploaded to this Size Chart', 'easy-frequently-bought-together-for-woocommerce'),
            'items_list'            => esc_html__('Fbt Bought Together', 'easy-frequently-bought-together-for-woocommerce'),
            'items_list_navigation' => esc_html__('Fbt Bought Togethers navigation', 'easy-frequently-bought-together-for-woocommerce'),
            'filter_items_list'     => esc_html__('Filter from Fbt Bought Together', 'easy-frequently-bought-together-for-woocommerce'),
        ];
    }

    /**
     * Sets the arguments for the custom post type.
     */
    public function set_args()
    {
        $this->args = [
            'label'               => esc_html__('Fbt Bought Together', 'easy-frequently-bought-together-for-woocommerce'),
            'description'         => esc_html__('Fbt Bought Together', 'easy-frequently-bought-together-for-woocommerce'),
            'labels'              => $this->labels,
            'supports'            => array('title', 'editor', 'revisions'),
            'hierarchical'        => false,
            'public'              => false,
            'show_ui'             => true,
            'show_in_menu'        => false,
            'show_in_admin_bar'   => true,
            'show_in_nav_menus'   => false,
            'can_export'          => true,
            'has_archive'         => false,
            'rewrite'             => array(
                'slug'       => 'easyfbt_bundle',
                'pages'      => false,
                'with_front' => true,
                'feeds'      => false,
            ),
            'query_var'           => true,
            'exclude_from_search' => true,
            'publicly_queryable'  => false,
            'capability_type'     => 'page',
            'show_in_rest'        => true,
            'rest_base'           => $this->post_type,
            'menu_icon'           => 'dashicons-chart-bar',
        ];
    }

    public function efbtw_gutenberg_can_edit_post_type($can_edit, $post_type)
    {

        $edit_post = $this->post_type == 'easyfbt_bundle' ? false : $can_edit;
        return $edit_post;
    }

    /**
     * Registers the custom post type.
     */
    public function register_custom_post_type()
    {
        register_post_type($this->post_type, $this->args);
    }

    /**
     * Flushes rewrite rules upon theme activation.
     */
    public function flush_rewrite_rules()
    {
        $this->register_custom_post_type();
        flush_rewrite_rules();
    }
}
