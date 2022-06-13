<?php

if (!function_exists('hasPermission')){
    function hasPermission($access): bool
    {
        $permissions = [];

        $resolver = config('navbar.permissions-resolver');

        if (class_exists($resolver)){
            $permissions = (new $resolver())->resolve();
        }

        if (is_array($access) && is_countable($access)){ // if access param is array
            foreach ($access as $value){
                return in_array($value, $permissions);
            }
        }

        return in_array($access, $permissions);
    }
}