<?php


namespace RadiateCode\LaravelNavbar\Contracts;


interface PermissionsResolver
{
   public function resolve(): array;
}