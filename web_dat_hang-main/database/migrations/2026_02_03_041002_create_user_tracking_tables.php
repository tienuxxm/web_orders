<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Cập nhật bảng users (Thêm cột nếu chưa có)
        Schema::table('users', function (Blueprint $table) {
            // Kiểm tra cột trước khi thêm để tránh lỗi "Column already exists"
            if (!Schema::hasColumn('users', 'last_login_at')) {
                $table->dateTime('last_login_at')->nullable();
            }
            if (!Schema::hasColumn('users', 'last_login_ip')) {
                $table->string('last_login_ip', 50)->nullable();
            }
            if (!Schema::hasColumn('users', 'login_count')) {
                $table->integer('login_count')->default(0);
            }
        });

        // 2. Tạo bảng user_activities
        if (!Schema::hasTable('user_activities')) {
            Schema::create('user_activities', function (Blueprint $table) {
                $table->id(); // Tự động tạo bigint identity(1,1) primary key
                
                $table->bigInteger('user_id')->index(); // Không dùng foreign key cứng
                $table->string('action', 50)->index(); 
                
                $table->string('target_type', 100)->nullable(); 
                $table->string('target_id', 50)->nullable();    
                
                $table->text('description')->nullable(); // SQL Server dùng nvarchar(max) cho text
                $table->string('ip_address', 50)->nullable();
                $table->text('user_agent')->nullable();
                
                $table->dateTime('created_at')->useCurrent()->index();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_activities');
        
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['last_login_at', 'last_login_ip', 'login_count']);
        });
    }
};