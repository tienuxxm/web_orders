<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class SSOController extends Controller
{
    public function login(Request $request) {
        $email = $request->email;
        $signature = $request->signature;

        // 1. Kiểm tra chữ ký bảo mật (MD5 của email + mã bí mật)
        $check = md5($email . env('SSO_SECRET'));

        if ($signature !== $check) {
            return response()->json(['error' => 'Chữ ký sai!'], 403);
        }

        // 2. Tìm user và cấp Token
        $user = User::where('email', $email)->first();
        if (!$user) return response()->json(['error' => 'Không tìm thấy user'], 404);

        $token = $user->createToken('sso-token')->plainTextToken;

        return response()->json(['token' => $token, 'user' => $user]);
    }
}