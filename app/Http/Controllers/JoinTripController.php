<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Trip;

class JoinTripController extends Controller
{
    public function joinViaUrl(Request $request)
    {
        try {
            $url = $request->input('url');
            
            if (!$this->isValidTripUrl($url)) {
                return response()->json([
                    'success' => false,
                    'message' => '無効なURLです'
                ]);
            }
    
            $tripId = $this->extractTripId($url);
            $trip = Trip::find($tripId);
    
            if (!$trip) {
                return response()->json([
                    'success' => false,
                    'message' => '指定された旅行計画が見つかりません'
                ]);
            }
    
            // 既に参加しているかチェック
            if ($trip->users->contains(auth()->id())) {
                return response()->json([
                    'success' => true,
                    'message' => '既に旅行計画に参加しています'
                ]);
            }
    
            // 新規参加の処理
            $trip->users()->syncWithoutDetaching(auth()->id());
    
            return response()->json([
                'success' => true,
                'message' => '旅行計画に参加しました'
            ]);
    
        } catch (\Exception $e) {
            \Log::error('Join trip error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => '参加処理中にエラーが発生しました'
            ]);
        }
    }

    private function isValidTripUrl($url)
    {
        try {
            \Log::info('Validating URL: ' . $url);
            
            // URLのパターンを修正
            $pattern = '/\/trips\/join\/[a-zA-Z0-9]+/';
            $isValid = preg_match($pattern, $url);
            
            \Log::info('URL validation result: ' . ($isValid ? 'valid' : 'invalid'));
            
            return $isValid;
        } catch (\Exception $e) {
            \Log::error('URL validation error: ' . $e->getMessage());
            return false;
        }
    }
    
    private function extractTripId($url)
    {
        try {
            // URLからトークンを抽出
            $parts = explode('/', rtrim($url, '/'));
            $token = end($parts); // "zzZK7j6kWIvV7KRqn4SremDaPDECKBo1" の部分を取得
            
            \Log::info('Extracted token: ' . $token);
            
            // トークンからTripを検索
            $trip = Trip::where('share_token', $token)->first();
            
            if ($trip) {
                \Log::info('Found trip with ID: ' . $trip->id);
                return $trip->id;
            }
            
            \Log::info('No trip found for token: ' . $token);
            return null;
        } catch (\Exception $e) {
            \Log::error('Token extraction error: ' . $e->getMessage());
            return null;
        }
    }
}
