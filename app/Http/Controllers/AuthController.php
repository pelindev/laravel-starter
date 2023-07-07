<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\AuthLoginRequest;
use App\Http\Requests\Auth\AuthRegisterRequest;

use App\Models\User;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

    public function __construct()
    {
        $this->middleware(
            'auth:api',
            [
                'except' => [
                    'login',
                    'register'
                ]
            ]
        );
    }

    public function login(AuthLoginRequest $request)
    {
        $request->validated();

        $credentials = $request->only('email', 'password');

        $token = Auth::attempt($credentials);

        if (!$token) {
            return response()->json(
                [
                    'status'  => 'error',
                    'message' => 'Unauthorized',
                ],
                401
            );
        }

        $user = Auth::user();

        return response()->json([
                'status'        => 'success',
                'user'          => $user,
                'authorisation' => [
                    'token' => $token,
                    'type'  => 'bearer',
                ]
            ]);

    }

    public function register(AuthRegisterRequest $request)
    {
        $validatedData = $request->validated();

        $user = User::create([
            'name'     => $validatedData['name'],
            'email'    => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
        ]);

        $token = Auth::login($user);

        return response()->json([
            'status'        => 'success',
            'message'       => 'User created successfully',
            'user'          => $user,
            'authorisation' => [
                'token' => $token,
                'type'  => 'bearer',
            ]
        ]);
    }

    public function logout()
    {
        Auth::logout();
        return response()->json([
            'status'  => 'success',
            'message' => 'Successfully logged out',
        ]);
    }

    public function refresh()
    {
        return response()->json([
            'status'        => 'success',
            'user'          => Auth::user(),
            'authorisation' => [
                'token' => Auth::refresh(),
                'type'  => 'bearer',
            ]
        ]);
    }

}