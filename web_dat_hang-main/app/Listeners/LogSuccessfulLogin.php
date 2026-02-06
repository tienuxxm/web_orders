<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use App\Models\UserActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogSuccessfulLogin
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function handle(Login $event)
    {
        Log::info('--- DEBUG LOGIN EVENT ---'); // Ghi dấu
        Log::info('User ID: ' . $event->user->id);
        /** @var \App\Models\User $user */
        $user = $event->user;

        // 1. Cập nhật thông tin vào bảng Users
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $this->request->ip(),
            'login_count' => $user->login_count + 1
        ]);

        // 2. Ghi lịch sử vào user_activities
        UserActivity::create([
            'user_id' => $user->id,
            'action' => 'login',
            'description' => 'Đăng nhập hệ thống thành công',
            'ip_address' => $this->request->ip(),
            'user_agent' => $this->request->userAgent(),
            'created_at' => now(),
        ]);
    }
}
