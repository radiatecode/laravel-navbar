<?php


namespace RadiateCode\LaravelNavbar;


use Closure;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;
use RadiateCode\LaravelNavbar\Enums\Constant;


class Navbar
{
    private $navs = [];

    private $tempMenu = [];

    private $nav = null;

    private $tail = null;

    private $isCreatable = true;

    public static function create($name, Closure $closure): Navbar
    {
        $obj = new self();

        $closure($obj);

        return $obj;
    }

    public function add(string $title, string $icon = 'fa fa-home'): Navbar
    {
        $nav = Str::slug($title);

        $this->nav = $nav;

        $this->tail = $this->nav;

        $this->tempMenu[$nav] = [
            'icon'  => $icon,
            'title' => $title,
            'type'  => 'menu',
        ];

        return $this;
    }

    /**
     * @param  string|array  $url
     * @param  string|null  $title
     * @param  string|null  $icon
     * @param  array  $css_classes
     *
     * @return $this
     */
    public function link(
        $url,
        string $title = null,
        string $icon = null,
        array $css_classes = []
    ): Navbar {
        if ( ! $this->isCreatable) {
            return $this;
        }

        $injectTo = $this->tail.'.nav-links';

        $values = Arr::get($this->tempMenu, $injectTo);

        $url = $this->resolveUrl($url);

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

    public function linkIf(
        $condition,
        $url,
        string $title = null,
        string $icon = null,
        array $css_classes = []
    ): Navbar {
        if (is_bool($condition) && ! $condition) {
            return $this;
        }

        if (is_callable($condition) && ! $condition()) {
            return $this;
        }

        $this->link($url, $title, $icon, $css_classes);

        return $this;
    }

    public function children(string $title, string $icon = 'fa fa-home'): Navbar
    {
        if ( ! $this->isCreatable) {
            return $this;
        }

        $nav = Str::slug($title);

        $this->tail = $this->tail.'.children.'.$nav;

        Arr::set(
            $this->tempMenu,
            $this->tail,
            [
                'title' => $title,
                'icon'  => $icon,
                'type'  => 'menu',
            ]
        );

        return $this;
    }

    public function make(): bool
    {
        $this->pushNav($this->nav, $this->tempMenu[$this->nav])
            ->emptyCycle();

        return true;
    }

    public function makeIf($condition): bool
    {
        if (is_bool($condition) && ! $condition) {
            return false;
        }

        if (is_callable($condition) && ! $condition()) {
            return false;
        }

        $this->make();

        return true;
    }

    private function pushNav(string $name, array $value): Navbar
    {
        $this->navs[$name] = $value;

        return $this;
    }

    private function emptyCycle()
    {
        $this->tempMenu = [];

        $this->nav = null;

        $this->tail = null;
    }

    private function resolveUrl($url)
    {
        if (is_array($url)) {
            $resolveUrl = $url[0].'@'
                .$url[1]; // 0 index is controller, 1 index is it's method

            return URL::action($resolveUrl);
        } elseif (is_string($url) && Str::contains($url, '@')) {
            return URL::action($url);
        }

        return $url;
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
        return $this->navs;
    }
}
