<?php

namespace Zamzamcoders\Easybundlefbt\Front;

use Zamzamcoders\Easybundlefbt\Traitval\Traitval;

/**
 * Class SizeChartProduct
 * 
 * Handles the front-end functionality for the Size Chart Manager for WooCommerce plugin.
 */
class WooFbtFrontend
{
    use Traitval;

    public $settings = [];

    /**
     * Frequently bought together main product id.
     *
     * @var string
     */
    protected $main_product_id = '';

    /**
     * Bundle ID.
     *
     * @var string
     */
    protected $bundle_id = '';

    /**
     * Subtotal bundle products price.
     *
     * @var array
     */
    protected $subtotal_products_price = array();

    /**
     * Constructor for initializing settings and hooking necessary actions.
     */
    public function __construct()
    {

        $this->efbtw_bought_together_init();
    }

    /**
     * Initialize the rendering of the product.
     *
     * This function sets up any necessary initialization for rendering the frequently bought together products.
     */
    public function efbtw_bought_together_init()
    {

        add_action('woocommerce_after_single_product_summary', array($this, 'efbtw_bought_together_display'), 5);
        add_action('wp_ajax_efbtw_update_total_price', array($this, 'efbtw_fbt_ajax_update_total_price'));
        add_action('wp_ajax_nopriv_efbtw_update_total_price', array($this, 'efbtw_fbt_ajax_update_total_price'));
    }

    function efbtw_bought_together_display()
    {

        global $product;

        if (! $product || ! is_a($product, 'WC_Product')) {
            return;
        }

        if ($product->is_type('grouped') || $product->is_type('external') || $product->get_stock_status() == 'outofstock') {
            return;
        }

        $product_id = $product->get_id();
        wc_get_template(
            'single-product/frequently-bought-together.php',
            array(
                'product_id' => $product_id,
                'instance'   => $this,
            ),
            '',
            EFBTW_PLUGIN_PATH . 'src/Front/templates/'
        );
    }


    /**
     * Get bundle ID safely.
     */
    public function get_bundle_id()
    {
        return $this->bundle_id;
    }

    public function set_bundle_id($id)
    {
        $this->bundle_id = $id;
    }

    /**
     * Add price to subtotal.
     */
    public function add_to_subtotal($old, $new)
    {
        $this->subtotal_products_price[] = [
            'old' => $old,
            'new' => $new,
        ];
    }

    /**
     * Get subtotal prices.
     */
    public function get_subtotal_prices()
    {
        return $this->subtotal_products_price;
    }

    public function reset_subtotal()
    {
        $this->subtotal_prices = [];
    }

    public function efbtw_fbt_ajax_update_total_price()
    {

        if (
            !isset($_POST['nonce']) ||
            !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'efbtw_update_total_price_nonce')
        ) {
            return;
        }


        $fragments = array();
        $total = 0;
        $regular_total = 0;
        $total_items = 0;

        if (isset($_POST['products']) && is_array($_POST['products'])) {
            $total_items = count($_POST['products']);
            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
            $raw_products = isset($_POST['products']) ? wp_unslash($_POST['products']) : [];

            $products = is_array($raw_products) ? efbtw_clean($raw_products) : [];

            foreach ($products as $data) {
                $product_id = absint($data['id']);
                $discount_value = floatval($data['discount_value']);
                $discount_type = sanitize_text_field($data['discount_type']);

                $product = wc_get_product($product_id);
                if ($product) {
                    if ($product->is_type('variation')) {
                        $price_regular = (float) $product->get_regular_price();
                        $price = (float) $product->get_price();
                    } elseif ($product->is_type('variable')) {
                        $price_regular = (float) $product->get_variation_regular_price('max');
                        $price = (float) $product->get_variation_price('max');
                    } else {
                        $price_regular = (float) $product->get_regular_price();
                        $price = (float) $product->get_price();
                    }

                    $discounted_price = efbtw_apply_discount($price, $discount_value, $discount_type);

                    $total += $discounted_price;
                    $regular_total += $price_regular;
                }
            }
        }

        $savings = $regular_total - $total;

        ob_start();
?>
        <div class="efbtw-price-info">
            <span class="efbtw-price-label">
                <?php
                // translators: %d: Total number of items.
                echo wp_kses_post(sprintf(__('Price for %d items:', 'easy-frequently-bought-together-for-woocommerce'), $total_items));
                ?>
            </span>
            <span class="efbtw-total-price">
                <?php echo wp_kses_post(wc_price($total)); ?>
                <?php if ($regular_total > $total) : ?>
                    <del><?php echo wp_kses_post(wc_price($regular_total)); ?></del>
                <?php endif; ?>
            </span>
            <?php if ($savings > 0) : ?>
                <span class="efbtw-savings">
                    <?php
                    // translators: %s: Amount saved including the currency symbol.
                    echo wp_kses_post(sprintf(__('Save %s', 'easy-frequently-bought-together-for-woocommerce'), wc_price($savings)));
                    ?>

                </span>
            <?php endif; ?>
        </div>
<?php

        $fragments['.efbtw-price-info'] = ob_get_clean();
        wp_send_json([
            'fragments' => $fragments,
        ]);
    }
}
