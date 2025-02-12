<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CandidateDate;
use App\Models\DateVote;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ScheduleController extends Controller
{
    public function showDatePlanning(Trip $trip)
    {
        // 候補日を取得
        $candidateDates = CandidateDate::where('trip_id', $trip->id)
            ->orderBy('proposed_date')
            ->get();

        // この旅行に関連するすべてのユーザーを取得
        $userIds = collect();
        
        // DateVotesからユーザーIDを取得
        $voteUserIds = DateVote::where('trip_id', $trip->id)
            ->pluck('user_id');
        $userIds = $userIds->concat($voteUserIds);

        // CandidateDatesからユーザーIDを取得
        $candidateUserIds = CandidateDate::where('trip_id', $trip->id)
            ->pluck('user_id');
        $userIds = $userIds->concat($candidateUserIds);

        // 重複を除去してユーザーを取得
        $users = User::whereIn('id', $userIds->unique())->get();

        // 投票データを取得（現在のユーザーの投票のみ）
        $dateVotes = DateVote::where('trip_id', $trip->id)
            ->where('user_id', Auth::id())
            ->get();

        // デバッグ用のログ出力
        \Log::info('CandidateDates:', $candidateDates->toArray());
        \Log::info('DateVotes:', $dateVotes->toArray());

        return view('trips.dateplanning', compact(
            'trip',
            'candidateDates',
            'users',
            'dateVotes'
        ));
    }

    public function addCandidateDate(Request $request, $tripId)
    {
        $request->validate([
            'proposed_date' => 'required|date',
        ]);
    
        // 候補日を作成
        $candidateDate = new CandidateDate();
        $candidateDate->trip_id = $tripId;
        $candidateDate->proposed_date = $request->proposed_date;
        $candidateDate->user_id = auth()->id();  // ログインユーザーのIDを追加
        $candidateDate->save();
    
        // 同じページにリダイレクト
        return redirect()->route('trips.schedule', ['trip' => $tripId])
                        ->with('success', '候補日を追加しました');
    }

    public function voteDate(Request $request, Trip $trip)
    {
        \Log::info('Request all:', $request->all());
        \Log::info('Trip ID:', ['id' => $trip->id]); // デバッグ用

        try {
            $validated = $request->validate([
                'date_id' => 'required|exists:candidate_dates,id',
                'judgement' => 'required|in:〇,△,×'
            ]);

            // trip_idを明示的に設定
            $vote = DateVote::updateOrCreate(
                [
                    'user_id' => auth()->id(),
                    'trip_id' => $trip->id,  // ここが重要
                    'date_id' => $validated['date_id']
                ],
                [
                    'judgement' => $validated['judgement']
                ]
            );

            \Log::info('Vote created:', $vote->toArray());

            return response()->json([
                'success' => true,
                'message' => '判定を保存しました。',
                'data' => $vote
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in voteDate:', [
                'message' => $e->getMessage(),
                'trip_id' => $trip->id,
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'エラーが発生しました: ' . $e->getMessage()
            ], 500);
        }
    }

    public function finalizeSchedule(Request $request, $tripId)
    {
        // 確定処理（例: 最も支持された日を取得するなど）
        return redirect()->route('schedule.show', $tripId)->with('message', 'スケジュールが確定しました。');
    }
}