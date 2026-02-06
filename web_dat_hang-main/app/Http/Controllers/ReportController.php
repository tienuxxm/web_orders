<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserActivity;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function exportUserActivity(Request $request)
    {
        // 1. Lấy dữ liệu
        $query = UserActivity::with('user')->orderBy('created_at', 'desc');

        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('created_at', [
                Carbon::parse($request->start_date)->startOfDay(), 
                Carbon::parse($request->end_date)->endOfDay()
            ]);
        }

        $activities = $query->limit(5000)->get();

        // 2. Tạo Excel
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Tiêu đề cột
        $sheet->setCellValue('A1', 'Thời gian');
        $sheet->setCellValue('B1', 'Nhân viên');
        $sheet->setCellValue('C1', 'Hành động');
        $sheet->setCellValue('D1', 'Chi tiết');
        $sheet->setCellValue('E1', 'IP');

        // Điền dữ liệu
        $row = 2;
        foreach ($activities as $act) {
            $sheet->setCellValue('A' . $row, $act->created_at);
            $sheet->setCellValue('B' . $row, $act->user ? $act->user->name : 'N/A');
            $sheet->setCellValue('C' . $row, $act->action);
            $sheet->setCellValue('D' . $row, $act->description);
            $sheet->setCellValue('E' . $row, $act->ip_address);
            $row++;
        }

        // 3. Xuất file
        $fileName = 'Log_Hoat_Dong.xlsx';
        return response()->streamDownload(function() use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }
}