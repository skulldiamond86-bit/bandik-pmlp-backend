<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CategoryController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $categories = Category::where('user_id', auth()->id())
            ->latest()
            ->get();

        return CategoryResource::collection($categories);
    }

    public function store(CategoryRequest $request): CategoryResource
    {
        $category = Category::create([
            'user_id' => auth()->id(),
            'name' => $request->name,
            'type' => $request->type,
        ]);

        return new CategoryResource($category);
    }

    public function show(string $id): CategoryResource
    {
        $category = $this->findUserCategory($id);

        return new CategoryResource($category);
    }

    public function update(CategoryRequest $request, string $id): CategoryResource
    {
        $category = $this->findUserCategory($id);
        $category->update($request->only('name', 'type'));

        return new CategoryResource($category);
    }

    public function destroy(string $id): JsonResponse
    {
        $category = $this->findUserCategory($id);
        $category->delete();

        return response()->json([
            'message' => 'Category berhasil dihapus',
        ]);
    }

    private function findUserCategory(string $id): Category
    {
        return Category::where('user_id', auth()->id())->findOrFail($id);
    }
}
