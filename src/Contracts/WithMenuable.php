<?php


namespace RadiateCode\LaravelNavbar\Contracts;

interface WithMenuable
{
    public function menuInstantiateException();

    public function getMenuInstance();
}
