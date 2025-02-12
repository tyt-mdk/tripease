<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Trip;

return new class extends Migration
{
    public function up(): void
    {
        // 既存の全ての旅行プランに対して
        Trip::chunk(100, function ($trips) {
            foreach ($trips as $trip) {
                // creator_idのユーザーがまだ参加者として登録されていない場合
                if (!$trip->users()->where('user_id', $trip->creator_id)->exists()) {
                    // 参加者として追加
                    $trip->users()->attach($trip->creator_id, [
                        'created_at' => $trip->created_at,
                        'updated_at' => $trip->updated_at
                    ]);
                }
            }
        });
    }

    public function down(): void
    {
        // ロールバックの必要がある場合の処理
        // この場合は特に必要ないかもしれません
    }
};