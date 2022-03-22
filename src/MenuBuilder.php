<?php


namespace RadiateCode\LaravelNavbar;


use Exception;
use Illuminate\Support\Arr;
use RadiateCode\LaravelNavbar\Presenter\MenuBarPresenter;

class MenuBuilder
{
    private $menus = [];

    /**
     * @var MenuBarPresenter $presenter
     */
    private $presenter = MenuBarPresenter::class;

    /**
     * @throws Exception
     */
    public function build(): string
    {
        $presenter = $this->getPresenter();

        $this->resolveMenus();

        $html = $presenter->openNavTag()
            . $presenter->openNavULTag();

        foreach ($this->menus as $key => $menu) {
            $html .= $presenter->nav($menu);
        }

        $html .= $presenter->closeNavULTag()
            . $presenter->closeNavTag();

        return $html;
    }

    public function injectMenus(array $menus,string $key = null): MenuBuilder
    {
        if ($key){
            Arr::set($this->menus,$key,$menus);

            return $this;
        }

        $this->menus = array_merge($this->menus,$menus);

        return $this;
    }

    /**
     * @throws Exception
     */
    private function resolveMenus()
    {
        $service = new MenuService();

        $this->menus = $service->getMenus();
    }

    private function getPresenter(): MenuBarPresenter
    {
        return new $this->presenter();
    }
}
