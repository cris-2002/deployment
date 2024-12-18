<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Order_product;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ReceiptController extends Controller
{
    public function index($id)
    {

        $order = $this->alldata($id)->getData();
        $products = $this->order_product_alldata($id)->getData();

        $order[0]->created_at = Carbon::parse($order[0]->created_at)->format('m/d/Y h:i:s A');

        $imagePath = public_path('favicon.ico');
        $imageData = base64_encode(file_get_contents($imagePath));
        $src = 'data:image/png;base64,'.$imageData;

        $data = compact('order', 'products', 'src');

        // dd($data);
        return view('receipt', $data);

        $pdf = Pdf::loadView('receipt', $data);

        // Pdf::loadHTML($data)->setPaper('a4', 'landscape')->setWarnings(false)->save('myfile.pdf');
        return $pdf->download('receipt.pdf');

    }

    public function alldata($id)
    {
        // $Product = Product::all();
        $Order = Order::with(['user', 'customer_user'])->where('id', $id)->get();

        $transformedOrders = $Order->map(function ($order) {
            return [
                'id' => $order->id,
                'type' => $order->type,
                'status' => $order->status,
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
}
