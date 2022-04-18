<?php


namespace RadiateCode\LaravelNavbar\Contracts;


interface MenuPrepare
{
    public function getMenu(): array;

    public function getParent(): string;

    public function hasParent(): bool;

    public function hasInconsistencyInAppend(): bool;

    public function getLinks(): array;

    public function isAppendable(): bool;

    public function getAppends(): array;
}
