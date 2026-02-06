<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Services\AuthService;
use App\Http\Resources\UserResource;
use App\Models\User;

class AuthController extends Controller
{
    protected $authService;
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
        
    }
    
    public function login(Request $request)
    {
        // 1. Validate
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        try {
            // 2. Gọi Service
            $result = $this->authService->login($request->email, $request->password);

            // 3. Trả về
            return response()->json([
                'message' => 'Đăng nhập thành công',
                'token' => $result['token'],
                'user' => new UserResource($result['user']), // Dùng Resource
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    public function loginViaEmail(Request $request)
    {
        // Đảm bảo URL không có dấu gạch chéo ở cuối
        $frontendUrl = rtrim(env('FRONTEND_URL', 'http://localhost:5173'), '/');

        // 1. Kiểm tra chữ ký
        if (! $request->hasValidSignature()) {
            // SỬA: Dùng away() để giữ nguyên dấu #
            return redirect()->away("$frontendUrl/#/magic-auth?error=expired");
        }

        try {
            $user = User::findOrFail($request->id);

            // 3. Tạo Token
            $result = $this->authService->generateTokenForUser($user);
            $token = $result['token'];

            // 4. Lấy tham số điều hướng
            $targetPath = $request->query('target_path', '/');
            $orderId = $request->query('order_id');

            // --- QUAN TRỌNG NHẤT ---
            // Cấu trúc: BaseUrl + /#/ + RouteName + Params
            $redirectUrl = "$frontendUrl/#/magic-auth?token=$token&redirect=$targetPath&open_order=$orderId";

            // SỬA: Thay redirect($url) thành redirect()->away($url)
            return redirect()->away($redirectUrl);

        } catch (\Exception $e) {
            // SỬA: Dùng away()
            return redirect()->away("$frontendUrl/#/login?error=system_error");
        }
    }

    public function register(Request $request)
    {
         return response()->json(['error' => 'Registration is not supported'], 403);
    }

    // app/Http/Controllers/AuthController.php

    public function ssoLogin(Request $request)
    {
        // 1. Nhận dữ liệu
        $email = $request->input('email');
        $timestamp = $request->input('timestamp');
        $signature = $request->input('signature');
        $secret = env('SSO_SECRET', 'MatKhauBiMatGiua2Ben_123456');

        // // 2. Kiểm tra thời gian (tránh request cũ bị dùng lại quá 5 phút)
        // if (abs(time() - $timestamp) > 300) {
        //     return response()->json(['error' => 'Link đã hết hạn'], 401);
        // }

        // 3. Kiểm tra chữ ký (Phải khớp quy tắc bên Worksuite)
        $validSignature = hash_hmac('sha256', $email . '|' . $timestamp, $secret);

        if (!hash_equals($validSignature, $signature)) {
            return response()->json(['error' => 'Chữ ký không hợp lệ'], 403);
        }

        // 4. Tìm User và cấp Token (Giống loginViaEmail)
        $user = \App\Models\User::where('email', $email)->first();

        if (!$user) {
            return response()->json(['error' => 'Không tìm thấy tài khoản user này trong hệ thống đặt hàng'], 404);
        }

        // Sử dụng service của bạn để tạo token
        $result = $this->authService->generateTokenForUser($user);

        return response()->json([
            'token' => $result['token'],
            'user' => new \App\Http\Resources\UserResource($user),
        ]);
    }
    public function logout()
    {
        try {
            // Hàm logout() của JWTAuth sẽ tự động đưa token hiện tại vào Blacklist
            Auth::logout();

            return response()->json([
                'status' => 'success',
                'message' => 'Đăng xuất thành công'
            ]);
        } catch (\Exception $e) {
            // Dù lỗi (ví dụ token hết hạn) vẫn trả về success để FE clear storage
            return response()->json([
                'status' => 'success', 
                'message' => 'Đã đăng xuất (Token không hợp lệ hoặc đã hết hạn)'
            ]);
        }
    }
}