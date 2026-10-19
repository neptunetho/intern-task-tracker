<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    // GET /api/tasks
    public function index()
    {
        return response()->json(Task::latest()->get());
    }

    // POST /api/tasks
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
        ]);

        $task = Task::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'due_date' => $validated['due_date'] ?? null,
            'completed' => false,
        ]);

        return response()->json($task, 201);
    }

    // GET /api/tasks/{task}
    public function show(Task $task)
    {
        return response()->json($task);
    }

    // PUT/PATCH /api/tasks/{task}
    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'completed' => 'sometimes|boolean',
        ]);

        $task->update($validated);

        return response()->json($task);
    }

    // DELETE /api/tasks/{task}
    public function destroy(Task $task)
    {
        $task->delete();

        return response()->json(['message' => 'Task deleted successfully']);
    }
}