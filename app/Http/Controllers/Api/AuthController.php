<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'role' => 'nullable|in:admin,petugas',
        ]);

        $credentials = request(['email', 'password']);

        if (! $token = auth()->guard('api')->attempt($credentials)) {
            return response()->json([
                'message' => 'Kredensial tidak valid'
            ], 401);
        }

        $user = auth()->guard('api')->user();

        // Cek status user
        if (!$user->status) {
            auth()->guard('api')->logout();
            return response()->json([
                'message' => 'Akun Anda tidak aktif'
            ], 403);
        }

        // Cek role jika dikirimkan oleh frontend (dibikin lebih fleksibel, misal "petugas" cocok dengan "Petugas Gudang")
        if ($request->has('role')) {
            $userRole = strtolower($user->role->nama_role ?? '');
            $requestedRole = strtolower($request->role);
            
            if (!str_contains($userRole, $requestedRole)) {
                auth()->guard('api')->logout();
                return response()->json([
                    'message' => 'Anda tidak memiliki akses sebagai ' . ucfirst($request->role)
                ], 403);
            }
        }

        return $this->respondWithToken($token, $user);
    }
    
    public function logout()
    {
        auth()->guard('api')->logout();

        return response()->json([
            'message' => 'Logout berhasil'
        ]);
    }

    protected function respondWithToken($token, $user)
    {
        return response()->json([
            'message' => 'Login berhasil',
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->guard('api')->factory()->getTTL() * 60,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role->nama_role ?? '-',
                'status' => $user->status ? 'Aktif' : 'Tidak Aktif'
            ]
        ]);
    }
}
