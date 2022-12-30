<?php

namespace RadiateCode\LaravelNavbar\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Nav
{
    public function __construct(public int $serial, public string $header, public string $name, public string $icon)
    {
    }
}
