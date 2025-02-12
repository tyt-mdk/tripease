<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <script src="https://kit.fontawesome.com/ef96165231.js" crossorigin="anonymous"></script><!-- FontAwesome -->
    <title>Tripease</title>

    @vite('resources/css/app.css')
</head>
<body class="flex flex-col min-h-[100vh] text-[0.65rem] bg-slate-100 text-slate-800 font-notosans">
    <header>
    </header>
    <main class="pb-24">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- ヘッダー部分 -->
            <div class="py-6">
                <h1 class="text-xl md:text-2xl font-medium text-slate-900">
                    新しい旅行を作成
                </h1>
                <div class="text-sm text-slate-600 mt-2">
                    {{ Auth::user()->name }}さん
                </div>
            </div>
    
            <!-- フォーム部分 -->
            <form method="POST" action="{{ route('trips.store') }}" class="space-y-6">
                @csrf
                <!-- 旅行タイトル -->
                <div class="space-y-2">
                    <label for="title" class="block text-sm font-medium text-slate-700">
                        旅行タイトル
                    </label>
                    <input type="text" 
                           name="title" 
                           id="title" 
                           required 
                           class="w-full px-4 py-3 text-base md:text-lg border border-slate-200 rounded-lg focus:outline-none focus:border-sky-500"
                           placeholder="例：北海道旅行">
                </div>
    
                <!-- 概要メモ -->
                <div class="space-y-2">
                    <label for="description" class="block text-sm font-medium text-slate-700">
                        概要メモ
                    </label>
                    <textarea name="description" 
                              id="description" 
                              rows="4" 
                              class="w-full px-4 py-3 text-base md:text-lg border border-slate-200 rounded-lg focus:outline-none focus:border-sky-500"
                              placeholder="旅行の概要や目的などを記入"></textarea>
                </div>
    
                <!-- 作成ボタン -->
                <div class="flex justify-end pt-4">
                    <button type="submit" 
                            class="touch-feedback px-6 py-3 md:px-8 md:py-4 bg-sky-500 hover:bg-sky-600 text-white text-base md:text-lg font-medium rounded-lg transition-colors">
                        作成する
                    </button>
                </div>
            </form>
        </div>
    </main>
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
                <!-- 中央と右側は空 -->
                <div></div>
                <div></div>
            </div>
        </div>
    </footer>
</body>
</html>