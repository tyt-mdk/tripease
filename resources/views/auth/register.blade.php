<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>新規アカウント作成 - TripEase</title>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body class="flex flex-col min-h-screen bg-slate-100">
    <main class="flex-grow flex items-center justify-center">
        <div class="w-full max-w-md px-6 py-8">
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-slate-800">新規アカウント作成</h1>
            </div>

            <form method="POST" action="{{ route('register') }}" class="space-y-6">
                @csrf

                <!-- 名前 -->
                <div>
                    <label for="name" class="block text-sm font-medium text-slate-700 mb-1">名前</label>
                    <input type="text" name="name" id="name" 
                           class="w-full px-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent"
                           value="{{ old('name') }}" required autofocus>
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- メールアドレス -->
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 mb-1">メールアドレス</label>
                    <input type="email" name="email" id="email" 
                           class="w-full px-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent"
                           value="{{ old('email') }}" required>
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- パスワード -->
                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700 mb-1">パスワード</label>
                    <input type="password" name="password" id="password" 
                           class="w-full px-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent"
                           required>
                    <p class="mt-1 text-xs text-slate-500">8文字以上で入力してください</p>
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- パスワード（確認用） -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-slate-700 mb-1">パスワード（確認用）</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" 
                           class="w-full px-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent"
                           required>
                </div>

                <!-- 登録ボタン -->
                <button type="submit" 
                        class="w-full py-3 px-4 bg-sky-500 text-white rounded-lg shadow hover:bg-sky-600 transition-colors">
                    アカウントを作成
                </button>
            </form>

            <!-- ログインページへのリンク -->
            <div class="mt-6 text-center">
                <p class="text-sm text-slate-600">
                    すでにアカウントをお持ちの方は
                    <a href="{{ route('login') }}" class="text-sky-600 hover:text-sky-700">
                        こちらからログイン
                    </a>
                </p>
            </div>

            <!-- 戻るボタン -->
            <div class="mt-4 text-center">
                <a href="{{ route('toppage') }}" class="text-sm text-slate-600 hover:text-slate-800">
                    トップページに戻る
                </a>
            </div>
        </div>
    </main>
</body>
</html>