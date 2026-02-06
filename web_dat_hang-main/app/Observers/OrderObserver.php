<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\UserActivity;
use App\Models\OrderStatus;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class OrderObserver
{
    public function created(Order $order)
    {
        $this->log('create_order', $order, "Tạo đơn hàng mới");
    }

    public function updated(Order $order)
    {
        $changes = [];
        $description = [];

        // 1. Theo dõi Trạng thái (Status)
        if ($order->isDirty('Status')) {
            $oldSt = $this->getStatusName($order->getOriginal('Status'));
            $newSt = $this->getStatusName($order->Status);
            $changes['Status'] = ['old' => $oldSt, 'new' => $newSt];
            $description[] = "Đổi trạng thái: $oldSt -> $newSt";
        }

        // 2. Theo dõi Ngày giao (ShipmentDate)
        if ($order->isDirty('ShipmentDate')) {
            $oldDate = $order->getOriginal('ShipmentDate');
            $newDate = $order->ShipmentDate;
            // Format ngày cho dễ nhìn
            $oldFmt = $oldDate ? date('d/m/Y', strtotime($oldDate)) : 'Trống';
            $newFmt = $newDate ? date('d/m/Y', strtotime($newDate)) : 'Trống';
            
            $changes['ShipmentDate'] = ['old' => $oldFmt, 'new' => $newFmt];
            $description[] = "Đổi ngày giao: $oldFmt -> $newFmt";
        }

        // 3. Theo dõi Ghi chú (Note)
        if ($order->isDirty('Note')) {
            $changes['Note'] = ['old' => $order->getOriginal('Note'), 'new' => $order->Note];
            $description[] = "Cập nhật ghi chú chung";
        }

        // Nếu có bất kỳ thay đổi nào trong danh sách trên thì mới ghi Log
        if (!empty($changes)) {
            $finalDesc = empty($description) ? "Cập nhật thông tin đơn hàng" : implode('. ', $description);
            $this->log('update_order', $order, $finalDesc, $changes);
        }
    }

    private function getStatusName($typeValue)
    {
        $status = OrderStatus::where('Type', $typeValue)->where('Table', 'Order Purchasing')->first();
        return $status ? $status->Name : $typeValue;
    }

    private function log($action, $order, $desc, $props = null)
    {
        if (Auth::check()) {
            UserActivity::create([
                'user_id' => Auth::id(),
                'action' => $action,
                'target_type' => 'Order',
                'target_id' => $order->DocumentNo,
                'description' => $desc,
                'properties' => $props ? json_encode($props, JSON_UNESCAPED_UNICODE) : null, // Lưu JSON
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
                'created_at' => now(),
            ]);
        }
    }
}