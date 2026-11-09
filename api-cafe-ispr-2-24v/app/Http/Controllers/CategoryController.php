<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Http\Resources\CategoryResource;
use App\Http\Requests\StoreCategoryRequest;
class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::all();
        return CategoryResource::collection($categories);
    }
    public function show($id)
    {
        $category = Category::with('dishes')->find($id);

        if (!$category) {
            return response()->json(['message' => 'Категория не найдена'], 404);
        }

        return new CategoryResource($category);
    }

public function store(StoreCategoryRequest $request)
{
    $category = Category::create($request->validated());
    return new CategoryResource($category);
}

public function update(StoreCategoryRequest $request, $id)
{
    $category = Category::find($id);
    if (!$category) return response()->json(['message' => 'Категория не найдена'], 404);

    $category->update($request->validated());
    return new CategoryResource($category);
}

public function destroy($id)
{
    $category = Category::find($id);
    if (!$category) return response()->json(['message' => 'Категория не найдена'], 404);

    $category->delete();
    return response()->json(null, 204);
}
}
