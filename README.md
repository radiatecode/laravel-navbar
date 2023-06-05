# Laravel Navbar

This package generates navigation/navbar for laravel application. The package also provide a build in html navigation UI, it also allows you to build your own custom navigation UI. 


# Sample & Usages
```php
$navitems = Nav::make()
    ->header('Adminland', function (Nav $nav) {
        $nav
            ->addIf(hasPermission('role-list'), 'Roles', route('role-list'), ['icon' => 'fa fa-user-tag'])
            ->addIf(hasPermission('system-user-list'), 'Users', route('system-user-list'), ['icon' => 'fa fa-users']);
    })
    ->header('Employee Management', function (Nav $nav) {
        $nav
            ->add('Employee', '#', ['icon' => 'fa fa-user'], function (Children $children) {
                $children
                    ->add('List', route('employee-list'), ['icon' => 'fa fa-list'])
                    ->add('Create', route('create-employee'), ['icon' => 'fa fa-plus-circle']);
            })
            ->add('Transfer', '#', ['icon' => 'fa fa-money-check-alt'], function (Children $children) {
                $children
                    ->add('List', route('transfer-list'), ['icon' => 'fa fa-list'])
                    ->add('Create', route('create-transfer'), ['icon' => 'fa fa-plus-circle']);
            });
    })
    ->render(); // array of nav items

$navbar = Navbar::navs($navitems)->render(); // navbar html
```
> **Note:** You can(should) generate the navbar in the [View Composer](https://laravel.com/docs/10.x/views#view-composers)

### Navbar In View Composer Example
```php
use RadiateCode\LaravelNavbar\Nav;
use RadiateCode\LaravelNavbar\Children;
use RadiateCode\LaravelNavbar\Facades\Navbar;

class ViewServiceProvider extends ServiceProvider
{

    public function boot()
    {
        View::composer('layouts.partials._left_nav',function(View $view){
            $navitems = Nav::make()
                ->add('Roles', route('role-list'), ['icon' => 'fa fa-user-tag'])
                ->add('Users', route('system-user-list'), ['icon' => 'fa fa-users'])
                 ->add('Employee', '#', ['icon' => 'fa fa-user'], function (Children $children) {
                    $children
                        ->addif(condition: true, 'List', route('employee-list'), ['icon' => 'fa fa-list'])
                        ->addif(condition: false, 'Create', route('create-employee'), ['icon' => 'fa fa-plus-circle']);
                })
                ->render(); // array of nav items

                // Navbar UI builder
                $navbar = Navbar::navs($navitems)); 

                // Now attach the $navbar to your view.
                $view->with('navbar', $navbar->render();
        });

        // Or you can use `class based view composer`. place the Navbar generator code inside the compose().
        View::composer('layouts.partials._left_nav', NavComposer::class);
    }

}
```
In **_left_nav** partials

```html
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar Menu -->
        {!! $navbar !!}
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>
```

**Output**

![Stats](img/navbar.png)

# Requirements
- [PHP >= 7.1](https://www.php.net/)
- [Laravel 5.7|6.x|7.x|8.x|9.x](https://github.com/laravel/framework)
- [JQuery](https://jquery.com/) [Optional for custom navbar UI styling]
- [Bootstrap](https://getbootstrap.com/) [Optional for  custom navbar UI styling]

# Installation
You can install the package via composer:

    composer require radiatecode/laravel-navbar

Publish config file (optional)

    php artisan vendor:publish --provider="RadiateCode\LaravelNavbar\NavbarServiceProvider" --tag="navbar-config"

# Usage

## Nav available methods
### 1. Header : it is use to group certain nav items

Syntax:

`header(string $name, Closure $closure, array $attributes = [])` : 1st arg is the name of the header, 2nd arg is a closure to add nav items under the header, 3rd is for any extra attributes (ex: icon, class etc.) 
```php
// example
Nav::make()
->header('Adminland', function (Nav $nav) {
    // add nav items under the Adminland header
})
```
### 2. Add: add nav items
Syntax:

`add(string $title, string $url, ?array $attributes = null, ?callable $children = null)`: 1st arg name of the nav item, 2nd arg is the nav url, 3rd is for any extra attributes (ex: nav icon, classes), 4th arg is for if you want to add children nav.

```php
//Example 1
$navitems = Nav::make()
            ->add('Roles', route('role-list'), ['icon' => 'fa fa-user-tag'])
            ->add('Users', route('user-list'), ['icon' => 'fa fa-users'])
            ->render();

// Example 2: with header
$navitems = Nav::make()
        ->header('Adminland', function (Nav $nav) {
            $nav
                ->add('Roles', route('role-list'), ['icon' => 'fa fa-user-tag'])
                ->add('Users', route('system-user-list'), ['icon' => 'fa fa-users'])
                ->add('Settings', route('system-settings'), ['icon' => 'fa fa-wrench'])
        })
        ->render();
```

### 3. Add If: Conditionally add nav
Syntax:

`addIf($condition, string $title, string $url, array $attributes = [], ?callable $configure = null)`: 1st arg is the condition bool or closure return bool, 2nd name of the nav, 3rd nav url, 4th extra attributes, 5th a closure for adding children nav.
```php 
//Example 1
$navitems = Nav::make()
        ->addIf(true, 'Roles', route('role-list'), ['icon' => 'fa fa-user-tag'])
        ->addIf(false, 'Users', route('user-list'), ['icon' => 'fa fa-users'])
        ->render();

//Example 2: with header
        $navitems = Nav::make()
        ->header('Adminland', function (Nav $nav) {
            $nav
                ->addIf(true, 'Roles', route('role-list'), ['icon' => 'fa fa-user-tag'])
                ->addIf(false, 'Users', route('system-user-list'), ['icon' => 'fa fa-users'])
                ->addIf(true, 'Settings', route('system-settings'), ['icon' => 'fa fa-wrench'])
        })
        ->render();
```
### 4. Chidlren nav: you can add children navs
You have already noticed how we added children nav. We can also conditionally add children nav
```php
// Example
$navitems = Nav::make()
->header('Employee Management', function (Nav $nav) {
    $nav
        ->add('Employee', '#', ['icon' => 'fa fa-user'], function (Children $children) {
            $children
                ->add('List', route('employee-list'), ['icon' => 'fa fa-list'])
                ->add('Create', route('create-employee'), ['icon' => 'fa fa-plus-circle']);
        })
        ->add('Transfer', '#', ['icon' => 'fa fa-money-check-alt'], function (Children $children) {
            // we can also conditionally add children nav
            $children
                ->addIf(true, 'List', route('transfer-list'), ['icon' => 'fa fa-list'])
                ->addIf(true, 'Create', route('create-transfer'), ['icon' => 'fa fa-plus-circle']);
        })
})
->render();
```
### 5. Render: and the render method to get the array of nav items
```php
// render() result sample
[
    "adminland" => [ // header
        "title" => "Adminland",
        "attributes" => [],
        "type" => "header",
        "nav-items" => [ // nav items under the adminland header
            [
                "title" => "Roles",
                "url" => "http://hrp.test/role-list",
                "attributes" => [
                    "icon" => 'fa fa-user-tag'
                ],
                "is_active" => false,
                "type" => "menu",
                "children" => [] // no children
            ],
            [
                "title" => "User",
                "url" => "http://hrp.test/system-user-list",
                "attributes" => [
                    "icon" => 'fa fa-users'
                ],
                "is_active" => false,
                "type" => "menu",
                "children" => [] // no children
            ]
        ]
    ],
    "employee-management"  => [ // header
        "title" => "Employee Management",
        "attributes" => [],
        "type" => "header", 
        "nav-items" => [ // nav items under the employee managment
            [
                "title" => "Employee", // parent nav
                "url" => "#",
                "attributes" => [
                    "icon" => 'fa fa-user'
                ],
                "is_active" => false,
                "type" => "menu",
                "children" => [ // children nav items of employee nav
                    "nav-items" => [
                        [
                            "title" => "List",
                            "url" => "http://hrp.test/employee-list",
                            "attributes" => [
                                "icon" => 'fa fa-list'
                            ],
                            "is_active" => false,
                            "type" => "menu",
                            "children" => []
                        ],
                        [
                            "title" => "Create",
                            "url" => "http://hrp.test/create-employee",
                            "attributes" => [
                                "icon" => 'fa fa-plus-circle'
                            ],
                            "is_active" => false,
                            "type" => "menu",
                            "children" => []
                        ]
                    ]
                ]
            ]
        ]
    ],
    "nav-items" => [] // nav items without header will be append here
]
```
## Navbar UI Builder
`Laravel-Navbar` provide a built in navbar UI builder so that you can easily integrate the UI with your app.
> Note: You can built your own custom Navbar UI by defining custom [Navbar Presenter](#navbar-presenter). Or, you can comes up with your own approch to show navbar.

**Example:** see the view composer [example](#navbar-in-view-composer-example)

### Methods
Available methods of the builder
- `navs(array $navItems)` : generated nav items
- `render()` : Render the html
- `navActiveScript()` : Nav active script usefull if you want to active the current nav item in the front-end by Js(JQuery). It has another benefit, if you cache the generated navbar this script will help you to active your current nav because the back-end active function only active once before cache, after cached it always show that same active nav. So it is **recommended** if you want to cache your navbar you should disable back-end `nav-active` from the [Config](#Config) and use this script in the front-end.
    ```php
    // Example of nav active script
    $navbar = Navbar::navs($navitems); 

    $view->with('navbar', $navbar->render())
         ->with('navScript',$navbar->navActiveScript());
    ```
    ```html
    <!-- assume you have layouts.partials._left_nav.blade.php -->

    <div class="sidebar">
        <!-- Sidebar Menu -->
        {!! $navbar !!}
        <!-- /.sidebar-menu -->
    </div>

    <!-- Note: We assume you have @stack('js') in your template layout-->
    @prepend('js')
        {!! $navScript !!}
    @endprepend
    <!-- ./ end Js-->
    ```
    Or, you can add it to you script partials
    ```html
    <!-- assume: you have layouts.partials._script.blade.php -->

    {!! RadiateCode\LaravelNavbar\Facades\Navbar::navActiveScript(); !!}

    <script>
        // other js code
    </script>
    ```
## Navbar Presenter
Navbar presenter is nothing but a class which contain some functionality to generate navbar html. Under the hood the [Navbar builder](#navbar-ui-builder) use this presenter. You can use your own custom presenter. If you use custom presenter make sure you have add it in your [Navbar Config](#config)

## Config
```php
 /**
 * Presenter for navbar style
 * 
 * [HTML presenter]
 */
'nav-presenter' => NavbarPresenter::class,

/**
 * It will set active to requested/current nav
 * 
 * [Note: if you want to set nav active by front-end (Js/Jquery) 
 * Or, if you cached your rendered navbar, then you should disable it]
 */
'enable-nav-active' => true
```

## Contributing
Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security
If you discover any security related issues, please email [radiate126@gmail.com](mailto:radiate126@gmail.com) instead of using the issue tracker. 

## Credits
- [Noor Alam](https://github.com/radiatecode)
- [All Contributors](https://github.com/radiatecode/laravel-route-permission/contributors)


## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

