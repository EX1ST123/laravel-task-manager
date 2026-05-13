<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = $request->user()->tasks()->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        return response()->json($query->get()->map(fn ($t) => $this->taskResponse($t)));
    }

    public function stats(Request $request): JsonResponse
    {
        $user  = $request->user();
        $total = $user->tasks()->count();
        $done  = $user->tasks()->where('status', 'DONE')->count();

        return response()->json([
            'total'          => $total,
            'todo'           => $user->tasks()->where('status', 'TODO')->count(),
            'inProgress'     => $user->tasks()->where('status', 'IN_PROGRESS')->count(),
            'done'           => $done,
            'completionRate' => $total > 0 ? round($done / $total * 100, 2) : 0,
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $task = $request->user()->tasks()->findOrFail($id);

        return response()->json($this->taskResponse($task));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority'    => 'required|in:LOW,MEDIUM,HIGH',
            'category'    => 'required|in:STUDIES,WORK,PERSONAL',
            'status'      => 'nullable|in:TODO,IN_PROGRESS,DONE',
        ]);

        $task = $request->user()->tasks()->create([
            'title'       => $data['title'],
            'description' => $data['description'] ?? null,
            'priority'    => $data['priority'],
            'category'    => $data['category'],
            'status'      => $data['status'] ?? 'TODO',
        ]);

        return response()->json($this->taskResponse($task), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $task = $request->user()->tasks()->findOrFail($id);

        $data = $request->validate([
            'title'       => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'priority'    => 'sometimes|in:LOW,MEDIUM,HIGH',
            'category'    => 'sometimes|in:STUDIES,WORK,PERSONAL',
            'status'      => 'sometimes|in:TODO,IN_PROGRESS,DONE',
        ]);

        $task->update($data);

        return response()->json($this->taskResponse($task->fresh()));
    }

    public function complete(Request $request, int $id): JsonResponse
    {
        $task = $request->user()->tasks()->findOrFail($id);
        $task->markAsCompleted();

        return response()->json($this->taskResponse($task->fresh()));
    }

    public function inProgress(Request $request, int $id): JsonResponse
    {
        $task = $request->user()->tasks()->findOrFail($id);
        $task->markAsInProgress();

        return response()->json($this->taskResponse($task->fresh()));
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $task = $request->user()->tasks()->findOrFail($id);
        $task->delete();

        return response()->json(null, 204);
    }

    private function taskResponse(Task $task): array
    {
        return [
            'id'                  => $task->id,
            'title'               => $task->title,
            'description'         => $task->description,
            'priority'            => $task->priority,
            'status'              => $task->status,
            'category'            => $task->category,
            'categoryDisplayName' => $task->category_display_name,
            'userId'              => $task->user_id,
            'createdAt'           => $task->created_at,
            'updatedAt'           => $task->updated_at,
        ];
    }
}