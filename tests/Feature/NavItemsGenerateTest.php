<?php

namespace RadiateCode\LaravelNavbar\tests\Feature;

use RadiateCode\LaravelNavbar\Nav;
use RadiateCode\LaravelNavbar\Children;
use RadiateCode\LaravelNavbar\Facades\Navbar;
use RadiateCode\LaravelNavbar\tests\TestCase;

class NavItemsGenerateTest extends TestCase
{
    // run test by vendor/bin/phpunit --testsuite Feature
    // or vendor/phpunit/phpunit/phpunit --testsuite Feature

    /** @test */
    public function make_nav_items()
    {
        $navitems = Nav::make()
            ->header('Adminland', function (Nav $nav) {
                $nav
                    ->addIf(true, 'Roles', 'http://hrp.test/role-list', ['icon' => 'fa fa-user-tag'])
                    ->addIf(true, 'User', 'http://hrp.test/system-user-list', ['icon' => 'fa fa-users']);
            })
            ->header('Settings', function (Nav $nav) {
                $nav
                    ->add('Organisation', '#', ['icon' => 'fa fa-wrench'], function (Children $children) {
                        $children
                            ->add('Profile', 'http://hrp.test/view-organisation-profile', ['icon' => 'fa fa-address-card'])
                            ->add('Offices', 'http://hrp.test/office-list', ['icon' => 'fa fa-landmark'])
                            ->add('Project', 'http://hrp.test/project-list', ['icon' => 'fa fa-project-diagram'])
                            ->add('Departments', 'http://hrp.test/department-list', ['icon' => 'fa fa-building'])
                            ->add('Designations', 'http://hrp.test/designation-list', ['icon' => 'fa fa-user-tag']);
                    })
                    ->add('Rules', '#', ['icon' => 'fa fa-wrench'], function (Children $children) {
                        $children
                            ->add('Bonus Rule', '#', ['icon' => 'fa fa-money-bill-wave'], function (Children $children) {
                                $children
                                    ->addIf(true, 'List', 'http://hrp.test/bonus-rule-list', ['icon' => 'fa fa-list'])
                                    ->addIf(true, 'Create', 'http://hrp.test/create-bonus-rule', ['icon' => 'fa fa-plus-circle']);;
                            })
                            ->add('Pay Grade', '#', ['icon' => 'fa fa-money-check-alt'], function (Children $children) {
                                $children
                                    ->addIf(true, 'List', 'http://hrp.test/monthly-pay-grade-list', ['icon' => 'fa fa-list'])
                                    ->addIf(false, 'Create', 'http://hrp.test/create-monthly-pay-grade', ['icon' => 'fa fa-plus-circle']);
                            });
                    });
            })
            ->header('Employee Management', function (Nav $nav) {
                $nav
                    ->add('Employee', '#', ['icon' => 'fa fa-user'], function (Children $children) {
                        $children
                            ->addIf(true, 'List', 'http://hrp.test/employee-list', ['icon' => 'fa fa-list'])
                            ->addIf(true, 'Create', 'http://hrp.test/create-employee', ['icon' => 'fa fa-plus-circle']);
                    })
                    ->add('Transfer', '#', ['icon' => 'fa fa-money-check-alt'], function (Children $children) {
                        $children
                            ->addIf(true, 'List', 'http://hrp.test/transfer-list', ['icon' => 'fa fa-list'])
                            ->addIf(true, 'Create', 'http://hrp.test/create-transfer', ['icon' => 'fa fa-plus-circle']);
                    });
            })
            ->header('Salary Management', function (Nav $nav) {
                $nav
                    ->add('Salary', '#', ['icon' => 'fa fa-money-bill-wave'], function (Children $children) {
                        $children
                            ->addIf(true, 'List', 'http://hrp.test/salary-list', ['icon' => 'fa fa-list'])
                            ->addIf(true, 'Create', 'http://hrp.test/create-salary', ['icon' => 'fa fa-plus-circle']);
                    })
                    
                    ->add('Bonus', '#', ['icon' => 'fa fa-money-bill-wave'], function (Children $children) {
                        $children
                            ->addIf(true, 'List', 'http://hrp.test/bonus-list', ['icon' => 'fa fa-list'])
                            ->addIf(true, 'Create', 'http://hrp.test/create-bonus', ['icon' => 'fa fa-plus-circle']);;
                    })
                    ->add('Advance', '#', ['icon' => 'fa fa-money-bill-wave'], function (Children $children) {
                        $children
                            ->addIf(true, 'List', 'http://hrp.test/advance-list', ['icon' => 'fa fa-list'])
                            ->addIf(true, 'Create', 'http://hrp.test/create-advance', ['icon' => 'fa fa-plus-circle']);;
                    });
            })
            ->header('Reports', function (Nav $nav) {
                $nav
                    ->add('Statement', '#', ['icon' => 'fa fa-circle'], function (Children $children) {
                        $children
                            ->add('Financial Year', 'http://hrp.test/financial-year-salary-statement', ['icon' => 'fa fa-money-check'])
                            ->add('Salary Statment', 'http://hrp.test/employee-salary-statement', ['icon' => 'fa fa-money-check'])
                            ->add('Bonus Statment', 'http://hrp.test/employee-bonus-statement', ['icon' => 'fa fa-money-check']);
                    })
                    ->add('Salary Sheet', 'http://hrp.test/generate-salary-sheet', ['icon' => 'fa fa-money-check'])
                    ->add('Bonus Sheet', 'http://hrp.test/generate-bonus-sheet', ['icon' => 'fa fa-money-check'])
                    ->add('Remuneration Sheet', '#', ['icon' => 'fa fa-money-check'], function (Children $children) {
                        $children
                            ->add('Cash Sheet', 'http://hrp.test/generate-cash-remuneration-sheet', ['icon' => 'fa fa-circle'])
                            ->add('Bank Sheet', 'http://hrp.test/generate-bank-remuneration-sheet', ['icon' => 'fa fa-circle']);
                    });
            })
            ->add('Remuneration', '#', ['icon' => 'fas fa-money-bills'], function (Children $children) {
                $children
                    ->addIf(true, 'Cash Paygrade', 'http://hrp.test/remuneration-cash-payment-list', ['icon' => 'fa fa-list'])
                    ->addIf(true, 'Cash Remuneration', 'http://hrp.test/cash-remuneration-list', ['icon' => 'fa fa-list'])
                    ->addIf(true, 'Bank Remuneration', 'http://hrp.test/bank-remuneration-list', ['icon' => 'fa fa-list']);
            })
            ->render();
        
        $navbar = Navbar::navs($navitems);

        $html = $navbar->render();
        $script = $navbar->navActiveScript();

        //dd($nav, $navbar);
        $this->assertStringContainsString('<nav class="mt-2">', $html);
        $this->assertStringContainsString('<script type="text/javascript">', $script);
        $this->assertIsArray($navitems);
        $this->assertArrayHasKey('adminland', $navitems);
        $this->assertNotEmpty($navitems['nav-items'], 'Some of the nav items doesn\'t have header');
    }
}
