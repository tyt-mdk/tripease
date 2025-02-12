<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover"><!-- レスポンシブ -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://kit.fontawesome.com/ef96165231.js" crossorigin="anonymous"></script><!-- FontAwesome -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script><!-- Alpine.js -->
    <title>TripEase</title>

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body class="flex flex-col min-h-screen bg-slate-100 text-slate-800 font-notosans">
    <header class="bg-white shadow-sm">
        <div class="max-w-4xl mx-auto px-4 py-4">
            <h1 class="text-lg md:text-xl font-bold text-slate-900">マイページ</h1>
        </div>
    </header>

    <main class="flex-1 pb-20 md:pb-0">
        <div class="max-w-4xl mx-auto p-4">
            <!-- ユーザー情報カード -->
            <div class="rounded-lg shadow-md bg-white p-4 md:p-6 my-4 md:my-6">
                <h2 class="text-lg md:text-xl font-bold text-slate-900 mb-4">
                    {{ $user->name }}さん
                </h2>
                <div class="grid grid-cols-2 gap-3 md:gap-4">
                    <div class="bg-slate-50 rounded-lg p-3 md:p-4">
                        <p class="text-xl md:text-2xl font-bold text-sky-600 text-center">
                            {{ $user->trips()->count() }}
                        </p>
                        <p class="text-xs md:text-sm text-slate-600 text-center mt-1">計画参加中</p>
                    </div>
                    <div class="bg-slate-50 rounded-lg p-3 md:p-4">
                        <p class="text-xl md:text-2xl font-bold text-sky-600 text-center">
                            {{ $user->trips()->where('creator_id', $user->id)->count() }}
                        </p>
                        <p class="text-xs md:text-sm text-slate-600 text-center mt-1">管理中</p>
                    </div>
                </div>
            </div>

            <!-- クイックアクション -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-4 mb-6">
                <a href="{{ route('trips.create') }}" 
                class="touch-feedback flex items-center justify-center gap-2 p-3 md:p-4 bg-sky-500 text-white rounded-lg hover:bg-sky-600 transition-colors shadow-md text-sm md:text-base">
                    <i class="fa-solid fa-plus"></i>
                    <span class="font-medium">新しい旅行を計画する</span>
                </a>
                
                <!-- URLで参加するボタンとモーダル -->
                <div x-data="{ showModal: false }">
                    <!-- モーダルを開くボタン -->
                    <button @click="showModal = true" 
                            class="touch-feedback w-full flex items-center justify-center gap-2 p-3 md:p-4 bg-white text-slate-700 rounded-lg hover:bg-slate-50 transition-colors shadow-md text-sm md:text-base">
                        <i class="fa-solid fa-link"></i>
                        <span class="font-medium">URLで参加する</span>
                    </button>

                    <!-- モーダル -->
                    <div x-show="showModal" 
                        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
                        <div @click.away="showModal = false" 
                            class="bg-white rounded-lg shadow-xl max-w-md w-full p-4 md:p-6">
                            <h3 class="text-lg font-semibold text-slate-900 mb-4">URLで参加</h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <label for="joinUrl" class="block text-sm font-medium text-slate-700 mb-1">
                                        共有URLを入力
                                    </label>
                                    <input type="url" 
                                        id="joinUrl" 
                                        class="w-full px-3 py-2 border rounded-md"
                                        placeholder="https://...">
                                </div>

                                <div class="flex justify-end gap-3">
                                    <button @click="showModal = false" 
                                            class="px-4 py-2 text-sm text-slate-700 hover:bg-slate-100 rounded-md">
                                        キャンセル
                                    </button>
                                    <button onclick="window.location.href = document.getElementById('joinUrl').value" 
                                            class="px-4 py-2 text-sm text-white bg-sky-500 hover:bg-sky-600 rounded-md">
                                        参加する
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 参加中の旅行一覧 -->
            <div class="bg-white rounded-lg shadow-md p-4 md:p-6 mb-6">
                <h3 class="text-base md:text-lg font-semibold text-slate-900 mb-3 md:mb-4">参加中の旅行</h3>
                <div class="space-y-3">
                    @forelse($user->trips()->latest()->take(3)->get() as $trip)
                        <a href="{{ route('trips.eachplanning', $trip) }}" 
                           class="touch-feedback block p-3 bg-slate-50 rounded-lg hover:bg-slate-100 transition-colors">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-medium text-slate-900 text-sm md:text-base">{{ $trip->title }}</p>
                                    <p class="text-xs md:text-sm text-slate-500 mt-1">
                                        {{ $trip->created_at->format('Y/m/d') }} 作成
                                    </p>
                                </div>
                                <i class="fa-solid fa-chevron-right text-slate-400"></i>
                            </div>
                        </a>
                    @empty
                        <p class="text-slate-500 text-center py-4 text-sm">参加中の旅行はありません</p>
                    @endforelse
                </div>
                
                <a href="{{ route('trips.participating') }}" 
                   class="touch-feedback block text-center text-sky-500 hover:text-sky-600 mt-4 text-sm md:text-base">
                    すべての旅行を見る
                </a>
            </div>
        </div>
    </main>

    <!-- フッター -->
    <footer class="fixed md:static bottom-0 left-0 right-0 bg-white shadow-lg md:shadow-none">
        <div class="max-w-4xl mx-auto">
            <div class="flex h-16 md:h-20">
                <!-- メニューボタン -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" 
                            class="touch-feedback flex flex-col items-center justify-center h-full w-16 md:w-20 hover:bg-slate-50 transition-colors">
                        <i class="fa-solid fa-bars text-base md:text-lg"></i>
                        <span class="mt-1 text-xs">メニュー</span>
                    </button>
                    
                    <!-- ドロップダウン -->
                    <div x-show="open" 
                         @click.away="open = false"
                         x-transition
                         class="absolute bottom-full left-0 mb-2 w-48 rounded-lg shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                        <div class="py-1">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" 
                                        class="w-full text-left px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                    ログアウト
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>