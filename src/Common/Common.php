<?php

namespace Zamzamcoders\Easybundlefbt\Common;

use Zamzamcoders\Easybundlefbt\Common\Posttype\Posttype;
use Zamzamcoders\Easybundlefbt\Traitval\Traitval;

/**
 * Class Common
 * 
 * This class uses the Traitval trait to implement singleton functionality and
 * provides initialization for post types within the Frequently Bought Together plugin.
 */
class Common
{
    use Traitval;

    /**
     * @var Posttype $posttypes_instance An instance of the Posttype class.
     */
    public $posttypes_instance;

    /**
     * Initialize the class
     * 
     * This method overrides the initialize method from the Traitval trait.
     * It sets up the necessary hooks for the class.
     */
    protected function initialize()
    {
        $this->init_hooks();
    }

    /**
     * Initialize Hooks
     * 
     * This method initializes hooks and assigns an instance of the Posttype class
     * to the $posttypes_instance property.
     */
    public function init_hooks()
    {
        $this->posttypes_instance = Posttype::getInstance();
    }
}
