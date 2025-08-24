<?php

namespace Zamzamcoders\Easybundlefbt\Traitval;

/**
 * Traitval
 * 
 * This trait provides a singleton implementation for initializing and managing
 * certain functionalities within the Frequently Bought Together plugin.
 */
trait Traitval
{
	/**
	 * @var bool|self $singleton The singleton instance of this trait.
	 */
	private static $singleton = false;

	/**
	 * @var string $plugin_pref The prefix used for plugin-related options and settings.
	 */
	public $plugin_pref = 'efbtw_';

	/**
	 * Constructor
	 * 
	 * The private constructor prevents direct instantiation. It initializes the trait
	 * by calling the initialize method.
	 */
	private function __construct()
	{
		$this->initialize();
	}

	/**
	 * Initialize the trait
	 * 
	 * This protected method can be overridden by classes using this trait to include
	 * additional initialization code.
	 */
	protected function initialize()
	{
		// Initialization code can be added here by the class using this trait.

	}

	/**
	 * Get the Singleton Instance
	 * 
	 * This static method ensures that only one instance of the trait is created.
	 * It returns the singleton instance, creating it if it does not exist.
	 * 
	 * @return self The singleton instance of the trait.
	 */
	public static function getInstance()
	{
		if (self::$singleton === false) {
			self::$singleton = new self();
		}
		return self::$singleton;
	}

	// Utility method to check option and return checked value
	public function get_option_checked($option_name)
	{
		$option_value = get_option($option_name, true);
		return ($option_value == 'yes' || $option_value == '1' || !empty($checked_value)) ? "checked='checked'" : '';
	}

	/**
	 * Renders a multi-select field for WooCommerce products.
	 *
	 * @param string $label           Label for the field (unused).
	 * @param string $name            Name attribute of the select field.
	 * @param array  $selected_values Pre-selected product IDs.
	 */
	public function efbtw_render_select_product_field($label, $name, $selected_values)
	{

		$select_product_class = ($name == 'efbtw_select_product') ? 'cmfw-select-product-fields' : '';
?>
		<div class="cmfw-general-item <?php echo esc_attr($select_product_class); ?>">
			<div class="cmfw-gen-item-con cmfw-extra-product-fields-select">
				<select multiple name="<?php echo esc_attr($name); ?>[]" class="cmfw-select-product">
					<?php
					echo wp_kses_post(efbtw_all_product_panel_output($selected_values));
					?>
				</select>
			</div>
		</div>
	<?php
	}

	// render select categories field
	public function efbtw_render_select_product_cat_field($label, $name, $selected_values)
	{
		$select_product_class = ($name == 'efbtw_select_product_categories') ? 'cmfw-select-product-categories' : '';
	?>
		<div class="cmfw-general-item <?php echo esc_attr($select_product_class); ?>">
			<div class="cmfw-gen-item-con cmfw-extra-product-fields-select">
				<select multiple name="<?php echo esc_attr($name); ?>[]" class="cmfw-select-product">
					<?php
					efbtw_get_product_categories($selected_values);
					?>
				</select>
			</div>
		</div>
	<?php
	}

	// Method to render text input items
	public function efbtw_render_color_input($label, $name, $value)
	{
	?>
		<div class="cmfw-general-item">
			<div class="cmfw-gen-item-con">
				<label for="<?php echo esc_attr($name); ?>"><?php echo esc_html($label) ?></label>
				<input type="text" class="cmfw-section-bg" name="<?php echo esc_attr($name); ?>"
					value="<?php echo esc_attr($value); ?>">
			</div>
		</div>
	<?php
	}

	// render checkbox input field
	public function render_checkbox_item($label, $name, $checked_value = 0)
	{
	?>
		<label for="<?php echo esc_attr($name); ?>"><?php echo esc_html($label) ?></label>
		<label class="cmfw-toggle">
			<input type="checkbox" name="<?php echo esc_attr($name); ?>" <?php echo esc_attr($checked_value); ?> value="1">
			<span class="cmfw-slider"></span>
		</label>
	<?php
	}

	// render text input field
	public function render_text_input($label, $name, $value, $placeholder = '', $class = '')
	{
	?>
		<div id="cmfw-input-field-settings" class="<?php echo esc_attr($class); ?>">
			<label for="<?php echo esc_attr($name); ?>"><?php echo esc_html($label) ?></label>
			<input type="text" id="cmfw-tab-title" value="<?php echo esc_attr($value); ?>" placeholder="<?php echo esc_attr($placeholder); ?>" name="<?php echo esc_attr($name); ?>">
		</div>
	<?php
	}

	// render select field
	public function render_select_item($name, $label, $options, $class = '')
	{

	?>
		<div class="<?php echo esc_attr($class); ?>" id="cmfw-select-item-field">
			<label for="<?php echo esc_attr($name); ?>"><?php echo esc_html($label) ?></label>
			<select id="difficulty-level" name="<?php echo esc_attr($name); ?>" class="<?php echo esc_attr($class); ?>">
				<option value=""><?php echo esc_html__('select', 'easy-frequently-bought-together-for-woocommerce') ?></option>
				<?php foreach ($options as $value => $option_label) : ?>
					<option value="<?php echo esc_attr($value); ?>" <?php selected($this->options[$name], $value); ?>>
						<?php echo esc_html($option_label); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>
<?php
	}
}
