<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Product;
use App\Models\User_cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;
use Spatie\Permission\Middleware\PermissionMiddleware;

class CartController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(PermissionMiddleware::using('create cart'), only: ['create', 'store']),
            new Middleware(PermissionMiddleware::using('read cart'), only: ['index', 'alldata']),
            new Middleware(PermissionMiddleware::using('update cart'), only: ['update', 'edit']),
            new Middleware(PermissionMiddleware::using('delete cart'), only: ['destroy', 'deleteAllForUser']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        return view('mobile.cart.index');
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
        $userId = Auth::id();

        $data = $request->validate([
            'quantity' => ['nullable', 'numeric'],
            'product_id' => ['required', 'numeric'],
        ]);
        if ($data['quantity'] < 1) {
            $data['quantity'] = 1;
        }

        $data['user_id'] = $userId;

        $carts = new User_cart;
        $cart = $carts->where('product_id', $data['product_id'])->where('user_id', $userId)->get();

        if ($cart->count() > 0) {

            $newquantity = $cart[0]['quantity'] + $data['quantity'];
            $cartId = $cart[0]['id'];

            $cartsupdate = User_cart::find($cartId);
            $data2 = [
                'quantity' => $newquantity,
            ];

            $products = new Product;
            $product = $products->where('id', $cart[0]['product_id'])->get();

            if ($data2['quantity'] > $product[0]['stock']) {

                $data2['quantity'] = $product[0]['stock'];

                return response()->json(['message' => 'Maximum Stock Available'], 500);

            }

            $cartsupdate->update($data2);

            return response()->json($cartsupdate, 201);
        }

        $cart = User_cart::create($data);

        return response()->json($cart, 201);
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
    public function update(Request $request, User_cart $cart)
    {

        $userId = Auth::id();
        $data = $request->validate([
            'quantity' => ['required', 'numeric'],
        ]);

        if ($data['quantity'] == 0) {
            $cart->delete();
        }

        $products = new Product;
        $product = $products->where('id', $cart['product_id'])->get();

        if ($data['quantity'] > $product[0]['stock']) {
            $data['quantity'] = $product[0]['stock'];
            $cart->update($data);

            return response()->json(['message' => 'Maximum Stock Available'], 500);

        }

        $cart->update($data);

        return response()->json($cart);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //

        $delete = User_cart::findOrFail($id);

        $delete->delete();

        return response()->json(['message' => 'deleted successfully'], 200);
    }

    public function deleteAllForUser($userId)
    {
        // Delete all cart items where user_id is 1
        User_cart::where('user_id', $userId)->delete();

        return response()->json(['message' => 'All cart items deleted for user '.$userId], 200);
    }

    public function alldata(Request $request)
    {
        $userId = Auth::id();
        // $Product = Product::all();
        $Carts = User_cart::with(['user', 'product'])->where('user_id', $userId)->get();

        $transformedProducts = $Carts->map(function ($cart) {
            return [
                'id' => $cart->id,
                'user_id' => $cart->user_id,
                'product_id' => $cart->product_id,
                'quantity' => $cart->quantity,
                'product_name' => $cart->product->name,
                'price' => $cart->product->price,
                'barcode' => $cart->product->barcode,
                'sku' => $cart->product->sku,
                'calories' => $cart->product->calories,
                'user_name' => $cart->user->name,
                'created_at' => $cart->created_at,
                'updated_at' => $cart->updated_at,
            ];
        });

        return response()->json($transformedProducts);
    }
    public function userinfo(){
        $userId = Auth::id();
        $user = User::find($userId);
        return response()->json($user);
    }
}
