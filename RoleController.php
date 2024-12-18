<?php

namespace App\Http\Controllers;

// use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [

            new Middleware(PermissionMiddleware::using('create role'), only: ['create', 'store', 'addPermissionToRole', 'givePermissionToRole']),
            new Middleware(PermissionMiddleware::using('read role'), only: ['index', 'alldata']),
            new Middleware(PermissionMiddleware::using('update role'), only: ['update', 'edit']),
            new Middleware(PermissionMiddleware::using('delete role'), only: ['destroy']),
        ];
    }

    public function index(Request $Request)
    {

        $roles = Role::get();

        return view('role-permission.role.index', [
            'roles' => $roles,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        return view('role-permission.role.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $request->validate([
            'name' => ['required', 'string', 'unique:roles,name'],
        ]);

        $role = Role::create(['name' => $request->name]);

        return response()->json($role, 201);
        // return redirect('roles')->with('status','Role Createad Successfuly');
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $permission)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {
        //
        return view('role-permission.role.edit', [
            'role' => $role,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role)
    {
        //
        $request->validate([
            'name' => ['required', 'string', 'unique:roles,name,'.$role->id],
        ]);

        $role->update(['name' => $request->name]);

        return response()->json($role, 201);
        // return redirect('roles')->with('status','Role Update Successfuly');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
        // return $permission;
        // $role->delete();
        // return redirect('roles')->with('status','Role deleted Successfuly');

        $delete = Role::findOrFail($id);
        $delete->delete();

        return response()->json(['message' => 'deleted successfully'], 200);

    }

    public function addPermissionToRole(Request $request, $roleId)
    {

        $permissions = Permission::get();
        $role = Role::findOrFail($roleId);
        $rolePermissions = DB::table('role_has_permissions')
            ->where('role_has_permissions.role_id', $role->id)
            ->pluck('role_has_permissions.permission_id', 'role_has_permissions.permission_id')
            ->all();

        return view('role-permission.role.add-permissions', [
            'role' => $role,
            'permissions' => $permissions,
            'rolePermissions' => $rolePermissions,
        ]);
    }

    public function givePermissionToRole(Request $request, $roleId)
    {
        $request->validate([
            'permission' => 'required',
        ]);

        $role = Role::findOrFail($roleId);
        $role->syncPermissions($request->permission);

        return redirect()->back()->with('status', 'Permissions added to role');
    }

    public function alldata(Request $request)
    {
        $Role = Role::all();

        return response()->json($Role);
    }
}
