<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Tags",
 *     description="API для управления тегами"
 * )
 */

class TagController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'role:Admin']);
    }

    /**
     * @OA\Get(
     *     path="/tags",
     *     summary="Получить все теги",
     *     description="Возвращает список всех тегов.",
     *     operationId="getTags",
     *     tags={"Tags"},
     *     security={{"sanctum": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Список тегов",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", description="ID тега"),
     *                 @OA\Property(property="name", type="string", description="Название тега")
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
        $tags = Tag::all();
        return response()->json($tags);
    }

    /**
     * @OA\Post(
     *     path="/tags",
     *     summary="Создать тег",
     *     description="Создаёт новый тег с указанным названием.",
     *     operationId="createTag",
     *     tags={"Tags"},
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *                 @OA\Property(property="name", type="string", description="Название тега", maxLength=255)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Тег успешно создан",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", description="ID тега"),
     *             @OA\Property(property="name", type="string", description="Название тега")
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
            'name' => 'required|string|unique:tags,name',
        ]);

        $tag = Tag::create($request->all());
        return response()->json($tag, 201);
    }

    /**
     * @OA\Get(
     *     path="/tags/{id}",
     *     summary="Получить тег по ID",
     *     description="Возвращает данные тега по указанному ID.",
     *     operationId="getTagById",
     *     tags={"Tags"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID тега",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Данные тега",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", description="ID тега"),
     *             @OA\Property(property="name", type="string", description="Название тега")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Тег не найден",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Tag not found")
     *         )
     *     )
     * )
     */

    public function show($id)
    {
        $tag = Tag::findOrFail($id);
        return response()->json($tag);
    }

    /**
     * @OA\Put(
     *     path="/tags/{id}",
     *     summary="Обновить тег",
     *     description="Обновляет данные тега по ID.",
     *     operationId="updateTag",
     *     tags={"Tags"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID тега",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *                 @OA\Property(property="name", type="string", description="Новое название тега", maxLength=255)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Тег обновлен",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", description="ID тега"),
     *             @OA\Property(property="name", type="string", description="Название тега")
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
     *         description="Тег не найден",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Tag not found")
     *         )
     *     )
     * )
     */

    public function update(Request $request, $id)
    {
        $tag = Tag::findOrFail($id);
        $tag->update($request->all());
        return response()->json($tag);
    }

    /**
     * @OA\Delete(
     *     path="/tags/{id}",
     *     summary="Удалить тег",
     *     description="Удаляет тег по ID.",
     *     operationId="deleteTag",
     *     tags={"Tags"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID тега",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Тег успешно удален",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Tag deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Тег не найден",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Tag not found")
     *         )
     *     )
     * )
     */

    public function destroy($id)
    {
        $tag = Tag::findOrFail($id);
        $tag->delete();
        return response()->json(['message' => 'Tag deleted successfully']);
    }
}
