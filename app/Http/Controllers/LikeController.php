<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Like;
use App\Models\Post;
use Illuminate\Http\Request;

class LikeController extends BaseController
{
    public function likePost(Request $request, Post $post)
    {
        $like = Like::firstOrCreate([
            'user_id' => auth()->id(),
            'post_id' => $post->id,
        ]);

        return $this->sendResponse($like,'Post liked successfully',201); // 201 created
    }

    public function likeComment(Request $request, Comment $comment)
    {
        $like = Like::firstOrCreate([
            'user_id' => auth()->id(),
            'comment_id' => $comment->id,
        ]);

        return $this->sendResponse($like,'Comment liked successfully',201); // 201 created
    }

    // ==============================================================================================

    public function unlikePost(Post $post)
    {
        $userId = auth()->id();

        $like = Like::where('user_id', $userId)->first();

        if ($like) {
            $like->delete();
            return $this->sendResponse([],'Post unliked successfully',200); // 200 created
        }

        return $this->sendError('Like not found',[],404);
    }

    public function unlikeComment(Comment $comment)
    {
        $userId = auth()->id();

        $like = Like::where('user_id', $userId)->first();

        if ($like) {
            $like->delete();
            return $this->sendResponse([],'Comment unliked successfully',200); // 200 created
        }

        return $this->sendError('Like not found',[],404);
    }
}
