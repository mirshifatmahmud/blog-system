<?php

namespace App\Http\Controllers;

use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Like;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CommentController extends BaseController
{
    public function index()
    {
        $comments = Comment::all(); // Post::all(); user data not add.

        foreach ($comments as $comment) {
            $likes = Like::where('comment_id', $comment->id)->get();

            $comment['likes_data'] = [
                'total_likes' => count($likes) ?? 0,
            ];
        }

        return $this->sendResponse($comments, 'All comments retrieved');;
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string',
            'post_id' => 'required|exists:posts,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation error', $validator->errors(), 422); // 422 validation error
        }

        $comment = Comment::create([
            'content' => $request->content,
            'user_id' => auth()->id(),
            'post_id' => $request->post_id,
        ]);

        return $this->sendResponse(new CommentResource($comment), 'Comment created successfully', 201); // 201 created
    }

    public function update(Request $request, $id)
    {
        $comment = Comment::find($id);

        if (!$comment) {
            return $this->sendError('Comment not found', [], 404); // 404 page not found
        }

        if ($comment->user_id !== auth()->id()) {
            return $this->sendError('Unauthorized user'[], 403); // 403 Unauthorized user
        }

        $validator = Validator::make($request->all(), [
            'content' => 'sometimes|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation error', $validator->errors(), 422); // 422 validation error
        }

        $comment->update($request->only(['content']));

        return $this->sendResponse(new CommentResource($comment), 'Comment updated successfully', 200); // 200 ok
    }

    public function destroy($id)
    {
        $comment = Comment::find($id);

        if (!$comment) {
            return $this->sendError('Comment not found', [], 404); // 404 page not found
        }

        if ($comment->user_id !== auth()->id()) {
            return $this->sendError('Unauthorized user'[], 403); // 403 Unauthorized user
        }

        $comment->delete();

        return $this->sendResponse([], 'Comment deleted successfully', 200); // 200 ok
    }
}
