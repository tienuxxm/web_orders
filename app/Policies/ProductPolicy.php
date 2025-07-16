<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    /**
     * Create a new policy instance.
     */
    public const HEAD      = 'truong_phong';
    public const DEPUTY    = 'pho_phong';
    public const EMPLOYEE  = 'nhan_vien_chinh_thuc';
    public const INTERN    = 'thuc_tap_sinh';

    public function __construct()
    {
        //
    }
    public function viewAny(User $user): bool
    {
        return true;
    }
    public function view(User $user, $product): bool
    {
        if(in_array($user->role->name_role, [self::HEAD, self::DEPUTY], true)){
            return true; // Trưởng phòng và phó phòng có thể xem tất cả sản phẩm
        }
        return $product->created_by === $user->id; // Chỉ người tạo mới
    }
    public function create(User $user): bool
    {
        return in_array($user->role->name_role, [self::HEAD, self::DEPUTY, self::EMPLOYEE], true);
    }
    public function update(User $user, $product): bool
    {
        
       if(
            $user->department->name_department === 'KINH_DOANH' &&
            $user->role->name_role !== self::INTERN
        ){
        return true;
        } 
        // Chỉ cho phép nhân viên phòng kinh doanh và không phải thực tập sinh tạo danh mục
        if($user->role->name_role === self::EMPLOYEE
        && $user->department->name_department === 'KINH_DOANH')
        {
            return $product->create_by === $user->id;
        }
        return false;
    }


    
    public function delete(User $user, $product): bool
    {
        if(
            $user->department->name_department === 'KINH_DOANH' &&
            $user->role->name_role !== self::INTERN
        ){
        return true;
        } 
        // Chỉ cho phép nhân viên phòng kinh doanh và không phải thực tập sinh tạo danh mục
        if($user->role->name_role === self::EMPLOYEE
        && $user->department->name_department === 'KINH_DOANH')
        {
            return $product->create_by === $user->id;
        }
        return false;
    }

}
