<?php

namespace App\Http\Controllers;

// use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller implements HasMiddleware
{
    /**
     * Display a listing of the resource.
     */
    public static function middleware(): array
    {
        return [

            new Middleware(PermissionMiddleware::using('create permission'), only: ['create', 'store']),
            new Middleware(PermissionMiddleware::using('read permission'), only: ['index', 'alldata']),
            new Middleware(PermissionMiddleware::using('update permission'), only: ['update', 'edit']),
            new Middleware(PermissionMiddleware::using('delete permission'), only: ['destroy']),
        ];
    }

    public function index()
    {
        //
        $permissions = Permission::get();

        return view('role-permission.permission.index', [
            'permissions' => $permissions,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        return view('role-permission.permission.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $request->validate([
            'name' => ['required', 'string', 'unique:permissions,name'],
        ]);

        $permission = Permission::create(['name' => $request->name]);

        return response()->json($permission, 201);
        // return redirect('permissions')->with('status','Permission Createad Successfuly');
    }

    /**
     * Display the specified resource.
     */
    public function show(Permission $permission)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Permission $permission)
    {
        //
        return view('role-permission.permission.edit', [
            'permission' => $permission,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Permission $permission)
    {
        //
        $request->validate([
            'name' => ['required', 'string', 'unique:permissions,name,'.$permission->id],
        ]);

        $permission->update(['name' => $request->name]);

        return response()->json($permission, 201);
        // return redirect('permissions')->with('status','Permission Update Successfuly');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
        // return $permission;
        // $permission->delete();
        // return redirect('permissions')->with('status','Permission deleted Successfuly');

        $delete = Permission::findOrFail($id);
        $delete->delete();

        return response()->json(['message' => 'deleted successfully'], 200);
    }

    public function alldata(Request $request)
    {
        $Permission = Permission::all();

        // $Permission = Permission::whereNotBetween('id', [1, 8])->get();
        return response()->json($Permission);
    }
}
