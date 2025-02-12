<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>パスワードリセット - TripEase</title>
    @vite('resources/css/app.css')
</head>
<body class="flex flex-col min-h-screen bg-slate-100">
    <main class="flex-grow flex items-center justify-center">
        <div class="w-full max-w-md px-6 py-8">
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-slate-800">パスワードリセット</h1>
                <p class="mt-2 text-sm text-slate-600">
                    登録したメールアドレスを入力してください。<br>
                    パスワードリセット用のリンクをお送りします。
                </p>
            </div>

            @if (session('status'))
                <div class="mb-4 p-4 bg-green-100 text-green-700 rounded">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
                @csrf

                <!-- メールアドレス -->
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 mb-1">メールアドレス</label>
                    <input type="email" name="email" id="email" 
                           class="w-full px-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent"
                           value="{{ old('email') }}" required autofocus>
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- 送信ボタン -->
                <button type="submit" 
                        class="w-full py-3 px-4 bg-sky-500 text-white rounded-lg shadow hover:bg-sky-600 transition-colors">
                    パスワードリセットリンクを送信
                </button>
            </form>

            <!-- 戻るボタン -->
            <div class="mt-6 text-center">
                <a href="{{ route('login') }}" class="text-sm text-slate-600 hover:text-slate-800">
                    ログイン画面に戻る
                </a>
            </div>
        </div>
    </main>
</body>
</html>