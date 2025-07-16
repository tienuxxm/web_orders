<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name','prefix','status', 'description'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
