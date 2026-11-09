<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Dish;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\UpdateOrderStatusRequest;
class OrderController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if ($user->role === 'admin') {
            $orders = Order::latest()->get();
        } else {
            $orders = Order::where('user_id', $user->id)->latest()->get();
        }

        return OrderResource::collection($orders);
    }
    public function store(StoreOrderRequest $request)
    {
        $user = $request->user();
        if ($user->role !== 'client') {
            return response()->json(['message' => 'Только клиенты могут оформлять заказы'], 403);
        }
        $order = DB::transaction(function () use ($request, $user) {
            $order = Order::create([
                'user_id' => $user->id,
                'status' => 'pending',
                'comment' => $request->comment,
                'total_price' => 0,
            ]);

            $totalPrice = 0;
            $attachData = [];
            foreach ($request->items as $item) {
                $dish = Dish::findOrFail($item['dish_id']);
                if (!$dish->is_available) {
                    abort(response()->json(['message' => "Блюдо '{$dish->name}' временно недоступно"], 422));
                }

                $priceAtSale = $dish->price;
                $quantity = $item['quantity'];
                $totalPrice += $priceAtSale * $quantity;
                $attachData[$dish->id] = [
                    'quantity' => $quantity,
                    'price_at_sale' => $priceAtSale,
                ];
            }
            $order->dishes()->attach($attachData);
            $order->update(['total_price' => $totalPrice]);

            return $order;
        });
        $order->load('dishes');

        return new OrderResource($order);
    }
    public function show(Request $request, $id)
    {
        $user = $request->user();
        $order = Order::with('dishes')->find($id);

        if (!$order) {
            return response()->json(['message' => 'Заказ не найден'], 404);
        }
        if ($user->role !== 'admin' && $order->user_id !== $user->id) {
            return response()->json(['message' => 'Доступ запрещен'], 403);
        }

        return new OrderResource($order);
    }
    public function updateStatus(UpdateOrderStatusRequest $request, $id)
{
    $order = Order::find($id);
    if (!$order) {
        return response()->json(['message' => 'Заказ не найден'], 404);
    }

    $order->update([
        'status' => $request->status
    ]);

    return new OrderResource($order);
}
}
