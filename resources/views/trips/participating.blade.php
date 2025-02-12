<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-100">
    <div class="min-h-screen">
        <!-- メインコンテンツ -->
        <main class="pb-24">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8"> <!-- コンテナを統一 -->
                <!-- ヘッダー部分 -->
                <div class="py-6">
                    <h1 class="text-xl md:text-2xl font-medium text-slate-900">
                        参加中の旅行計画一覧
                    </h1>
                    <div class="text-sm text-slate-600 mt-2">
                        {{ Auth::user()->name }}さん
                    </div>
                </div>
        
                <!-- 旅行計画一覧 -->
                @if ($user->trips->isNotEmpty())
                    <div class="space-y-3">
                        @foreach($user->trips as $trip)
                            <div class="bg-white rounded-lg shadow-sm hover:shadow transition-all duration-200">
                                <!-- 旅行情報部分 -->
                                <div class="relative p-4"> <!-- relativeを追加 -->
                                    <!-- 左側：リンクとして機能する部分 -->
                                    <a href="{{ route('trips.show', ['trip' => $trip->id]) }}" 
                                    class="block">
                                        <div class="space-y-3">
                                            <!-- タイトルと説明 -->
                                            <div class="flex justify-between items-start">
                                                <div class="pr-8"> <!-- 右側の余白を追加 -->
                                                    <h3 class="text-base font-medium text-slate-900">
                                                        {{ $trip->title }}
                                                    </h3>
                                                    @if($trip->description)
                                                        <p class="text-sm text-slate-600 mt-1">
                                                            {{ $trip->description }}
                                                        </p>
                                                    @endif
                                                </div>
                                                <div class="text-slate-400">
                                                    <i class="fa-solid fa-chevron-right"></i>
                                                </div>
                                            </div>
                                            
                                            <!-- メタ情報 -->
                                            <div class="flex items-center gap-4 text-sm text-slate-500">
                                                <div class="flex items-center gap-1.5">
                                                    <i class="fa-solid fa-users"></i>
                                                    <span>{{ $trip->users->count() }}人</span>
                                                </div>
                                                <div class="flex items-center gap-1.5">
                                                    <i class="fa-regular fa-clock"></i>
                                                    <span>{{ $trip->updated_at->format('Y/m/d') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </a>

                                    <!-- 右側：削除ボタン（作成者のみ表示） -->
                                    @if($trip->creator_id === Auth::id())
                                        <button type="button" 
                                                onclick="confirmDelete({{ $trip->id }})"
                                                class="touch-feedback absolute bottom-4 right-4 w-8 h-8 flex items-center justify-center text-slate-400 hover:text-rose-500 transition-colors">
                                            <i class="fa-regular fa-trash-can text-base"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="bg-white rounded-lg p-6 text-center text-slate-500 shadow-sm">
                        参加している旅行プランはありません
                    </div>
                @endif
            </div>
        </main>

        <!-- フッター -->
        <footer class="fixed bottom-0 left-0 right-0 bg-slate-50 shadow-lg">
            <div class="max-w-4xl mx-auto px-4">
                <!-- フッターの本体部分 -->
                <div class="grid grid-cols-3 items-start h-20 text-sm pt-1">
                    <!-- 戻るボタン（左） -->
                    <div class="justify-self-start">
                        <a href="{{ route('dashboard') }}" class="touch-feedback flex items-center justify-center w-10 h-10 bg-slate-200 rounded-full hover:bg-slate-300 transition-colors">
                            <i class="fa-solid fa-chevron-left text-slate-600"></i>
                        </a>
                    </div>
                    <!-- 中央の空のスペース -->
                    <div class="justify-self-center"></div>
                    <!-- 右側の空のスペース -->
                    <div class="justify-self-end"></div>
                </div>
            </div>
        </footer>
    </div>
    <script>
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            // フォントファミリーとサイズを明示的に指定
            toast.className = `fixed bottom-20 left-1/2 transform -translate-x-1/2 px-4 py-2 rounded-full font-sans text-sm ${
                type === 'success' ? 'bg-sky-500/90' : 'bg-rose-500/90'
            } text-white shadow-sm backdrop-blur-sm z-50`;
            toast.style.fontFamily = '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif';
            toast.textContent = message;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.remove();
            }, 3000);
        }

        function confirmDelete(tripId) {
            const toast = document.createElement('div');
            toast.className = 'fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 p-6 rounded-lg bg-white shadow-lg z-50 w-80 text-center confirm-toast font-sans';
            toast.innerHTML = `
                <p class="text-lg text-slate-900 mb-4 font-medium">この旅行計画を削除します。</p>
                <p class="text-sm text-slate-600 mb-6">削除された旅行計画は復旧できません。</p>
                <div class="flex justify-center gap-3">
                    <button class="px-6 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-full text-sm transition-colors font-medium"
                            onclick="removeConfirmDialog()">
                        キャンセル
                    </button>
                    <button class="px-6 py-2 bg-rose-500 hover:bg-rose-600 text-white rounded-full text-sm transition-colors font-medium"
                            onclick="deleteTrip(${tripId})">
                        削除
                    </button>
                </div>
            `;
            
            const overlay = document.createElement('div');
            overlay.className = 'fixed inset-0 bg-black/50 z-40 confirm-overlay backdrop-blur-sm';
            overlay.onclick = removeConfirmDialog;
            
            document.body.appendChild(overlay);
            document.body.appendChild(toast);
        }

        function removeConfirmDialog() {
            document.querySelector('.confirm-toast')?.remove();
            document.querySelector('.confirm-overlay')?.remove();
        }

        function deleteTrip(tripId) {
            removeConfirmDialog();
            fetch(`/trips/${tripId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => {
                // レスポンスのステータスコードを確認
                if (response.status === 204) {
                    // 204 No Content の場合は成功として処理
                    showToast('旅行計画を削除しました');
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                    return;
                }
                
                // その他のレスポンスの場合はJSONとしてパース
                return response.json().then(data => {
                    if (response.ok && data.success) {
                        showToast('旅行計画を削除しました');
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    } else {
                        throw new Error(data.message || '削除に失敗しました');
                    }
                });
            })
            .catch(error => {
                console.error('Delete error:', error);
                showToast('削除に失敗しました', 'error');
            });
        }
    </script>
</body>
</html>