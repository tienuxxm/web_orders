<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'unit_price',
        'product_name',
        'line_total',
    ];
    public function order()   { return $this->belongsTo(Order::class); }
    public function product() 
    { 
        return $this->belongsTo(Product::class); 
    }
}

