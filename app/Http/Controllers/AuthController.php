<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email'       => 'required|email|unique:users,email',
            'password'    => 'required|min:6',
            'mainGoal'    => 'nullable|string',
            'workRhythm'  => 'nullable|string',
        ]);

        $user = User::create([
            'email'       => $data['email'],
            'password'    => Hash::make($data['password']),
            'main_goal'   => $data['mainGoal'] ?? null,
            'work_rhythm' => $data['workRhythm'] ?? null,
            'ai_enabled'  => true,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json($this->authResponse($token, $user), 201);
    }

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::query()->where('email', '=', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.'],
            ])->status(401);
        }

        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json($this->authResponse($token, $user));
    }

    public function logout(Request $request): JsonResponse
    {
        if ($request->user()) {
            $request->user()->tokens()->delete();
        }

        return response()->json(['message' => 'Logged out successfully']);
    }

    private function authResponse(string $token, User $user): array
    {
        return [
            'token'      => $token,
            'type'       => 'Bearer',
            'userId'     => $user->id,
            'email'      => $user->email,
            'mainGoal'   => $user->main_goal,
            'workRhythm' => $user->work_rhythm,
            'iaEnabled'  => $user->ai_enabled,
        ];
    }
}