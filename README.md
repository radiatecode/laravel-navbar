# Laravel Navbar

This package generate navigation bar for laravel application. In two ways we can generate navbar. Manually define menus, Or define menus during route defination, it means we can define the menus by controller basis.


## Sample

```php
use RadiateCode\LaravelNavbar\Traits\Navbar;
use RadiateCode\LaravelNavbar\Contracts\WithNavbar;

class OfficeController extends Controller implements WithNavbar
{
    use Navbar;

    public function __construct()
    {
        $this->menu()
            ->addNav('Offices','fa fa-landmark')
            ->addNavLink('index') // route associate method
            ->childOf('Meta','fa fa-wrench');
    }


    public function index(){
        // code
    }

}

.............

class ProjectController extends Controller implements WithNavbar
{
   
    public function __construct()
    {
        $this->menu()
            ->addNav('Project','fa fa-project-diagram')
            ->addNavLink('create','New','fa fa-plus-circle')
            ->addNavLink('index','List','fa fa-list');
    }

    public function index(){
        // code
    }

    public function create(){
        // code
    }

}

```
Generate navigation bar using view composer

```php
use RadiateCode\LaravelNavbar\NavService;

class ViewServiceProvider extends ServiceProvider
{

    public function boot()
    {
        View::composer('layouts.partials._left_nav',function(View $view){
            $view->with('navbar', NavService::instance()->menus()->toHtml())
        });
    }

}

```
In **_left_nav** partials

```html
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar Menu -->
        {!! $menu !!}
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
- [JQuery](https://jquery.com/) [Optional]
- [Bootstrap](https://getbootstrap.com/) [Optional when you use custom navbar styling]

# Installation
You can install the package via composer:

    composer require radiatecode/laravel-navbar

Publish config file (optional)

    php artisan vendor:publish --provider="RadiateCode\LaravelNavbar\NavbarServiceProvider" --tag="navbar-config"

# Usage

## Controller Basis

To create menu by route definiation implement the **WithMenuable** Interface and use the **Manuable** trait in controller. Define the menu in the **construct** by using `$this->menu()`. This will provide some methods to prepare the menu items.

```php
use RadiateCode\LaravelNavbar\Contracts\WithNavbar;
use RadiateCode\LaravelNavbar\Traits\Navbar;

class ExampleController extends Controller implements WithNavbar
{
    use Navbar;
   
    public function __construct()
    {
        $this->menu()
            ->addNav('ManuName','fa fa-project-diagram')
            ->addNavLink('create','New','fa fa-plus-circle')
            ->addNavLink('index','List','fa fa-list');
    }

}
```
### Methods

- `addHeader(string $name)` : Add header for navbar

- `addNav(string $name, string $icon = 'fa fa-home')` : Define the menu name

- `addNavLink(string $method_name,string $title = null,string $icon = null,array $css_classes = [])` : Define the method name which will work as a menu item link. Defined method must be a route associative method. `$title` param is optional if no title pass the package generate a title from the name of method associate **route**.

- `childOf(string $name,string $icon = 'fa fa-circle')` : Define the parent menu. If the menu is a child of another menu then define that parent menu. We can pass controler name as a parent menu or we can pass just string name as a parent menu. See [example]()

## Render Menu

To rendered the menus you can use `MenuService` class.

    MenuService::instance()->menus()->toHtml();

Or

    MenuService::instance()->menus()->toArray();

> `toHtml()` return built-in html navbar, which is built top of the **bootstrap navbar**. But you can modify the style of the navbar by defining a custom **Menu Presenter** class.


## Contributing
Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security
If you discover any security related issues, please email [radiate126@gmail.com](mailto:radiate126@gmail.com) instead of using the issue tracker. 

## Credits
- [Noor Alam](https://github.com/radiatecode)
- [All Contributors](https://github.com/radiatecode/laravel-route-permission/contributors)


## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

