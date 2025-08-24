<?php

namespace Zamzamcoders\Easybundlefbt\Admin;

use Zamzamcoders\Easybundlefbt\Admin\Metaboxes\Metaboxes;
use Zamzamcoders\Easybundlefbt\Admin\Menu\Menu;
use Zamzamcoders\Easybundlefbt\Admin\Woopaneltab\Woopaneltab;
use Zamzamcoders\Easybundlefbt\Traitval\Traitval;
use Zamzamcoders\Easybundlefbt\Admin\AdminPanel\AdminPanel;

/**
 * Class Admin
 * 
 * This class uses the Traitval trait to implement singleton functionality and
 * provides methods for initializing the admin menu and other admin-related features
 * within the Frequently Bought Together plugin.
 */
class Admin
{
    use Traitval;

    /**
     * @var Menu $menu_instance An instance of the Menu class.
     */
    protected $metabox_instance;
    protected $menu_instance;
    protected $admin_panel_instance;
    protected $woo_panel_tab;

    /**
     * Initialize the class
     * 
     * This method overrides the initialize method from the Traitval trait.
     * It sets up the necessary classes and features for the admin area.
     */
    protected function initialize()
    {

        $this->define_classes();
    }

    /**
     * Define Classes
     * 
     * This method initializes the classes used in the admin area, specifically the
     * Menu class, and assigns an instance of it to the $menu_instance property.
     */
    private function define_classes()
    {
        $this->metabox_instance     = new Metaboxes();
        $this->admin_panel_instance = new AdminPanel();
        $this->menu_instance        = new Menu();
        $this->woo_panel_tab        = new Woopaneltab();
    }
}
