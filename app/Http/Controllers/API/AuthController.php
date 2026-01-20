<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\LoginUserRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}
    public function register(CreateUserRequest $createUserRequest): UserResource
    {
        $user = $this->authService->register($createUserRequest->validated());
        return $user ? new UserResource($user) : throw new \Exception('User registration failed');
    }

    public function login(LoginUserRequest $loginUserRequest)
    {
        $data = $loginUserRequest->validated();
        $response = $this->authService->login($data);
        return response()->json([
            'user' => $response['user'],
            'access_token' => $response['token']
        ]);
    }
}
