<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Like;
use App\Models\Post;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    public function likePost(Request $request, Post $post)
    {
        $like = Like::firstOrCreate([
            'user_id' => auth()->id(),
            'post_id' => $post->id,
        ]);

        return response()->json(['message' => 'Post liked successfully', 'like' => $like], 201);
    }

    public function likeComment(Request $request, Comment $comment)
    {
        $like = Like::firstOrCreate([
            'user_id' => auth()->id(),
            'comment_id' => $comment->id,
        ]);

        return response()->json(['message' => 'Comment liked successfully', 'like' => $like], 201);
    }
}
