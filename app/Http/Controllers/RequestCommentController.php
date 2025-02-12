<?php

namespace App\Http\Controllers;

use App\Models\RequestComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RequestCommentController extends Controller
{
    public function update(Request $request, RequestComment $comment)
    {
        // 権限チェック
        if ($comment->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => '他のユーザーのコメントは編集できません。'
            ], 403);
        }

        $validated = $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $comment->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'コメントを更新しました'
        ]);
    }

    public function destroy(RequestComment $comment)
    {
        try {
            // 権限チェック
            if ($comment->user_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => '他のユーザーのコメントは削除できません。'
                ], 403);
            }
    
            // 削除実行
            $comment->delete();
    
            return response()->json([
                'success' => true,
                'message' => 'コメントを削除しました'
            ]);
    
        } catch (\Exception $e) {
            \Log::error('Comment deletion failed:', [
                'comment_id' => $comment->id,
                'error' => $e->getMessage()
            ]);
    
            return response()->json([
                'success' => false,
                'message' => 'コメントの削除に失敗しました'
            ], 500);
        }
    }
}