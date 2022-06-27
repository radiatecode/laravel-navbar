<?php


namespace RadiateCode\LaravelNavbar\Traits;


use Exception;
use RadiateCode\LaravelNavbar\NavPrepare;

trait Navbar
{
    /**
     * @var NavPrepare $menu
     */
    private $nav = null;

    private function nav(): NavPrepare
    {
        return $this->nav = new NavPrepare();
    }

    /**
     * @throws Exception
     */
    public function navbarInstantiateException()
    {
        if (empty($this->nav)) {
            throw new Exception(
                'No navigation instantiated for this controller ['
                .class_basename($this).']'
            );
        }
    }

    public function getNavbarInstance(): NavPrepare
    {
        return $this->nav;
    }

    abstract public function navigation(): void;
}
