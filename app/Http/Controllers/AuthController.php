<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class AuthController extends Controller
{
    // LOGIN
    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'username' => 'required|string',
                'password' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'isSuccess' => false,
                    'message'   => 'Validation failed.',
                    'errors'    => $validator->errors(),
                ], 422);
            }

            $user = User::where('username', $request->username)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'isSuccess' => false,
                    'message'   => 'Invalid username or password.',
                ], 401);
            }

            // 🔥 Generate Sanctum token
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'isSuccess' => true,
                'message'   => 'Login successful.',
                'user'      => [
                    'id'       => $user->id,
                    'username' => $user->username,
                ],
                'token' => $token,
            ]);
        } catch (\Exception $e) {
            Log::error('Login error: ' . $e->getMessage());

            return response()->json([
                'isSuccess' => false,
                'message'   => 'An error occurred during login.',
                'error'     => $e->getMessage(),
            ], 500);
        }
    }

    // LOGOUT
    public function logout(Request $request)
    {
        try {
            $user = $request->user(); // Sanctum handles this automatically

            if (!$user) {
                return response()->json([
                    'isSuccess' => false,
                    'message'   => 'User not authenticated.',
                ], 401);
            }

            // 🧹 Revoke the current access token
            $user->currentAccessToken()->delete();

            return response()->json([
                'isSuccess' => true,
                'message'   => 'Logout successful.',
            ]);
        } catch (\Exception $e) {
            Log::error('Logout error: ' . $e->getMessage());

            return response()->json([
                'isSuccess' => false,
                'message'   => 'An error occurred during logout.',
                'error'     => $e->getMessage(),
            ], 500);
        }
    }
}
