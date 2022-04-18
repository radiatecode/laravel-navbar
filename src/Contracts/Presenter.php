<?php


namespace RadiateCode\LaravelNavbar\Contracts;


interface Presenter
{
    public function openNavTag(string $class = null, array $attributes = []): string;

    public function closeNavTag(): string;

    public function openNavULTag(string $class = null, array $attributes = []): string;

    public function closeNavULTag(): string;

    public function header(string $title, string $class = null, array $attributes = []): string;

    public function nav($menu): string;
}