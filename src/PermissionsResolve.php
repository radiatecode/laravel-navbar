<?php


namespace RadiateCode\LaravelNavbar;


class PermissionsResolve
{
    protected $permissions = [];

    public function __construct()
    {
        $this->resolvePermissions();
    }

    public function make(): PermissionsResolve
    {
        return new self();
    }

    protected function resolvePermissions(): void
    {
        $resolver = config('navbar.permissions-resolver');

        if (class_exists($resolver)){
            $this->permissions = (new $resolver())->resolve();
        }
    }

    /**
     * @param array|string $access
     *
     * @return bool
     */
    public function hasPermission($access): bool
    {
        if (is_array($access) && is_countable($access)){ // if access param is array
            foreach ($access as $value){
                return in_array($value, $this->permissions);
            }
        }

        return in_array((string) $access, $this->permissions);
    }
}