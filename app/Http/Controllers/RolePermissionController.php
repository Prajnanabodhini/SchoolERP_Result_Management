<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\RolePermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class RolePermissionController extends Controller
{
    public function index(Request $request)
    {
        $roles = Role::where(
            'is_active',
            1
        )->get();

        $permissions = collect();

        if ($request->role_id) {

            $permissions =
                RolePermission::where(
                    'role_id',
                    $request->role_id
                )
                ->get()
                ->keyBy('menu_name');
        }

        $menus =
            include app_path(
                'Helpers/MenuConfig.php'
            );

        return view(
            'role_permissions.index',
            compact(
                'roles',
                'permissions',
                'menus'
            )
        );
    }

    public function store(Request $request)
    {
        $menus = include app_path('Helpers/MenuConfig.php');

        foreach ($menus as $group => $items) {

            foreach ($items as $item) {

                $menuName = $item['name'];

                $permission =
                    $request->permissions[$menuName] ?? [];

                RolePermission::updateOrCreate(

                    [
                        'role_id'   => $request->role_id,
                        'menu_name' => $menuName
                    ],

                    [
                        'can_view'   => !empty($permission['view']),
                        'can_add'    => !empty($permission['add']),
                        'can_edit'   => !empty($permission['edit']),
                        'can_delete' => !empty($permission['delete']),
                        'can_print'  => !empty($permission['print']),
                        'can_export' => !empty($permission['export']),
                    ]
                );
            }
        }

        return redirect()
            ->route(
                'role-permissions.index',
                [
                    'role_id' => $request->role_id
                ]
            )
            ->with(
                'success',
                'Permissions saved successfully.'
            );
    }
}
