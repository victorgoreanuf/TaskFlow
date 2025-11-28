<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class TaskController extends Controller
{
    /**
     * Store a newly created task.
     */
    public function store(Request $request, Project $project)
    {
        // 1. Authorization
        if ($project->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized access'], 403);
        }

        // 2. Validation
        $request->validate([
            'name' => 'required|string|max:255',
            'due_date' => 'nullable|date', // <--- This was already good in your code
            'column_id' => [
                'required',
                'integer',
                Rule::exists('board_columns', 'id')->where(function ($query) use ($project) {
                    return $query->where('project_id', $project->id);
                }),
            ],
        ]);

        $maxOrder = Task::where('column_id', $request->column_id)->max('order') ?? 0;

        $task = $project->tasks()->create([
            'column_id' => $request->column_id,
            'name' => $request->name,
            'description' => $request->description,
            'due_date' => $request->due_date,
            'order' => $maxOrder + 1,
        ]);

        return response()->json([
            'data' => $task,
            'message' => 'Task created successfully.'
        ], 201);
    }

    /**
     * Update the specified task.
     * 🚨 CRITICAL CHANGE: Added "Project $project" to the signature.
     */
    public function update(Request $request, Project $project, Task $task)
    {
        // 1. Authorization check
        if ($project->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized access'], 403);
        }

        // Ensure task belongs to this project (URL integrity check)
        if ($task->project_id !== $project->id) {
            return response()->json(['message' => 'Task does not belong to this project'], 403);
        }

        // 2. Validation
        $validatedData = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date', // <--- ADDED THIS so edits save the date
            'priority' => 'sometimes|integer|min:1|max:3',
            'column_id' => 'sometimes|integer|exists:board_columns,id',
            'order' => 'sometimes|integer|min:1',
        ]);

        // 3. Handle Logic
        if ($request->has(['column_id', 'order'])) {
            // Drag and Drop Logic
            $this->handleTaskReordering($task, $validatedData['column_id'], $validatedData['order']);
        } else {
            // Content Update (Name, Description, Date)
            $task->update($validatedData);
        }

        return response()->json([
            'data' => $task->fresh(),
            'message' => 'Task updated successfully.'
        ]);
    }

    /**
     * NEW: Move Task (Drag and Drop).
     * Handles Column ID and Order updates only.
     */
    public function move(Request $request, Project $project, Task $task)
    {
        // 1. Authorization
        if ($project->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized access'], 403);
        }

        if ($task->project_id !== $project->id) {
            return response()->json(['message' => 'Task does not belong to this project'], 403);
        }

        // 2. Validation
        $request->validate([
            'column_id' => [
                'required',
                'integer',
                Rule::exists('board_columns', 'id')->where(function ($query) use ($project) {
                    return $query->where('project_id', $project->id);
                }),
            ],
            'order' => 'required|integer|min:1',
        ]);

        $newColumnId = $request->column_id;
        $newOrder = $request->order;
        $oldColumnId = $task->column_id;
        $oldOrder = $task->order;

        Log::info("Called", [
            'newColumnId' => $newColumnId,
            'newOrder' => $newOrder,
            'oldColumnId' => $oldColumnId,
            'oldOrder' => $oldOrder,
        ]);
//        [2025-11-28 12:49:57] local.INFO:
//        Called {"newColumnId":7,"newOrder":1,"oldColumnId":6,"oldOrder":1}

        // 3. Logic: Shift tasks inside a transaction to prevent data corruption
        DB::transaction(function () use ($task, $newColumnId, $newOrder, $oldColumnId, $oldOrder) {

            // SCENARIO A: Moving to a DIFFERENT Column
            if ($oldColumnId != $newColumnId) {

                // 1. Make room in the NEW column
                // Shift all tasks with order >= newOrder DOWN (+1)
                Task::where('column_id', $newColumnId)
                    ->where('order', '>=', $newOrder)
                    ->increment('order');

                // 2. Update the task
                $task->column_id = $newColumnId;
                $task->order = $newOrder;
                $task->save();

                // 3. Close the gap in the OLD column
                // Shift all tasks with order > oldOrder UP (-1) to fill the hole
                Task::where('column_id', $oldColumnId)
                    ->where('order', '>', $oldOrder)
                    ->decrement('order');
            }

            // SCENARIO B: Reordering in the SAME Column
            else {
                if ($newOrder > $oldOrder) {
                    Log::info("Called", []);
                    // Moving DOWN (e.g. pos 1 -> pos 3)
                    // We need to shift items between (old+1) and (new) UP (-1)
                    Task::where('column_id', $newColumnId)
                        ->whereIn('order', [$oldOrder + 1, $newOrder])
                        ->decrement('order');
                } elseif ($newOrder < $oldOrder) {
                    // Moving UP (e.g. pos 3 -> pos 1)
                    // We need to shift items between (new) and (old-1) DOWN (+1)
                    Task::where('column_id', $newColumnId)
                        ->whereIn('order', [$newOrder, $oldOrder - 1])
                        ->increment('order');
                }

                // Finally, set the new order for the task itself
                $task->order = $newOrder;
                $task->save();
            }
        });

        return response()->json([
            'message' => 'Task moved successfully',
            'data' => $task->fresh()
        ]);
    }
    protected function handleTaskReordering(Task $task, $newColumnId, $newOrder)
    {
        $oldColumnId = $task->column_id;
        if ($oldColumnId != $newColumnId) {
            $task->column_id = $newColumnId;
        }
        $task->order = $newOrder;
        $task->save();
    }

    /**
     * Remove the specified task.
     * 🚨 CRITICAL CHANGE: Added "Project $project" to the signature.
     */
    public function destroy(Project $project, Task $task)
    {
        // 1. Authorization check
        if ($project->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized access'], 403);
        }

        if ($task->project_id !== $project->id) {
            return response()->json(['message' => 'Task does not belong to this project'], 403);
        }

        $task->delete();

        return response()->json(['message' => 'Task deleted successfully'], 204);
    }
}