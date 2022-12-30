<?php

namespace RadiateCode\LaravelNavbar\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class ParentNav
{
    public function __construct(public string $name, public string $icon = 'fa fa-home')
    {
    }
}
