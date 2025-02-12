<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DateVote extends Model
{
    protected $table = 'date_votes';  // テーブル名を明示的に指定

    protected $fillable = [
        'trip_id',
        'date_id',
        'user_id',
        'judgement'
    ];

    // リレーションシップの定義
    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    public function candidateDate()
    {
        return $this->belongsTo(CandidateDate::class, 'date_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
