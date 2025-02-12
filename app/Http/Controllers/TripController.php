<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Trip;//追加
use Illuminate\Support\Facades\Validator;//バリデーション追加
use Illuminate\Support\Facades\Auth;//ユーザー情報
use Illuminate\Support\Facades\DB; // DBファサードをインポート
use App\Models\User;
use App\Models\CandidateDate;
use App\Models\DateVote;
use App\Models\TripRequest;
use Illuminate\Support\Str;

class TripController extends Controller
{
    public function __construct()
    {
        // すべてのメソッドに認証を要求
        $this->middleware('auth');
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // 現在のユーザーが参加している旅行のみを表示するように修正
        $trips = Trip::whereHas('users', function($query) {
            $query->where('user_id', auth()->id());
        })->orWhere('creator_id', auth()->id())->get();
        
        return view('trips.dashboard', compact('trips'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();
        return view('trips.tripplanning', ['user' => $user]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // バリデーション
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        // トランザクション開始
        DB::beginTransaction();
        try {
            // 旅行プランの作成
            $trip = Trip::create([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'creator_id' => Auth::id(),
            ]);

            // 作成者を参加者としても追加（必要な場合）
            $trip->users()->attach(Auth::id());

            DB::commit();
            return redirect()->route('trips.show', $trip)
                           ->with('success', '旅行プランを作成しました。');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', '旅行プランの作成に失敗しました。')
                        ->withInput();
        }
    }

    public function storeRequest(Request $request, Trip $trip)
    {
        // アクセス権限チェック
        if (!$trip->users->contains(auth()->id())) {
            return response()->json([
                'success' => false,
                'message' => '権限がありません'
            ], 403);
        }
    
        try {
            $validated = $request->validate([
                'content' => 'required|string|max:1000'
            ]);
        
            $tripRequest = new TripRequest([
                'trip_id' => $trip->id,
                'user_id' => Auth::id(),
                'content' => $validated['content']
            ]);
        
            $tripRequest->save();
        
            return response()->json([
                'success' => true,
                'message' => '要望を保存しました'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'バリデーションエラー',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error storing request:', ['message' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'エラーが発生しました'
            ], 500);
        }
    }    

    /**
     * Display the specified resource.
     */
    public function eachplanning(Trip $trip)
    {
        // ユーザーがこの旅行に参加しているか確認
        if (!$trip->users->contains(auth()->id()) && $trip->creator_id !== auth()->id()) {
            return redirect()->route('trips.index')
                ->with('error', 'この旅行計画にアクセスする権限がありません。');
        }    
    
        // 作成者と参加者を結合して一意のユーザーリストを作成
        $trip->load(['creator', 'users']);
        $allUsers = collect([$trip->creator])->concat($trip->users)->unique('id');

    
        // 要望（trip_requests）とそれに関連するコメントといいねを取得
        $userRequests = $trip->tripRequests()
            ->with(['user', 'comments.user', 'likes.user'])
            ->get();
    
        // 候補日とその投票を取得
        $candidateDates = $trip->candidateDates()
            ->with(['user', 'dateVotes.user'])
            ->orderBy('proposed_date')
            ->get();
    
        // 日程の投票を取得
        $dateVotes = DateVote::where('trip_id', $trip->id)
            ->with(['user', 'candidateDate'])
            ->get();
    
        return view('trips.eachplanning', [
            'trip' => $trip,
            'users' => $allUsers,
            'user' => auth()->user(),
            'userRequests' => $userRequests ?? collect(),
            'candidateDates' => $candidateDates ?? collect(),
            'dateVotes' => $dateVotes ?? collect(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Trip $trip)
    {
        // 権限チェック
        if (!$trip->users->contains(auth()->id()) && $trip->creator_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'この旅行計画を編集する権限がありません。'
            ], 403);
        }
    
        // バリデーション
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);
    
        // データ更新
        $trip->update($validated);
    
        return response()->json([
            'success' => true,
            'message' => '更新しました'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Trip $trip)
    {
        try {
            if ($trip->creator_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => '削除権限がありません'
                ], 403);
            }
    
            $trip->delete();
    
            // 204ステータスコードを返すか、JSONレスポンスを返す
            return response()->json([
                'success' => true,
                'message' => '旅行計画を削除しました'
            ]);
            // または
            // return response()->noContent(); // 204レスポンス
        } catch (\Exception $e) {
            \Log::error('Trip deletion failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => '削除に失敗しました'
            ], 500);
        }
    }

    public function voteDateJudgement(Request $request, Trip $trip)
    {
        // アクセス権限チェックを追加
        if (!$trip->users->contains(auth()->id())) {
            return response()->json([
                'success' => false,
                'message' => '権限がありません'
            ], 403);
        }
    
        \Log::info('Request all:', $request->all());
    
        try {
            $validated = $request->validate([
                'date_id' => 'required|exists:candidate_dates,id',
                'judgement' => 'required|in:〇,△,×'
            ]);
    
            // date_idが本当にこのtripに属しているか確認
            $candidateDate = CandidateDate::where('id', $validated['date_id'])
                ->where('trip_id', $trip->id)
                ->firstOrFail();
    
            $vote = DateVote::updateOrCreate(
                [
                    'user_id' => auth()->id(),
                    'trip_id' => $trip->id,
                    'date_id' => $validated['date_id']
                ],
                [
                    'judgement' => $validated['judgement']
                ]
            );
    
            return response()->json([
                'success' => true,
                'message' => '判定を保存しました。',
                'data' => $vote
            ]);
    
        } catch (ValidationException $e) {
            \Log::error('Validation error:', $e->errors());
            return response()->json([
                'success' => false,
                'message' => 'バリデーションエラー',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error:', ['message' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'エラーが発生しました'  // エラーメッセージの詳細は本番環境では表示しない
            ], 500);
        }
    }

    public function showJoinConfirmation($token)
    {
        // トークンの有効性チェックを追加
        $trip = Trip::where('share_token', $token)
            ->whereNotNull('share_token')
            ->firstOrFail();
    
        // 未ログインの場合
        if (!Auth::check()) {
            return redirect()->route('login')
                ->with('message', 'この旅行計画に参加するにはログインが必要です。');
        }

        // 既に参加している場合
        if ($trip->users->contains(Auth::id())) {
            return view('trips.join-confirmation', [
                'trip' => $trip,
                'alreadyJoined' => true
            ]);
        }

        // 未参加の場合
        return view('trips.join-confirmation', [
            'trip' => $trip,
            'alreadyJoined' => false
        ]);
    }
    
    public function joinByToken($token)
    {
        // トークンの有効性チェックを追加
        $trip = Trip::where('share_token', $token)
            ->whereNotNull('share_token')
            ->firstOrFail();
        
        // 認証チェック
        if (!auth()->check()) {
            return redirect()->route('login');
        }
        
        // 既に参加している場合
        if ($trip->users->contains(auth()->id())) {
            return redirect()->route('trips.eachplanning', ['trip' => $trip->id])
                ->with('info', 'すでにこの旅行計画に参加しています');
        }
        
        try {
            DB::beginTransaction();
            // 参加処理
            $trip->users()->attach(auth()->id());
            DB::commit();
            
            return redirect()->route('trips.eachplanning', ['trip' => $trip->id])
                ->with('success', '旅行計画に参加しました！');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Join trip failed: ' . $e->getMessage());
            return redirect()->route('trips.index')
                ->with('error', '参加処理に失敗しました');
        }
    }

    public function generateShareLink(Trip $trip)
    {
        try {
            if (!$trip->share_token) {
                $trip->share_token = Str::random(32);
                $trip->save();
            }
    
            // 完全なURLを生成して返す
            return response()->json([
                'success' => true,
                'share_url' => route('trips.join', ['token' => $trip->share_token])
            ]);
    
        } catch (\Exception $e) {
            \Log::error('Share link generation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => '共有リンクの生成に失敗しました'
            ], 500);
        }
    }

    /**
     * 参加中の旅行計画一覧を表示
     */
    public function participating()
    {
        try {
            \Log::info('Participating method called'); // デバッグ用
            
            // ユーザーの取得を確認
            $user = auth()->user();
            if (!$user) {
                \Log::error('User not authenticated');
                return redirect()->route('login');
            }
            \Log::info('User found', ['id' => $user->id]);
    
            // リレーションのロードを確認
            try {
                $user->load(['trips' => function($query) {
                    $query->orderBy('updated_at', 'desc');
                }]);
                \Log::info('Trips loaded', ['count' => $user->trips->count()]);
            } catch (\Exception $e) {
                \Log::error('Error loading trips: ' . $e->getMessage());
                throw $e;
            }
    
            // ビューの存在確認
            if (!view()->exists('trips.participating')) {
                \Log::error('View trips.participating does not exist');
                throw new \Exception('View not found');
            }
            
            return view('trips.participating', compact('user'));
        } catch (\Exception $e) {
            \Log::error('Error in participating method: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            // 開発環境でのみ詳細なエラーを表示
            if (config('app.debug')) {
                throw $e;
            }
            
            return response()->view('errors.500', [], 500);
        }
    }

    public function updateDates(Request $request, Trip $trip)
    {
        // アクセス権限チェック（作成者のみ更新可能）
        if ($trip->creator_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => '権限がありません'
            ], 403);
        }
    
        try {
            $validated = $request->validate([
                'confirmed_start_date' => 'required|date',
                'confirmed_end_date' => 'required|date|after_or_equal:confirmed_start_date',
            ]);
        
            if ($request->has('isDayTrip') && $request->isDayTrip === 'on') {
                $validated['confirmed_end_date'] = $validated['confirmed_start_date'];
            }
        
            $trip->update($validated);
        
            return redirect()->back()->with('success', '日程を更新しました');
        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            \Log::error('Error updating dates:', ['message' => $e->getMessage()]);
            return redirect()->back()
                ->with('error', '更新に失敗しました');
        }
    }

    // 要望投稿用のメソッドを追加
    public function storeWish(Request $request, Trip $trip)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $wish = new TripRequest();
        $wish->trip_id = $trip->id;
        $wish->user_id = auth()->id();
        $wish->content = $validated['content'];
        $wish->save();

        return response()->json(['success' => true, 'wish' => $wish]);
    }
}
