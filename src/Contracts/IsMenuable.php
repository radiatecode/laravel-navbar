<?php


namespace RadiateCode\LaravelNavbar\Contracts;

interface IsMenuable
{
    public function menuInstantiateException();

    public function getMenuInstance();
}
