<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use Illuminate\Http\Request;
use App\Models\CandidateDate;
use App\Models\DateVote;

class CandidateDateController extends Controller
{
    public function getCandidateDates(Request $request)
    {
        $tripId = $request->query('trip_id');
        $dates = CandidateDate::where('trip_id', $tripId)
            ->with(['dateVotes' => function ($query) {
                $query->select('date_id', 'judgement');
            }])
            ->get();

        $events = $dates->map(function ($date) {
            return [
                'title' => '', // 必要に応じてタイトルを変更
                'start' => $date->date,
                'extendedProps' => [
                    'judgement' => $date->dateVotes->first()->judgement ?? ''
                ]
            ];
        });

        return response()->json($events);
    }

    public function setJudgement(Request $request)
    {
        try {
            $validated = $request->validate([
                'date' => 'required|date',
                'judgement' => 'nullable|string'  // nullableに変更
            ]);
    
            $userId = auth()->id();
            $candidateDate = CandidateDate::where('proposed_date', $validated['date'])->first();
    
            if (!$candidateDate) {
                return response()->json(['message' => '候補日が見つかりません'], 404);
            }
    
            $dateVote = DateVote::updateOrCreate(
                [
                    'user_id' => $userId,
                    'trip_id' => $candidateDate->trip_id,  // trip_idを追加
                    'date_id' => $candidateDate->id
                ],
                ['judgement' => $validated['judgement']]
            );
    
            \Log::info('投票処理:', [
                'request' => $request->all(),
                'validated' => $validated,
                'vote' => $dateVote
            ]);
    
            return response()->json(['success' => true, 'data' => $dateVote]);
    
        } catch (\Exception $e) {
            \Log::error('投票エラー:', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);
            return response()->json(['message' => '判定の保存に失敗しました: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(Trip $trip, CandidateDate $candidateDate)
    {
        try {
            // トリップの作成者とメンバーが削除可能
            $userId = auth()->id();
            $isMember = \DB::table('trip_user')
                ->where('trip_id', $trip->id)
                ->where('user_id', $userId)
                ->exists();
    
            if ($trip->user_id !== $userId && !$isMember) {
                return response()->json(['message' => '権限がありません'], 403);
            }
    
            // 候補日が指定されたトリップに属しているか確認
            if ($candidateDate->trip_id !== $trip->id) {
                return response()->json(['message' => '無効な候補日です'], 404);
            }
    
            // 関連する投票を削除
            DateVote::where('date_id', $candidateDate->id)->delete();
            
            // 候補日を削除
            $candidateDate->delete();
    
            return response()->json(['success' => true]);
    
        } catch (\Exception $e) {
            \Log::error('候補日削除エラー:', ['error' => $e->getMessage()]);
            return response()->json(['message' => '削除に失敗しました'], 500);
        }
    }

    public function store(Request $request, Trip $trip)
    {
        try {
            // バリデーション
            $request->validate([
                'date' => 'required|date'
            ]);
    
            // 既に同じ日付が登録されていないかチェック
            $exists = CandidateDate::where('trip_id', $trip->id)
                ->where('proposed_date', $request->date)
                ->exists();
    
            if ($exists) {
                return response()->json(['message' => 'この日付は既に候補日として登録されています'], 422);
            }
    
            // 現在のユーザーIDを取得
            $userId = auth()->id();
    
            // 新しい候補日を作成
            $candidateDate = CandidateDate::create([
                'trip_id' => $trip->id,
                'user_id' => $userId,  // user_idを追加
                'proposed_date' => $request->date
            ]);
    
            return response()->json([
                'success' => true,
                'id' => $candidateDate->id
            ]);
    
        } catch (\Exception $e) {
            \Log::error('候補日追加エラー:', [
                'error' => $e->getMessage(),
                'trip_id' => $trip->id,
                'date' => $request->date
            ]);
            return response()->json(['message' => '候補日の追加に失敗しました'], 500);
        }
    }
}
