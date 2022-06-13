<?php


use Illuminate\Support\Facades\Auth;

class PermissionsResolver implements \RadiateCode\LaravelNavbar\Contracts\PermissionsResolver
{
    public function resolve(): array
    {
        $guard = config('auth.defaults.guard');

        if (Auth::guard($guard)->check()){
            $user = Auth::guard($guard)->user();

            $permissions = $user->role->permissions; // assume role relationship defines in the user model and there is a permissions column in the role table

            return json_decode($permissions);   // assume the permissions stored as json encoded
        }

        return [];
    }
}