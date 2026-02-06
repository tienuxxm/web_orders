<?php

namespace App\Observers;

use App\Models\OrderItem;
use App\Models\UserActivity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class OrderItemObserver
{
    // Khi thêm sản phẩm mới vào đơn
    public function created(OrderItem $item)
    {
        $desc = "Thêm sản phẩm: {$item->ItemName} (SL: " . (float)$item->Quantity . ")";
        $this->log('add_item', $item->DocumentNo, $desc, [
            'item_code' => $item->ItemCode,
            'quantity' => $item->Quantity,
            'price' => $item->Price
        ]);
    }

    // Khi sửa số lượng hoặc đơn giá
    public function updated(OrderItem $item)
    {
        $changes = [];
        $descParts = [];

        // Check số lượng
        if ($item->isDirty('Quantity')) {
            $old = (float)$item->getOriginal('Quantity');
            $new = (float)$item->Quantity;
            $changes['Quantity'] = ['old' => $old, 'new' => $new];
            $descParts[] = "Sửa SL {$item->ItemName}: $old -> $new";
        }

        // Check đơn giá
        if ($item->isDirty('Price')) {
            $old = (float)$item->getOriginal('Price');
            $new = (float)$item->Price;
            $changes['Price'] = ['old' => $old, 'new' => $new];
            $descParts[] = "Sửa giá {$item->ItemName}";
        }

        if (!empty($changes)) {
            $finalDesc = implode('. ', $descParts);
            $this->log('update_item', $item->DocumentNo, $finalDesc, $changes);
        }
    }

    // Khi xóa sản phẩm khỏi đơn
    public function deleted(OrderItem $item)
    {
        $desc = "Xóa sản phẩm: {$item->ItemName}";
        $this->log('remove_item', $item->DocumentNo, $desc, [
            'item_code' => $item->ItemCode,
            'item_name' => $item->ItemName
        ]);
    }

    private function log($action, $orderId, $desc, $props = null)
    {
        if (Auth::check()) {
            UserActivity::create([
                'user_id' => Auth::id(),
                'action' => $action,
                'target_type' => 'Order', 
                'target_id' => $orderId,  
                'description' => $desc,
                'properties' => $props ? json_encode($props, JSON_UNESCAPED_UNICODE) : null,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
                'created_at' => now(),
            ]);
        }
    }
}