<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Trip extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'creator_id',
        'share_token',
        'start_date',
        'end_date',
        'confirmed_start_date',
        'confirmed_end_date',
    ];

    protected $casts = [
        'confirmed_start_date' => 'date',
        'confirmed_end_date' => 'date',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function requests()
    {
        return $this->hasMany(TripRequest::class);
    }
    // ユーザーとのリレーション
    public function users()
    {
        return $this->belongsToMany(User::class, 'trip_user')
                    ->withTimestamps();
    }

    // 作成者とのリレーション
    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }
    // 共有トークンを生成
    public function generateShareToken()
    {
        $this->share_token = \Str::random(32);
        $this->save();
        
        return $this->share_token;
    }
    public function tripRequests()
    {
        return $this->hasMany(TripRequest::class);
    }
    public function candidateDates()
    {
        return $this->hasMany(CandidateDate::class);
    }
    public function itineraries()
    {
        return $this->hasMany(Itinerary::class)->orderBy('order');
    }
}