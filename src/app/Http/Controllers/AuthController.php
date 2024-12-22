<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Services\UserService;

class AuthController extends Controller
{

    public function __construct(protected UserService $userService)
    {
    }

    public function register(RegisterRequest $request)
    {
        $user = $this->userService->registerUser($request);

        $token = $user->createToken('API Token')->plainTextToken;

        return response()->json(['user' => $user, 'token' => $token], 201);
    }
}
