<?php

namespace RadiateCode\LaravelNavbar;

class Children
{
    protected array $navs = [];

    protected Nav $nav;

    public function __construct()
    {
        $this->nav = new Nav();
    }

    public function add(string $title, string $url, array $attributes = [], ?callable $configure = null): Children
    {
        $this->nav->add($title, $url, $attributes, $configure);

        return $this;
    }

    public function addIf($condition, string $title, string $url, array $attributes = [], ?callable $configure = null): Children
    {
        $condition = is_callable($condition) ? $condition() : $condition;

        if ($condition) {
            $this->add($title, $url, $attributes, $configure);
        }

        return $this;
    }

    public function render()
    {
        return $this->nav->render();
    }
}
