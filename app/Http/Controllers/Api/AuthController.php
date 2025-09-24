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
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

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

            $token = $user->createToken('auth_token')->plainTextToken;

            $userData = (new UserResource($user))->withToken($token);

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

            $userData = (new UserResource($user))->withToken($token);

            return ResponseHelper::success($userData, 'Login successful', 200);

        } catch (\Exception $e) {
            
            Log::error('Login Error: ' . $e->getMessage(), ['stack' => $e->getTraceAsString()]);
            return ResponseHelper::error('Error', 'Login failed', 500);

        }
    }

    public function logout(Request $request)
    {
        try {
            // Delete current access token
            $request->user()->currentAccessToken()->delete();

            return ResponseHelper::success(null, 'User logged out successfully', 200);

        } catch (\Exception $e) {
            Log::error('Logout Error: ' . $e->getMessage(), ['stack' => $e->getTraceAsString()]);
            return ResponseHelper::error('Error', 'Logout failed', 500);
        }
    }

    public function profile(Request $request)
    {
        try {
            $user = $request->user();

            $userData = new UserResource($user);

            return ResponseHelper::success($userData, 'Profile retrieved successfully', 200);

        } catch (\Exception $e) {
            Log::error('Profile Error: ' . $e->getMessage(), ['stack' => $e->getTraceAsString()]);
            return ResponseHelper::error('Error', 'Failed to retrieve profile', 500);
        }
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        try {
            $user = $request->user();

            DB::beginTransaction();

            // Update name if provided
            if ($request->has('name')) {
                $user->name = $request->name;
            }

            // Update email if provided
            if ($request->has('email')) {
                $user->email = $request->email;
                // Reset email verification if email changed
                if ($user->isDirty('email')) {
                    $user->email_verified_at = null;
                }
            }

            // Update password if provided
            if ($request->has('password')) {
                // Verify current password
                if (!Hash::check($request->current_password, $user->password)) {
                    return ResponseHelper::error('Current password is incorrect', null, 400);
                }
                $user->password = Hash::make($request->password);
                
                // Revoke all tokens when password is changed
                $user->tokens()->delete();
            }

            $user->save();

            DB::commit();

            $userData = new UserResource($user);

            // If password was changed, include new token
            if ($request->has('password')) {
                $token = $user->createToken('auth_token')->plainTextToken;
                $userData = (new UserResource($user))->withToken($token);
                return ResponseHelper::success($userData, 'Profile updated successfully. Please use the new token.', 200);
            }

            return ResponseHelper::success($userData, 'Profile updated successfully', 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Update Profile Error: ' . $e->getMessage(), ['stack' => $e->getTraceAsString()]);
            return ResponseHelper::error('Error', 'Failed to update profile', 500);
        }
    }
}
