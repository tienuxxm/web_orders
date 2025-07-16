<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ReportController;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    use AuthorizesRequests;
    public function __construct()
    {
        $this->middleware('auth:api');
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'prefix' => 'required|string|max:10',
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'description' => 'nullable|string',
            'user_emails' => 'array',
            'user_emails.*' => 'email|exists:users,email',
        ]);

        $category = Category::create($validated);

        if (!empty($validated['user_emails'])) {
            $userIds = User::whereIn('email', $validated['user_emails'])->pluck('id');
            $category->users()->sync($userIds); // bảng category_user
        }

        return response()->json(['category' => $category->load('users')]);
    }


    // Xem danh sách danh mục
    public function index()
    {
        $user = JWTAuth::user();
        $this->authorize('viewAny', Category::class);
        $categories = Category::with('users')
            ->get();
        return response()->json([
            'message' => 'Danh sách danh mục',
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'prefix'       => 'required|string|max:50',
            'status'       => 'required|in:active,inactive',
            'description'  => 'nullable|string',
            'user_emails'  => 'nullable|array',
            'user_emails.*'=> 'email|exists:users,email',
        ]);

        DB::beginTransaction();
        try {
            // Cập nhật category chính
            $category->update([
                'name'        => $validated['name'],
                'prefix'      => $validated['prefix'],
                'status'      => $validated['status'],
                'description' => $validated['description'] ?? null,
            ]);

            // Xử lý user_emails
            if (isset($validated['user_emails'])) {
                // Lấy danh sách user_id tương ứng
                $userIds = User::whereIn('email', $validated['user_emails'])->pluck('id')->toArray();

                // Gán lại hoàn toàn (xóa cái cũ, gán cái mới)
                $category->users()->sync($userIds);
            }

            DB::commit();

            return response()->json([
                'message'  => 'Category updated successfully',
                'category' => $category->load('users:id,name,email')
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Update failed',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    
   public function show($id)
    {
        $category = Category::with('users')->findOrFail($id);
        $this->authorize('view', $category);

        return response()->json([
            'message' => 'Chi tiết danh mục',
            'category' => $category
        ]);
    }



}
