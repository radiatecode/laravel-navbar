# Laravel Navbar

This package generate navigation bar for laravel application. In two ways we can generate navbar. One is defining navigation during route definition, it means define the navigation in the controller. Other one is defining the navigation using `Navbar::class`


## Sample
Define the navigation in the controller
```php
use RadiateCode\LaravelNavbar\Traits\Navbar;
use RadiateCode\LaravelNavbar\Contracts\WithNavbar;

class OfficeController extends Controller implements WithNavbar
{
    use Navbar;

    public function navigation()
    {
        $this->nav()
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
   
    public function navigation()
    {
        $this->nav()
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
- [JQuery](https://jquery.com/) [Optional]
- [Bootstrap](https://getbootstrap.com/) [Optional when you use custom navbar styling]

# Installation
You can install the package via composer:

    composer require radiatecode/laravel-navbar

Publish config file (optional)

    php artisan vendor:publish --provider="RadiateCode\LaravelNavbar\NavbarServiceProvider" --tag="navbar-config"

# Usage

## Controller Basis

To create navigation by route definiation implement the **WithNavbar** Interface and use the **Navbar** trait in controller. Define the menu in the `navigation()` by using `$this->nav()`. This will provide some methods to prepare the nav items.

```php
use RadiateCode\LaravelNavbar\Contracts\WithNavbar;
use RadiateCode\LaravelNavbar\Traits\Navbar;

class ExampleController extends Controller implements WithNavbar
{
    use Navbar;
   
    public function navigation(): void
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

- `addNavLink(string $method_name,string $title = null,string $icon = null,array $css_classes = [])` : Define the method name which will work as a nav item link. Defined method must be a route associative method. `$title` param is optional if no title pass the package generate a title from the name of **route**.

- `addNavLinkIf($condition,string $method_name,string $title = null,string $icon = null,array $css_classes = [])` : Conditionally create a nav link. Under the hood the method implement the `addNavLink()` if certain condition is true.

- `childOf(string $name,string $icon = 'fa fa-circle')` : Define the parent menu. If the menu is a child of another menu then define that parent menu. We can pass controler name as a parent menu or we can pass just string name as a parent menu. See [example]()

- `createIf($condition)` : If certain condition is true then create the navigation with nav links.

## Render Menu

To rendered the menus you can use `MenuService` class.

    NavService::instance()->menus()->toHtml();

Or

    NavService::instance()->menus()->toArray();

> `toHtml()` return built-in html navbar, which is built top of the **bootstrap navbar**. But you can modify the style of the navbar by defining a custom **Nav Presenter** class.


## Contributing
Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security
If you discover any security related issues, please email [radiate126@gmail.com](mailto:radiate126@gmail.com) instead of using the issue tracker. 

## Credits
- [Noor Alam](https://github.com/radiatecode)
- [All Contributors](https://github.com/radiatecode/laravel-route-permission/contributors)


## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

