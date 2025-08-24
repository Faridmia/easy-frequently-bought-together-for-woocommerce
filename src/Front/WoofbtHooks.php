<?php

namespace Zamzamcoders\Easybundlefbt\Front;

use WC_Product;
use Zamzamcoders\Easybundlefbt\Traitval\Traitval;

/**
 * Class SizeChartProduct
 * 
 * Handles the front-end functionality for the Size Chart Manager for WooCommerce plugin.
 */
class WoofbtHooks
{
	use Traitval;

	/**
	 * Constructor for initializing settings and hooking necessary actions.
	 */
	public function __construct()
	{

		$this->efbtw_cart_hooks();
	}

	public function efbtw_cart_hooks()
	{
		add_action('wp_ajax_efbtw_purchasable_fbt_products', array($this, 'efbtw_purchasable_fbt_products'));
		add_action('wp_ajax_nopriv_efbtw_purchasable_fbt_products', array($this, 'efbtw_purchasable_fbt_products'));
		add_action('woocommerce_before_calculate_totals', array($this, 'before_calculate_totals'), 100);
		add_filter('woocommerce_cart_item_remove_link', array($this, 'cart_item_remove_link'), 10, 2);
		add_filter('woocommerce_cart_item_price', array($this, 'cart_item_price'), 10, 2);
		add_filter('woocommerce_cart_item_quantity', array($this, 'cart_item_quantity'), 10, 2);
		add_filter('woocommerce_cart_item_subtotal', array($this, 'cart_item_subtotal'), 10, 2);
		add_filter('woofbt_show_widget_cart_item_quantity', array($this, 'widget_cart_item_quantity'), 10, 2);
		add_action('woocommerce_after_cart_item_quantity_update', array($this, 'update_cart_item_quantity'), 10, 4);
		add_filter('woocommerce_order_item_name', array($this, 'cart_item_name'), 10, 2);
		add_filter('woocommerce_cart_item_name', array($this, 'cart_item_name'), 10, 2);
		add_filter('woocommerce_cart_item_class', array($this, 'cart_item_class'), 10, 3);
		add_filter('woocommerce_mini_cart_item_class', array($this, 'cart_item_class'), 10, 3);
		add_action('woocommerce_cart_item_removed', array($this, 'cart_item_removed'), 10, 2);
		add_action('woocommerce_cart_item_restored', array($this, 'restore_cart_items'), 10, 2);
		add_filter('woocommerce_get_cart_item_from_session', array($this, 'get_cart_item_from_session'), 10, 2);
	}

	public function efbtw_purchasable_fbt_products()
	{
		if (
			!isset($_POST['nonce']) ||
			!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'efbtw_update_total_price_nonce')
		) {
			return;
		}

		if (empty($_POST['products_id']) || empty($_POST['main_product']) || empty($_POST['bundle_id'])) {
			wp_send_json_error();
		}
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$raw_products_id = isset($_POST['products_id']) ? efbtw_clean(wp_unslash($_POST['products_id'])) : '';
		$products_id = $raw_products_id;

		$main_product_id = isset($_POST['main_product']) ? sanitize_text_field(wp_unslash($_POST['main_product'])) : 0;
		$bundle_id    = isset($_POST['bundle_id']) ? sanitize_text_field(wp_unslash($_POST['bundle_id'])) : '';

		$bundle_products_group = get_post_meta($bundle_id, 'efbtw_bundle_item_group', true);

		$mian_product_discount_type   = get_post_meta($bundle_id, 'efbtw_primary_discount_type', true);
		$main_product_discount        = get_post_meta($bundle_id, 'efbtw_bundle_primary_item_price', true);

		$main_product = wc_get_product($main_product_id);

		$main_product_qty          = 1;
		$main_product_variation_id = 0;
		$main_product_variation    = array();
		$item_keys                 = array();

		if (empty($products_id) || count($products_id) < 2 || !$bundle_products_group) {
			wp_send_json_error();
		}

		if ($main_product->is_type('variable')) {
			if (empty($products_id[$main_product_id])) {
				wp_send_json_error();
			}

			$main_variation_product    = wc_get_product($products_id[$main_product_id]);
			$main_product_variation_id = $main_variation_product->get_id();
			$main_product_variation    = $main_variation_product->get_variation_attributes();
		}

		$main_key_item = WC()->cart->add_to_cart(
			$main_product->get_id(),
			$main_product_qty,
			$main_product_variation_id,
			$main_product_variation,
			array(
				'efbtw_parent_id'       => $main_product->get_id(),
				'efbtw_discount'        => $main_product_discount,
				'efbtw_discount_type'   => $mian_product_discount_type,
				'efbtw_bundle_id'       => $bundle_id,
				'efbtw_keys'            => array(),
				'efbtw_product_ids'     => $products_id,
				'efbtw_bundle_modified' => get_the_modified_date('U', $bundle_id),
			)
		);

		if (!$main_key_item) {
			wp_send_json(
				array(
					'success' => false,
					'notices' => wp_print_notices(true),
				)
			);
		}

		foreach ($bundle_products_group as $fbt_product) {
			$selectproduct = isset($fbt_product['efbtw_select_product']) ? $fbt_product['efbtw_select_product'] : '';
			$product_id = apply_filters('wpml_object_id', $selectproduct, 'product', true);
			$discount_price = $fbt_product['efbtw_bundle_item_price'];
			$discount_type = isset($fbt_product['efbtw_discount_type']) ? $fbt_product['efbtw_discount_type'] : '';

			if (! isset($products_id[$product_id]) || $main_product->get_id() === (int) $product_id) {
				continue;
			}

			$product           = wc_get_product($product_id);
			$item_qty          = 1;
			$item_variation_id = 0;
			$item_variation    = array();

			if ($product->is_type('variable')) {
				if (! $products_id[$product_id]) {
					continue;
				}

				$variation_product = wc_get_product($products_id[$product_id]);
				$item_variation_id = $variation_product->get_id();
				$item_variation    = $variation_product->get_variation_attributes();
			}

			$item_keys[] = WC()->cart->add_to_cart(
				$product->get_id(),
				$item_qty,
				$item_variation_id,
				$item_variation,
				array(
					'efbtw_parent_id'   => $main_product->get_id(),
					'efbtw_discount'     => $discount_price,
					'efbtw_discount_type'   => $discount_type,
					'efbtw_bundle_id'   => $bundle_id,
					'efbtw_fbt_parent_keys' => $main_key_item,
				)
			);
		}

		if (! $item_keys || wc_notice_count('error')) {
			if (isset(WC()->cart->cart_contents[$main_key_item])) {
				unset(WC()->cart->cart_contents[$main_key_item]);
			}

			if ($item_keys) {
				foreach ($item_keys as $item_key) {
					if (isset(WC()->cart->cart_contents[$item_key])) {
						unset(WC()->cart->cart_contents[$item_key]);
					}
				}
			}

			WC()->cart->set_session();

			wp_send_json(
				array(
					'success' => false,
					'notices' => wc_print_notices(true),
				)
			);
		}

		WC()->cart->cart_contents[$main_key_item]['efbtw_keys'] = $item_keys;
		WC()->cart->set_session();

		$added_products = array_merge(
			[$main_product->get_id()],
			array_map(function ($item_key) {
				return WC()->cart->cart_contents[$item_key]['product_id'] ?? 0;
			}, $item_keys)
		);

		$added_products_objects = array_filter(array_map('wc_get_product', $added_products));

		wc_clear_notices();

		if (!empty($added_products_objects)) {
			$titles = array();
			$count = 0;

			foreach ($added_products_objects as $product) {
				if ($product instanceof WC_Product) {
					$titles[] = $product->get_name();
					$count++;
				}
			}


			$message = sprintf(
				// Translators: 1: Product titles, 2: Count of products.
				_n('%s has been added to your cart.', '%s have been added to your cart.', $count, 'easy-frequently-bought-together-for-woocommerce'),
				wc_format_list_of_items($titles)
			);

			$message .= ' <a href="' . esc_url(wc_get_cart_url()) . '" class="button wc-forward">' . esc_html__('View cart', 'easy-frequently-bought-together-for-woocommerce') . '</a>';

			wc_add_notice($message, 'success');
		}

		ob_start();
		wc_print_notices();
		$notices = ob_get_clean();

		ob_start();
		woocommerce_mini_cart();
		$mini_cart = ob_get_clean();

		$fragments = apply_filters('woocommerce_add_to_cart_fragments', [
			'div.widget_shopping_cart_content' => '<div class="widget_shopping_cart_content">' . $mini_cart . '</div>',
		]);

		$cart_hash = apply_filters('woocommerce_add_to_cart_hash', WC()->cart->get_cart_for_session() ? md5(json_encode(WC()->cart->get_cart_for_session())) : '', WC()->cart->get_cart_for_session());

		$cart_data = [
			'cart_count' => WC()->cart->get_cart_contents_count(),
			'cart_total' => WC()->cart->get_cart_total(),
			'cart_items' => WC()->cart->get_cart()
		];

		wp_send_json([
			'success'   => true,
			'notices'   => $notices,
			'fragments' => $fragments,
			'cart_hash' => $cart_hash,
			'cart_data' => $cart_data,
		]);

		die();
	}


	/**
	 * Adjust cart item prices before calculating totals.
	 *
	 * @param WC_Cart $cart Cart object.
	 * @return void
	 */
	public function before_calculate_totals($cart)
	{
		if (is_admin() && ! defined('DOING_AJAX')) {
			return;
		}

		foreach ($cart->get_cart() as $key => $cart_item) {

			if (! empty($cart_item['efbtw_fbt_parent_keys']) && empty($cart->cart_contents[$cart_item['efbtw_fbt_parent_keys']])) {
				unset($cart->cart_contents[$key]);
				WC()->cart->set_session();
				continue;
			}

			if (isset($cart_item['efbtw_bundle_id'], $cart_item['efbtw_bundle_modified'])) {
				$bundle_modified = get_the_modified_date('U', $cart_item['efbtw_bundle_id']);
				$post_status     = get_post_status($cart_item['efbtw_bundle_id']);


				if ($bundle_modified !== $cart_item['efbtw_bundle_modified'] || $post_status !== 'publish') {
					$this->update_data_bundle_product($cart_item);
				}
			}
		}

		foreach ($cart->get_cart() as $cart_item_key => $cart_item) {

			if (isset($cart_item['efbtw_discount_applied']) && $cart_item['efbtw_discount_applied']) {

				continue;
			}

			if (isset($cart_item['data'], $cart_item['efbtw_discount'], $cart_item['efbtw_discount_type'])) {

				if (! empty($cart_item['variation_id'])) {
					$variation_product = wc_get_product($cart_item['variation_id']);
					$price             = (float) $variation_product->get_price();
				} else {
					$current_product = wc_get_product($cart_item['data']->get_id());
					$price           = $current_product->get_price();
				}

				$discounted_price = efbtw_apply_discount($price, $cart_item['efbtw_discount'], $cart_item['efbtw_discount_type']);

				$discounted_price = number_format($discounted_price, 2, '.', '');

				$cart_item['data']->set_price($discounted_price);

				$cart_item['efbtw_discount_applied'] = true;
			}
		}
	}


	/**
	 * Update bundle product data in the cart.
	 *
	 * @param array $cart_item Bundle parent cart item.
	 * @return void
	 */
	private function update_data_bundle_product($cart_item)
	{

		$cart_object        = WC()->cart;
		$cart_key           = $cart_item['key'];
		$fbt_products       = get_post_meta($cart_item['efbtw_bundle_id'], 'efbtw_bundle_item_group', true);
		$main_discount      = get_post_meta($cart_item['efbtw_bundle_id'], 'efbtw_bundle_primary_item_price', true);
		$discount_type      = get_post_meta($cart_item['efbtw_bundle_id'], 'efbtw_primary_discount_type', true);
		$show_checkbox      = get_post_meta($cart_item['efbtw_bundle_id'], '_efbtw_fbtcheckboxstate', true);

		$fbt_products_count = count($fbt_products);

		if ($fbt_products) {
			foreach ($fbt_products as $key => $fbt_product) {
				if (! empty($fbt_product['efbtw_select_product'])) {
					$fbt_products[$key]['efbtw_select_product'] = apply_filters('wpml_object_id', $fbt_product['efbtw_select_product'], 'product', true);
				}
			}
		}

		$bundles_id = get_post_meta($cart_item['efbtw_parent_id'], '_efbtw_select_bundle_product', true);

		$bundles_id = array($bundles_id);

		if (in_array($cart_item['efbtw_parent_id'], array_column($fbt_products, 'efbtw_select_product'))) { //phpcs:ignore
			--$fbt_products_count;
		}

		if ('publish' !== get_post_status($cart_item['efbtw_bundle_id']) || ! $show_checkbox && $fbt_products_count !== count($cart_item['efbtw_keys']) || ! in_array($cart_item['efbtw_bundle_id'], $bundles_id)) { //phpcs:ignore
			foreach ($cart_item['efbtw_keys'] as $fbt_keys) {
				unset(WC()->cart->cart_contents[$fbt_keys]);
			}

			unset(WC()->cart->cart_contents[$cart_key]);

			WC()->cart->set_session();

			return;
		}

		if ($main_discount && $cart_item['efbtw_discount'] !== $main_discount) {
			WC()->cart->cart_contents[$cart_key]['efbtw_discount'] = $main_discount;
		}

		$fbt_products = array_combine(array_column($fbt_products, 'efbtw_select_product'), $fbt_products);


		foreach ($cart_item['efbtw_keys'] as $key) {
			if (! isset($cart_object->cart_contents[$key])) {
				continue;
			}

			$product_id   = $cart_object->cart_contents[$key]['product_id'];
			$variation_id = $cart_object->cart_contents[$key]['variation_id'];
			$discount     = 0;

			if (isset($fbt_products[$product_id]['efbtw_bundle_item_price'])) {
				$discount = (int) $fbt_products[$product_id]['efbtw_bundle_item_price'];
			} elseif (isset($fbt_products[$variation_id]['efbtw_bundle_item_price'])) {
				$discount = (int) $fbt_products[$variation_id]['efbtw_bundle_item_price'];
			}

			if (empty($fbt_products[$product_id]) && empty($fbt_products[$variation_id])) {
				foreach ($cart_item['efbtw_keys'] as $fbt_keys) {
					unset(WC()->cart->cart_contents[$fbt_keys]);
				}

				unset(WC()->cart->cart_contents[$cart_key]);

				WC()->cart->set_session();

				return;
			}

			if ((int) $cart_object->cart_contents[$key]['efbtw_discount'] !== $discount) {
				WC()->cart->cart_contents[$key]['efbtw_discount'] = $discount;
			}
		}

		WC()->cart->cart_contents[$cart_key]['efbtw_bundle_modified'] = get_the_modified_date('U', $cart_item['efbtw_bundle_id']);
		WC()->cart->set_session();
	}

	/**
	 * Update price in cart.
	 *
	 * @codeCoverageIgnore
	 * @param string $price Product price.
	 * @param array  $cart_item Product data.
	 * @return string
	 */
	public function cart_item_price($price, $cart_item)
	{

		if (isset($cart_item['efbtw_parent_id'], $cart_item['efbtw_discount']) && $cart_item['efbtw_discount']) {
			if (! empty($cart_item['variation_id'])) {
				$variation_product = wc_get_product($cart_item['variation_id']);
				$product_price     = (float) $variation_product->get_price();
			} elseif (! empty($cart_item['product_id'])) {
				$product       = wc_get_product($cart_item['product_id']);
				$product_price = (float) $product->get_price();
			} else {
				$product_price = (float) $cart_item['data']->get_price();
			}

			$new_price = apply_filters('efbtw_fbt_product_cart_price', $product_price, $cart_item);

			return wc_price(wc_get_price_to_display($cart_item['data'], array('price' => $new_price)));
		}

		return $price;
	}

	/**
	 * Update subtotal price in cart.
	 *
	 * @codeCoverageIgnore
	 * @param string $price Product price.
	 * @param array  $cart_item Product data.
	 * @return string
	 */
	public function cart_item_subtotal($price, $cart_item)
	{

		if (isset($cart_item['efbtw_parent_id'], $cart_item['efbtw_discount']) && $cart_item['efbtw_discount']) {

			if (! empty($cart_item['variation_id'])) {
				$variation_product = wc_get_product($cart_item['variation_id']);
				$product_price     = (float) $variation_product->get_price();
			} elseif (! empty($cart_item['product_id'])) {
				$product       = wc_get_product($cart_item['product_id']);
				$product_price = (float) $product->get_price();
			} else {
				$product_price = (float) $cart_item['data']->get_price();
			}

			$discount_type  = $cart_item['efbtw_discount_type'];
			$discount_value = $cart_item['efbtw_discount'];
			$new_price = efbtw_apply_discount($product_price, $discount_value, $discount_type);
			$new_price = $new_price * $cart_item['quantity'];
			$new_price = apply_filters('efbtw_product_cart_subtotal', $product_price, $cart_item);

			return wc_price(wc_get_price_to_display($cart_item['data'], array('price' => $new_price)));
		}

		return $price;
	}

	/**
	 * Cart item remove link.
	 *
	 * @param string $link Quantity content.
	 * @param string $cart_item_key Product key.
	 *
	 * @return string
	 */
	public function cart_item_remove_link($link, $cart_item_key)
	{
		$item = WC()->cart->get_cart_item($cart_item_key);

		if (isset($item['efbtw_bundle_id'], $item['efbtw_parent_id']) && $item['efbtw_parent_id'] !== $item['product_id']) {
			return '';
		}

		return $link;
	}

	/**
	 * Cart item quantity.
	 *
	 * @codeCoverageIgnore
	 * @param string $quantity Quantity content.
	 * @param string $cart_item_key Product key.
	 *
	 * @return string
	 */
	public function cart_item_quantity($quantity, $cart_item_key)
	{
		$item = WC()->cart->get_cart_item($cart_item_key);

		if (isset($item['efbtw_bundle_id'], $item['efbtw_parent_id']) && $item['efbtw_parent_id'] !== $item['product_id']) {
			return woocommerce_quantity_input(
				array(
					'input_name'   => "cart[{$cart_item_key}][qty]",
					'input_value'  => $item['quantity'],
					'product_name' => $item['data']->get_name(),
					'readonly'     => true,
				),
				$item['data'],
				false
			);
		}

		return $quantity;
	}

	/**
	 * Update bundles products quantity.
	 *
	 * @param string  $cart_item_key Item key.
	 * @param integer $quantity New quantity.
	 * @param integer $old_quantity Old quantity.
	 * @param object  $cart Cart data.
	 *
	 * @return void
	 */
	public function update_cart_item_quantity($cart_item_key, $quantity, $old_quantity, $cart)
	{
		$cart_items = $cart->cart_contents;
		$item_key   = array();

		if (! empty($cart_items[$cart_item_key]['efbtw_keys'])) {
			$item_key = $cart_items[$cart_item_key]['efbtw_keys'];
		} elseif (! empty($cart_items[$cart_item_key]['efbtw_fbt_parent_keys']) && ! empty($cart_items[$cart_items[$cart_item_key]['efbtw_fbt_parent_keys']]['efbtw_keys'])) {
			$item_key   = $cart_items[$cart_items[$cart_item_key]['efbtw_fbt_parent_keys']]['efbtw_keys'];
			$item_key[] = $cart_items[$cart_item_key]['efbtw_fbt_parent_keys'];
		}

		if ($item_key) {
			foreach ($item_key as $key) {
				$cart->cart_contents[$key]['quantity'] = $quantity;
			}
		}
	}

	/**
	 * Widget cart item quantity.
	 *
	 * @param boolean $show Show quantity.
	 * @param string  $cart_item_key Product key.
	 *
	 * @return false
	 */
	public function widget_cart_item_quantity($show, $cart_item_key)
	{
		$item = WC()->cart->get_cart_item($cart_item_key);

		if (isset($item['efbtw_bundle_id'], $item['efbtw_parent_id']) && $item['efbtw_parent_id'] !== $item['product_id']) {
			return false;
		}

		return $show;
	}

	/**
	 * Update item class cart for frequently bought together product.
	 *
	 * @param string $classes Item classes.
	 * @param array  $cart_item Product data.
	 * @param string $cart_item_key Product key.
	 *
	 * @return string
	 */
	public function cart_item_class($classes, $cart_item, $cart_item_key)
	{
		if (! empty($cart_item['efbtw_fbt_parent_keys']) && isset(WC()->cart->cart_contents[$cart_item['efbtw_fbt_parent_keys']])) {
			$parent_product = WC()->cart->cart_contents[$cart_item['efbtw_fbt_parent_keys']];

			if (! empty($parent_product['efbtw_keys']) && count($parent_product['efbtw_keys']) === array_search($cart_item_key, $parent_product['efbtw_keys'], true) + 1) {
				$classes .= ' wd-fbt-item-last';
			} else {
				$classes .= ' wd-fbt-item';
			}
		} elseif (! empty($cart_item['efbtw_keys'])) {
			$classes .= ' wd-fbt-item-first';
		}

		return $classes;
	}

	/**
	 * Update title in cart for frequently bought together product.
	 *
	 * @codeCoverageIgnore
	 * @param string $item_name Product title.
	 * @param array  $item Product data.
	 * @return string
	 */
	public function cart_item_name($item_name, $item)
	{
		if (! empty($item['efbtw_parent_id'])) {
			ob_start();

?>
			<span class="wd-cart-label wd-fbt-label wd-tooltip">
				<?php esc_html_e('Bundled product', 'easy-frequently-bought-together-for-woocommerce'); ?>
			</span>
<?php

			$item_name .= ob_get_clean();
		}

		return $item_name;
	}

	/**
	 * Remove frequently bought together products.
	 *
	 * @param string $cart_item_key Product key.
	 * @param array  $cart Cart data.
	 * @return void
	 */
	public function cart_item_removed($cart_item_key, $cart)
	{
		$cart_data = $cart->removed_cart_contents;
		$item_key  = array();

		if (! empty($cart_data[$cart_item_key]['efbtw_keys'])) {
			$item_key = $cart_data[$cart_item_key]['efbtw_keys'];
		} elseif (! empty($cart_data[$cart_item_key]['efbtw_fbt_parent_keys'])) {
			$item_key   = $cart->cart_contents[$cart_data[$cart_item_key]['efbtw_fbt_parent_keys']]['efbtw_keys'];
			$item_key[] = $cart_data[$cart_item_key]['efbtw_fbt_parent_keys'];
		}

		if ($item_key) {
			foreach ($item_key as $key) {
				$cart->removed_cart_contents[$key] = $cart->cart_contents[$key];

				unset($cart->cart_contents[$key]);
			}
		}
	}

	/**
	 * Restore cart items.
	 *
	 * @param string $cart_item_key Cart item.
	 * @param object $cart Cart data.
	 *
	 * @return void
	 */
	public function restore_cart_items($cart_item_key, $cart)
	{
		$cart_data = $cart->cart_contents;
		$item_key  = array();

		if (! empty($cart_data[$cart_item_key]['efbtw_keys'])) {
			$item_key = $cart_data[$cart_item_key]['efbtw_keys'];
		} elseif (! empty($cart_data[$cart_item_key]['efbtw_fbt_parent_keys'])) {
			$item_key   = $cart->removed_cart_contents[$cart_data[$cart_item_key]['efbtw_fbt_parent_keys']]['efbtw_keys'];
			$item_key[] = $cart_data[$cart_item_key]['efbtw_fbt_parent_keys'];
		}

		if ($item_key) {
			foreach ($item_key as $key) {
				$cart->cart_contents[$key] = $cart->removed_cart_contents[$key];
				unset($cart->removed_cart_contents[$key]);
			}
		}
	}

	/**
	 * Get item from session.
	 *
	 * @param array $cart_item Cart data.
	 * @param array $item_session_values Session cart data.
	 *
	 * @return array
	 */
	public function get_cart_item_from_session($cart_item, $item_session_values)
	{
		if (isset($item_session_values['efbtw_parent_id'])) {
			$cart_item['efbtw_parent_id'] = $item_session_values['efbtw_parent_id'];
			$cart_item['efbtw_discount']  = $item_session_values['efbtw_discount'];
			$cart_item['efbtw_bundle_id'] = $item_session_values['efbtw_bundle_id'];
			$cart_item['efbtw_discount_type'] = $item_session_values['efbtw_discount_type'];
		}

		if (isset($item_session_values['efbtw_keys'])) {
			$cart_item['efbtw_keys'] = $item_session_values['efbtw_keys'];
		}

		if (isset($item_session_values['efbtw_fbt_parent_keys'])) {
			$cart_item['efbtw_fbt_parent_keys'] = $item_session_values['efbtw_fbt_parent_keys'];
		}

		if (isset($item_session_values['efbtw_bundle_modified'])) {
			$cart_item['efbtw_bundle_modified'] = $item_session_values['efbtw_bundle_modified'];
		}

		return $cart_item;
	}
}
