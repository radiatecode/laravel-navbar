<?php

namespace RadiateCode\LaravelNavbar\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class AppendTo
{
    public function __construct(public string $name)
    {
    }
}
