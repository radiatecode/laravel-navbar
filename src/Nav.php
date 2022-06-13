<?php


namespace RadiateCode\LaravelNavbar;


use Closure;
use Exception;
use Illuminate\Support\Arr;


class Nav
{
    private $menus = [];

    private $tempMenu = [];

    private $nav = null;

    private $tail = null;

    public static function create($name, Closure $closure): Nav
    {
        $obj = self::new();

        $closure($obj);

        return $obj;
    }

    public static function new(): Nav
    {
        return new self();
    }

    public function add(string $title, string $icon = 'fa fa-home'): Nav
    {
        $nav = strtolower($title);

        $this->nav = $nav;

        $this->tail = $this->nav;

        $this->tempMenu[$nav] = [
            'icon'  => $icon,
            'title' => $title,
        ];

        return $this;
    }

    public function link(
        string $url,
        string $title = null,
        string $icon = null,
        array $css_classes = []
    ): Nav {
        $injectTo = $this->tail.'.nav-links';

        $values = Arr::get($this->tempMenu, $injectTo);

        $links = [
            'link-title'     => $title ?: $this->resolveLinkTitle($url),
            'link-url'       => $url,
            'link-icon'      => $icon ?: 'far fa-circle nav-icon',
            'link-css-class' => $css_classes,
        ];

        if ($values == null) {
            Arr::set($this->tempMenu, $injectTo, [$links]);
        } else {
            array_push($values, $links);

            Arr::set($this->tempMenu, $injectTo, $values);
        }

        return $this;
    }

    public function children(string $title, string $icon = 'fa fa-home'): Nav
    {
        $nav = strtolower($title);

        $this->tail = $this->tail.'.children.'.$nav;

        Arr::set(
            $this->tempMenu,
            $this->tail,
            [
                'icon'  => $icon,
                'title' => $title,
            ]
        );

        return $this;
    }

    public function make()
    {
        $this->pushMenu($this->nav, $this->tempMenu[$this->nav]);

        $this->tempMenu = [];

        $this->nav = null;
    }

    private function pushMenu(string $name, array $value)
    {
        $this->menus[$name] = $value;
    }

    private function resolveLinkTitle(string $url): string
    {
        $name = app('router')->getRoutes()->match(
            app('request')->create($url, 'GET')
        )->getName();

        return ucwords(str_replace('.', ' ', $name));
    }


    /**
     * @throws Exception
     */
    public function toHtml(): string
    {
        return NavBuilder::instance()->menus($this->toArray())->build();
    }

    public function toArray(): array
    {
        return $this->menus;
    }
}
