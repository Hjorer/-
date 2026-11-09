<?php

namespace App\Http\Controllers;

use App\Models\Dish;
use App\Http\Resources\DishResource;
use Illuminate\Http\Request;

class DishController extends Controller
{
    public function index(Request $request)
    {
        $query = Dish::query();
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->has('is_available')) {
            $query->where('is_available', $request->is_available);
        }

        return DishResource::collection($query->get());
    }
    public function show($id)
    {
        $dish = Dish::find($id);

        if (!$dish) {
            return response()->json(['message' => 'Блюдо не найдено'], 404);
        }

        return new DishResource($dish);
    }
}
