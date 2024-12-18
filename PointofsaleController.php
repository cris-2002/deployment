<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;
use Spatie\Permission\Middleware\PermissionMiddleware;

class PointofsaleController extends Controller implements HasMiddleware
{
    /**
     * Display a listing of the resource.
     */
    public static function middleware(): array
    {
        return [
            new Middleware(PermissionMiddleware::using('create pos'), only: ['create', 'store']),
            new Middleware(PermissionMiddleware::using('read pos'), only: ['index', 'alldata','userlist']),
            new Middleware(PermissionMiddleware::using('update pos'), only: ['update', 'edit']),
            new Middleware(PermissionMiddleware::using('delete pos'), only: ['destroy']),
        ];
    }

    public function index()
    {
        //
        return view('pos.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function allproductpublish(Request $request)
    {
        // $Product = Product::all();
        $Product = Product::with(['category', 'updater_user', 'creator_user'])
            ->where('status', 'publish')
            ->where('stock', '>', '0')
            ->get();

        $transformedProducts = $Product->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'images' => $product->images,
                'calories' => $product->calories,
                'stock' => $product->stock,
                'status' => $product->status,
                'sku' => $product->sku,
                'barcode' => $product->barcode,
                'category_id' => $product->category_id,
                'created_at' => $product->created_at,
                'updated_at' => $product->updated_at,
                'category_name' => $product->category->name,
                'created_by' => $product->creator_user->name,
                'updated_by' => $product->updater_user->name,
            ];
        });

        return response()->json($transformedProducts);
    }
    public function userlist()
    {
        $users = User::get();
        return response()->json($users, 201);
    }
}
