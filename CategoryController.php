<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Middleware\PermissionMiddleware;

class CategoryController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(PermissionMiddleware::using('create category'), only: ['create', 'store']),
            new Middleware(PermissionMiddleware::using('read category'), only: ['index', 'alldata']),
            new Middleware(PermissionMiddleware::using('update category'), only: ['update', 'edit']),
            new Middleware(PermissionMiddleware::using('delete category'), only: ['destroy']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('category.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('category.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $userId = Auth::id();

        $data = $request->validate([
            'name' => ['required', 'string', 'unique:categories,name'],
        ]);

        $data['created_by'] = $userId;
        $data['updated_by'] = $userId;

        $category = Category::create($data);

        return response()->json($category, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {

        return response()->json($category);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        //
        return view('category.edit', [
            'category' => $category,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        //
        $userId = Auth::id();
        $data = $request->validate([
            'name' => ['required', 'string', 'unique:categories,name,'.$category->id],
        ]);
        $data['updated_by'] = $userId;
        $category->update($data);

        return response()->json($category);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $delete = Category::findOrFail($id);
        $delete->products()->delete();
        $delete->delete();

        return response()->json(['message' => 'deleted successfully'], 200);

    }

    public function alldata(Request $request)
    {
        $categories = Category::with(['updater_user', 'creator_user'])->get();

        $transformedProducts = $categories->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'created_at' => $category->created_at,
                'updated_at' => $category->updated_at,
                'created_by' => $category->creator_user->name,
                'updated_by' => $category->updater_user->name,
            ];
        });

        return response()->json($transformedProducts);

    }
}
