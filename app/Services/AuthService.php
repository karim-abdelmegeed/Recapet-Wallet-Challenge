<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function register(array $data): User
    {
        return  User::create($data);
    }

    public function login(array $credentials): array
    {
        if (!Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid user credentials.'],
            ]);
        }
        $user = User::where('email', $credentials['email'])->first();
        return [
            'user' => $user,
            'token' => $user->createToken($user->name)->plainTextToken,
        ];
    }
}
