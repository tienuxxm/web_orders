<?php

namespace App\Observers;

use App\Models\MergeOrder; // <--- Model MergeOrder
use App\Models\UserActivity;
use App\Models\OrderStatus;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class MergeOrderObserver
{
    // 1. Khi tạo đơn gộp mới
    public function created(MergeOrder $order)
    {
        $this->log('create_merge_order', $order->DocumentNo, "Tạo đơn gộp mới (MP)");
    }

    // 2. Khi cập nhật trạng thái đơn gộp
    public function updated(MergeOrder $order)
    {
        if ($order->isDirty('Status')) {
            $oldStatusVal = $order->getOriginal('Status');
            $newStatusVal = $order->Status;

            // Lấy tên hiển thị
            $oldName = $this->getStatusName($oldStatusVal);
            $newName = $this->getStatusName($newStatusVal);
            
            $desc = "Đổi trạng thái Gộp: $oldName -> $newName";

            $this->log('update_merge_status', $order->DocumentNo, $desc);
        }
    }

    // Hàm lấy tên trạng thái (Giống bên Order)
    private function getStatusName($typeValue)
    {
        $status = OrderStatus::where('Type', $typeValue)
                             ->where('Table', 'Order Purchasing') 
                             ->first();
        return $status ? $status->Name : $typeValue;
    }

    // Hàm ghi log chung
    private function log($action, $targetId, $desc)
    {
        if (Auth::check()) {
            UserActivity::create([
                'user_id' => Auth::id(),
                'action' => $action,
                'target_type' => 'MergeOrder', // <--- Đánh dấu là đơn Gộp
                'target_id' => $targetId,
                'description' => $desc,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
                'created_at' => now(),
            ]);
        }
    }
}