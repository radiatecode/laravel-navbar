<?php


namespace RadiateCode\LaravelNavbar;


use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Contracts\Foundation\Application;
use RadiateCode\LaravelNavbar\Contracts\WithMenuable;

class MenuService
{
    private $tail = [];

    private $menus = [];

    private $currentMenuInstance = null;

    private $currentControllerMethod = null;

    private $currentMenuLinks = [];

    private const MENU_CACHE_KEY = 'laravel-navbar';

    private const MENU_COUNT_CACHE_KEY = 'laravel-navbar-count';

    public static function instance(): MenuService
    {
        return new self();
    }

    /**
     * @throws Exception
     */
    public function menus(): MenuService
    {
        $routes = Route::getRoutes();

        $routesCount = count($routes);

        $cacheRoutes = $this->getCacheMenus($routesCount);

        if ($cacheRoutes !== null) {
            $this->menus = $cacheRoutes;

            return $this;
        }

        $menus = [];

        $childrenTobeInjectInParent = [];

        $navLinksTobeInject = [];

        foreach ($routes as $route) {
            $actionName = $route->getActionName();

            $actionExtract = explode('@', $actionName);

            $currentController = $actionExtract[0];

            if ($currentController == 'Closure') {
                continue;
            }

            $currentControllerInstance = app('\\'.$currentController);

            if ( ! $currentControllerInstance instanceof WithMenuable) {
                continue;
            }

            $currentControllerInstance->menuInstantiateException();

            $this->currentControllerMethod = $actionExtract[1]; // $route->getActionMethod()

            $this->currentMenuInstance = $currentControllerInstance->getMenuInstance();

            $this->currentMenuLinks = $this->currentMenuInstance->getLinks();

            if ( ! in_array($this->currentControllerMethod, array_keys($this->currentMenuLinks))) {
                continue;
            }

            /**
             * -------------------------------------------------------
             * Current menu is appendable to another menu
             * -------------------------------------------------------
             */
            if ($this->currentMenuInstance->isAppendable()) {
                $this->prepareOrAppendLinks($menus, $navLinksTobeInject, $route);

                continue;
            }

            $currentMenu = $this->currentMenuInstance->getMenu();

            /**
             * ------------------------------------------------------
             * Current menu is children of another menu
             * ------------------------------------------------------
             */
            if ($this->currentMenuInstance->hasParent()) {
                $this->prepareChildrenMenu($menus, $childrenTobeInjectInParent, $currentMenu, $route);
            }

            /**
             * ----------------------------------------------------
             * Current menu is a base menu
             * -----------------------------------------------------
             */
            else {

                $currentMenuName = $currentMenu['name'];

                if (Arr::get($menus, $currentMenuName.'.nav-links')) {
                    $this->arr_push($menus,$currentMenuName.'.nav-links',$this->prepareNavLink($route));
                }else{
                    $preparedMenu = $this->prepareMenu($currentMenu, $this->prepareNavLink($route));

                    $menus[$currentMenuName] = $preparedMenu[$currentMenuName];
                }

                if (array_key_exists($currentMenuName, $childrenTobeInjectInParent)) {
                    $menus[$currentMenuName]['children'] = $childrenTobeInjectInParent[$currentMenuName]['children'];
                }
            }

            $this->emptyTheTail();
        }

        // check is there any nav-links waiting to be inject in the menus
        $this->linksToBeAppend($navLinksTobeInject, $menus);

        $this->menus = $menus;

        $this->cacheMenus($routesCount,$this->menus);

        return $this;
    }

    protected function getCacheMenus(int $routesCount)
    {
        if ($routesCount !== Cache::get(self::MENU_COUNT_CACHE_KEY)) {
            Cache::forget(self::MENU_COUNT_CACHE_KEY);

            Cache::forget(self::MENU_CACHE_KEY);

            return null;
        }

        return Cache::get(self::MENU_CACHE_KEY);
    }

    protected function cacheMenus(int $routesCount, $menus)
    {
        $ttl = config('navbar.cache-time');

        if (! Cache::has(self::MENU_COUNT_CACHE_KEY)) {
            Cache::put(self::MENU_COUNT_CACHE_KEY, $routesCount, $ttl);

            Cache::put(self::MENU_CACHE_KEY, $menus, $ttl);
        }
    }

    /**
     * @throws Exception
     */
    public function toHtml(): string
    {
        return MenuBuilder::instance()->menus($this->toArray())->build();
    }

    public function toArray(): array
    {
        return $this->menus;
    }

    /**
     * @param $menus
     * @param $childrenTobeInjectInParent
     * @param $currentMenu
     * @param $route
     *
     * @return bool
     */
    protected function prepareChildrenMenu(&$menus, &$childrenTobeInjectInParent, $currentMenu, $route): bool
    {
        $parentControllerInstance = app('\\'.$this->currentMenuInstance->getParent());

        if ( ! $parentControllerInstance instanceof WithMenuable) {
            return false;
        }

        $parentControllerInstance->menuInstantiateException();

        $parentMenuInstance = $parentControllerInstance->getMenuInstance();

        $parentMenu = $parentMenuInstance->getMenu();

        $currentMenuName = $currentMenu['name'];

        $parentMenuName = $parentMenu['name'];

        // find is the parent menu already live in the $menus
        $exist = $this->keyExists($menus, $parentMenuName);

        // if parent menu is not exist temporarily store it to $childrenTobeInjectInParent
        // so that when parent menu appear we can inject the children menu
        if ( ! $exist) {
            $navLinks = $this->prepareNavLink($route);

            $preparedMenu = $this->prepareMenu($currentMenu, $navLinks);

            // check is there any children need to be injected in this current currentMenu
            if (array_key_exists($currentMenuName, $childrenTobeInjectInParent)) {
                $preparedMenu[$currentMenuName]['children'] = $childrenTobeInjectInParent[$currentMenuName]['children'];
            }

            // parent menu save to $childrenTobeInjectInParent
            // so that when the parent menu will appear we can injects it's children
            $navLinksOfChildren = $parentMenuName.'.children.'.$currentMenuName.'.nav-links';

            if (Arr::get($childrenTobeInjectInParent, $navLinksOfChildren)) {
                $this->arr_push($childrenTobeInjectInParent, $navLinksOfChildren, $navLinks);
            } else {
                $childrenTobeInjectInParent[$parentMenuName]['children'][$currentMenuName] = $preparedMenu[$currentMenuName];
            }

            return true;
        }

        /**
         * if parent menu is exist then add children menus
         */

        // get the position of the parent menu
        $livingTails = $this->tail();

        // add children key to the position of the parent menu
        $children = $livingTails.".children";

        // prepare the current menu array
        $navLinks = $this->prepareNavLink($route);

        $preparedMenu = $this->prepareMenu($currentMenu, $navLinks);

        // check is there any children need to be injected in this parent menu
        if (array_key_exists($currentMenuName, $childrenTobeInjectInParent)) {
            $preparedMenu[$currentMenuName]['children']
                = $childrenTobeInjectInParent[$currentMenuName]['children'];
        }

        $navLinksOfChildren = $children.'.'.$currentMenuName.'.nav-links';

        if (Arr::get($menus, $children)) { // if nav links of children of parent exist
            // then add only a nav link to existing children's nav-links
            $this->arr_push($menus, $navLinksOfChildren, $navLinks);
        } else { // if children not exist then add whole prepared menu as children to parent menu
            Arr::set($menus, $children, $preparedMenu);
        }

        return true;
    }

    /**
     * Links append to existing nav-links
     * Or prepare links to be append later
     *
     * @throws Exception
     */
    protected function prepareOrAppendLinks(&$menus, &$navLinksTobeInject, $route) {
        if ($this->currentMenuInstance->hasInconsistencyInAppend()) {
            throw new Exception(
                'Inconsistency occurred between links and append to!'
            );
        }

        $appendControllerInstance = $this->makeAppendControllerInstance();

        if ( ! $appendControllerInstance instanceof WithMenuable) {
            return null;
        }

        $appendControllerInstance->menuInstantiateException();

        $appendMenuInstance = $appendControllerInstance->getMenuInstance();

        $appendMenu = $appendMenuInstance->getMenu();

        $appendMenuName = $appendMenu['name'];

        // find is the append menu already live in the $menus
        $exist = $this->keyExists($menus, $appendMenuName);

        /**
         * If append to menu exist in the menus then push it to the correct menu links
         * If not found then temporary store it for later use
         */
        if ($exist) {
            // get the position of the append menu
            $livingTails = $this->tail();

            // add nav-links key to the position of the append menu
            $navLinks = $livingTails.".nav-links";

            // prepare the appended link
            $navLink = $this->prepareNavLink($route);

            // add the nav-link to the menu
            $this->arr_push($menus, $navLinks, $navLink);
        } else {
            $navLink = $this->prepareNavLink($route);

            // store the nav link so that it can be inject latter when it's menu appear
            $navLinksTobeInject[$appendMenuName]['nav-links'][] = $navLink;
        }

        $this->emptyTheTail();
    }

    /**
     * Append links to existing nav-links
     *
     * @param $navLinksTobeInject
     * @param $menus
     */
    protected function linksToBeAppend(&$navLinksTobeInject, &$menus)
    {
        foreach ($navLinksTobeInject as $key => $item) {
            $exist = $this->keyExists($menus, $key);

            if ($exist) {
                // get the position of the parent menu
                $livingTails = $this->tail().'.nav-links';

                $links = Arr::get($menus, $livingTails);

                $combinedLinks = array_merge($links, $item['nav-links']);

                Arr::set($menus, $livingTails, $combinedLinks);
            }

            unset($navLinksTobeInject[$key]);
        }
    }

    /**
     * @param $menu
     * @param $navLinks
     *
     * @return array
     */
    protected function prepareMenu($menu, $navLinks): array
    {
        $menuPreparation = [];

        $menuName = $menu['name'];

        $menuPreparation[$menuName]['icon'] = $menu['icon'];

        $menuPreparation[$menuName]['title'] = ucwords(
            str_replace('-', ' ', $menuName)
        );

        $menuPreparation[$menuName]['nav-links'][] = $navLinks;

        return $menuPreparation;
    }

    /**
     * @param $route
     *
     * @return array
     */
    protected function prepareNavLink($route): array
    {
        $title = $this->currentMenuLinks[$this->currentControllerMethod]['link-title'];

        return [
            'link-title' => $title ?: ucwords(str_replace('.', ' ', $route->getName())),
            'link-url' => url($route->uri()),
            'link-icon' => $this->currentMenuLinks[$this->currentControllerMethod]['link-icon'],
            'link-css-class' => $this->currentMenuLinks[$this->currentControllerMethod]['link-css-class'],
        ];
    }

    /**
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
     * @return Application|mixed
     */
    protected function makeAppendControllerInstance()
    {
        $appendMenus = $this->currentMenuInstance->getAppends();

        $position = array_search(
            $this->currentControllerMethod,
            array_keys($this->currentMenuLinks)
        );

        $appendTo = $appendMenus[$position];

        return app('\\'.$appendTo);
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

    /**
     * @param $array
     * @param $key
     * @param $value
     *
     * @return array|mixed
     */
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
            if ( ! isset($array[$key]) || ! is_array($array[$key])) {
                $array[$key] = [];
            }

            $array = &$array[$key];
        }

        $array[array_shift($keys)][] = $value;

        return $array;
    }
}
