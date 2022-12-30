<?php

namespace RadiateCode\LaravelNavbar\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class NavLinks
{
    public function __construct(
        public bool $condition,
        public string $title,
        public ?string $icon = null,
    ) {
    }
}
