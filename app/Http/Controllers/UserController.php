<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        return response()->json($this->profileResponse($request->user()));
    }

    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'mainGoal'   => 'nullable|string',
            'workRhythm' => 'nullable|string',
            'iaEnabled'  => 'nullable|boolean',
        ]);

        $request->user()->update(array_filter([
            'main_goal'   => $data['mainGoal'] ?? null,
            'work_rhythm' => $data['workRhythm'] ?? null,
            'ai_enabled'  => $data['iaEnabled'] ?? null,
        ], fn ($v) => $v !== null));

        return response()->json($this->profileResponse($request->user()->fresh()));
    }

    private function profileResponse($user): array
    {
        return [
            'id'         => $user->id,
            'email'      => $user->email,
            'mainGoal'   => $user->main_goal,
            'workRhythm' => $user->work_rhythm,
            'iaEnabled'  => $user->ai_enabled,
        ];
    }
}