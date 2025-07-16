<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Models\Order;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Carbon\Carbon;

class ExportController extends Controller
{
   public function exportMergedOrdersMultipleMonths(Request $request)
    {
        $user = JWTAuth::user();

        if (!$user || $user->department->name_department !== 'KINH_DOANH') {
            return response()->json(['message' => 'Bạn không có quyền xuất đơn'], 403);
        }

        $months = $request->input('months'); // ['07/2025', '08/2025']
        if (!is_array($months) || empty($months)) {
            return response()->json(['message' => 'Cần cung cấp danh sách tháng'], 422);
        }

        $orders = Order::with('items.product')
            ->where('merged', true)
            ->where('status', 'fulfilled')
            ->where('payment_status', 'paid')
            ->where(function ($query) use ($months) {
                foreach ($months as $month) {
                    if (preg_match('/^\d{2}\/\d{4}$/', $month)) {
                        [$m, $y] = explode('/', $month);
                        $query->orWhere(function ($sub) use ($m, $y) {
                            $sub->whereMonth('order_date', $m)
                                ->whereYear('order_date', $y);
                        });
                    }
                }
            })->get();

        if ($orders->isEmpty()) {
            return response()->json(['message' => 'Không có đơn hợp lệ'], 404);
        }

        $grouped = $orders->groupBy(function ($order) {
            return Carbon::parse($order->order_date)->format('m/Y');
        });

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $row = 1;

        foreach ($grouped as $month => $ordersInMonth) {
            // Ghi tiêu đề tháng
            $sheet->setCellValue("A{$row}", "Tháng: {$month}");
            $sheet->mergeCells("A{$row}:D{$row}");
            $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(14);
            $row += 1;

            // Ghi header
            $sheet->fromArray(['Mã SP', 'Tên SP', 'Đơn giá', 'Tổng SL'], null, "A{$row}");
            $sheet->getStyle("A{$row}:D{$row}")->getFont()->setBold(true);
            $row += 1;

            // Gom sản phẩm của tháng
            $productMap = [];
            foreach ($ordersInMonth as $order) {
                foreach ($order->items as $item) {
                    $key = $item->product_id;
                    if (!isset($productMap[$key])) {
                        $productMap[$key] = [
                            'product_code' => $item->product->code,
                            'product_name' => $item->product->name,
                            'price' => $item->unit_price,
                            'quantity' => 0
                        ];
                    }
                    $productMap[$key]['quantity'] += $item->quantity;
                }
            }

            // Ghi dữ liệu sản phẩm
            foreach ($productMap as $product) {
                $sheet->fromArray([
                    $product['product_code'],
                    $product['product_name'],
                    $product['price'],
                    $product['quantity']
                ], null, "A{$row}");
                $row++;
            }

            // Thêm dòng trống giữa các tháng
            $row++;
        }

        $filename = 'don-gop-theo-thang-' . now()->format('Ymd_His') . '.xlsx';
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
