<?php


namespace RadiateCode\LaravelNavbar;


use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Str;
use RadiateCode\LaravelNavbar\Contracts\WithNavGenerator;

class NavService
{
    private $tail = [];

    private $menus = [];

    private $currentMenuInstance = null;

    private $currentControllerMethod = null;

    private $currentMenuLinks = [];

    private $permissions = [];

    private const MENU_CACHE_KEY = 'laravel-navbar';

    private const MENU_COUNT_CACHE_KEY = 'laravel-navbar-count';

    public function __construct()
    {
        $resolver = config('navbar.permissions-resolver');

        if (class_exists($resolver)){
            $this->permissions = (new $resolver())->resolve();
        }
    }


    public static function instance(): NavService
    {
        return new self();
    }

    /**
     * @throws Exception
     */
    public function menus(): NavService
    {
        $routes = Route::getRoutes();

        $routesCount = count($routes);

        $cacheRoutes = $this->getCacheMenus($routesCount);

        if (config('navbar.cache-enable') && $cacheRoutes !== null) {
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

            if ( ! $currentControllerInstance instanceof WithNavGenerator) {
                continue;
            }

            $currentControllerInstance->navbarInstantiateException();

            $this->currentControllerMethod = $actionExtract[1]; // $route->getActionMethod()

            $this->currentMenuInstance = $currentControllerInstance->getNavbarInstance();

            $currentMenuPermissions = $this->currentMenuInstance->getMenuPermissions();

            if (!empty($currentMenuPermissions) && !$this->hasPermission($currentMenuPermissions)){
                continue;
            }

            if($this->currentMenuInstance->hasHeader()){
                $this->prepareHeader($menus);
            }

            $this->currentMenuLinks = $this->currentMenuInstance->getNavLinks();

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

            $currentMenu = $this->currentMenuInstance->getNav();

            /**
             * ------------------------------------------------------
             * Current menu is a children of another menu
             * ------------------------------------------------------
             */
            if ($this->currentMenuInstance->hasParent()) {
                $this->prepareChildrenNav($menus, $childrenTobeInjectInParent, $currentMenu, $route);
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
                    $preparedMenu = $this->prepareNav($currentMenu, $this->prepareNavLink($route));

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

        $enable = config('navbar.cache-enable');

        if ($enable && ! Cache::has(self::MENU_COUNT_CACHE_KEY)) {
            Cache::put(self::MENU_COUNT_CACHE_KEY, $routesCount, $ttl);

            Cache::put(self::MENU_CACHE_KEY, $menus, $ttl);
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

    protected function prepareHeader(&$menus){
        $header = $this->currentMenuInstance->getHeader();

        if (! Arr::get($menus, $header['name'])) {
            $menus[$header['name']] = [
                 'title'     => ucwords(str_replace('-', ' ', $header['name'])),
                 'type' => $header['type']
            ];
        }
    }

    /**
     * @param $menus
     * @param $childrenTobeInjectInParent
     * @param $currentMenu
     * @param $route
     *
     * @return bool
     */
    protected function prepareChildrenNav(&$menus, &$childrenTobeInjectInParent, $currentMenu, $route): bool
    {
        $parent = $this->currentMenuInstance->getParent();

        /**
         * If parent menu is not a controller class then prepare the parent menu from the given string
         * [Note: This non-class parent menu only works as root level menu, it will not append as child]
         */
        if (! class_exists('\\'.$parent['name'])){
            $parentMenuName = Str::slug($parent['name']);

            if (! Arr::get($menus, $parentMenuName)) {
                $menus[$parentMenuName] = [
                    'icon'      => $parent['icon'],
                    'title'     => ucwords(str_replace('-', ' ', $parentMenuName)),
                    'type' => $parent['type'],
                    'nav-links' => []
                ];
            }
        }

        /**
         * If the parent menu is a controller then get the menu info from the controller
         */
        
        else
        {
            $parentControllerInstance = app('\\'.$parent['name']);

            if ( ! $parentControllerInstance instanceof WithNavGenerator) {
                return false;
            }

            $parentControllerInstance->navbarInstantiateException();

            $parentMenuInstance = $parentControllerInstance->getNavbarInstance();

            $parentMenuName = $parentMenuInstance->getNav()['name'];
        }

        $currentMenuName = $currentMenu['name'];

        // find is the parent menu already live in the $menus
        $exist = $this->keyExists($menus, $parentMenuName);

        // if parent menu is not exist temporarily store it to $childrenTobeInjectInParent
        // so that when parent menu appear we can inject the children menu
        if ( ! $exist) {
            $navLinks = $this->prepareNavLink($route);

            $preparedMenu = $this->prepareNav($currentMenu, $navLinks);

            // check is there any children need to be injected in this current menu
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

        $preparedMenu = $this->prepareNav($currentMenu, $navLinks);

        // check is there any children need to be injected in this current menu
        if (array_key_exists($currentMenuName, $childrenTobeInjectInParent)) {
            $preparedMenu[$currentMenuName]['children'] = $childrenTobeInjectInParent[$currentMenuName]['children'];
        }

        $childrenMenu = $children.'.'.$currentMenuName;

        $navLinksOfChildren = $childrenMenu.'.nav-links';

        if (Arr::get($menus, $childrenMenu)) { // if children menu exist
            // then add only a nav link to the nav-links of that children menu
            $this->arr_push($menus, $navLinksOfChildren, $navLinks);
        } else { // if children not exist then add whole prepared menu as children
            Arr::set($menus, $childrenMenu, $preparedMenu[$currentMenuName]);
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

        if ( ! $appendControllerInstance instanceof WithNavGenerator) {
            return null;
        }

        $appendControllerInstance->navbarInstantiateException();

        $appendMenuInstance = $appendControllerInstance->getNavbarInstance();

        $appendMenu = $appendMenuInstance->getNav();

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
    protected function prepareNav($menu, $navLinks): array
    {
        $menuPreparation = [];

        $menuName = $menu['name'];

        $menuPreparation[$menuName]['icon'] = $menu['icon'];

        $menuPreparation[$menuName]['title'] = ucwords(
            str_replace('-', ' ', $menuName)
        );

        $menuPreparation[$menuName]['type'] = $menu['type'];

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

    /**
     * @param $access
     *
     * @return bool
     */
    private function hasPermission($access): bool
    {
        if (is_array($access) && is_countable($access)){ // if access param is array
            foreach ($access as $value){
                return in_array($value, $this->permissions);
            }
        }

        return in_array($access, $this->permissions);
    }
}
