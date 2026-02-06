<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event; 
use Illuminate\Auth\Events\Login;     
use App\Listeners\LogSuccessfulLogin; 
use App\Models\Order;    
use App\Models\MergeOrder;
use App\Observers\OrderItemObserver; 
use App\Models\OrderItem;          
use App\Observers\OrderObserver;   
use App\Observers\MergeOrderObserver;  

class AppServiceProvider extends ServiceProvider
{
   
    public function register(): void
    {
      
    }

    
    public function boot(): void
    {
        if (class_exists(OrderObserver::class)) {
        Order::observe(OrderObserver::class);
    }
    if (class_exists(MergeOrderObserver::class)) {
        MergeOrder::observe(MergeOrderObserver::class);
    }
    if (class_exists(OrderItemObserver::class)) {
        OrderItem::observe(OrderItemObserver::class);
    }
    }
}