<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\Permission\Middleware\PermissionMiddleware;

class SaleController extends Controller implements HasMiddleware
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

    public function index()
    {
        return view('sales.index');
    }

    public function alldata(Request $request)
    {
        $request->validate([
            'startdate' => 'required|date',
            'enddate' => 'required|date|after_or_equal:startdate',
        ]);

        if ($request->has('startdate') && $request->has('enddate')) {
            $Order = Order::with(['user', 'customer_user'])
                ->whereBetween('created_at', [$request->startdate, $request->enddate])
                ->paginate(100000);
        } else {
            $Order = Order::with(['user', 'customer_user'])->paginate(100000);
        }

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
}
