<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Categories",
 *     description="API для управления категориями"
 * )
 */

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'role:Admin']);
    }

    /**
     * @OA\Get(
     *     path="/categories",
     *     summary="Получить все категории",
     *     description="Возвращает список всех категорий.",
     *     operationId="getCategories",
     *     tags={"Categories"},
     *     security={{"sanctum": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Список категорий",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", description="ID категории"),
     *                 @OA\Property(property="name", type="string", description="Название категории")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Не авторизован",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     */

    public function index()
    {
        $categories = Category::all();
        return response()->json($categories);
    }

    /**
     * @OA\Post(
     *     path="/categories",
     *     summary="Создать категорию",
     *     description="Создаёт новую категорию с указанным названием.",
     *     operationId="createCategory",
     *     tags={"Categories"},
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
 *                 @OA\Property(property="name", type="string", description="Название категории", maxLength=255)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Категория успешно создана",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", description="ID категории"),
     *             @OA\Property(property="name", type="string", description="Название категории")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Ошибка валидации",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The name field is required.")
     *         )
     *     )
     * )
     */

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:categories,name',
        ]);

        $category = Category::create($request->all());
        return response()->json($category, 201);
    }

    /**
     * @OA\Get(
     *     path="/categories/{id}",
     *     summary="Получить категорию по ID",
     *     description="Возвращает данные категории по указанному ID.",
     *     operationId="getCategoryById",
     *     tags={"Categories"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID категории",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Данные категории",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", description="ID категории"),
     *             @OA\Property(property="name", type="string", description="Название категории")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Категория не найдена",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Category not found")
     *         )
     *     )
     * )
     */

    public function show($id)
    {
        $category = Category::findOrFail($id);
        return response()->json($category);
    }

    /**
     * @OA\Put(
     *     path="/categories/{id}",
     *     summary="Обновить категорию",
     *     description="Обновляет данные категории по ID.",
     *     operationId="updateCategory",
     *     tags={"Categories"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID категории",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *                 @OA\Property(property="name", type="string", description="Новое название категории", maxLength=255)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Категория обновлена",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", description="ID категории"),
     *             @OA\Property(property="name", type="string", description="Название категории")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Ошибка валидации",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The name field is required.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Категория не найдена",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Category not found")
     *         )
     *     )
     * )
     */

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);
        $category->update($request->all());
        return response()->json($category);
    }

    /**
     * @OA\Delete(
     *     path="/categories/{id}",
     *     summary="Удалить категорию",
     *     description="Удаляет категорию по ID.",
     *     operationId="deleteCategory",
     *     tags={"Categories"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID категории",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Категория успешно удалена",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Category deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Категория не найдена",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Category not found")
     *         )
     *     )
     * )
     */

    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();
        return response()->json(['message' => 'Category deleted successfully']);
    }
}

