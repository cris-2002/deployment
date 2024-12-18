<?php

namespace App\Http\Controllers;

use App\Models\Allergy;
use App\Models\User;
use App\Models\UserAllergy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        $users = User::get();
        $roles = Role::pluck('name', 'name')->all();
        $allergies = Allergy::pluck('name', 'id')->all();

        return view('role-permission.user.index', ['users' => $users, 'roles' => $roles, 'allergies' => $allergies]);
    }

    public function create()
    {
        $roles = Role::pluck('name', 'name')->all();

        return view('role-permission.user.create', ['roles' => $roles]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'school_id' => 'required|string|max:255|unique:users,school_id',
            'address' => 'required|string|max:255',
            'daily_calories' => 'required|numeric|max:255',
            'password' => 'required|string|min:8|max:50',
            'roles' => 'required',
        ]);

        // dd($request->allergies);
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'school_id' => $request->school_id,
            'address' => $request->address,
            'daily_calories' => $request->daily_calories,
            'password' => Hash::make($request->password),
        ]);

        if ($request->has('allergies')) {
            foreach ($request->allergies as $id) {
                UserAllergy::create([
                    'allergy_id' => $id,
                    'user_id' => $user->id,

                ]);
            }
        }

        $user->syncRoles($request->roles);

        return response()->json($user, 201);
        // return redirect('/users')->with('status','User created successfully with roles');
    }

    public function show(User $User)
    {
        //    dd($User->getRoleNames());
        return response()->json($User->getRoleNames());
    }

    public function edit(User $user)
    {
        $roles = Role::pluck('name', 'name')->all();
        $userRoles = $user->roles->pluck('name', 'name')->all();

        return view('role-permission.user.edit', [
            'user' => $user,
            'roles' => $roles,
            'userRoles' => $userRoles,
        ]);
    }

    public function update(Request $request, User $user)
    {
        // $request->validate([
        //     'name' => 'required|string|max:255',
        //     'password' => 'nullable|string|min:8|max:50',
        //     'roles' => 'required'
        // ]);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,'.$user->id,
            'school_id' => 'required|string|max:255|unique:users,school_id,'.$user->id,
            'address' => 'required|string|max:255',
            'daily_calories' => 'required|numeric',
            'password' => 'nullable|string|min:8|max:50',
            'roles' => 'required',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'school_id' => $request->school_id,
            'address' => $request->address,
            'daily_calories' => $request->daily_calories,
        ];

        if (! empty($request->password)) {
            $data += [
                'password' => Hash::make($request->password),
            ];
        }

        $user->update($data);
        $user->syncRoles($request->roles);

        $userallergy = UserAllergy::where('user_id', $user->id);
        $userallergy->delete();
        if ($request->has('allergies')) {
            foreach ($request->allergies as $id) {
                UserAllergy::create([
                    'allergy_id' => $id,
                    'user_id' => $user->id,
                ]);
            }
        }

        return response()->json($user, 201);
        // return redirect('/users')->with('status','User Updated Successfully with roles');
    }

    public function destroy($userId)
    {
        $user = User::findOrFail($userId);
        $user->delete();

        return response()->json(['message' => 'deleted successfully'], 200);
        // return redirect('/users')->with('status','User Delete Successfully');
    }

    public function alldata(Request $request)
    {
        // $User = User::all();
        // return response()->json($User);
        $users = User::with(['roles', 'user_allergies.allergy'])->get();

        $users = $users->map(function ($user) {

            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'school_id' => $user->school_id,
                'address' => $user->address,
                'daily_calories' => $user->daily_calories,
                'calories_credits' => $user->calories_credits,
                'calories_date_modified' => $user->calories_date_modified,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
                'roles' => $user->roles->pluck('name'),
                'userallergy' => $user->user_allergies->map(function ($userAllergy) {
                    return [
                        'name' => $userAllergy->allergy->name,
                    ];
                })->pluck('name'),
            ];
        });

        return response()->json($users);
    }
}
