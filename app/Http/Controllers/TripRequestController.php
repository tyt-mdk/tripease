<?php

namespace App\Http\Controllers;

use App\Models\TripRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\RequestComment;  // RequestCommentモデルをインポート

class TripRequestController extends Controller
{
    public function storeComment(Request $request, $requestId)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:1000'
        ]);

        $comment = RequestComment::create([
            'trip_request_id' => $requestId,
            'user_id' => Auth::id(),
            'content' => $validated['content']
        ]);

        return back();
    }

    public function toggleLike(Request $request, $requestId)
    {
        $tripRequest = TripRequest::findOrFail($requestId);
        $user = Auth::user();
        
        if ($tripRequest->likes()->where('user_id', $user->id)->exists()) {
            $tripRequest->likes()->where('user_id', $user->id)->delete();
            $liked = false;
        } else {
            $tripRequest->likes()->create(['user_id' => $user->id]);
            $liked = true;
        }
        
        return response()->json([
            'liked' => $liked,
            'count' => $tripRequest->likes()->count()
        ]);
    }
    public function update(Request $request, TripRequest $tripRequest)
    {
        // 権限チェック
        if ($tripRequest->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => '他のユーザーの要望は編集できません。'
            ], 403);
        }
    
        $validated = $request->validate([
            'content' => 'required|string|max:1000',
        ]);
    
        $tripRequest->update($validated);
    
        return response()->json([
            'success' => true,
            'message' => '要望を更新しました'
        ]);
    }

    public function destroy(TripRequest $request)
    {
        try {
            // 権限チェック
            if ($request->user_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => '他のユーザーの要望は削除できません。'
                ], 403);
            }
    
            // 削除実行
            $request->delete();
    
            return response()->json([
                'success' => true,
                'message' => '要望を削除しました'
            ]);
    
        } catch (\Exception $e) {
            \Log::error('Request deletion failed:', [
                'request_id' => $request->id,
                'error' => $e->getMessage()
            ]);
    
            return response()->json([
                'success' => false,
                'message' => '要望の削除に失敗しました'
            ], 500);
        }
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:1000',
            'trip_id' => 'required|exists:trips,id'
        ]);

        $tripRequest = TripRequest::create([
            'trip_id' => $validated['trip_id'],
            'user_id' => Auth::id(),
            'content' => $validated['content']
        ]);

        return back()->with('success', '要望を投稿しました。');
    }
}