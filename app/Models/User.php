<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject

{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'department_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function getJWTIdentifier() // đúng chính tả
    {
        return $this->getKey();
    }


    public function getJWTCustomClaims()
    {
        return [];
    }
    function role()
    {
        return $this->belongsTo(Role::class);
    }

    function department()
    {
        return $this->belongsTo(Department::class,'department_id');
    }
    // public function canCreateReport(): bool
    // {
    //     return (
    //         ($this->role->name_role === 'truong_phong' && in_array($this->department->name_department, ['IT', 'KE_TOAN'])) ||
    //         ($this->role->name_role === 'nhan_vien_chinh_thuc' && $this->department->name_department === 'KE_TOAN')
    //     );
    // }
    // public function isDepartmentHead($departmentName)
    // {
    //     return $this->hasRole('truong_phong') && 
    //            $this->department->name_department === $departmentName;
    // }

    // app/Models/User.php
    public function reports()
    {
        return $this->hasMany(Report::class);
    }
    public function allowedCategories() 
    {
         return $this->belongsToMany(Category::class); 
    }
    public function orders()            
    { 
        return $this->hasMany(Order::class); 
    }
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_user');
    }
    // App\Models\User.php

    public function isManager(): bool
    {
        return in_array($this->role->name_role, ['truong_phong', 'pho_phong','giam_doc'], true);
    }
    public function isEmployee(): bool
    {
        return $this->role->name_role === 'nhan_vien_chinh_thuc';
    }

    

    public function isInDepartment(string $name): bool
    {
        return $this->department?->name_department === $name;
    }

    public function isRole(string $role): bool
    {
        return $this->role?->name_role === $role;
    }

    

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
    'email_verified_at' => 'datetime',
    'password' => 'hashed',
    ];

}
