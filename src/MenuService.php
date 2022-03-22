<?php


namespace RadiateCode\LaravelNavbar;


use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;
use RadiateCode\LaravelNavbar\Contracts\IsMenuable;

class MenuService
{
    private $tail = [];

    private $currentMenuInstance = null;

    private $currentControllerMethod = null;

    private $currentMenuLinks = [];

    /**
     * @throws Exception
     */
    public function getMenus(): array
    {
        $routes = Route::getRoutes();

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

            $currentControllerInstance = app('\\' . $currentController);

            if (!$currentControllerInstance instanceof IsMenuable) {
                continue;
            }

            $currentControllerInstance->menuInstantiateException();

            $this->currentControllerMethod = $actionExtract[1];

            $this->currentMenuInstance = $currentControllerInstance->getMenuInstance();

            $this->currentMenuLinks = $this->currentMenuInstance->getLinks();

            if (!in_array($this->currentControllerMethod, array_keys($this->currentMenuLinks))) {
                continue;
            }

            // when a link need to append into a menu's nav-links
            if ($this->currentMenuInstance->isAppendable()) {
                $this->prepareOrAppendLinks($menus, $navLinksTobeInject, $route);

                continue;
            }

            $currentMenu = strtolower($this->currentMenuInstance->getMenu());

            // CHECK IS THE CURRENT MENU HAS ANY PARENT MENU in other words IS THE CURRENT MENU IS CHILD OF ANOTHER MENU
            if ($this->currentMenuInstance->hasParent()) {
               $this->prepareChildrenMenu($menus,$childrenTobeInjectInParent, $currentMenu, $route);
            } // IF NO CHILD OF CLASS THEN ADD MENU AS BASE
            else {
                $preparedMenu = $this->prepareMenu($currentMenu, $this->prepareNavLink($route));

                $menus[$currentMenu] = $preparedMenu[$currentMenu];

                if (array_key_exists($currentMenu, $childrenTobeInjectInParent)) {
                    $menus[$currentMenu]['children'] = $childrenTobeInjectInParent[$currentMenu]['children'];
                }
            }

            $this->emptyTheTail();
        }

        // check is there any nav-links waiting to be inject the current menu
        $this->appendLinks($navLinksTobeInject, $menus);

        return $menus;
    }

    /**
     * @param $menus
     * @param $childrenTobeInjectInParent
     * @param $currentMenu
     * @param $route
     *
     * @return bool
     */
    public function prepareChildrenMenu(&$menus, &$childrenTobeInjectInParent, $currentMenu, $route): bool
    {
        $parentControllerInstance = app('\\' . $this->currentMenuInstance->getParent());

        if (!$parentControllerInstance instanceof IsMenuable) {
            return false;
        }

        $parentControllerInstance->menuInstantiateException();

        $parentMenuInstance = $parentControllerInstance->getMenuInstance();

        $parentMenu = strtolower($parentMenuInstance->getMenu());
        // find is the parent menu of the current menu already live in the $menus
        $exist = $this->keyExists($menus, $parentMenu);

        // if parent menu is not exist store it prepared children menu to $childrenTobeInjectInParent
        // so that when parent menu appear we can inject the children menu
        if (! $exist){
            $navLinks = $this->prepareNavLink($route);

            $preparedMenu = $this->prepareMenu($currentMenu, $navLinks);

            // check is there any children need to be injected in this current currentMenu
            if (array_key_exists($currentMenu, $childrenTobeInjectInParent)) {
                $preparedMenu[$currentMenu]['children'] = $childrenTobeInjectInParent[$currentMenu]['children'];
            }

            // if the parent currentMenu does not exist in the menus then save this child currentMenu in $childrenTobeInjectInParent
            // so that when the parent currentMenu will be added we can injects it's children
            $navLinksOfChildren = $parentMenu . '.children.' . $currentMenu . '.nav-links';

            if (Arr::get($childrenTobeInjectInParent, $navLinksOfChildren)) {
                $this->arr_push($childrenTobeInjectInParent, $navLinksOfChildren, $navLinks);
            } else {
                $childrenTobeInjectInParent[$parentMenu]['children'][$currentMenu] = $preparedMenu[$currentMenu];
            }

            return true;
        }

        /**
         * if parent menu is exist then add children menus
         */

        // get the position of the parent menu
        $livingTails = $this->tail();

        // add children key to the position of the parent menu
        $children = $livingTails . ".children";

        // prepare the current menu array
        $navLinks = $this->prepareNavLink($route);

        $preparedMenu = $this->prepareMenu($currentMenu, $navLinks);

        // check is there any children need to be injected in this parent menu
        if (array_key_exists($currentMenu, $childrenTobeInjectInParent)) {
            $preparedMenu[$currentMenu]['children'] = $childrenTobeInjectInParent[$currentMenu]['children'];
        }

        $navLinksOfChildren = $children . '.' . $currentMenu . '.nav-links';

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
    public function prepareOrAppendLinks(&$menus, &$navLinksTobeInject, $route)
    {
        if ($this->currentMenuInstance->hasInconsistencyInAppend()) {
            throw new Exception(
                'Inconsistency occurred between links and append to!'
            );
        }

        $appendControllerInstance = $this->makeAppendControllerInstance();

        if (!$appendControllerInstance instanceof IsMenuable) {
            return null;
        }

        $appendControllerInstance->menuInstantiateException();

        $appendMenuInstance = $appendControllerInstance->getMenuInstance();

        $appendMenu = strtolower($appendMenuInstance->getMenu());

        // find is the append menu already live in the $menus
        $exist = $this->keyExists($menus, $appendMenu);

        if ($exist) {
            // get the position of the append menu
            $livingTails = $this->tail();

            // add nav-links key to the position of the append menu
            $navLinks = $livingTails . ".nav-links";

            // prepare the appended link
            $navLink = $this->prepareNavLink($route);

            // add the nav-link to the menu
            $this->arr_push($menus, $navLinks, $navLink);
        } else {
            $navLink = $this->prepareNavLink($route);

            // store the nav link so that it can be inject latter when it's menu appear
            $navLinksTobeInject[$appendMenu]['nav-links'][] = $navLink;
        }

        $this->emptyTheTail();
    }

    /**
     * Append links to existing nav-links
     *
     * @param $navLinksTobeInject
     * @param $menus
     */
    public function appendLinks(&$navLinksTobeInject, &$menus)
    {
        foreach ($navLinksTobeInject as $key => $item) {
            $exist = $this->keyExists($menus, $key);

            if ($exist) {
                // get the position of the parent menu
                $livingTails = $this->tail() . '.nav-links';

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
    public function prepareMenu($menu, $navLinks): array
    {
        $menuPreparation = [];

        $menuPreparation[$menu]['icon'] = 'fa fa-home';

        $menuPreparation[$menu]['title'] = ucfirst($menu);

        $menuPreparation[$menu]['nav-links'][] = $navLinks;

        return $menuPreparation;
    }

    /**
     * @param $route
     *
     * @return array
     */
    public function prepareNavLink($route): array
    {
        $title = $this->currentMenuLinks[$this->currentControllerMethod]['link-title'];

        return [
            'link-title' => $title ?: ucwords(str_replace('.', ' ', $route->getName())),
            'link-url' => $route->uri(),
            'link-icon' => $this->currentMenuLinks[$this->currentControllerMethod]['link-icon'],
            'link-css-class' => $this->currentMenuLinks[$this->currentControllerMethod]['link-css-class'],
        ];
    }

    /**
     * @param array $arr
     * @param       $keySearch
     *
     * @return bool
     */
    public function keyExists(array $arr, $keySearch): bool
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
     * @return string
     */
    public function tail(): string
    {
        return implode('.', array_reverse($this->tail));
    }

    public function emptyTheTail()
    {
        $this->tail = [];
    }

    /**
     * @return Application|mixed
     */
    private function makeAppendControllerInstance()
    {
        $appendMenus = $this->currentMenuInstance->getAppends();

        $position = array_search(
            $this->currentControllerMethod, array_keys($this->currentMenuLinks)
        );

        $appendTo = $appendMenus[$position];

        return app('\\' . $appendTo);
    }

    /**
     * @param $array
     * @param $key
     * @param $value
     *
     * @return array|mixed
     */
    public function arr_push(&$array, $key, $value)
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
