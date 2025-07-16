<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function register(Request $request)
{
    /* ---------- Validate ---------- */
    $request->validate([
        'name'     => 'required|string|max:255',
        'email'    => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:6|confirmed',
        'role_id'  => 'required|exists:roles,id',
        'department_id' => 'nullable|exists:departments,id',
    ]);

    /* ---------- Lấy Role & Department ---------- */
    $role = Role::findOrFail($request->role_id);

    // Nếu không phải giám đốc => department_id bắt buộc
    $isDirector = strtolower($role->name_role) === 'giam_doc';   // hoặc 'giam_doc'
    if (!$isDirector && empty($request->department_id)) {
        return response()->json(['error' => 'Vui lòng chọn phòng ban.'], 422);
    }

    if ($isDirector) {
        $existingDirector = User::where('role_id', $role->id)->first();
        if ($existingDirector) {
            return response()->json(['error' => 'Hệ thống chỉ được phép có duy nhất một Giám đốc.'], 422);
        }
    }

    $department = $request->department_id
        ? Department::findOrFail($request->department_id)
        : null;

    /* ---------- Trưởng phòng: kiểm tra trùng ---------- */
    if (strtolower($role->name_role) === 'truong_phong') {
        $existingHead = User::where('role_id', $role->id)
            ->where('department_id', $department->id)
            ->first();

        if ($existingHead) {
            return response()->json(['error' => 'Phòng ban này đã có trưởng phòng.'], 422);
        }
    }

    /* ---------- Tạo user ---------- */
    $user = User::create([
        'name'          => $request->name,
        'email'         => $request->email,
        'password'      => bcrypt($request->password),
        'role_id'       => $role->id,
        'department_id' => $department?->id, // null nếu giám đốc
    ]);

    $user->load('department', 'role');

    /* ---------- JWT custom claims ---------- */
    $claims = [
        'user_id'         => $user->id,
        'role_id'         => $role->id,
        'role_name'       => $role->name_role,
        'department_id'   => $department?->id,
        'department_name' => $department?->name_department,
    ];

    $token = JWTAuth::claims($claims)->fromUser($user);

    return response()->json([
        'user' => [
            'id'         => $user->id,
            'name'       => $user->name,
            'email'      => $user->email,
            'department' => $department?->name_department,
            'role'       => $role->name_role,
        ],
        'token'      => $token,
        'token_type' => 'bearer',
        'expires_in' => JWTAuth::factory()->getTTL() * 60,
    ], 201);
}



    // Đăng nhập
    public function login(Request $request)
{
    $credentials = $request->only('email', 'password');

    if (!$token = JWTAuth::attempt($credentials)) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    // Lấy user hiện tại và load thông tin liên kết
    $user = JWTAuth::user()->load('department', 'role');

    // Kiểm tra nếu là giám đốc
    $isDirector = strtolower($user->role->name_role) === 'giam_doc'; // hoặc 'giam_doc'

    $customClaims = [
        'user_id' => $user->id,
        'role_id' => $user->role_id,
        'role_name' => $user->role->name_role,
        'department_id' => $isDirector ? null : $user->department_id,
        'department_name' => $isDirector ? null : optional($user->department)->name_department,
    ];

    // Tạo token với custom claims
    $tokenWithClaims = JWTAuth::claims($customClaims)->attempt($credentials);

    return response()->json([
        'message' => 'Đăng nhập thành công',
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'department' => $isDirector ? null : optional($user->department)->name_department,
            'role' => $user->role->name_role,
        ],
        'token' => $tokenWithClaims,
        'token_type' => 'bearer',
        'expires_in' => auth()->factory()->getTTL() * 240
    ]);
}


    // Đăng xuất
    public function logout(Request $request)
    {
        try {
            // Lấy token hiện tại từ header Authorization
            $token = JWTAuth::getToken();

            if (!$token) {
                return response()->json(
                    ['error' => 'Token not provided'],
                    Response::HTTP_BAD_REQUEST
                );
            }

            // Vô hiệu hoá (blacklist) token này
            JWTAuth::invalidate($token);

            // (Tùy chọn) Xoá cookie nếu bạn lưu token trong cookie
            // return response()->json([...])->withCookie(cookie()->forget('token'));

            return response()->json(
                ['message' => 'Đăng xuất thành công'],
                Response::HTTP_OK
            );
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(
                ['error' => 'Token invalid hoặc đã hết hạn'],
                Response::HTTP_UNAUTHORIZED
            );
        } catch (\Exception $e) {
            return response()->json(
                ['error' => 'Không thể đăng xuất, thử lại sau'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    // Lấy thông tin người dùng
    public function me()
    {
        return response()->json(Auth::user());
    }
}
