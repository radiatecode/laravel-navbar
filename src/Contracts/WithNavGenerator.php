<?php


namespace RadiateCode\LaravelNavbar\Contracts;

interface WithNavGenerator
{
    public function navbarInstantiateException();

    public function getNavbarInstance();
}
