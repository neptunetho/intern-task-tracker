<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TaskController extends Controller
{
    // GET /api/tasks (Cached in Redis for 60 seconds)
    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $status = $request->query('status', 'all');
        $sort = $request->query('sort', 'created_at');
        $direction = strtolower($request->query('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        $cacheKey = "user_{$userId}_tasks_{$status}_{$sort}_{$direction}";

        $tasks = Cache::remember($cacheKey, 60, function () use ($request, $status, $sort, $direction) {
            $query = $request->user()->tasks();

            if ($status === 'completed') {
                $query->where('completed', true);
            } elseif ($status === 'pending') {
                $query->where('completed', false);
            }

            $sortField = in_array($sort, ['created_at', 'due_date', 'title']) ? $sort : 'created_at';

            if ($sortField === 'due_date') {
                $query->orderByRaw('due_date IS NULL, due_date ' . $direction);
            } else {
                $query->orderBy($sortField, $direction);
            }

            return $query->get();
        });

        return response()->json($tasks);
    }

    // POST /api/tasks (Invalidates cache on task creation)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
        ]);

        $task = $request->user()->tasks()->create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'due_date' => $validated['due_date'] ?? null,
            'completed' => false,
        ]);

        $this->invalidateUserCache($request->user()->id);

        return response()->json($task, 201);
    }

    // PUT/PATCH /api/tasks/{task} (Invalidates cache on update)
    public function update(Request $request, Task $task)
    {
        if ($task->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'completed' => 'sometimes|boolean',
        ]);

        $task->update($validated);

        $this->invalidateUserCache($request->user()->id);

        return response()->json($task);
    }

    // DELETE /api/tasks/{task} (Invalidates cache on delete)
    public function destroy(Request $request, Task $task)
    {
        if ($task->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $task->delete();

        $this->invalidateUserCache($request->user()->id);

        return response()->json(['message' => 'Task deleted successfully']);
    }

    // Helper: Flush cached task queries for this user
    private function invalidateUserCache(int $userId): void
    {
        $statuses = ['all', 'completed', 'pending'];
        $sorts = ['created_at', 'due_date', 'title'];
        $directions = ['asc', 'desc'];

        foreach ($statuses as $st) {
            foreach ($sorts as $so) {
                foreach ($directions as $dir) {
                    Cache::forget("user_{$userId}_tasks_{$st}_{$so}_{$dir}");
                }
            }
        }
    }
}