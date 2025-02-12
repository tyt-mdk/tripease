<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>ログイン - TripEase</title>
    @vite('resources/css/app.css')
</head>
<body class="flex flex-col min-h-screen bg-slate-100">
    <main class="flex-grow flex items-center justify-center">
        <div class="w-full max-w-md px-6 py-8">
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-slate-800">ログイン</h1>
            </div>

            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf
            
                <!-- メールアドレスまたは名前 -->
                <div>
                    <label for="login" class="block text-sm font-medium text-slate-700 mb-1">メールアドレスまたは名前</label>
                    <input type="text" name="login" id="login" 
                           class="w-full px-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent"
                           value="{{ old('login') }}" required autofocus>
                    @error('login')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            
                <!-- パスワード -->
                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700 mb-1">パスワード</label>
                    <input type="password" name="password" id="password" 
                           class="w-full px-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent"
                           required>
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- パスワードを忘れた場合のリンク -->
                <div class="text-right">
                    <a href="{{ route('password.request') }}" 
                       class="text-sm text-sky-600 hover:text-sky-700">
                        パスワードをお忘れですか？
                    </a>
                </div>
            
                <!-- ログインボタン -->
                <button type="submit" 
                        class="w-full py-3 px-4 bg-sky-500 text-white rounded-lg shadow hover:bg-sky-600 transition-colors">
                    ログイン
                </button>
            </form>

            <!-- 戻るボタン -->
            <div class="mt-6 text-center">
                <a href="{{ route('toppage') }}" class="text-sm text-slate-600 hover:text-slate-800">
                    トップページに戻る
                </a>
            </div>
        </div>
    </main>
</body>
</html>