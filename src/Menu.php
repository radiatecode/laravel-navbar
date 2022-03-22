<?php


namespace RadiateCode\LaravelNavbar;


use Closure;
use Exception;
use Illuminate\Support\Arr;


class Menu
{
    private $menus = [];

    private $tempMenu = [];

    private $nav = null;

    private $tail = null;

    public static function create($name, Closure $closure): Menu
    {
        $obj = self::new();

        $closure($obj);

        return $obj;
    }

    public static function new(): Menu
    {
        return new self();
    }

    public function add(string $title,string $icon = 'fa fa-home'): Menu
    {
        $nav = strtolower($title);

        $this->nav = $nav;

        $this->tail = $this->nav;

        $this->tempMenu[$nav] = [
            'icon' => $icon,
            'title' => $title
        ];

        return $this;
    }

    public function link(string $url, string $title = null,string $icon = null,array $css_classes = []): Menu
    {
        $injectTo = $this->tail . '.nav-links';

        $values = Arr::get($this->tempMenu, $injectTo);

        $links = [
            'link-title' => $this->resolveLinkTitle($url,$title),
            'link-url' => $url,
            'link-icon' => $icon,
            'link-css-class' => $css_classes,
        ];

        if ($values == null) {
            Arr::set($this->tempMenu, $injectTo, [$links]);
        } else {
            array_push($values,$links);

            Arr::set($this->tempMenu, $injectTo, $values);
        }

        return $this;
    }

    public function children(string $title,string $icon = 'fa fa-home'): Menu
    {
        $nav = strtolower($title);

        $this->tail = $this->tail . '.children.' . $nav;

        Arr::set($this->tempMenu, $this->tail, [
                'icon' => $icon,
                'title' => $title
            ]
        );

        return $this;
    }

    public function make(){
        $this->pushMenu($this->nav, $this->tempMenu[$this->nav]);

        $this->tempMenu = [];

        $this->nav = null;
    }

    private function pushMenu(string $name,array $value){
        $this->menus[$name] = $value;
    }

    private function resolveLinkTitle(string $url,string $title = null): string
    {
        if (empty($title)){
            $name = app('router')->getRoutes()->match(app('request')->create($url,'GET'))->getName();

            $title = ucwords(str_replace('.', ' ', $name));
        }

        return $title;
    }

    /**
     * @throws Exception
     */
    public function toHtml(): string
    {
        return (new MenuBuilder())->build();
    }

    public function toArray(): array
    {
        return $this->menus;
    }
}
