<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\RegisterRequest;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        try {
            
            DB::beginTransaction();

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
            ]);

            DB::commit();

            $userData = [
                'user'=> [
                    'name' => $user->name,
                    'email' => $user->email,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ],
            ];

            return ResponseHelper::success($userData, 'User registered successfully', 201);


        } catch (\Exception $e) {

            DB::rollBack();
            Log::error('Registration Error: ' . $e->getMessage(), ['stack' => $e->getTraceAsString()]);
            return ResponseHelper::error('Error', 'Registration failed', 500);

        }
    }

    public function login(LoginRequest $request)
    {
        try {
            
            $credentials = $request->only('email', 'password');

            if (!Auth::attempt($credentials)) {
                Log::warning('Login failed for email: ' . $request->email . ' - Invalid credentials');
                return ResponseHelper::error('Invalid login details', null, 401);
            }

            $user = User::where('email', $request->email)->firstOrFail();

            $user->tokens()->delete();

            $token = $user->createToken('auth_token')->plainTextToken;

            $userData = [
                'user'=> [
                    'name' => $user->name,
                    'email' => $user->email,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ],
                'access_token' => $token,
                'token_type' => 'Bearer',
            ];

            return ResponseHelper::success($userData, 'User logged in successfully', 200);

        } catch (\Exception $e) {
            
            Log::error('Login Error: ' . $e->getMessage(), ['stack' => $e->getTraceAsString()]);
            return ResponseHelper::error('Error', 'Login failed', 500);

        }
    }
}
