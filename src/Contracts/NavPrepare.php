<?php


namespace RadiateCode\LaravelNavbar\Contracts;


interface NavPrepare
{
    public function getNav(): array;

    public function getParent(): array;

    public function hasParent(): bool;

    public function hasInconsistencyInAppend(): bool;

    public function getNavLinks(): array;

    public function isAppendable(): bool;

    public function getAppends(): array;

    public function hasHeader(): bool;

    public function getHeader(): array;

    public function getMenuPermissions(): array;
}
