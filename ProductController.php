<?php

namespace App\Http\Controllers;

use App\Models\Allergy;
use App\Models\Product;
use App\Models\ProductAllergy;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Middleware\PermissionMiddleware;

class ProductController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(PermissionMiddleware::using('create product'), only: ['create', 'store']),
            new Middleware(PermissionMiddleware::using('read product'), only: ['index', 'alldata']),
            new Middleware(PermissionMiddleware::using('update product'), only: ['update', 'edit']),
            new Middleware(PermissionMiddleware::using('delete product'), only: ['destroy']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $allergies = Allergy::pluck('name', 'id')->all();

        return view('product.index', ['allergies' => $allergies]);
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
        // Get the authenticated user's ID
        $userId = Auth::id();

        // Merge the user ID with the request data
        // $data = $request->all();

        $data = $request->validate([
            'name' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric'],
            'category_id' => ['required', 'integer'],
            'calories' => ['nullable', 'numeric'],
            'images' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'stock' => ['required', 'integer'],
            'status' => ['nullable', 'string'],
            'sku' => ['required', 'string', 'unique:products,sku'],
            'barcode' => ['required', 'string', 'unique:products,barcode'],
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('product_images', 'public');
            $data['images'] = 'storage/'.$imagePath;
        } else {
            $data['images'] = 'https://via.placeholder.com/640x480.png/0033bb?text=Cafeteria';
        }

        $data['created_by'] = $userId;
        $data['updated_by'] = $userId;
        // Create the product with the merged data
        $product = Product::create($data);

        if ($request->has('allergies')) {
            foreach ($request->allergies as $id) {
                ProductAllergy::create([
                    'allergy_id' => $id,
                    'product_id' => $product->id,

                ]);
            }
        }

        return response()->json($product, 201);

    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        //
        $product = Product::find($id);

        return response()->json($product);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {

        $data = $request->validate([
            'name' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric'],
            'category_id' => ['required', 'integer'],
            'calories' => ['nullable', 'numeric'],
            'images' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'stock' => ['required', 'integer'],
            'status' => ['nullable', 'string'],
            'sku' => ['nullable', 'string', 'unique:products,sku,'.$product->id],
            'barcode' => ['nullable', 'string', 'unique:products,barcode,'.$product->id],
        ]);

        $userId = Auth::id();

        $data['updated_by'] = $userId;

        if ($request->hasFile('image')) {

            if ($product->images) {
                $path = str_replace('storage/', '', $product->images);
                Storage::disk('public')->delete($path);
            }

            $imagePath = $request->file('image')->store('product_images', 'public');
            $data['images'] = 'storage/'.$imagePath;
        }

        $product->update($data);

        $userallergy = ProductAllergy::where('product_id', $product->id);
        $userallergy->delete();
        if ($request->has('allergies')) {
            foreach ($request->allergies as $id) {
                ProductAllergy::create([
                    'allergy_id' => $id,
                    'product_id' => $product->id,
                ]);
            }
        }

        return response()->json($data);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
        // Product::destroy($id);
        // return response()->json(null, 204);
        $delete = Product::findOrFail($id);
        $delete->delete();

        return response()->json(['message' => 'deleted successfully'], 200);
    }

    public function alldata(Request $request)
    {
        // $Product = Product::all();
        $Product = Product::with(['category', 'updater_user', 'creator_user', 'product_allergies.allergy'])->get();

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
                'productallergy' => $product->product_allergies->map(function ($productalergy) {
                    return [
                        'name' => $productalergy->allergy->name,
                    ];
                })->pluck('name'),
            ];
        });

        return response()->json($transformedProducts);
    }
}
