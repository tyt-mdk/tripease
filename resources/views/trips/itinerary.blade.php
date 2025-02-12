<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>旅程ノート - {{ $trip->title }}</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .wishes-bottom-sheet {
            transform: translateY(100%);
            transition: transform 0.3s ease-out;
        }
        .wishes-bottom-sheet.active {
            transform: translateY(0);
        }
    </style>

    <script>
        // tripIdをグローバル変数として定義
        const tripId = {{ $trip->id }};
    </script>
</head>
<body class="bg-gray-50">
    <!-- ヘッダー -->
    <header class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex justify-between items-center">
                <h1 class="text-xl font-bold text-gray-900">{{ $trip->title }} - 旅程ノート</h1>
                <a href="{{ route('trips.show', $trip) }}" class="text-sky-600 hover:text-sky-700">
                    <i class="fas fa-arrow-left mr-2"></i>戻る
                </a>
            </div>
        </div>
    </header>

    <div id="notification" class="fixed top-4 right-4 transform transition-transform duration-300 translate-x-full">
        <div class="bg-white shadow-lg rounded-lg p-4 flex items-center">
            <span id="notificationMessage" class="text-sm"></span>
            <button class="ml-3 text-gray-400 hover:text-gray-600" onclick="hideNotification()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>

    <!-- メインコンテンツ -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- PC用サイドパネル（md以上で表示） -->
        <div class="md:flex gap-6">
            <div class="flex-1">
                <!-- 新規日程追加ボタン -->
                <button id="addDayBtn" class="mb-6 bg-sky-500 text-white px-4 py-2 rounded-md hover:bg-sky-600 transition-colors">
                    <i class="fas fa-plus mr-2"></i>新しい日程を追加
                </button>

                <!-- 日程リスト -->
                <div id="itineraryList" class="space-y-6">
                    @foreach($itineraries as $itinerary)
                    <div class="day-block bg-white rounded-lg shadow p-4" data-id="{{ $itinerary->id }}">
                        <div class="flex justify-between items-center mb-4">
                            <input type="text" class="day-label text-lg font-bold bg-transparent border-none focus:ring-0" 
                                value="{{ $itinerary->day_label }}" placeholder="日程のタイトル">
                            <button class="delete-day-btn text-gray-400 hover:text-gray-600">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        <textarea class="memo w-full min-h-[100px] p-2 border rounded-md" 
                            placeholder="メモを入力...">{{ $itinerary->memo }}</textarea>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- PC用要望サイドパネル -->
            <div class="hidden md:block w-80 bg-white rounded-lg shadow-lg p-4 h-fit sticky top-4">
                <h3 class="text-lg font-bold mb-4">みんなの要望</h3>
                <div class="space-y-4">
                    @foreach($wishes as $wish)
                    <div class="p-3 bg-gray-50 rounded-md">
                        <p class="text-sm text-gray-600">{{ $wish->user->name }}</p>
                        <p class="mt-1">{{ $wish->content }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- モバイル用要望ボタンとボトムシート -->
        <button id="showWishesBtn" class="md:hidden fixed bottom-4 right-4 bg-sky-500 text-white rounded-full p-3 shadow-lg">
            <i class="fas fa-lightbulb"></i>
        </button>

        <div id="wishesOverlay" class="fixed inset-0 bg-black bg-opacity-50 hidden md:hidden">
            <div id="wishesBottomSheet" class="wishes-bottom-sheet absolute bottom-0 left-0 right-0 bg-white rounded-t-xl p-4">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold">みんなの要望</h3>
                    <button id="closeWishesBtn" class="text-gray-500">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="wishes-list max-h-[60vh] overflow-y-auto space-y-4">
                    @foreach($wishes as $wish)
                    <div class="p-3 bg-gray-50 rounded-md">
                        <p class="text-sm text-gray-600">{{ $wish->user->name }}</p>
                        <p class="mt-1">{{ $wish->content }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            
            // モバイル用要望ボトムシート制御
            const showWishesBtn = document.getElementById('showWishesBtn');
            const closeWishesBtn = document.getElementById('closeWishesBtn');
            const wishesOverlay = document.getElementById('wishesOverlay');
            const wishesBottomSheet = document.getElementById('wishesBottomSheet');

            showWishesBtn?.addEventListener('click', () => {
                wishesOverlay.classList.remove('hidden');
                setTimeout(() => {
                    wishesBottomSheet.classList.add('active');
                }, 10);
            });

            const closeWishes = () => {
                wishesBottomSheet.classList.remove('active');
                setTimeout(() => {
                    wishesOverlay.classList.add('hidden');
                }, 300);
            };

            closeWishesBtn?.addEventListener('click', closeWishes);
            wishesOverlay?.addEventListener('click', (e) => {
                if (e.target === wishesOverlay) {
                    closeWishes();
                }
            });

            // 新規日程追加
            const addDayBtn = document.getElementById('addDayBtn');
            const itineraryList = document.getElementById('itineraryList');

            addDayBtn.addEventListener('click', async () => {
                try {
                    const response = await fetch(`/trips/${tripId}/itinerary`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            day_label: `${itineraryList.children.length + 1}日目`,
                            memo: '',
                            order: itineraryList.children.length
                        })
                    });

                    if (!response.ok) throw new Error('日程の追加に失敗しました');

                    const itinerary = await response.json();
                    const newDayBlock = createDayBlock(itinerary);
                    itineraryList.appendChild(newDayBlock);

                } catch (error) {
                    alert(error.message);
                }
            });

            // 日程ブロックの作成
            function createDayBlock(itinerary) {
                const div = document.createElement('div');
                div.className = 'day-block bg-white rounded-lg shadow p-4';
                div.dataset.id = itinerary.id;
                div.innerHTML = `
                    <div class="flex justify-between items-center mb-4">
                        <input type="text" class="day-label text-lg font-bold bg-transparent border-none focus:ring-0" 
                            value="${itinerary.day_label}" placeholder="日程のタイトル">
                        <button class="delete-day-btn text-gray-400 hover:text-gray-600">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    <textarea class="memo w-full min-h-[100px] p-2 border rounded-md" 
                        placeholder="メモを入力...">${itinerary.memo}</textarea>
                `;

                // イベントリスナーを追加
                attachEventListeners(div);
                return div;
            }

            // 通知機能
            function showNotification(message, isError = false) {
                const notification = document.getElementById('notification');
                const messageEl = document.getElementById('notificationMessage');
                
                messageEl.textContent = message;
                notification.classList.remove('translate-x-full');
                notification.classList.add(isError ? 'bg-rose-100' : 'bg-emerald-100');
                
                setTimeout(() => {
                    hideNotification();
                }, 3000);
            }

            function hideNotification() {
                const notification = document.getElementById('notification');
                notification.classList.add('translate-x-full');
            }

            // エラーハンドリングの改善
            async function handleRequest(url, options, successMessage) {
                try {
                    const response = await fetch(url, {
                        ...options,
                        headers: {
                            ...options.headers,
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        }
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.message || '操作に失敗しました');
                    }

                    if (successMessage) {
                        showNotification(successMessage);
                    }

                    return data;
                } catch (error) {
                    showNotification(error.message, true);
                    throw error;
                }
            }

            // 新規日程追加の改善
            addDayBtn.addEventListener('click', async () => {
                try {
                    const itinerary = await handleRequest(
                        `/trips/${tripId}/itinerary`,
                        {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                day_label: `${itineraryList.children.length + 1}日目`,
                                memo: '',
                                order: itineraryList.children.length
                            })
                        },
                        '新しい日程を追加しました'
                    );

                    const newDayBlock = createDayBlock(itinerary);
                    itineraryList.appendChild(newDayBlock);

                } catch (error) {
                    console.error('Error:', error);
                }
            });

            // イベントリスナーの追加
            function attachEventListeners(dayBlock) {
                const dayLabel = dayBlock.querySelector('.day-label');
                const memo = dayBlock.querySelector('.memo');
                const deleteBtn = dayBlock.querySelector('.delete-day-btn');
                const id = dayBlock.dataset.id;

                let saveTimeout;

                // 自動保存機能
                const autoSave = async (data) => {
                    try {
                        await handleRequest(
                            `/trips/${tripId}/itinerary/${id}`,
                            {
                                method: 'PUT',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify(data)
                            }
                        );
                    } catch (error) {
                        console.error('Error:', error);
                    }
                };

                // 日程ラベルの保存
                dayLabel.addEventListener('input', (e) => {
                    clearTimeout(saveTimeout);
                    saveTimeout = setTimeout(() => {
                        autoSave({ day_label: e.target.value });
                    }, 500);
                });

                // メモの保存
                memo.addEventListener('input', (e) => {
                    clearTimeout(saveTimeout);
                    saveTimeout = setTimeout(() => {
                        autoSave({ memo: e.target.value });
                    }, 500);
                });

                // 削除機能
                deleteBtn.addEventListener('click', async () => {
                    if (!confirm('この日程を削除してもよろしいですか？')) return;

                    try {
                        await handleRequest(
                            `/trips/${tripId}/itinerary/${id}`,
                            {
                                method: 'DELETE'
                            },
                            '日程を削除しました'
                        );

                        dayBlock.remove();

                    } catch (error) {
                        console.error('Error:', error);
                    }
                });
            }

            // 既存の日程ブロックにイベントリスナーを追加
            document.querySelectorAll('.day-block').forEach(attachEventListeners);
        });
    </script>
</body>
</html>