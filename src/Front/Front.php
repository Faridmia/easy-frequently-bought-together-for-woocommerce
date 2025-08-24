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
    }
}
