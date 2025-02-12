<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>TripEase - 旅行計画をもっと簡単に</title>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body class="flex flex-col min-h-screen bg-slate-100">
    <main class="flex-grow flex items-center justify-center">
        <div class="w-full max-w-md px-6 py-8">
            <!-- ロゴまたはタイトル -->
            <div class="text-center mb-12">
                <h1 class="text-3xl font-bold text-slate-800">TripEase</h1>
                <p class="mt-2 text-slate-600">旅行計画をもっと簡単に</p>
            </div>

            <!-- ボタン群 -->
            <div class="space-y-4">
                <a href="{{ route('login') }}" 
                   class="touch-feedback block w-full py-3 px-4 text-center bg-sky-500 text-white rounded-lg shadow hover:bg-sky-600 transition-colors">
                    ログイン
                </a>
                
                <a href="{{ route('register') }}" 
                   class="touch-feedback block w-full py-3 px-4 text-center bg-white text-slate-700 rounded-lg shadow hover:bg-slate-50 transition-colors">
                    新規アカウント作成
                </a>
            </div>

            <!-- 説明文やキャッチコピー -->
            <div class="mt-12 text-center text-sm text-slate-600">
                <p>TripEaseで旅行の計画を</p>
                <p>みんなで簡単に作成しよう</p>
            </div>

            <!-- お問い合わせリンク -->
            <div class="mt-8 text-center">
                <a href="#" class="touch-feedback text-sm text-sky-600 hover:text-sky-700 hover:underline">
                    お問い合わせ
                </a>
            </div>
        </div>
    </main>
</body>
</html>