<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequestLike extends Model
{
    protected $fillable = [
        'trip_request_id',
        'user_id',
    ];

    public function tripRequest()
    {
        return $this->belongsTo(TripRequest::class);
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}