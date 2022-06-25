<?php


namespace RadiateCode\LaravelNavbar\Traits;


use Exception;
use RadiateCode\LaravelNavbar\NavPrepare;

trait Navbar
{
    /**
     * @var NavPrepare $menu
     */
    private $menu = null;

    private function navigation(): NavPrepare
    {
        return $this->menu = new NavPrepare();
    }

    /**
     * @throws Exception
     */
    public function navbarInstantiateException()
    {
        if (empty($this->menu)){
            throw new Exception('No menu instantiated for this controller');
        }
    }

    public function getNavbarInstance(): NavPrepare
    {
        return $this->menu;
    }
}
