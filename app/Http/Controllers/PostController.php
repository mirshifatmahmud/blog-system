<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostResource;
use App\Models\Comment;
use App\Models\Like;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PostController extends BaseController
{
    public function index()
    {
        $posts = Post::with('user')->get(); // Post::all(); user data not add.

        foreach ($posts as $post) {
            $comments = Comment::where('post_id', $post->id)->get();

            $meg = [];
            foreach ($comments as $comment) {
                $meg[] = $comment->content;
            }
            $post['comments_data'] = [
                'total_comments' => count($comments) ?? 0,
                'comments' => $meg ?? [],
            ];

            $likes = Like::where('post_id', $post->id)->get();

            $post['likes_data'] = [
                'total_likes' => count($likes) ?? 0,
            ];
        }

        return $this->sendResponse(PostResource::collection($posts), 'All posts retrieved');
    }

    public function show($id)
    {
        $post = Post::find($id);

        if (!$post) {
            return $this->sendError('Post not found', [], 404); // 404 page not found
        }

        $comments = Comment::where('post_id', $post->id)->get();

        foreach ($comments as $comment) {
            $meg[] = $comment->content;
        }
        $post['comments_data'] = [
            'total_comments' => count($comments) ?? 0,
            'comments' => $meg ?? [],
        ];

        $likes = Like::where('post_id', $post->id)->get();

        $post['likes_data'] = [
            'total_likes' => count($likes) ?? 0,
        ];

        return $this->sendResponse(new PostResource($post), 'Single post view with comments & likes', 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'content' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation error', $validator->errors(), 422); // 422 validation error
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('posts', 'public');
        }

        $post = Post::create([
            'title' => $request->title,
            'content' => $request->content,
            'user_id' => Auth::id(), // current user id
            'image' => $imagePath,
        ]);

        return $this->sendResponse(new PostResource($post), 'Post created successfully', 201); // 201 created
    }

    public function update(Request $request, $id)
    {
        $post = Post::find($id);

        if (!$post) {
            return $this->sendError('Post not found', [], 404); // 404 page not found
        }

        if ($post->user_id !== Auth::id()) {
            return $this->sendError('Unauthorized', [], 403); // 403 Unauthorized user
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string',
            'content' => 'sometimes|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation error', $validator->errors(), 422); // 422 validation error
        }

        if ($request->hasFile('image')) {
            if ($post->image) {
                Storage::disk('public')->delete($post->image);
            }
            $imagePath = $request->file('image')->store('posts', 'public');
            $post->image = $imagePath;
        }

        $post->update($request->only(['title', 'content']));

        return $this->sendResponse(new PostResource($post), 'Post updated successfully', 200); // 200 ok
    }

    public function destroy($id)
    {
        $post = Post::find($id);

        if (!$post) {
            return $this->sendError('Post not found', [], 404); // 404 page not found
        }

        if ($post->user_id !== Auth::id()) {
            return $this->sendError('Unauthorized', [], 403); // 403 Unauthorized user
        }

        if ($post->image) {
            Storage::disk('public')->delete($post->image);
        }

        $post->delete();

        return $this->sendResponse([], 'Post deleted successfully', 200); // 200 ok
    }
}
