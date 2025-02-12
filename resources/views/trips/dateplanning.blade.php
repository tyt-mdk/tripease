<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover"><!-- レスポンシブ -->
    <meta name="csrf-token" content="{{ csrf_token() }}"><!-- csrf-token -->
    <script src="https://kit.fontawesome.com/ef96165231.js" crossorigin="anonymous"></script><!-- FontAwesome -->

    <!-- FullCalendarのCSSとJS -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/locales-all.min.js"></script>

    <style>
        /* 今日の日付のスタイル */
        .fc .fc-daygrid-day {
            position: relative !important;
        }

        /* FullCalendarのデフォルトの今日のハイライトを無効化 */
        .fc .fc-day-today {
            background: none !important;
        }

        .today-bg {
            position: absolute !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
            background-color: rgb(219 234 254 / 0.5) !important;  /* bg-blue-100 with opacity */
            margin: 0 !important;  /* マージンを0に */
            z-index: 0 !important;
            pointer-events: none !important;
        }

        /* 日付の数字を前面に */
        .fc .fc-daygrid-day-top {
            position: relative !important;
            z-index: 1 !important;
        }

        /* 曜日ヘッダーのスタイル */
        .fc .fc-col-header-cell {
            background-color: rgb(100 116 139) !important;  /* bg-slate-500 */
        }

        .fc .fc-col-header-cell-cushion {
            color: rgb(248 250 252) !important;  /* text-slate-50 */
            font-weight: normal !important;
            padding: 8px 0 !important;
        }
    </style>

    <title>Tripease</title>
    @vite('resources/css/app.css')
</head>
<body class="flex flex-col min-h-[100vh] text-[0.65rem] bg-slate-100 text-slate-800 font-notosans">
    <header>
    </header>
    <main class="flex-1 pb-20 md:pb-10">
        <div class="max-w-7xl mx-auto px-4 md:px-6">  <!-- コンテナ追加 -->
            <h1 class="text-xl md:text-2xl text-slate-950 mt-4 md:mt-6">
                {{ $trip->title }}の日程調整
            </h1>
        </div>

        <!-- カレンダー -->
        <div class="max-w-5xl mx-auto">  <!-- 最大幅を制限 -->
            <div class="rounded-lg shadow-md bg-slate-50 p-3 md:p-4 my-6 md:my-10 mx-4 md:mx-6">
                <div id="calendar" class="text-sm md:text-base"></div>
            </div>
        </div>

    </main>

    <!-- フッター -->
    <footer class="fixed md:static bottom-0 left-0 right-0 bg-slate-50 shadow-lg">
        <div class="max-w-4xl mx-auto">
            <div class="grid grid-cols-3 items-start h-20 text-sm pt-1 px-4">  <!-- h-16をh-20に、paddingを調整 -->
                <!-- 戻るボタン -->
                <div class="justify-self-start">
                    <a href="{{ route('trips.eachplanning', ['trip' => $trip->id]) }}" 
                    class="flex items-center justify-center w-10 h-10 bg-slate-200 rounded-full hover:bg-slate-300 transition-colors">  <!-- サイズとクラスを統一 -->
                        <i class="fa-solid fa-chevron-left text-slate-600"></i>  <!-- text-smとmd:text-baseを削除 -->
                    </a>
                </div>
                <!-- 中央のスペース -->
                <div class="justify-self-center"></div>
                <!-- 右側のスペース -->
                <div class="justify-self-end"></div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js"></script>

    <script>
        const tripId = {{ $trip->id }};  // この行を追加
        document.addEventListener('DOMContentLoaded', function() {
            // 候補日データの初期化
            const candidateDates = [
                @foreach ($candidateDates as $date)
                    {
                        date: '{{ $date->proposed_date }}',
                        id: {{ $date->id }},
                        judgement: '{{ $dateVotes->where("date_id", $date->id)->first()?->judgement ?? "" }}'
                    },
                @endforeach
            ];

            // 判定ボックスの作成
            function createJudgementBox(candidateDate) {
                const box = document.createElement('div');
                box.classList.add('judgement-box', 
                    'fixed', 'top-1/2', 'left-1/2', 'transform', '-translate-x-1/2', '-translate-y-1/2',
                    'bg-white', 'rounded-lg', 'shadow-lg', 'p-6', 'z-50'
                );

                // 日付をフォーマットする関数
                const formatDate = (dateStr) => {
                    const date = new Date(dateStr);
                    const year = date.getFullYear();
                    const month = (date.getMonth() + 1).toString().padStart(2, '0');
                    const day = date.getDate().toString().padStart(2, '0');
                    return `${year}年${month}月${day}日`;
                };

                box.innerHTML = `
                    <div class="space-y-4 w-[280px] md:w-[320px]">  <!-- 幅を固定 -->
                        <div class="flex justify-between items-center mb-4">
                            <div class="text-sm md:text-base">
                                <span class="text-slate-600">判定を選択</span>
                                <span class="text-slate-800 ml-2">${formatDate(candidateDate.date)}</span>
                            </div>
                            <button type="button" class="cancel-btn p-1 text-slate-400 hover:text-slate-600">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                        <div class="grid grid-cols-4 gap-2">  <!-- flexからgridに変更 -->
                            <button type="button" data-judgement="〇" 
                                class="judgement-btn h-12 bg-emerald-50 text-emerald-600 border-2 border-emerald-200 rounded-lg hover:bg-emerald-100 transition-colors">
                                <span class="text-lg">〇</span>
                            </button>
                            <button type="button" data-judgement="△" 
                                class="judgement-btn h-12 bg-amber-50 text-amber-600 border-2 border-amber-200 rounded-lg hover:bg-amber-100 transition-colors">
                                <span class="text-lg">△</span>
                            </button>
                            <button type="button" data-judgement="×" 
                                class="judgement-btn h-12 bg-rose-50 text-rose-600 border-2 border-rose-200 rounded-lg hover:bg-rose-100 transition-colors">
                                <span class="text-lg">×</span>
                            </button>
                            <button type="button" data-judgement="" 
                                class="judgement-btn h-12 bg-slate-50 text-slate-600 border-2 border-slate-200 rounded-lg hover:bg-slate-100 transition-colors text-sm">
                                クリア
                            </button>
                        </div>
                        <div class="pt-3 border-t border-slate-200">
                            <button type="button" class="delete-date-btn w-full px-4 py-2.5 text-sm text-rose-600 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition-colors">
                                <i class="fa-regular fa-trash-can mr-2"></i>この候補日を削除
                            </button>
                        </div>
                    </div>
                `;

                // 判定ボタンのイベントリスナー
                const judgementBtns = box.querySelectorAll('.judgement-btn');
                judgementBtns.forEach(btn => {
                    btn.addEventListener('click', async () => {
                        try {
                            const response = await fetch(`/set-judgement`, {  // URLを修正
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({
                                    date: candidateDate.date,
                                    judgement: btn.dataset.judgement
                                })
                            });

                            if (!response.ok) {
                                throw new Error('判定の保存に失敗しました');
                            }

                            // 成功時の処理
                            candidateDate.judgement = btn.dataset.judgement;
                            applyDateStyles();
                            box.remove();

                        } catch (error) {
                            console.error('Error:', error);
                            const messageP = box.querySelector('.text-slate-600');
                            messageP.classList.remove('text-slate-600');
                            messageP.classList.add('text-rose-600');
                            messageP.textContent = error.message;
                        }
                    });
                });

                // キャンセルボタンのイベントリスナー
                box.querySelector('.cancel-btn').addEventListener('click', () => {
                    box.remove();
                });

                // 削除ボタンのイベントリスナー
                const deleteBtn = box.querySelector('.delete-date-btn');
                deleteBtn.addEventListener('click', async (e) => {
                    e.stopPropagation(); // イベントの伝播を停止

                    // 確認用のモーダルを作成
                    const confirmModal = document.createElement('div');
                    confirmModal.classList.add(
                        'fixed', 'inset-0', 'bg-black/50', 'flex', 'items-center', 'justify-center', 'z-[60]'
                    );
                    
                    confirmModal.innerHTML = `
                        <div class="bg-white rounded-lg shadow-lg p-6 max-w-sm mx-4 w-full">
                            <div class="text-center">
                                <div class="mb-4">
                                    <i class="fa-regular fa-trash-can text-2xl text-rose-500"></i>
                                </div>
                                <h3 class="text-lg font-medium text-slate-900 mb-2">候補日の削除</h3>
                                <p class="text-slate-600 text-sm mb-6">
                                    この候補日を削除します。<br>
                                    この操作は取り消せません。
                                </p>
                                <div class="flex justify-center gap-3">
                                    <button type="button" class="cancel-btn px-6 py-2 text-sm text-slate-700 hover:bg-slate-100 rounded-full">
                                        キャンセル
                                    </button>
                                    <button type="button" class="confirm-btn px-6 py-2 text-sm text-white bg-rose-600 hover:bg-rose-700 rounded-full">
                                        削除する
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;

                    document.body.appendChild(confirmModal);

                    // モーダルのイベントリスナー
                    confirmModal.querySelector('.cancel-btn').addEventListener('click', () => {
                        confirmModal.remove();
                    });

                    confirmModal.querySelector('.confirm-btn').addEventListener('click', async () => {
                        try {
                            const response = await fetch(`/trips/{{ $trip->id }}/candidate-dates/${candidateDate.id}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'Accept': 'application/json'
                                }
                            });

                            if (!response.ok) {
                                const errorData = await response.json();
                                throw new Error(errorData.message || '削除に失敗しました');
                            }

                            // 成功時の処理
                            const index = candidateDates.findIndex(d => d.id === candidateDate.id);
                            if (index !== -1) {
                                candidateDates.splice(index, 1);
                            }

                            // スタイルを再適用
                            applyDateStyles();
                            box.remove();
                            confirmModal.remove();

                        } catch (error) {
                            console.error('Error:', error);
                            // エラーメッセージをモーダル内に表示
                            const errorDiv = confirmModal.querySelector('.text-slate-600');
                            errorDiv.classList.remove('text-slate-600');
                            errorDiv.classList.add('text-rose-600');
                            errorDiv.innerHTML = error.message || '削除に失敗しました';
                            
                            // 削除ボタンを無効化
                            const confirmBtn = confirmModal.querySelector('.confirm-btn');
                            confirmBtn.disabled = true;
                            confirmBtn.classList.add('opacity-50', 'cursor-not-allowed');
                        }
                    });

                    // モーダルの外側をクリックで閉じる
                    confirmModal.addEventListener('click', (e) => {
                        if (e.target === confirmModal) {
                            confirmModal.remove();
                        }
                    });
                });

                return box;
            }

            // 判定ボックスのイベント設定
            function setupJudgementBoxEvents(box, candidateDate) {
                // クリック外での閉じる処理
                setTimeout(() => {
                    document.addEventListener('click', function closeBox(e) {
                        if (!box.contains(e.target)) {
                            box.remove();
                            document.removeEventListener('click', closeBox);
                        }
                    });
                }, 0);

                // ボタンクリックの処理
                box.querySelectorAll('button').forEach(button => {
                    button.addEventListener('click', () => handleJudgementClick(button, candidateDate, box));
                });
            }

            // 判定クリック時の処理
            async function handleJudgementClick(button, candidateDate, box) {
                try {
                    // クリアボタンの場合は特別な処理
                    if (button.dataset.judgement === '') {
                        updateCandidateDate(candidateDate, '');
                        box.remove();
                        return;
                    }

                    // 通常の判定（〇、△、×）の場合は既存の処理
                    const response = await fetch(`/trips/{{ $trip->id }}/vote-date`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            date_id: candidateDate.id,
                            judgement: button.dataset.judgement,
                            trip_id: {{ $trip->id }}
                        })
                    });

                    if (!response.ok) throw new Error('Network response was not ok');

                    const data = await response.json();
                    updateCandidateDate(candidateDate, button.dataset.judgement);
                    box.remove();

                } catch (error) {
                    console.error('Error:', error);
                    alert('判定の保存に失敗しました。');
                }
            }

            // 候補日データの更新
            function updateCandidateDate(candidateDate, judgement) {
                const targetDate = candidateDates.find(d => d.id === candidateDate.id);
                if (targetDate) {
                    targetDate.judgement = judgement;
                    applyDateStyles();
                }
            }

            // 日付スタイルの適用
            function applyDateStyles() {
                // 既存のスタイルをクリア
                document.querySelectorAll('.date-bg').forEach(el => el.remove());

                // カレンダーの全セルに対して処理
                document.querySelectorAll('.fc-daygrid-day').forEach(el => {
                    const cellDate = el.getAttribute('data-date');
                    const candidateDate = candidateDates.find(date => date.date === cellDate);
                    
                    if (candidateDate) {
                        const mark = createDateMark(candidateDate.judgement);
                        el.appendChild(mark);
                    }
                });
            }

            // 日付マークの作成
            function createDateMark(judgement) {
                const mark = document.createElement('div');
                mark.className = 'date-bg absolute inset-0 flex items-center justify-center pointer-events-none';

                if (!judgement || judgement === '') {
                    mark.style.backgroundColor = 'rgb(219 234 254 / 0.5)';  // bg-blue-100/50
                    return mark;
                }

                mark.innerHTML = getJudgementMarkHTML(judgement);
                return mark;
            }

            // 判定マークのHTML取得
            function getJudgementMarkHTML(judgement) {
                const markStyles = {
                    '〇': `<div class="w-8 h-8 border-2 border-emerald-300/70 rounded-full"></div>`,
                    '△': `<div class="w-8 h-8 flex items-center justify-center">
                            <div class="w-6 h-6 bg-orange-200/50"
                                style="clip-path: polygon(50% 0%, 100% 100%, 0% 100%);">
                            </div>
                        </div>`,
                    '×': `<div class="w-8 h-8 flex items-center justify-center">
                            <div class="relative w-6 h-6">
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <div class="w-full h-[2px] bg-rose-300/50 transform rotate-45"></div>
                                </div>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <div class="w-full h-[2px] bg-rose-300/50 transform -rotate-45"></div>
                                </div>
                            </div>
                        </div>`
                };
                return markStyles[judgement] || '';
            }

            // カレンダーの初期化と設定
            const calendarEl = document.getElementById('calendar');
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'ja',
                headerToolbar: {
                    left: 'title',
                    center: '',
                    right: 'prev,next'
                },
                height: 'auto',
                viewDidMount: function(view) {
                    view.el.style.fontSize = window.innerWidth < 768 ? '0.875rem' : '1rem';
                },
                dayCellContent: arg => arg.dayNumberText.replace('日', ''),
                buttonText: {
                    prev: '▼',
                    next: '▲'
                },
                buttonIcons: false,
                nowIndicator: false,
                now: null,
                dayMaxEvents: true,
                dateClick: function(info) {
                    const candidateDate = candidateDates.find(date => date.date === info.dateStr);
                    
                    if (candidateDate) {
                        // 既存の候補日の場合は判定ボックスを表示
                        const existingBox = document.querySelector('.judgement-box');
                        if (existingBox) {
                            existingBox.remove();
                        }
                        const box = createJudgementBox(candidateDate);
                        document.body.appendChild(box);
                    } else {
                        // 新規の日付の場合は候補日追加の確認モーダルを表示
                        const confirmModal = document.createElement('div');
                        confirmModal.classList.add(
                            'fixed', 'inset-0', 'bg-black/50', 'flex', 'items-center', 'justify-center', 'z-[60]'
                        );
                        
                        confirmModal.innerHTML = `
                            <div class="bg-white rounded-lg shadow-lg p-6 max-w-sm mx-4 w-full">
                                <div class="text-center">
                                    <div class="mb-4">
                                        <i class="fa-regular fa-calendar-plus text-2xl text-blue-500"></i>
                                    </div>
                                    <h3 class="text-lg font-medium text-slate-900 mb-2">候補日の追加</h3>
                                    <p class="text-slate-600 text-sm mb-6">
                                        ${info.dateStr} を候補日として追加しますか？
                                    </p>
                                    <div class="flex justify-center gap-3">
                                        <button type="button" class="cancel-btn px-6 py-2 text-sm text-slate-700 hover:bg-slate-100 rounded-full">
                                            キャンセル
                                        </button>
                                        <button type="button" class="confirm-btn px-6 py-2 text-sm text-white bg-blue-600 hover:bg-blue-700 rounded-full">
                                            追加する
                                        </button>
                                    </div>
                                </div>
                            </div>
                        `;

                        document.body.appendChild(confirmModal);

                        // モーダルのイベントリスナー
                        confirmModal.querySelector('.cancel-btn').addEventListener('click', () => {
                            confirmModal.remove();
                        });

                        // モーダル内のエラー表示部分
                        confirmModal.querySelector('.confirm-btn').addEventListener('click', async () => {
                            try {
                                const response = await fetch(`/trips/{{ $trip->id }}/candidate-dates`, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                        'Accept': 'application/json'
                                    },
                                    body: JSON.stringify({
                                        date: info.dateStr
                                    })
                                });

                                if (!response.ok) {
                                    const errorData = await response.json();
                                    throw new Error(errorData.message || '候補日の追加に失敗しました');
                                }

                                const data = await response.json();
                                
                                // 新しい候補日を配列に追加
                                candidateDates.push({
                                    date: info.dateStr,
                                    id: data.id,
                                    judgement: ''
                                });

                                // スタイルを再適用
                                applyDateStyles();
                                confirmModal.remove();

                            } catch (error) {
                                console.error('Error:', error);
                                // エラーメッセージをモーダル内に表示
                                const messageP = confirmModal.querySelector('.text-slate-600');
                                messageP.classList.remove('text-slate-600');
                                messageP.classList.add('text-rose-600');
                                messageP.textContent = error.message || '候補日の追加に失敗しました';
                                
                                // 追加ボタンを無効化
                                const confirmBtn = confirmModal.querySelector('.confirm-btn');
                                confirmBtn.disabled = true;
                                confirmBtn.classList.add('opacity-50', 'cursor-not-allowed');
                            }
                        });

                        // モーダルの外側をクリックで閉じる
                        confirmModal.addEventListener('click', (e) => {
                            if (e.target === confirmModal) {
                                confirmModal.remove();
                            }
                        });
                    }
                }
            });

            // カレンダーの描画
            calendar.render();
            setTimeout(applyDateStyles, 100);
        });
    </script>

</body>
</html>