<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Category;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Log;

class CategoryPolicy
{
    /**
     * Create a new policy instance.
     */
    public const HEAD      = 'truong_phong';
    public const DEPUTY    = 'pho_phong';
    public const EMPLOYEE  = 'nhan_vien_chinh_thuc';
    public const INTERN    = 'thuc_tap_sinh';

    private const ALLOWED_DEPARTMENTS = ['KINH_DOANH', 'CUNG_UNG'];

    public function __construct()
    {
        //
    }
    public function viewAny(User $user): bool
    {
        return true; 
    }
    public function view(User $user, Category $category): bool
    {
        
        return true;
    }
    public function create(User $user): bool
    {
        if(
            $user->department->name_department === 'KINH_DOANH' &&
            $user->role->name_role !== self::INTERN
        ){
        return true;
        } // Chỉ cho phép nhân viên phòng kinh doanh và không phải thực tập sinh tạo danh mục
        return false; // Các trường hợp khác không được phép tạo danh mục
    }
    public function update(User $user, Category $category): bool

    {
       if(
            $user->department->name_department === 'KINH_DOANH' &&
            $user->role->name_role !== self::INTERN
        ){
        return true;
        } // Chỉ cho phép nhân viên phòng kinh doanh và không phải thực tập sinh tạo danh mục
        return false; //// Nhân viên chỉ có thể cập nhật danh mục của mình
    }
    public function delete(User $user, Category $category): bool
    {
       if(
            $user->department->name_department === 'KINH_DOANH' &&
            $user->role->name_role !== self::INTERN
        ){
        return true;
        } // Chỉ cho phép nhân viên phòng kinh doanh và không phải thực tập sinh tạo danh mục
        return false; //// Nhân viên không thể xóa danh mục
    }
}
