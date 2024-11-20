<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Posts",
 *     description="API для работы с постами"
 * )
 */

class PostController extends Controller
{
    /**
     * @OA\Schema (
     *     schema="Post",
     *     type="object",
     *     required={"title", "content"},
     *     @OA\Property(property="id", type="integer", example=1),
     *     @OA\Property(property="title", type="string", example="Заголовок поста"),
     *     @OA\Property(property="content", type="string", example="Контент поста"),
     *     @OA\Property(property="status", type="string", enum={"draft", "published"}, example="draft"),
     *     @OA\Property(property="categories", type="array", @OA\Items(type="integer"), example={1, 2}),
     *     @OA\Property(property="tags", type="array", @OA\Items(type="integer"), example={1, 3}),
     *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-11-19T12:34:56"),
     *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-11-19T12:34:56")
     * )
     */

    public function __construct()
    {
        $this->middleware('permission:manage posts')->only(['store', 'update'])->except(['index', 'show']);
        $this->middleware('permission:delete posts')->only(['destroy'])->except(['index', 'show']);
        $this->middleware('permission:publish posts')->only('publish')->except(['index', 'show']);
    }

    /**
     * @OA\Get (
     *     path="/api/posts",
     *     summary="Получить все посты",
     *     tags={"Posts"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Список постов",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Post")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Неавторизованный"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Внутренняя ошибка сервера"
     *     )
     * )
     */

    public function index()
    {
        return Post::with(['categories', 'tags', 'author'])->get();
    }


    /**
     * @OA\Post(
     *     path="/api/posts",
     *     summary="Создать новый пост",
     *     tags={"Posts"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title", "content"},
     *             @OA\Property(property="title", type="string", example="Заголовок поста"),
     *             @OA\Property(property="content", type="string", example="Контент поста"),
     *             @OA\Property(property="status", type="string", enum={"draft", "published"}, example="draft"),
     *             @OA\Property(property="categories", type="array", @OA\Items(type="integer")),
     *             @OA\Property(property="tags", type="array", @OA\Items(type="integer"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Пост успешно создан",
     *         @OA\JsonContent(ref="#/components/schemas/Post")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Неверный запрос"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Неавторизованный"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Внутренняя ошибка сервера"
     *     )
     * )
     */

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required',
            'categories' => 'array',
            'categories.*' => 'exists:categories,id',
            'tags' => 'array',
            'tags.*' => 'exists:tags,id',
            'cover_image' => 'nullable|image',
            'status' => 'required|in:draft,published',
        ]);

        $data['author_id'] = Auth::id();

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $request->file('cover_image')->store('cover_images', 'public');
        }

        $post = Post::create($data);

        if (!empty($data['categories'])) {
            $post->categories()->sync($data['categories']);
        }

        if (!empty($data['tags'])) {
            $post->tags()->sync($data['tags']);
        }

        return response()->json($post->load(['categories', 'tags', 'author']), 201);
    }

    /**
     * @OA\Get(
     *     path="/posts/{id}",
     *     summary="Получить пост по id",
     *     description="Возвращает информацию о посте.",
     *     tags={"Posts"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID поста",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Информация о посте",
     *         @OA\JsonContent(
     *              required={"title", "content"},
     *              @OA\Property(property="title", type="string", example="Заголовок поста"),
     *              @OA\Property(property="content", type="string", example="Контент поста"),
     *              @OA\Property(property="status", type="string", enum={"draft", "published"}, example="draft"),
     *              @OA\Property(property="categories", type="array", @OA\Items(type="integer")),
     *              @OA\Property(property="tags", type="array", @OA\Items(type="integer"))
     *         )
     *     ),
     *     @OA\Response(response=404, description="Пост не найден"),
     * )
     */

    public function show($id)
    {
        return Post::with(['categories', 'tags', 'author'])->findOrFail($id);
    }

    /**
     * @OA\Put(
     *     path="/posts/{id}",
     *     summary="Обновить пост",
     *     description="Обновляет существующий пост по ID. Требуется авторизация пользователя.",
     *     operationId="updatePost",
     *     tags={"Posts"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID поста для обновления",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
 *                 type="object",
 *                 @OA\Property(property="title", type="string", description="Заголовок поста", maxLength=255),
 *                 @OA\Property(property="content", type="string", description="Содержимое поста"),
 *                 @OA\Property(property="categories", type="array", @OA\Items(type="integer"), description="Массив категорий поста"),
 *                 @OA\Property(property="tags", type="array", @OA\Items(type="integer"), description="Массив тегов поста"),
 *                 @OA\Property(property="cover_image", type="string", format="binary", description="Изображение для обложки (файл)"),
 *                 @OA\Property(property="status", type="string", enum={"draft", "published"}, description="Статус поста")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Успешно обновлённый пост",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="content", type="string"),
     *             @OA\Property(property="categories", type="array", @OA\Items(type="integer")),
     *             @OA\Property(property="tags", type="array", @OA\Items(type="integer")),
     *             @OA\Property(property="cover_image", type="string"),
     *             @OA\Property(property="status", type="string", enum={"draft", "published"}),
     *             @OA\Property(property="author", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Неверные данные",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Ошибка валидации")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Пост не найден",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Post not found")
     *         )
     *     )
     * )
     */

    public function update(Request $request, $id)
    {
        $post = Post::findOrFail($id);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required',
            'categories' => 'array',
            'categories.*' => 'exists:categories,id',
            'tags' => 'array',
            'tags.*' => 'exists:tags,id',
            'cover_image' => 'nullable|image',
            'status' => 'required|in:draft,published',
        ]);

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $request->file('cover_image')->store('cover_images', 'public');
        }

        $post->update($data);

        if (isset($data['categories'])) {
            $post->categories()->sync($data['categories']);
        }

        if (isset($data['tags'])) {
            $post->tags()->sync($data['tags']);
        }

        return response()->json($post->load(['categories', 'tags', 'author']));
    }

    /**
     * @OA\Delete(
     *     path="/posts/{id}",
     *     summary="Удалить пост",
     *     description="Удаляет пост по ID. Требуется разрешение на удаление (delete).",
     *     operationId="destroyPost",
     *     tags={"Posts"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID поста для удаления",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Пост успешно удалён",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Post deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Нет прав на удаление",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="You do not have permission to delete this post")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Пост не найден",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Post not found")
     *         )
     *     )
     * )
     */

    public function destroy($id)
    {
        $post = Post::findOrFail($id);
        $post->delete();

        return response()->json(['message' => 'Post deleted successfully']);
    }

    public function publish(Request $request, $id)
    {
        $post = Post::findOrFail($id);

        $data = $request->validate([
            'status' => 'required|in:draft,published',
        ]);

        $post->update($data);
        return response()->json(['message' => 'Post published successfully']);
    }
}
