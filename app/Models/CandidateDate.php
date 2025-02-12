<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CandidateDate extends Model
{
    use HasFactory;
    protected $fillable = [
        'trip_id',
        'proposed_date',
        'judgement',
        'user_id'
    ];
    protected $dates = [
        'proposed_date'
    ];
    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function datevotes()
    {
        return $this->hasMany(DateVote::class, 'date_id', 'id');
    }
}
