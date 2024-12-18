<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use App\Models\Review;
use App\Models\Product;
use App\Models\User_cart;
use Illuminate\Http\Request;
use App\Models\Order_product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;
use Spatie\Permission\Middleware\PermissionMiddleware;

class OrderController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(PermissionMiddleware::using('create order'), only: ['create', 'store']),
            new Middleware(PermissionMiddleware::using('read order'), only: ['index', 'alldata', 'order_product_alldata']),
            new Middleware(PermissionMiddleware::using('update order'), only: ['update', 'edit']),
            new Middleware(PermissionMiddleware::using('delete order'), only: ['destroy']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        // $orders = Order::all();
        // return response()->json($orders);
        return view('order.index');
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



        $credit=$request['customer_credit_calories'];
        $total_product_calories=$request['total_product_calories'];

        if($total_product_calories<=0){
            return response()->json(["message"=>"no product selected"], 500);
        }

        if($credit<$total_product_calories && $request['customer_id']!=4){
            return response()->json(["message"=>"Carlories are not enough. the calories reset daily"], 500);
        }

        $updatecredit=   $credit - $total_product_calories ;
        if($request['customer_id']!=4){
            User::where('id',  $request['customer_id'])->update(['calories_credits' => $updatecredit]);
        }


        $userId = Auth::id();



        $data = $request->validate([
            'total_price' => ['required', 'string'],
            'customer_id' => ['required', 'string'],
        ]);

        $change = $data['total_price'] - $request['total'];

        $data['cash_tendered'] = $data['total_price'];
        $data['total_price'] = $request['total'];
        $data['changed'] = $change;
        $data['calories'] = $total_product_calories;
        $data['user_id'] = $userId;
        $data['status'] = 'paid';

        // create order
        $order = Order::create($data);

        $usercart = new User_cart;
        $cartdata = $usercart->where('user_id', $userId)->get();

        $data2 = [];
        foreach ($cartdata as $key => $value) {

            $product = new Product;
            $productdata = $product->where('id', $value['product_id'])->get();

            //create order_product
            $data2 = [
                'order_id' => $order['id'],
                'product_id' => $value['product_id'],
                'quantity' => $value['quantity'],
                'price' => $productdata[0]['price'],
            ];

            Order_product::create($data2);

            // update stock

            $product = Product::find($value['product_id']);
            $newstock = $product['stock'] - $value['quantity'];
            $data3 = [
                'stock' => $newstock,
            ];
            $product->update($data3);

        }
        // delete cart
        User_cart::where('user_id', $userId)->delete();

        $result = [
            'orderid' => $order['id'],
            'change' => $change,
        ];

        return response()->json($result, 201);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store_customer(Request $request)
    {
        //

        $credit=$request['customer_credit_calories'];
        $total_product_calories=$request['calories'];

        if($total_product_calories<=0){
            return response()->json(["message"=>"no product selected"], 500);
        }

        if($credit<$total_product_calories){
            return response()->json(["message"=>"Carlories are not enough.<br> Note: calories reset daily"], 500);
        }

        $updatecredit=   $credit - $total_product_calories ;


        $userId = Auth::id();

        User::where('id',   $userId )->update(['calories_credits' => $updatecredit]);





        $data = $request->validate([
            'total' => ['required', 'string'],
            'calories' => ['required', 'string'],
        ]);

        $change = 0;
        $data['customer_id'] = $userId;
        $data['cash_tendered'] = 0;
        $data['total_price'] = $request['total'];
        $data['changed'] = $change;
        $data['user_id'] = $userId;
        $data['status'] = 'pending';
        $data['type'] = 'APP';

        // create order
        $order = Order::create($data);

        $usercart = new User_cart;
        $cartdata = $usercart->where('user_id', $userId)->get();

        $data2 = [];
        foreach ($cartdata as $key => $value) {

            $product = new Product;
            $productdata = $product->where('id', $value['product_id'])->get();

            //create order_product
            $data2 = [
                'order_id' => $order['id'],
                'product_id' => $value['product_id'],
                'quantity' => $value['quantity'],
                'price' => $productdata[0]['price'],
            ];

            Order_product::create($data2);

            // update stock

            // $product = Product::find($value['product_id']);
            // $newstock = $product['stock'] - $value['quantity'];
            // $data3 = [
            //     'stock' => $newstock,
            // ];
            // $product->update($data3);

        }
        // delete cart
        User_cart::where('user_id', $userId)->delete();

        $result = [
            'orderid' => $order['id'],
            'change' => $change,
        ];

        return response()->json($result, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        //
        $order = Order::find($id);

        return response()->json($order);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Order $order)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {

        $Order = Order::find($id);

        $change = $request['cash_tendered'] - $Order['total_price'];

        if ($change < 0) {
            $message = 'Not Enough need'.$change;
            abort(500, $message);
        }

        $request['user_id'] = Auth::id();
        $request['changed'] = $change;
        $request['status'] = 'paid';

        // check product stock must not be negative stock
        $OrderProducts = Order_product::where('Order_id', $Order['id'])->get();
        foreach ($OrderProducts as $OrderProduct) {
            $product_id = $OrderProduct->product_id;
            $quantity = $OrderProduct->quantity;

            $product = Product::find($product_id);
            $newstock = $product['stock'] - $quantity;
            if ($newstock < 0) {
                $message = 'Product is out of stock. Product Name: <b>'.$product['name'].'</b><br> Stock Remaining:'.$product['stock'];
                $message .= '<br>Advice to <b>REORDER</b> again';

                $request['user_id'] = Auth::id();
                $request['changed'] = 0;
                $request['cash_tendered'] = 0;
                $request['status'] = 'cancelled';
                $Order->update($request->all());
                abort(500, $message);

            }
        }

        // update product stock
        $OrderProducts = Order_product::where('Order_id', $Order['id'])->get();
        foreach ($OrderProducts as $OrderProduct) {
            $product_id = $OrderProduct->product_id;
            $quantity = $OrderProduct->quantity;

            $product = Product::find($product_id);
            $newstock = $product['stock'] - $quantity;
            $product->update([
                'stock' => $newstock,
            ]);
        }

        // save order
        $Order->update($request->all());

        $result = [
            'orderid' => $Order['id'],
            'change' => $change,
        ];

        return response()->json($result, 201);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
        Order::destroy($id);

        return response()->json(null, 204);
    }

    public function alldata(Request $request)
    {
        $userId = Auth::id();

        $Order = Order::with(['user', 'customer_user'])->orderBy('created_at', 'desc')->get();

        $myrole = auth()->user()->hasRole('customer');
        if ($myrole) {
            $Order = Order::with(['user', 'customer_user'])->where('customer_id', $userId)->orderBy('created_at', 'desc')->get();
        }

        $transformedOrders = $Order->map(function ($order) {
            return [
                'id' => $order->id,
                'type' => $order->type,
                'status' => $order->status,
                'calories' => $order->calories,
                'name' => $order->user->name,
                'customer_name' => $order->customer_user->name,
                'total_price' => $order->total_price,
                'cash_tendered' => $order->cash_tendered,
                'changed' => $order->changed,
                'updated_at' => $order->updated_at,
                'created_at' => $order->created_at,
            ];
        });

        return response()->json($transformedOrders);
    }

    public function order_product_alldata($id)
    {
        $Order_product = Order_product::with(['product'])->where('order_id', $id)->get();

        // return response()->json($Order_product);

        // $Order = Order_product::with(['user'])->get();

        $transformedOrders = $Order_product->map(function ($order) {
            return [
                'id' => $order->id,
                'order_id' => $order->order_id,
                'product_id' => $order->product_id,
                'price' => $order->price,
                'quantity' => $order->quantity,
                'name' => $order->product->name,
                'description' => $order->product->description,
                'updated_at' => $order->updated_at,
                'created_at' => $order->created_at,
            ];
        });

        return response()->json($transformedOrders);
    }

    public function addreview(Request $request)
    {

        $request['user_id'] = Auth::id();
        $ProductId = $request['product_id'];
        $checkReview = Review::where('product_id', $ProductId)->where('user_id', Auth::id())->get();

        if ($checkReview->count() > 0) {
            abort(500, 'Already submitted');
        }

        $review = Review::create($request->all());

        return response()->json($review, 201);
    }

    public function updatestatus(Request $request)
    {
        $Order = Order::find($request->id);
        $Order->update($request->all());

        return $Order;
    }

    public function checkstatus()
    {
        $userId = Auth::id();

        $pending = Order::where('customer_id', $userId)->where('status', 'pending')->get();
        $process = Order::where('customer_id', $userId)->where('status', 'process')->get();
        $ready = Order::where('customer_id', $userId)->where('status', 'ready')->get();
        $data = [
            'pending' => $pending->count(),
            'process' => $process->count(),
            'ready' => $ready->count(),
        ];

        return response()->json($data);
    }
}
