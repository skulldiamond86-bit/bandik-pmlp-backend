<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = auth()->login($user);

        return response()->json([
            'message' => 'Register berhasil',
            'user' => $user,
            'token' => $token,
            'token_type' => 'bearer',
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        if (! $token = auth()->attempt($credentials)) {
            return response()->json([
                'message' => 'Email atau password salah',
            ], 401);
        }

        return response()->json([
            'message' => 'Login berhasil',
            'user' => auth()->user(),
            'token' => $token,
            'token_type' => 'bearer',
        ]);
    }

    public function logout(): JsonResponse
    {
        auth()->logout();

        return response()->json([
            'message' => 'Logout berhasil',
        ]);
    }

    public function profile(): JsonResponse
    {
        return response()->json([
            'user' => auth()->user(),
        ]);
    }
}
