<?php


namespace RadiateCode\LaravelNavbar\Services;


use Exception;
use ReflectionClass;
use ReflectionMethod;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use RadiateCode\LaravelNavbar\Attributes\AppendableNavLinks;
use RadiateCode\LaravelNavbar\Attributes\AppendTo;
use RadiateCode\LaravelNavbar\NavBuilder;
use RadiateCode\LaravelNavbar\Enums\Constant;
use RadiateCode\LaravelNavbar\FilesFinderService;
use RadiateCode\LaravelNavbar\Attributes\Nav;
use RadiateCode\LaravelNavbar\Attributes\ParentNav;
use RadiateCode\LaravelNavbar\Attributes\NavLinks;

class NavGenerateAttributes
{
    private $tail = [];

    private $menus = [];

    private bool $isCache = false;

    public static function instance(): NavGenerateAttributes
    {
        return new self();
    }

    /**
     * @throws Exception
     */
    public function menus(): NavGenerateAttributes
    {
        // if (config('navbar.cache-enable') && $this->hasCacheNavs()) {
        //     $this->menus = $this->getCacheNavs();

        //     return $this;
        // }

        $path = config('navbar.controllers-path');

        $controllers = FilesFinderService::make()->findClasses($path);

        $this->menus = $this->navItemsGenerate($controllers);

        //$this->cacheNavs();

        return $this;
    }

    protected function navItemsGenerate($controllers)
    {
        $menus = [];

        $childrenTobeInjectInParent = [];

        $navLinksToBeAppend = [];

        foreach ($controllers as $controller) {

            $reflectionClass = new ReflectionClass($controller);

            $attributes = $reflectionClass->getAttributes();

            $nav = [];

            $navName = "";

            $hasParent = false;

            if (count($attributes) > 0) { // check is there any class level nav attributes
                foreach ($attributes as $attribute) {
                    /**
                     * -------------------------------------
                     * Nav links append to anothe nav
                     * -------------------------------------
                     */
                    if ($attribute->getName() == AppendableNavLinks::class) {
                        $this->prepareOrAppendNavLinks($menus, $navLinksToBeAppend, $reflectionClass);

                        continue;
                    }

                    $attributeInstance = $attribute->newInstance(); // make instance of the attribute

                    /**
                     * -------------------------------------
                     * Prepare nav
                     * -------------------------------------
                     */
                    if ($attribute->getName() == Nav::class) {
                        $serial = $attributeInstance->serial;

                        $header = $attributeInstance->header;

                        $navName = Str::slug($attributeInstance->name);

                        $navIcon = $attributeInstance->icon;

                        $navLinks = $this->prepareNavLinks($reflectionClass);

                        // if (empty($navLinks)) { // if no nav links then no need to prepare a nav
                        //     continue;
                        // }

                        // $this->prepareHeader($menus, $header);

                        $nav = $this->prepareNav($navName, $navIcon, $navLinks);

                        // check is there any children need to be injected in this current nav
                        if (array_key_exists($navName, $childrenTobeInjectInParent)) {
                            $nav['children'] = $childrenTobeInjectInParent[$navName];
                        }

                        continue;
                    }

                    /**
                     * Children nav
                     */
                    if ($attribute->getName() == ParentNav::class && !empty($nav)) {
                        $hasParent = true; // flag to indicate whether the prepare nav is base nav or a child nav

                        /**
                         * If parent menu is not a controller class then prepare the parent nav from the given string
                         * [Note: This non-class parent menu only works as root level menu, it will not append as child]
                         */
                        if (!class_exists('\\' . $attributeInstance->name)) {
                            $parentNavName = Str::slug($attributeInstance->name);

                            if (Arr::get($menus, $parentNavName)) {
                                continue;
                            }

                            // add nav
                            $menus[$parentNavName] = [
                                'icon'      => $attributeInstance->icon,
                                'title'     => ucwords(str_replace('-', ' ', $parentNavName)),
                                'type' => 'menu',
                                'nav-links' => []
                            ];

                            continue;
                        }

                        /**
                         * Parent nav is a controller class
                         */
                        $parentNavClass = new ReflectionClass($attributeInstance->name);

                        $attributes = $parentNavClass->getAttributes();

                        $attributeInstance = $attributes[0]->newInstance();

                        if ($attributes[0]->getName() != Nav::class) {
                            continue;
                        }

                        $parentNavName = Str::slug($attributeInstance->name);

                        // find is the parent nav already live in the $menus
                        $exist = $this->keyExists($menus, $parentNavName);

                        if ($exist) { // inject the prepared nav to parent as children nav
                            $livingTails = $this->tail();

                            // add children key to the position of the parent menu
                            $children = $livingTails . ".children." . $navName;

                            // add children nav
                            Arr::set($menus, $children, $nav);

                            continue;
                        }

                        // prepared nav need to be wait for the parent
                        $childrenTobeInjectInParent[$parentNavName][$navName] = $nav;
                    }
                }
            }

            /**
             * --------------------------
             * Base nav
             * --------------------------
             */
            if (!$hasParent && !empty($nav)) {
                $menus[$navName] = $nav;
            }

            $this->emptyTheTail();
        }

        $this->linksToBeAppend($navLinksToBeAppend,$menus);

        return $menus;
    }

    public function cache()
    {
        $this->isCache = true;

        return $this;
    }

    protected function getCacheNavs()
    {
        return Cache::get(Constant::CACHE_NAVS);
    }

    protected function hasCacheNavs(): bool
    {
        return Cache::has(Constant::CACHE_NAVS);
    }

    protected function cacheNavs()
    {
        $ttl = config('navbar.cache-time');

        $enable = config('navbar.cache-enable');

        if ($enable && !$this->hasCacheNavs()) {
            Cache::put(Constant::CACHE_NAVS, $this->menus, $ttl);
        }
    }

    /**
     * @throws Exception
     */
    public function toHtml(): string
    {
        return NavBuilder::instance()->menus($this->toArray())->build();
    }

    public function toArray(): array
    {
        return $this->menus;
    }

    /**
     * Append links to existing nav-links
     *
     * @param $navLinksToBeAppend
     * @param $menus
     */
    protected function linksToBeAppend(&$navLinksToBeAppend, &$menus)
    {
        foreach ($navLinksToBeAppend as $key => $item) {
            $exist = $this->keyExists($menus, $key);

            if ($exist) {
                // get the position of the parent menu
                $livingTails = $this->tail() . '.nav-links';

                $links = Arr::get($menus, $livingTails);

                $combinedLinks = array_merge($links, $item);

                Arr::set($menus, $livingTails, $combinedLinks);
            }

            unset($navLinksToBeAppend[$key]);
        }
    }

    protected function prepareOrAppendNavLinks(&$menus, &$navLinksToBeAppend, $reflectionClass): void
    {
        $methods = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $method) { // get the methods of the reflected class
            $attributes = $method->getAttributes(); // get the attributes of the method

            $link = [];

            foreach ($attributes as $attribute) {
                /**
                 * ----------------------------------
                 * Preparing appendanble nav-links
                 * ----------------------------------
                 */
                if ($attribute->getName() == NavLinks::class) {
                    $attributeInstance = $attribute->newInstance();

                    if (!$attributeInstance->condition) { // if condition is false then skip the nav links preparetion
                        continue;
                    }

                    $method_name = $method->getName();
                    $title = $attributeInstance->title;
                    $icon = $attributeInstance->icon;

                    $link = [
                        'link-title' => $title,
                        'link-url' => action($reflectionClass->getName() . '@' . $method_name),
                        'link-icon' => $icon ?: 'far fa-circle nav-icon',
                        'link-css-class' => [],
                    ];
                }

                /**
                 * ------------------------------------
                 * Prepared nav-links append to another nav
                 * ------------------------------------
                 */
                if ($attribute->getName() == AppendTo::class && !empty($link)) {
                    $appendToInstance = $attribute->newInstance();

                    // if appendanble nav is non-controller-class 
                    if (!class_exists('\\' . $appendToInstance->name)) {
                        $appendableNavName = Str::slug($appendToInstance->name);

                        if (Arr::get($menus, $appendableNavName)) {
                            $menus[$appendableNavName]['nav-links'][] = $link;

                            continue;
                        }

                        // when appendanble nav is not exist in $menus
                        $navLinksToBeAppend[$appendableNavName][] = $link;

                        continue;
                    }

                    // if appendanble nav is a controller class
                    $appendableNavClass = new ReflectionClass($appendToInstance->name);

                    $attributes = $appendableNavClass->getAttributes();

                    if (count($attributes) == 0) {
                        continue;
                    }

                    if ($attributes[0]->getName() != Nav::class) {
                        continue;
                    }

                    $attributeInstance = $attributes[0]->newInstance();

                    $appendableNavName = Str::slug($attributeInstance->name);

                    // find is the appendable nav already exist in the $menus
                    $exist = $this->keyExists($menus, $appendableNavName);

                    if ($exist) { // append the nav links
                        $appendPositionKey = $this->tail() . ".nav-links";

                        // append the nav-link to the menu
                        $this->arr_push($menus, $appendPositionKey, $link);

                        continue;
                    }

                    // nav links need to be wait to append
                    $navLinksToBeAppend[$appendableNavName][] = $link;
                }
            }
        }
    }

    protected function prepareHeader(&$menus, $header)
    {
        $header = Str::slug($header);

        if (!Arr::get($menus, $header)) {
            $menus[$header] = [
                'title'     => ucwords(str_replace('-', ' ', $header)),
                'type' => 'header'
            ];
        }
    }

    /**
     * @param $navLinks
     *
     * @return array
     */
    protected function prepareNav(string $navName, string $navIcon, array $navLinks): array
    {
        return [
            'icon' => $navIcon,
            'title' => ucwords(str_replace('-', ' ', $navName)),
            'type' => 'menu',
            'nav-links' => $navLinks
        ];
    }

    /**
     * @param $route
     *
     * @return array
     */
    protected function prepareNavLinks($reflectionClass): array
    {
        $methods = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);

        $links = [];

        foreach ($methods as $method) { // get the methods of the reflected class
            $attributes = $method->getAttributes(NavLinks::class); // get the attributes of the method

            foreach ($attributes as $attribute) {
                $attributeInstance = $attribute->newInstance(); // make instance of the method attribute

                if (!$attributeInstance->condition) { // if condition is false then skip the nav links preparetion
                    continue;
                }

                $method_name = $method->getName();
                $title = $attributeInstance->title;
                $icon = $attributeInstance->icon;

                $links[] = [
                    'link-title' => $title,
                    'link-url' => action($reflectionClass->getName() . '@' . $method_name),
                    'link-icon' => $icon ?: 'far fa-circle nav-icon',
                    'link-css-class' => [],
                ];
            }
        }

        return $links;
    }

    /**
     * Track the nav key depth
     * 
     * @return string
     */
    protected function tail(): string
    {
        return implode('.', array_reverse($this->tail));
    }

    protected function emptyTheTail()
    {
        $this->tail = [];
    }

    /**
     * @param  array  $arr
     * @param       $keySearch
     *
     * @return bool
     */
    private function keyExists(array $arr, $keySearch): bool
    {
        // is in base array?
        if (array_key_exists($keySearch, $arr)) {
            $this->tail[] = $keySearch;

            return true;
        }

        // check arrays contained in this array
        foreach ($arr as $key => $element) {
            if (is_array($element)) {
                if (array_key_exists('children', $element)) {
                    if ($this->keyExists($element['children'], $keySearch)) {
                        $this->tail[] = 'children';

                        $this->tail[] = $key;

                        return true;
                    }
                }
            }
        }

        return false;
    }

    private function arr_push(&$array, $key, $value)
    {
        if (is_null($key)) {
            return $array = $value;
        }

        $keys = explode('.', $key);

        foreach ($keys as $i => $key) {
            if (count($keys) === 1) {
                break;
            }

            unset($keys[$i]);

            // If the key doesn't exist at this depth, we will just create an empty array
            // to hold the next value, allowing us to create the arrays to hold final
            // values at the correct depth. Then we'll keep digging into the array.
            if (!isset($array[$key]) || !is_array($array[$key])) {
                $array[$key] = [];
            }

            $array = &$array[$key];
        }

        $array[array_shift($keys)][] = $value;

        return $array;
    }
}
