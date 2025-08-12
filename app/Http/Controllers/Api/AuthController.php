<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $validatedData = $request->validated();
        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => $validatedData['password'],
            'role' => $validatedData['role'],
        ]);
        $token = $user->createToken('auth_token')->plainTextToken;
        return ApiResponse::SendResponse(201, 'User registered successfully', ['user' => $user,'token' => $token]);
    }  

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        if (auth()->attempt($credentials)) {
            $user = auth()->user();
            $token = $user->createToken('auth_token')->plainTextToken;
            return ApiResponse::sendResponse(200, 'Login successful', ['user' => $user, 'token' => $token]);
        }
        return ApiResponse::sendResponse(401, 'Unauthorized', null);
    }
}
