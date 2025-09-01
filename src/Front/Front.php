<?php

namespace Zamzamcoders\Easybundlefbt\Front;

use Zamzamcoders\Easybundlefbt\Traitval\Traitval;
use Zamzamcoders\Easybundlefbt\Front\WooFbtFrontend;
use Zamzamcoders\Easybundlefbt\Front\WoofbtHooks;


/**
 * Class Front
 * 
 * Handles the front-end functionality for the Frequently Bought Together plugin.
 */
class Front
{
    use Traitval;

    /**
     * @var Options $options_instance An instance of the Options class.
     */
    protected $woobundle_fbt_instance;
    protected $woobundle_fbt_hook;

    /**
     * Initialize the class
     */
    protected function initialize()
    {
        $this->woobundle_fbt_instance = WooFbtFrontend::getInstance();
        $this->woobundle_fbt_hook     = WoofbtHooks::getInstance();

        add_action('wp_head', [$this, 'easy_add_generate_custom_css']);
    }

    /**
     * Add custom CSS to the front-end
     */
    public function easy_add_generate_custom_css()
    {


        $settings = get_option('efbtw_global_settings', []);
        $formBgColor           = isset($settings['formBgColor']) ? $settings['formBgColor'] : '';
        $btnBg                   = isset($settings['btnBg']) ? $settings['btnBg'] : '';
        $btnBgHover                   = isset($settings['btnBgHover']) ? $settings['btnBgHover'] : '';
        $btnText                   = isset($settings['btnText']) ? $settings['btnText'] : '';
        $btnTextHover                   = isset($settings['btnTextHover']) ? $settings['btnTextHover'] : '';

        $styles = [
            ".efbtw-product-bundle-wrapper" => [
                'background-color' => $formBgColor,
            ],
            ".easyefbtw-add-to-cart-btn" => [
                'background-color' => $btnBg,
                'color' => $btnText,
            ],
            ".easyefbtw-add-to-cart-btn:hover" => [
                'background-color' =>  $btnBgHover,
                'color' => $btnTextHover,
            ],
        ];

        $custom_style = $this->generate_css($styles);

        if (!empty($custom_style)) {
            wp_register_style('easy_custom_css_global_options', false, array(), EFBTW_VERSION);
            wp_enqueue_style('easy_custom_css_global_options');
            wp_add_inline_style('easy_custom_css_global_options', $custom_style);
        }
    }

    /**
     * Generate custom CSS from styles array
     *
     * @param array $styles Array of CSS rules and values.
     * @return string Generated CSS.
     */
    private function generate_css(array $styles)
    {
        $css = '';

        foreach ($styles as $selector => $properties) {
            $css .= esc_html($selector) . ' {';

            foreach ($properties as $property => $value) {
                if ($value !== '') {
                    $css .= esc_html($property) . ': ' . esc_html(wp_strip_all_tags($value)) . '; ';
                }
            }

            $css .= '} ';
        }

        return $css;
    }
}
