<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>旅行計画への参加 - TripEase</title>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body class="flex flex-col min-h-screen bg-slate-100">
    <main class="flex-grow flex items-center justify-center">
        <div class="w-full max-w-md px-6 py-8">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h1 class="text-2xl font-bold text-center mb-6">旅行計画への参加</h1>
                
                <div class="mb-6">
                    <h2 class="text-lg font-semibold mb-2">{{ $trip->title }}</h2>
                    <p class="text-slate-600 mb-4">
                        作成者: {{ $trip->creator->name }}
                    </p>
                    
                    @if(auth()->check())
                        @if($trip->users->contains(auth()->id()))
                            <div class="bg-sky-50 text-sky-700 p-4 rounded-md mb-4">
                                すでにこの旅行計画に参加しています。
                            </div>
                            <a href="{{ route('trips.eachplanning', ['trip' => $trip->id]) }}" 
                               class="touch-feedback block w-full text-center bg-sky-500 text-white py-2 px-4 rounded hover:bg-sky-600">
                                旅行計画へ
                            </a>
                        @else
                            <form method="POST" action="{{ route('trips.join.confirm', $trip->share_token) }}">
                                @csrf
                                <button type="submit" 
                                        class="touch-feedback w-full bg-sky-500 text-white py-2 px-4 rounded hover:bg-sky-600">
                                    この旅行計画に参加する
                                </button>
                            </form>
                        @endif
                    @else
                        <div class="bg-yellow-50 text-yellow-700 p-4 rounded-md mb-4">
                            旅行計画に参加するにはログインが必要です。
                        </div>
                        <a href="{{ route('login') }}" 
                           class="touch-feedback block w-full text-center bg-sky-500 text-white py-2 px-4 rounded hover:bg-sky-600">
                            ログインする
                        </a>
                    @endif
                </div>

                <div class="text-center">
                    <a href="{{ route('trips.eachplanning', ['trip' => $trip->id]) }}" class="touch-feedback text-slate-600 hover:text-slate-800">
                        トップページに戻る
                    </a>
                    
                    @if(auth()->check())
                        <form method="POST" action="{{ route('logout') }}" class="mt-4">
                            @csrf
                            <button type="submit" 
                                    class="touch-feedback text-slate-600 hover:text-slate-800">
                                ログアウト
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </main>
</body>
</html>