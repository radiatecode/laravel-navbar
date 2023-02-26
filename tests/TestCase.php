<?php

namespace RadiateCode\LaravelNavbar\tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use RadiateCode\LaravelNavbar\NavbarServiceProvider;

class TestCase extends OrchestraTestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [NavbarServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {

    }
}
