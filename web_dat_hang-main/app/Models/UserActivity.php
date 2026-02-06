<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserActivity extends Model
{
    protected $table = 'user_activities';
    public $timestamps = false;
    protected $guarded = [];
    protected $fillable = [
        'user_id', 'action', 'target_type', 'target_id', 
        'description', 'ip_address', 'user_agent', 'created_at','properties'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}