<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OpenApi\Annotations as OA;

class PostController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:manage posts')->only(['store', 'update'])->except(['index', 'show']);
        $this->middleware('permission:delete posts')->only(['destroy'])->except(['index', 'show']);
        $this->middleware('permission:publish posts')->only('publish')->except(['index', 'show']);
    }

    public function index()
    {
        return Post::with(['categories', 'tags', 'author'])->get();
    }

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

    public function show($id)
    {
        return Post::with(['categories', 'tags', 'author'])->findOrFail($id);
    }

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
