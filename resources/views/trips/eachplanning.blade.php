<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script src="https://kit.fontawesome.com/ef96165231.js" crossorigin="anonymous"></script>

    <style>
        /* プレースホルダーのスタイル */
        #request-add-form input::placeholder {
            color: #94a3b8; /* text-slate-400 */
        }

        /* フォーカス時のアウトラインを削除 */
        #request-add-form input:focus {
            outline: none;
        }
        /* プレースホルダーのスタイル */
        form[id^="comment-add-form-"] input::placeholder {
            color: #94a3b8; /* text-slate-400 */
        }

        /* フォーカス時のアウトラインを削除 */
        form[id^="comment-add-form-"] input:focus {
            outline: none;
        }
        .mode-tab {
            transition: all 0.3s ease;
            color: #64748b;  /* text-slate-500相当 */
        }
        .mode-tab:hover {
            color: #1e293b;  /* text-slate-900相当 */
        }
        .mode-tab.active > div {
            background-color: white;
            padding: 0.5rem 1rem;  /* py-2 px-4相当 */
            border-radius: 9999px;  /* rounded-full相当 */
            color: #1e293b;  /* text-slate-900相当 */
            box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1);  /* shadow-sm相当 */
        }
        .edit-mode-only {
            display: none !important;
        }
        body.edit-mode .edit-mode-only {
            display: block !important;
        }
        /* flexコンテナ用の追加定義 */
        body.edit-mode .edit-mode-only.flex {
            display: flex !important;
        }
        body:not(.edit-mode) .view-mode-only {
            display: block;
        }
        body.edit-mode .view-mode-only {
            display: none;
        }
        .edit-mode [data-editable] p {
            cursor: pointer;
        }
        [data-editable] form {
            display: none;
        }
        body.edit-mode [data-editable].editing form {
            display: block;
        }
        // 削除確認用のトースト通知のスタイルを追加
        .confirm-toast {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background-color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 9999px;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            z-index: 50;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .confirm-toast button {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            transition: all 0.2s;
        }
    </style>

    <title>Tripease</title>
    @vite('resources/css/app.css')
</head>
<body class="flex flex-col min-h-screen bg-slate-100 text-slate-800 font-notosans text-sm md:text-base">
    <!-- フラッシュメッセージ（固定位置） -->
    <div class="fixed top-4 left-1/2 transform -translate-x-1/2 z-50 w-full max-w-4xl px-4">
        @if(session('success'))
            <div id="successMessage" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4 transition-opacity duration-500" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div id="errorMessage" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4 transition-opacity duration-500" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif
    </div>
    <!-- 通知メッセージ -->
    <div id="notification" class="fixed bottom-24 left-1/2 transform -translate-x-1/2 bg-slate-800 text-white px-4 py-2 rounded-full shadow-lg opacity-0 transition-opacity duration-300 z-50" style="display: none;">
        <span id="notificationText"></span>
    </div>
    <header>
    </header>
    <!-- タブナビゲーション -->
    <div class="border-b border-slate-200 mb-6 mt-6">
        <nav class="max-w-4xl mx-auto px-4">
            <div class="flex space-x-8" aria-label="Tabs">
                <!-- 概要タブ -->
                <a href="#overview" 
                onclick="switchTab('overview')"
                class="touch-feedback tab-link px-3 py-2 text-sm font-medium border-b-2 border-sky-500 text-sky-600">
                    <i class="fa-regular fa-calendar-days mr-2"></i>概要
                </a>
                
                <!-- 要望タブ -->
                <a href="#requests" 
                onclick="switchTab('requests')"
                class="touch-feedback tab-link px-3 py-2 text-sm font-medium border-b-2 border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300">
                    <i class="fa-regular fa-comment mr-2"></i>要望
                </a>
                
                <!-- 旅程ノートタブ -->
                <a href="#itinerary" 
                onclick="switchTab('itinerary')"
                class="touch-feedback tab-link px-3 py-2 text-sm font-medium border-b-2 border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300">
                    <i class="fa-regular fa-note-sticky mr-2"></i>旅程ノート
                </a>
            </div>
        </nav>
    </div>
    <main class="flex-1 max-w-4xl mx-auto w-full px-4 py-4 md:py-6 space-y-4 md:space-y-6 pb-32 md:pb-24">
        <!-- タブコンテンツ全体を囲む -->
        <div class="tab-content">
            <!-- 概要タブの内容 -->
            <div id="overview-content" class="tab-pane">
                <!-- タイトルと目的セクション -->
                <section class="bg-white rounded-lg shadow-sm p-3 md:p-4 space-y-3 md:space-y-4 relative mb-6">
                    <form id="tripEditForm" method="POST" action="{{ route('trips.update', $trip) }}" class="space-y-3 md:space-y-4">
                        @csrf
                        @method('PUT')
                        
                        <!-- タイトル -->
                        <div class="space-y-1">
                            <p class="text-slate-400 text-sm md:text-base">タイトル</p>
                            <div class="view-mode-only">
                                <h1 class="text-lg md:text-xl font-medium text-slate-800">{{ $trip->title }}</h1>
                            </div>
                            <div class="edit-mode-only">
                                <input type="text" 
                                    name="title" 
                                    value="{{ $trip->title }}" 
                                    class="w-full px-3 py-2 border border-slate-200 rounded-md focus:outline-none focus:border-sky-500"
                                    required>
                            </div>
                        </div>

                        <!-- 区切り線 -->
                        <div class="border-t border-slate-200"></div>

                        <!-- 概要メモ -->
                        <div class="space-y-1">
                            <p class="text-slate-400 text-sm">概要メモ</p>
                            <div class="view-mode-only">
                                <p class="text-slate-700 whitespace-pre-wrap">{{ $trip->description }}</p>
                            </div>
                            <div class="edit-mode-only">
                                <textarea name="description" 
                                        rows="4" 
                                        class="w-full px-3 py-2 border border-slate-200 rounded-md focus:outline-none focus:border-sky-500"
                                        required>{{ $trip->description }}</textarea>
                            </div>
                        </div>
                    </form>
                </section>

                <!-- 共有リンク作成ボタン -->
                <div x-data="{ 
                    showShareLink: false, 
                    shareUrl: '',
                    generateShare() {
                        fetch('{{ route('trips.generateShareLink', $trip) }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // share_urlを直接使用
                                this.shareUrl = data.share_url;
                                this.showShareLink = true;
                            } else {
                                throw new Error(data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('共有リンクの生成に失敗しました');
                        });
                    }
                }" class="absolute top-4 right-4">
                    <!-- 共有ボタン -->
                    <button @click="generateShare()"
                            class="inline-flex items-center justify-center space-x-2 px-3 py-2 bg-white text-slate-600 text-sm font-medium rounded-full border border-slate-200 hover:bg-slate-50 transition-colors">
                        <i class="fa-solid fa-share-nodes"></i>
                        <span>共有</span>
                    </button>

                    <!-- 共有リンクのモーダル -->
                    <template x-if="showShareLink">
                        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
                            @click.self="showShareLink = false">
                            <div class="bg-white p-6 rounded-lg shadow-xl max-w-md w-full mx-4">
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="text-lg font-medium text-slate-900">共有リンク</h3>
                                    <button @click="showShareLink = false" class="text-slate-400 hover:text-slate-500">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </div>
                                <div class="space-y-4">
                                    <div class="flex items-center space-x-2">
                                        <input type="text" 
                                            x-model="shareUrl" 
                                            readonly 
                                            class="flex-1 px-3 py-2 bg-slate-50 border border-slate-200 rounded-md text-slate-600 text-sm focus:outline-none">
                                        <button @click="navigator.clipboard.writeText(shareUrl)"
                                                class="inline-flex items-center justify-center px-3 py-2 bg-sky-500 text-white text-sm font-medium rounded-md hover:bg-sky-600 transition-colors">
                                            <i class="fa-regular fa-copy mr-2"></i>
                                            コピー
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- 確定した日程の表示セクション -->
                <section class="bg-white rounded-lg shadow-sm overflow-hidden mb-6">
                    <div class="p-4 bg-slate-50 border-b border-slate-200">
                        <h3 class="font-medium text-slate-800">確定した日程</h3>
                    </div>

                    <!-- 確定日程の表示（常に表示） -->
                    <div id="confirmedDateDisplay" class="p-4">
                        <div class="flex items-center gap-2 text-slate-700">
                            <i class="fa-regular fa-calendar text-sky-500"></i>
                            <span class="text-base" id="dateDisplayText">
                                @if($trip->confirmed_start_date && $trip->confirmed_end_date)
                                    @php
                                        $startDate = \Carbon\Carbon::parse($trip->confirmed_start_date);
                                        $endDate = \Carbon\Carbon::parse($trip->confirmed_end_date);
                                        $isSameDay = $startDate->isSameDay($endDate);
                                    @endphp
                                    
                                    {{ $startDate->format('Y年n月j日') }}
                                    @if($isSameDay)
                                        <span class="ml-2 text-sm text-slate-500">(日帰り)</span>
                                    @else
                                        <span class="mx-2">～</span>
                                        {{ $endDate->format('Y年n月j日') }}
                                        <span class="ml-2 text-sm text-slate-500">
                                            ({{ $startDate->diffInDays($endDate) }}泊
                                            {{ $startDate->diffInDays($endDate) + 1 }}日)
                                        </span>
                                    @endif
                                @else
                                    <span class="text-slate-500">
                                        <i class="fa-regular fa-calendar-xmark mr-2"></i>
                                        日程はまだ確定していません
                                    </span>
                                @endif
                            </span>
                        </div>
                    </div>

                    <!-- 編集モードの場合のみ表示 -->
                    <!-- 編集フォーム -->
                    <form id="confirmedDateForm" action="{{ route('trips.update-dates', ['trip' => $trip->id]) }}" 
                        method="POST" class="p-4 space-y-4 border-t border-slate-200 bg-slate-50 edit-mode-only hidden">
                        @csrf
                        @method('PATCH')
                        
                        <!-- 日帰り旅行かどうかの選択 -->
                        <div class="mb-4">
                            <label class="inline-flex items-center text-sm text-slate-700">
                                <input type="checkbox" name="isDayTrip" id="isDayTrip" class="form-checkbox text-sky-500" 
                                    onchange="toggleDateInputs(this.checked)"
                                    {{ $trip->confirmed_start_date === $trip->confirmed_end_date ? 'checked' : '' }}>
                                <span class="ml-2">日帰り旅行</span>
                            </label>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block">
                                    <span class="text-sm font-medium text-slate-700" id="dateLabel">
                                        {{ $trip->confirmed_start_date === $trip->confirmed_end_date ? '旅行日' : '開始日' }}
                                    </span>
                                    <input type="date" name="confirmed_start_date" id="startDate"
                                        class="mt-1 w-full rounded shadow-sm border-slate-200 focus:border-sky-500 focus:ring focus:ring-sky-200 focus:ring-opacity-50"
                                        value="{{ $trip->confirmed_start_date }}"
                                        onchange="updateDateDisplay()">
                                </label>
                            </div>
                            <div id="endDateContainer">
                                <label class="block">
                                    <span class="text-sm font-medium text-slate-700">終了日</span>
                                    <input type="date" name="confirmed_end_date" id="endDate"
                                        class="mt-1 w-full rounded shadow-sm border-slate-200 focus:border-sky-500 focus:ring focus:ring-sky-200 focus:ring-opacity-50"
                                        value="{{ $trip->confirmed_end_date }}"
                                        onchange="updateDateDisplay()">
                                </label>
                            </div>
                        </div>
                    </form>
                </section>

                <!-- 候補日一覧テーブル -->
                <section class="bg-white rounded-lg shadow-sm overflow-hidden mb-6">
                    @php
                        // 投票済みのユーザーのみを取得
                        $votedUsers = $users->filter(function($user) use ($dateVotes) {
                            return $dateVotes->where('user_id', $user->id)->count() > 0;
                        });
                        
                        $hasAnyVotes = $dateVotes->count() > 0;
                        $loginUserVoted = $dateVotes->where('user_id', auth()->id())->count() > 0;
                        $otherUsersVoted = $dateVotes->where('user_id', '!=', auth()->id())->count() > 0;
                        $allUsersVoted = $users->every(function($user) use ($dateVotes, $candidateDates) {
                            return $dateVotes->where('user_id', $user->id)->count() === $candidateDates->unique('proposed_date')->count();
                        });
                        
                        // 全参加者が投票済みかどうかを確認
                        $allParticipantsVoted = $votedUsers->count() === $users->count();
                    @endphp

                    @if($candidateDates->count() > 0 && $votedUsers->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="bg-slate-50 border-b border-slate-200">
                                        <th class="p-3 text-center font-medium text-slate-600 min-w-[100px]">参加者</th>
                                        @foreach($candidateDates->sortBy('proposed_date')->unique('proposed_date') as $date)
                                            <th class="p-3 text-center font-medium text-slate-600 whitespace-nowrap">
                                                {{ \Carbon\Carbon::parse($date->proposed_date)->format('n/j') }}
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($votedUsers as $user)
                                        <tr class="border-b border-slate-200 last:border-0">
                                            <td class="p-3 text-center font-medium min-w-[100px]">
                                                {{ $user->name }}
                                            </td>
                                            @foreach($candidateDates->sortBy('proposed_date')->unique('proposed_date') as $date)
                                                <td class="p-3 text-center">
                                                    @php
                                                        $vote = $dateVotes->where('user_id', $user->id)
                                                                    ->where('date_id', $date->id)
                                                                    ->first();
                                                    @endphp
                                                    <span class="
                                                        @if($vote && $vote->judgement === '〇') text-emerald-500
                                                        @elseif($vote && $vote->judgement === '△') text-orange-500
                                                        @elseif($vote && $vote->judgement === '×') text-rose-500
                                                        @else text-slate-400
                                                        @endif
                                                    ">
                                                        {{ $vote ? $vote->judgement : '未' }}
                                                    </span>
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- 状態メッセージ -->
                        <div class="p-4 text-center text-slate-500">
                            @if($allParticipantsVoted)
                                結果を表示しています。
                            @elseif($loginUserVoted && !$otherUsersVoted)
                                他の参加者の登録待ちです。
                            @elseif(!$loginUserVoted && $otherUsersVoted)
                                候補日はまだ登録されていません。
                            @elseif($loginUserVoted && $otherUsersVoted)
                                他の参加者の登録待ちです。
                            @endif
                        </div>
                    @else
                        <div class="p-4 text-center text-slate-500">
                            候補日はまだ登録されていません。
                        </div>
                    @endif
                </section>

                <!-- 日程調整ボタン -->
                <div class="text-center edit-mode-only mb-6">
                    <a href="{{ route('trips.schedule', $trip->id) }}" 
                    class="touch-feedback inline-flex items-center justify-center px-6 py-2.5 bg-sky-500 text-white font-medium rounded-md shadow-sm hover:bg-sky-600 transition-colors">
                    <i class="fa-regular fa-calendar-check mr-2"></i>
                    日程を調整する
                    </a>
                </div>
            </div><!-- 概要タブ終わり -->

            <!-- 要望タブの内容 -->
            <div id="requests-content" class="tab-pane hidden">
                <!-- ユーザー要望一覧 -->
                <section class="bg-white rounded-lg shadow-sm p-3 md:p-4 space-y-3 md:space-y-4">
                    <h2 class="text-base md:text-lg font-medium text-slate-700 border-b border-slate-200 pb-2">みんなの要望</h2>

                    <!-- 要望追加フォーム -->
                    <div class="mt-4 mb-6 edit-mode-only" style="display: none;">
                        <div id="request-add-placeholder" class="cursor-pointer">
                            <div class="text-slate-400 border-b-2 border-slate-600 py-2">
                                要望を追加する
                            </div>
                        </div>
                    
                        <form id="request-add-form" action="{{ route('requests.store') }}" method="POST" class="hidden">
                            @csrf
                            <input type="hidden" name="trip_id" value="{{ $trip->id }}">
                            <div class="flex flex-col space-y-3">
                                <input type="text" 
                                    name="content" 
                                    class="w-full border-0 border-b-2 border-slate-600 focus:ring-0 focus:border-sky-500 py-2 text-slate-700 bg-transparent transition-colors"
                                    placeholder="要望を追加する">
                                <div class="flex items-center justify-end space-x-3 pt-2">
                                    <button type="button" 
                                            onclick="cancelAddRequest()"
                                            class="touch-feedback px-4 py-1.5 text-slate-500 hover:text-slate-600 rounded-full text-sm transition-colors">
                                        キャンセル
                                    </button>
                                    <button type="submit" 
                                            class="touch-feedback px-4 py-1.5 bg-sky-500 hover:bg-sky-600 text-white rounded-full text-sm transition-colors">
                                        投稿
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <!-- 要望一覧 -->
                    <div class="space-y-4">
                        @foreach($userRequests as $request)
                            <div class="border-b border-slate-100 last:border-0 pb-4">
                                <!-- ヘッダー部分（ユーザー名、日時） -->
                                <div class="flex items-start justify-between">
                                    <div class="flex items-start space-x-2">
                                        <p class="font-medium text-slate-700">{{ $request->user->name }}</p>
                                        <p class="text-slate-400">
                                            {{ \Carbon\Carbon::parse($request->created_at)->format('n/j H:i') }}
                                        </p>
                                    </div>
                                </div>

                                <!-- 要望内容 -->
                                <div class="flex-1" 
                                    data-editable="{{ $request->user_id === Auth::id() ? 'true' : 'false' }}" 
                                    data-type="request" 
                                    data-id="{{ $request->id }}">
                                    <!-- 表示モード -->
                                    <div id="request-content-{{ $request->id }}">
                                        <p class="text-slate-600 rounded px-2 py-1 transition-colors {{ $request->user_id === Auth::id() ? 'hover:bg-slate-50' : '' }}">
                                            {{ $request->content }}
                                        </p>
                                    </div>
                                    <!-- 編集モード（作成者のみ表示） -->
                                    @if($request->user_id === Auth::id())
                                        <form id="request-edit-form-{{ $request->id }}"
                                                style="display: none;"
                                                onsubmit="return false;">
                                            @csrf
                                            @method('PUT')
                                            <div class="flex items-start space-x-2">
                                                <textarea name="content" 
                                                        class="flex-1 px-2 py-1 border border-slate-200 rounded-md focus:outline-none focus:border-sky-500"
                                                        rows="2">{{ $request->content }}</textarea>
                                                <div class="flex items-center space-x-1">
                                                    <button type="button" 
                                                            onclick="cancelEdit('request', {{ $request->id }})"
                                                            class="touch-feedback p-1 text-slate-400 hover:text-slate-600">
                                                        <i class="fa-solid fa-xmark"></i>
                                                    </button>
                                                    <button type="button" 
                                                            onclick="deleteRequest({{ $request->id }})"
                                                            class="touch-feedback p-1 text-slate-400 hover:text-rose-500 edit-mode-only">
                                                        <i class="fa-solid fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    @endif
                                </div>

                                <!-- アクションボタン部分 -->
                                <div class="flex items-center space-x-3 mt-2">
                                    <!-- いいねボタン（常に表示） -->
                                    <button 
                                        onclick="toggleLike({{ $request->id }}, this)" 
                                        class="touch-feedback like-button flex items-center space-x-1 {{ $request->isLikedBy(Auth::user()) ? 'text-red-500' : 'text-slate-400' }}"
                                    >
                                        <i class="fa-heart {{ $request->isLikedBy(Auth::user()) ? 'fas' : 'far' }}"></i>
                                        <span class="like-count">{{ $request->likes->count() }}</span>
                                    </button>

                                    <!-- 返信ボタン（編集モード時のみ表示） -->
                                    <button type="button" 
                                            onclick="showCommentForm({{ $request->id }})"
                                            class="touch-feedback edit-mode-only text-slate-400 hover:text-slate-600 text-sm transition-colors"
                                            style="display: none;">
                                        <i class="far fa-comment"></i>
                                    </button>

                                    <!-- コメント数表示/折りたたみボタン -->
                                    <button type="button"
                                            onclick="toggleComments({{ $request->id }})"
                                            class="touch-feedback text-slate-400 hover:text-slate-600 text-sm transition-colors">
                                        <i class="fa-solid fa-caret-down comment-icon-{{ $request->id }} {{ $request->comments->count() > 0 ? '' : 'hidden' }}"></i>
                                        <span class="comment-count-{{ $request->id }}">{{ $request->comments->count() > 0 ? $request->comments->count().'件の返信' : '' }}</span>
                                    </button>
                                </div>

                                <!-- コメント追加フォーム -->
                                <div id="comments-section-{{ $request->id }}" class="mt-2 hidden">
                                    <!-- コメント入力フォーム -->
                                    <form id="comment-add-form-{{ $request->id }}" 
                                        action="{{ route('requests.comment', $request->id) }}" 
                                        method="POST" 
                                        class="hidden mb-4">
                                        @csrf
                                        <div class="flex flex-col space-y-3">
                                            <input type="text" 
                                                name="content" 
                                                class="w-full border-0 border-b-2 border-slate-600 focus:ring-0 focus:border-sky-500 py-2 text-slate-700 bg-transparent transition-colors text-sm"
                                                placeholder="コメントを追加">
                                            <div class="flex items-center justify-end space-x-3 pt-2">
                                                <button type="button" 
                                                        onclick="cancelCommentForm({{ $request->id }})"
                                                        class="touch-feedback px-4 py-1.5 text-slate-500 hover:text-slate-600 rounded-full text-sm transition-colors">
                                                    キャンセル
                                                </button>
                                                <button type="submit" 
                                                        class="touch-feedback px-4 py-1.5 bg-sky-500 hover:bg-sky-600 text-white rounded-full text-sm transition-colors">
                                                    送信
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                    <div class="mt-4 pl-4 space-y-2">
                                        @foreach($request->comments as $comment)
                                            <div class="group">
                                                <!-- コメントヘッダー -->
                                                <div class="flex items-start space-x-2">
                                                    <p class="font-medium text-slate-700">{{ $comment->user->name }}</p>
                                                    <p class="text-slate-400">
                                                        {{ \Carbon\Carbon::parse($comment->created_at)->format('n/j H:i') }}
                                                    </p>
                                                </div>
                                                
                                                <!-- コメント内容 -->
                                                @if($comment->user_id === Auth::id())
                                                    <div class="flex items-start justify-between">
                                                        <div class="flex-1" data-editable data-type="comment" data-id="{{ $comment->id }}">
                                                            <!-- 表示モード -->
                                                            <div id="comment-content-{{ $comment->id }}">
                                                                <p class="text-slate-600 rounded px-2 py-1 transition-colors hover:bg-slate-50">
                                                                    {{ $comment->content }}
                                                                </p>
                                                            </div>
                                                            <!-- コメントの編集フォーム -->
                                                            <form action="{{ route('request.comments.update', $comment->id) }}" 
                                                                method="POST" 
                                                                style="display: none;"
                                                                id="comment-edit-form-{{ $comment->id }}"
                                                                onsubmit="return false;">
                                                            @csrf
                                                            @method('PUT')
                                                            <div class="flex items-start space-x-2">
                                                                <input type="text" 
                                                                        name="content" 
                                                                        value="{{ $comment->content }}"
                                                                        class="flex-1 px-2 py-1 border border-slate-200 rounded-md focus:outline-none focus:border-sky-500">
                                                                <div class="flex items-center space-x-1">
                                                                    <button type="button" 
                                                                            onclick="cancelEdit('comment', {{ $comment->id }})"
                                                                            class="touch-feedback p-1 text-slate-400 hover:text-slate-600">
                                                                        <i class="fa-solid fa-xmark"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                            </form>
                                                        </div>
                                                        <!-- 削除ボタン（編集モードのみ表示） -->
                                                        <div class="edit-mode-only opacity-0 group-hover:opacity-100 transition-opacity">
                                                            <button type="button"
                                                                    onclick="deleteComment({{ $comment->id }})" 
                                                                    class="touch-feedback p-1 text-slate-400 hover:text-rose-500">
                                                                <i class="fa-solid fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                @else
                                                    <p class="text-slate-600 px-2 py-1">{{ $comment->content }}</p>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            </div><!-- 要望タブ終わり -->

            <!-- 旅程ノートタブの内容 -->
            <div id="itinerary-content" class="tab-pane hidden">
                <div class="md:flex gap-6">
                    <!-- 左側：旅程ノート一覧 -->
                    <div class="flex-1 space-y-4 mb-6">
                        <!-- 旅程ノート追加フォーム -->
                        <div class="edit-mode-only">
                            <form id="itinerary-add-form" 
                                action="{{ route('trips.itinerary.store', $trip) }}"
                                method="POST" 
                                class="bg-white rounded-lg shadow-sm p-4 space-y-4">
                                @csrf
                                <div class="space-y-3">
                                    <input type="text" 
                                        name="day_label" 
                                        placeholder="日程ラベル（例：1日目、最終日）" 
                                        class="w-full px-3 py-2 border border-slate-200 rounded-md focus:outline-none focus:border-sky-500">
                                    <textarea name="memo" 
                                            rows="4" 
                                            placeholder="メモを入力" 
                                            class="w-full px-3 py-2 border border-slate-200 rounded-md focus:outline-none focus:border-sky-500"></textarea>
                                </div>
                                <div class="flex justify-end">
                                    <button type="submit" 
                                            class="touch-feedback px-4 py-2 bg-sky-500 text-white rounded-md hover:bg-sky-600 transition-colors">
                                        追加
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- 旅程ノート一覧 -->
                        <div id="itinerary-list" class="space-y-4">
                            @foreach($trip->itineraries->sortBy('order') as $itinerary)
                                <div class="bg-white rounded-lg shadow-sm p-4 space-y-3 group cursor-pointer" 
                                    data-id="{{ $itinerary->id }}"
                                    data-type="itinerary"
                                    data-editable
                                    onclick="startEdit('itinerary', {{ $itinerary->id }})">
                                    <!-- 表示モード -->
                                    <div id="itinerary-content-{{ $itinerary->id }}">
                                        <div class="flex justify-between items-start">                                    
                                            <h3 class="font-medium text-slate-800">{{ $itinerary->day_label }}</h3>
                                        </div>
                                        <p class="text-slate-600 whitespace-pre-wrap mt-2">{{ $itinerary->memo }}</p>
                                    </div>
                                    
                                    <!-- 編集モード -->
                                    <form id="itinerary-edit-form-{{ $itinerary->id }}"
                                        data-type="itinerary"
                                        data-id="{{ $itinerary->id }}"
                                        style="display: none;"
                                        onsubmit="return false;"
                                        onclick="event.stopPropagation()">
                                        @csrf
                                        @method('PUT')
                                        <div class="space-y-3">
                                            <input type="text" 
                                                name="day_label" 
                                                value="{{ $itinerary->day_label }}" 
                                                class="w-full px-3 py-2 border border-slate-200 rounded-md focus:outline-none focus:border-sky-500">
                                            <textarea name="memo" 
                                                    rows="4" 
                                                    class="w-full px-3 py-2 border border-slate-200 rounded-md focus:outline-none focus:border-sky-500">{{ $itinerary->memo }}</textarea>
                                        </div>
                                        <div class="flex items-center justify-end space-x-1">
                                            <button type="button" 
                                                    onclick="cancelEdit('itinerary', {{ $itinerary->id }})"
                                                    class="touch-feedback p-1 text-slate-400 hover:text-slate-600">
                                                <i class="fa-solid fa-xmark"></i>
                                            </button>
                                            <button type="button" 
                                                    onclick="deleteItinerary({{ $itinerary->id }})"
                                                    class="touch-feedback p-1 text-slate-400 hover:text-rose-500 edit-mode-only">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- 右側：要望一覧（参考用） -->
                    <div class="md:w-80 space-y-4 mb-6">
                        <div class="bg-white rounded-lg shadow-sm p-4">
                            <h3 class="font-medium text-slate-700 mb-3">みんなの要望</h3>
                            <div class="space-y-2">
                                @foreach($trip->requests as $request)
                                    <div class="text-sm text-slate-600 pb-2 border-b border-slate-100 last:border-0">
                                        {{ $request->content }}
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- 旅程ノートタブ終わり -->
        </div><!-- タブコンテンツ終わり -->
    </main>

    <!-- フッター -->
    <footer class="fixed md:static bottom-0 left-0 right-0 bg-slate-50 shadow-lg">
        <!-- モード切り替えタブ（max-w-4xlを削除） -->
        <div class="flex justify-center -mt-8">
            <div class="flex w-screen bg-slate-50 overflow-hidden rounded-t-lg">
                <button 
                    onclick="switchMode('view')" 
                    class="touch-feedback flex-1 px-6 py-2 text-sm font-medium mode-tab flex items-center justify-center" 
                    id="viewTab"
                >
                    <div class="flex items-center justify-center">
                        <i class="fa-regular fa-eye mr-2"></i>表示モード
                    </div>
                </button>
                <button 
                    onclick="switchMode('edit')" 
                    class="touch-feedback flex-1 px-6 py-2 text-sm font-medium mode-tab active flex items-center justify-center" 
                    id="editTab"
                >
                    <div class="flex items-center justify-center">
                        <i class="fa-solid fa-pen mr-2"></i>編集モード
                    </div>
                </button>
            </div>
        </div>
        
        <!-- フッターの本体部分 -->
        <div class="max-w-4xl mx-auto">
            <div class="grid grid-cols-3 items-start h-20 text-sm pt-1 px-4">
                <!-- 戻るボタン（左） -->
                <div class="justify-self-start">
                    <a href="{{ route('trips.participating') }}" class="touch-feedback flex items-center justify-center w-10 h-10 bg-slate-200 rounded-full hover:bg-slate-300 transition-colors">
                        <i class="fa-solid fa-chevron-left text-slate-600"></i>
                    </a>
                </div>
    
                <!-- 確定ボタン（中央） -->
                <div class="justify-self-center w-full px-2 edit-mode-only" style="display: none;">
                    <button type="submit" 
                            onclick="submitAllForms()"
                            class="touch-feedback flex items-center justify-center mx-auto w-32 h-10 bg-sky-500 hover:bg-sky-600 text-white rounded-full transition-colors">
                        <i class="fa-solid fa-check"></i>
                    </button>
                </div>
    
                <!-- 右側の空のスペース -->
                <div class="justify-self-end"></div>
            </div>
        </div>
    </footer>

    <script>
        // 通知関連の関数を統一
        function showNotification(message, type = 'success') {
            showToast(message, type);
        }

        // 既存の成功メッセージをカスタム通知に置き換え
        function showSuccessNotification(message) {
            showToast(message, 'success');
        }

        // タブ切り替え機能
        function switchTab(tabName) {
            // タブのスタイルを更新
            document.querySelectorAll('.tab-link').forEach(tab => {
                const isActive = tab.getAttribute('href') === `#${tabName}`;
                tab.classList.toggle('border-sky-500', isActive);
                tab.classList.toggle('text-sky-600', isActive);
                tab.classList.toggle('border-transparent', !isActive);
                tab.classList.toggle('text-gray-500', !isActive);
            });

            // コンテンツの表示/非表示を切り替え
            document.querySelectorAll('.tab-pane').forEach(content => {
                content.classList.toggle('hidden', content.id !== `${tabName}-content`);
            });

            // URLのハッシュを更新
            history.pushState(null, '', `#${tabName}`);
        }

        // 旅程ノート追加のイベントリスナー
        document.getElementById('itinerary-add-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            fetch(this.action, {
                method: 'POST',
                body: new FormData(this),
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => {
                if (response.ok) {
                    showToast('旅程ノートを追加しました', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    throw new Error('旅程ノートの追加に失敗しました');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('旅程ノートの追加に失敗しました', 'error');
            });
        });

        // 旅程ノートの編集フォーム送信
        document.querySelectorAll('form[data-edit-form]').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const itineraryId = this.getAttribute('data-edit-form');
                
                fetch(`{{ url('trips/' . $trip->id . '/itinerary') }}/${itineraryId}`, {  // URLパスを修正
                    method: 'PUT',
                    body: new FormData(this),
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => {
                    if (response.ok) {
                        location.reload();
                    } else {
                        throw new Error('旅程ノートの更新に失敗しました');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('旅程ノートの更新に失敗しました', 'error');
                });
            });
        });

        // 旅程ノートの削除
        function deleteItinerary(id) {
            createDeleteConfirmation('itinerary', id);
        }


        // 日付フォーマットを修正する関数を追加
        function formatDateForInput(dateString) {
            if (!dateString) return '';
            return dateString.split(' ')[0]; // "yyyy-MM-dd" 形式に変換
        }

        function updateDateDisplay() {
            const startDate = document.getElementById('startDate');
            const endDate = document.getElementById('endDate');
            const isDayTrip = document.getElementById('isDayTrip')?.checked;
            const displayElement = document.getElementById('dateDisplayText');

            if (!startDate || !displayElement) {
                return;
            }

            if (!startDate.value || (!isDayTrip && !endDate.value)) {
                displayElement.innerHTML = '<span class="text-slate-500"><i class="fa-regular fa-calendar-xmark mr-2"></i>日程はまだ確定していません</span>';
                return;
            }

            const start = new Date(startDate.value);
            const end = new Date(isDayTrip ? startDate.value : endDate.value);
            
            const formatDate = (date) => {
                return `${date.getFullYear()}年${date.getMonth() + 1}月${date.getDate()}日`;
            };

            if (isDayTrip || startDate.value === endDate.value) {
                displayElement.innerHTML = `${formatDate(start)} <span class="ml-2 text-sm text-slate-500">(日帰り)</span>`;
            } else {
                const diffDays = Math.floor((end - start) / (1000 * 60 * 60 * 24));
                displayElement.innerHTML = `${formatDate(start)} <span class="mx-2">～</span> ${formatDate(end)} <span class="ml-2 text-sm text-slate-500">(${diffDays}泊${diffDays + 1}日)</span>`;
            }
        }

        function toggleDateInputs(isDayTrip) {
            const endDateContainer = document.getElementById('endDateContainer');
            const startDate = document.getElementById('startDate');
            const endDate = document.getElementById('endDate');
            const dateLabel = document.getElementById('dateLabel');

            if (isDayTrip) {
                endDateContainer.classList.add('hidden');
                dateLabel.textContent = '旅行日';
                // 開始日が入力されている場合は終了日も同じ値に設定
                if (startDate.value) {
                    endDate.value = startDate.value;
                }
            } else {
                endDateContainer.classList.remove('hidden');
                dateLabel.textContent = '開始日';
            }
            
            clearError(); // エラーメッセージをクリア
            validateDates();
            updateDateDisplay();
        }

        // startDateの変更時にも終了日を更新
        document.getElementById('startDate')?.addEventListener('change', function() {
            const isDayTrip = document.getElementById('isDayTrip')?.checked;
            if (isDayTrip) {
                document.getElementById('endDate').value = this.value;
            }
            validateDates();
            updateDateDisplay();
        });

        function validateDates() {
            const startDate = document.getElementById('startDate');
            const endDate = document.getElementById('endDate');
            const submitButton = document.getElementById('submitButton');
            const isDayTrip = document.getElementById('isDayTrip')?.checked;
            
            if (!startDate || !endDate || !submitButton) {
                return;
            }

            submitButton.disabled = false;

            // 開始日が未入力の場合
            if (!startDate.value) {
                showError('開始日を入力してください');
                submitButton.disabled = true;
                return;
            }

            // 日帰り旅行でない場合の終了日チェック
            if (!isDayTrip) {
                if (!endDate.value) {
                    showError('終了日を入力してください');
                    submitButton.disabled = true;
                    return;
                }

                // 開始日より終了日が前の場合
                if (new Date(startDate.value) > new Date(endDate.value)) {
                    showError('終了日は開始日より後の日付を選択してください');
                    submitButton.disabled = true;
                    return;
                }
            }

            // エラーメッセージをクリア
            clearError();
            updateDateDisplay();
        }

        // エラーメッセージを表示する関数
        function showError(message) {
            const errorDiv = document.createElement('div');
            errorDiv.id = 'dateError';
            errorDiv.className = 'text-rose-500 text-sm mt-2';
            errorDiv.textContent = message;

            // 既存のエラーメッセージがあれば削除
            clearError();

            // フォームの最後にエラーメッセージを追加
            const form = document.getElementById('confirmedDateForm');
            if (form) {
                form.querySelector('.grid').appendChild(errorDiv);
            }
        }

        // エラーメッセージをクリアする関数
        function clearError() {
            const existingError = document.getElementById('dateError');
            if (existingError) {
                existingError.remove();
            }
        }

        function showCommentForm(requestId) {
            const section = document.getElementById(`comments-section-${requestId}`);
            const form = document.getElementById(`comment-add-form-${requestId}`);
            
            section.classList.remove('hidden');
            form.classList.remove('hidden');
            form.querySelector('input').focus();
        }

        function cancelCommentForm(requestId) {
            const form = document.getElementById(`comment-add-form-${requestId}`);
            form.classList.add('hidden');
            form.reset();
        }

        document.querySelectorAll('form[id^="comment-add-form-"]').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const requestId = this.id.match(/\d+/)[0];
                const formData = new FormData(this);
                
                fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => {
                    if (response.ok) {
                        // 成功時は画面をリロード
                        location.reload();
                    } else {
                        throw new Error('コメントの投稿に失敗しました');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('コメントの投稿に失敗しました', 'error');
                });
            });
        });

        document.getElementById('request-add-placeholder').addEventListener('click', function() {
            this.style.display = 'none';
            const form = document.getElementById('request-add-form');
            form.style.display = 'block';
            const input = form.querySelector('input');
            input.focus();
            
            // プレースホルダーテキストの位置を調整するためのスタイル
            input.style.height = 'auto';
        });

        function cancelAddRequest() {
            document.getElementById('request-add-form').style.display = 'none';
            document.getElementById('request-add-placeholder').style.display = 'block';
            document.getElementById('request-add-form').reset();
        }

        // スタイルの定義
        const toastStyles = {
            confirm: `
                fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 
                bg-white/95 backdrop-blur-sm p-6 rounded-lg shadow-lg z-50 
                flex flex-col items-center space-y-4 min-w-[300px]
            `,
            message: `
                flex flex-col items-center text-center space-y-1
            `,
            buttonContainer: `
                flex items-center justify-center space-x-3 w-full
            `,
            button: {
                cancel: 'px-6 py-2 bg-slate-100 text-slate-600 rounded-full hover:bg-slate-200 transition-colors flex-1',
                delete: 'px-6 py-2 bg-rose-500 text-white rounded-full hover:bg-rose-600 transition-colors flex-1'
            }
        };

        function createDeleteConfirmation(type, id) {
            // 既存のトーストがあれば削除
            document.querySelector('.confirm-toast')?.remove();

            const typeText = {
                'comment': 'コメント',
                'request': '要望',
                'itinerary': '旅程ノート'
            }[type];
            
            const toast = document.createElement('div');
            toast.className = `confirm-toast ${toastStyles.confirm}`;
            toast.innerHTML = `
                <div class="${toastStyles.message}">
                    <p class="text-slate-700">この${typeText}を削除します。</p>
                    <p class="text-slate-500 text-sm">削除された${typeText}は復旧できません。</p>
                </div>
                <div class="${toastStyles.buttonContainer}">
                    <button type="button" class="${toastStyles.button.cancel}" data-action="cancel">キャンセル</button>
                    <button type="button" class="${toastStyles.button.delete}" data-action="delete">削除</button>
                </div>
            `;
            
            // イベントリスナーを追加
            toast.querySelector('[data-action="delete"]').addEventListener('click', () => {
                confirmDelete(type, id);
            });
            
            toast.querySelector('[data-action="cancel"]').addEventListener('click', () => {
                toast.remove();
            });
            
            document.body.appendChild(toast);
        }

        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `fixed bottom-20 left-1/2 transform -translate-x-1/2 px-4 py-2 rounded-full text-sm ${
                type === 'success' ? 'bg-sky-500/90' : 'bg-rose-500/90'
            } text-white shadow-sm backdrop-blur-sm z-50`;
            toast.textContent = message;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.remove();
            }, 3000);
        }

        // コピー完了通知
        function copyShareLink() {
            navigator.clipboard.writeText(shareUrl).then(() => {
                showNotification('リンクをコピーしました');
            });
        }

        // いいね機能の実装
        function toggleLike(requestId, button) {
            fetch(`/trip-requests/${requestId}/like`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                const icon = button.querySelector('i');
                const countSpan = button.querySelector('.like-count');
                
                if (data.liked) {
                    icon.classList.remove('far');
                    icon.classList.add('fas');
                    button.classList.remove('text-slate-400');
                    button.classList.add('text-red-500');
                } else {
                    icon.classList.remove('fas');
                    icon.classList.add('far');
                    button.classList.remove('text-red-500');
                    button.classList.add('text-slate-400');
                }
                
                countSpan.textContent = data.count;
            })
            .catch(error => console.error('Error:', error));
        }

        function toggleComments(requestId) {
            const section = document.getElementById(`comments-section-${requestId}`);
            const icon = document.querySelector(`.comment-icon-${requestId}`);
            
            if (section.classList.contains('hidden')) {
                section.classList.remove('hidden');
                icon.classList.remove('fa-caret-down');
                icon.classList.add('fa-caret-up');
            } else {
                section.classList.add('hidden');
                icon.classList.remove('fa-caret-up');
                icon.classList.add('fa-caret-down');
            }
        }

        function submitAllForms() {
            // タイトルと概要の更新
            const tripEditForm = document.getElementById('tripEditForm');
            const formData = new FormData(tripEditForm);

            // 要望とコメントの更新
            const promises = [];
            
            // タイトルと概要の更新
            promises.push(
                fetch(tripEditForm.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                })
            );

            // 日程フォームの更新を追加
            const dateForm = document.getElementById('confirmedDateForm');
            if (dateForm) {
                promises.push(
                    fetch(dateForm.action, {
                        method: 'POST',
                        body: new FormData(dateForm),
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        }
                    })
                );
            }

            // 要望の更新
            document.querySelectorAll('[data-type="request"] textarea').forEach(textarea => {
                const requestId = textarea.closest('form').id.match(/\d+/)[0];
                promises.push(
                    fetch(`/trip-requests/${requestId}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ content: textarea.value })
                    })
                );
            });

            // コメントの更新
            document.querySelectorAll('[data-type="comment"] input[type="text"]').forEach(input => {
                const commentId = input.closest('form').id.match(/\d+/)[0];
                promises.push(
                    fetch(`/request-comments/${commentId}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ content: input.value })
                    })
                );
            });

            // 旅程ノートの更新
            document.querySelectorAll('[data-type="itinerary"]').forEach(form => {
                const itineraryId = form.getAttribute('data-id');
                if (form.style.display !== 'none') {  // 編集中のフォームのみ処理
                    promises.push(
                        fetch(`/trips/{{ $trip->id }}/itinerary/${itineraryId}`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                day_label: form.querySelector('[name="day_label"]').value,
                                memo: form.querySelector('[name="memo"]').value
                            })
                        })
                    );
                }
            });

            Promise.all(promises)
                .then(() => {
                    showToast('更新しました', 'success');
                    setTimeout(() => location.reload(), 1000);
                })
                .catch(error => {
                    showToast('更新に失敗しました', 'error');
                });
        }

        // 編集モードの切り替え
        function switchMode(mode) {
            const viewTab = document.getElementById('viewTab');
            const editTab = document.getElementById('editTab');
            const requestAddContainer = document.getElementById('request-add-placeholder')?.closest('.edit-mode-only');
            
            if (!viewTab || !editTab) return;

            if (mode === 'edit') {
                document.body.classList.add('edit-mode');
                editTab.classList.add('active');
                viewTab.classList.remove('active');
                
                // validateDatesを呼び出す前に要素の存在確認
                // 日付フォームの初期設定
                const startDate = document.getElementById('startDate');
                const endDate = document.getElementById('endDate');
                const isDayTripCheckbox = document.getElementById('isDayTrip');

                if (startDate && endDate && isDayTripCheckbox) {
                    // 開始日と終了日が同じ場合は日帰り旅行にチェック
                    const isSameDay = startDate.value === endDate.value;
                    isDayTripCheckbox.checked = isSameDay;
                    toggleDateInputs(isSameDay);
                    validateDates();
                }
                
                // 要望追加フォームを表示
                if (requestAddContainer) {
                    requestAddContainer.style.display = 'block';
                }
                
                // 編集可能な要素にイベントリスナーを追加
                document.querySelectorAll('[data-editable]').forEach(el => {
                    const contentDiv = el.querySelector('div[id^="request-content-"], div[id^="comment-content-"]');
                    if (contentDiv) {
                        contentDiv.style.cursor = 'pointer';
                        contentDiv.onclick = function() {
                            const type = el.dataset.type;
                            const id = el.dataset.id;
                            startEdit(type, id);
                        };
                    }
                });
                // 返信ボタンの表示制御を追加
                document.querySelectorAll('.edit-mode-only').forEach(el => {
                    el.style.display = 'block';
                });
            } else {
                document.body.classList.remove('edit-mode');
                viewTab.classList.add('active');
                editTab.classList.remove('active');
                
                // 日程フォームを非表示
                if (confirmedDateForm) {
                    confirmedDateForm.classList.add('hidden');
                }
                
                // 要望追加フォームを非表示にし、リセット
                if (requestAddContainer) {
                    requestAddContainer.style.display = 'none';
                }
                
                // イベントリスナーを削除し、すべての要素を表示モードに戻す
                document.querySelectorAll('[data-editable]').forEach(el => {
                    const contentDiv = el.querySelector('div[id^="request-content-"], div[id^="comment-content-"]');
                    const formEl = el.querySelector('form');
                    if (contentDiv) {
                        contentDiv.style.cursor = 'default';
                        contentDiv.onclick = null;
                        contentDiv.style.display = 'block';
                    }
                    if (formEl) {
                        formEl.style.display = 'none';
                    }
                });
                // 返信ボタンの非表示制御を追加
                document.querySelectorAll('.edit-mode-only').forEach(el => {
                    el.style.display = 'none';
                });
            }
        }
    
        // 編集の開始
        function startEdit(type, id) {
            if (!document.body.classList.contains('edit-mode')) return;
            
            const contentDiv = document.getElementById(`${type}-content-${id}`);
            const formEl = document.getElementById(`${type}-edit-form-${id}`);
            
            if (!contentDiv || !formEl) {
                // 旅程ノート用の処理
                if (type === 'itinerary') {
                    const viewMode = document.querySelector(`[data-id="${id}"] .view-mode`);
                    const editMode = document.querySelector(`[data-id="${id}"] .edit-mode`);
                    if (viewMode && editMode) {
                        viewMode.style.display = 'none';
                        editMode.style.display = 'block';
                        
                        const input = editMode.querySelector('textarea, input[type="text"]');
                        if (input) {
                            input.focus();
                            input.selectionStart = input.selectionEnd = input.value.length;
                        }
                    }
                }
                return;
            }

            contentDiv.style.display = 'none';
            formEl.style.display = 'block';

            // そのコメントの編集モード要素を表示
            if (type === 'comment') {
                const commentGroup = contentDiv.closest('.group');
                const editModeElements = commentGroup.querySelectorAll('.edit-mode-only');
                editModeElements.forEach(el => {
                    el.style.opacity = '1';
                });
            }
            
            const input = formEl.querySelector('textarea, input[type="text"]');
            if (input) {
                input.focus();
                input.selectionStart = input.selectionEnd = input.value.length;
            }
        }
    
        // フォームのイベントリスナー設定
        function setupFormListeners(form, type, id) {
            // キャンセルボタンのイベント
            const cancelBtn = form.querySelector('button[type="button"]');
            if (cancelBtn) {
                cancelBtn.onclick = () => cancelEdit(type, id);
            }
    
            // フォームの送信イベント
            form.onsubmit = function(e) {
                e.preventDefault();
                submitEdit(form, type, id);
            };
        }
    
        // 編集のキャンセル
        function cancelEdit(type, id) {
            const contentDiv = document.getElementById(`${type}-content-${id}`);
            const formEl = document.getElementById(`${type}-edit-form-${id}`);
            
            if (contentDiv && formEl) {
                contentDiv.style.display = 'block';
                formEl.style.display = 'none';

                // そのコメントの編集モード要素（ゴミ箱アイコンなど）を非表示
                if (type === 'comment') {
                    const commentGroup = contentDiv.closest('.group');
                    const editModeElements = commentGroup.querySelectorAll('.edit-mode-only');
                    editModeElements.forEach(el => {
                        el.style.opacity = '0';
                    });
                }
            }
        }
    
        // 編集内容の送信
        function submitEdit(form, type, id) {
            const formData = new FormData(form);
            formData.append('_method', 'PUT');
    
            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const contentEl = document.getElementById(`${type}-content-${id}`);
                    if (contentEl) {
                        contentEl.textContent = formData.get('content');
                        cancelEdit(type, id);
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('更新に失敗しました');
            });
        }
    
        // 要望の削除
        function deleteRequest(requestId) {
            createDeleteConfirmation('request', requestId);
        }

        // コメントの削除
        function deleteComment(commentId) {
            createDeleteConfirmation('comment', commentId);
        }

        // 実際の削除処理を行う関数
        function confirmDelete(type, id) {
            console.log('Confirming delete:', type, id);

            const url = type === 'comment' 
                ? `/request-comments/${id}` 
                : type === 'request' 
                    ? `/trip-requests/${id}`
                    : `{{ url('trips/' . $trip->id . '/itinerary') }}/${id}`; // 旅程ノート用のURL

            console.log('Delete URL:', url);

            fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    throw new Error('削除に失敗しました');
                }
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                if (data.success) {
                    if (type === 'comment') {
                        const commentElement = document.querySelector(`[data-type="comment"][data-id="${id}"]`);
                        if (commentElement) {
                            commentElement.closest('.group').remove();
                        }
                    } else if (type === 'request') {
                        const requestElement = document.querySelector(`[data-type="request"][data-id="${id}"]`);
                        if (requestElement) {
                            requestElement.closest('.border-b').remove();
                        }
                    } else if (type === 'itinerary') {
                        // 旅程ノートの要素を削除
                        const itineraryElement = document.querySelector(`[data-id="${id}"]`);
                        if (itineraryElement) {
                            itineraryElement.remove();
                        }
                    }
                    showToast('削除しました', 'success');
                } else {
                    throw new Error(data.message || '削除に失敗しました');
                }
            })
            .catch(error => {
                console.error('Delete error:', error);
                showToast(error.message || '削除に失敗しました', 'error');
            })
            .finally(() => {
                document.querySelector('.confirm-toast')?.remove();
            });
        }

        // 結果表示用のトースト
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `fixed bottom-20 left-1/2 transform -translate-x-1/2 px-4 py-2 rounded-full text-sm ${
                type === 'success' ? 'bg-sky-500/90' : 'bg-rose-500/90'
            } text-white shadow-sm backdrop-blur-sm z-50`;
            toast.textContent = message;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.remove();
            }, 3000);
        }

        // 要望の投稿
        document.getElementById('request-add-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            fetch(this.action, {
                method: 'POST',
                body: new FormData(this),
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => {
                if (response.ok) {
                    location.reload();
                } else {
                    throw new Error('要望の投稿に失敗しました');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('要望の投稿に失敗しました', 'error');
            });
        });  
    
        // 初期設定
        document.addEventListener('DOMContentLoaded', function() {
            // 初期表示は表示モード
            switchMode('view');

            // URLのハッシュに基づいてタブを切り替え
            const hash = window.location.hash.substring(1) || 'overview';
            switchTab(hash);
            
            // 日付関連の初期設定
            const isDayTripCheckbox = document.getElementById('isDayTrip');
            const startDate = document.getElementById('startDate');
            const endDate = document.getElementById('endDate');
            // フラッシュメッセージの自動非表示
            const successMessage = document.getElementById('successMessage');
            const errorMessage = document.getElementById('errorMessage');

            if (successMessage) {
                setTimeout(() => {
                    successMessage.style.opacity = '0';
                    setTimeout(() => {
                        successMessage.remove();
                    }, 500);
                }, 3000);
            }

            if (errorMessage) {
                setTimeout(() => {
                    errorMessage.style.opacity = '0';
                    setTimeout(() => {
                        errorMessage.remove();
                    }, 500);
                }, 3000);
            }

            if (startDate) {
                startDate.value = formatDateForInput('{{ $trip->confirmed_start_date }}');
            }
            if (endDate) {
                endDate.value = formatDateForInput('{{ $trip->confirmed_end_date }}');
            }

            if (isDayTripCheckbox && startDate && endDate) {
                if (isDayTripCheckbox.checked) {
                    toggleDateInputs(true);
                }

                // 各入力要素の変更時にvalidateDatesを呼び出す
                startDate.addEventListener('change', validateDates);
                endDate.addEventListener('change', validateDates);
                isDayTripCheckbox.addEventListener('change', function() {
                    toggleDateInputs(this.checked);
                    validateDates();
                });

                // 初期表示時のバリデーション
                validateDates();
            }
        });
    </script>
</body>
</html>