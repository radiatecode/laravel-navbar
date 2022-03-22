<?php


namespace App\Traits;


use Exception;
use RadiateCode\LaravelNavbar\MenuPrepare;

trait HasMenu
{
    /**
     * @var MenuPrepare $menu
     */
    private $menu = null;

    private function menu(): MenuPrepare
    {
        return $this->menu = new MenuPrepare();
    }

    /**
     * @throws Exception
     */
    public function menuInstantiateException()
    {
        if (empty($this->menu)){
            throw new Exception('No menu instantiated for this controller');
        }
    }

    public function getMenuInstance(): MenuPrepare
    {
        return $this->menu;
    }
}
