<?php

use App\Models\Role;
use App\Models\RolePermission;

if (!function_exists('canView')) {

    function canView($menuName)
    {
        if (!auth()->check()) {
            return false;
        }

        $userRole = auth()->user()->role;

        $role = Role::where('name', $userRole)->first();

        if (!$role) {
            return false;
        }

        return RolePermission::where('role_id', $role->id)
            ->where('menu_name', $menuName)
            ->where('can_view', 1)
            ->exists();
    }
    if (!function_exists('hasAdministrationAccess')) {


        function hasAdministrationAccess()
        {
            return canView('User Master')
                || canView('Role Master')
                || canView('Role Permission Master');
        }
    }

    function canAccessAny($menus)
{
    foreach ($menus as $menu) {

        if (is_array($menu)) {

            if (canView($menu['name'])) {
                return true;
            }

        } else {

            if (canView($menu)) {
                return true;
            }

        }
    }

    return false;
}
    
}
