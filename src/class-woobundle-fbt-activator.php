<?php

/**
 * Fired during plugin activation
 *
 * @link       https://github.com/Faridmia/frequently-bought-together
 * @since      1.0.0
 *
 * @package    Frequently Bought Together
 * @subpackage Frequently Bought Together/src
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Frequently Bought Together
 * @subpackage Frequently Bought Together/src
 * @author     Farid Mia <mdfarid7830@gmail.com>
 */
class EFBTW_fbt_Activator
{

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate()
	{
		if (!class_exists('WooCommerce')) {
			return false;
		}
	}
}
