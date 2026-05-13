<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Services\GeminiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AIController extends Controller
{
    protected GeminiService $geminiService;

    public function __construct(GeminiService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    public function chat(Request $request): JsonResponse
    {
        $data = $request->validate([
            'message' => 'required|string',
            'history' => 'nullable|array',
        ]);

        $user = $request->user();
        
        $userTasks = Task::query()
            ->where('user_id', '=', $user->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($task) {
                return [
                    'title' => $task->title,
                    'priority' => $task->priority,
                    'status' => $task->status,
                ];
            })
            ->toArray();

        $response = $this->geminiService->chatWithContext(
            $data['message'],
            $data['history'] ?? [],
            $userTasks,
            $user->id
        );

        return response()->json(['response' => $response]);
    }

    public function tip(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $total = Task::query()->where('user_id', '=', $user->id)->count();
        $todo = Task::query()->where('user_id', '=', $user->id)->where('status', '=', 'TODO')->count();
        $inProgress = Task::query()->where('user_id', '=', $user->id)->where('status', '=', 'IN_PROGRESS')->count();
        $done = Task::query()->where('user_id', '=', $user->id)->where('status', '=', 'DONE')->count();
        $completionRate = $total > 0 ? round($done / $total * 100, 2) : 0;

        $context = [
            'stats' => [
                'total' => $total,
                'todo' => $todo,
                'inProgress' => $inProgress,
                'done' => $done,
                'completionRate' => $completionRate,
            ]
        ];

        if ($request->has('stats')) {
            $context['stats'] = array_merge($context['stats'], $request->input('stats', []));
        }
        if ($request->has('tasks')) {
            $context['tasks'] = $request->input('tasks');
        }

        $tip = $this->geminiService->generateProductivityTip($context);

        return response()->json(['tip' => $tip]);
    }
}