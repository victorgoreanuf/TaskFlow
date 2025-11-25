<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    /**
     * Store a newly created task in storage.
     * This is typically called when a user adds a new card to a specific column.
     * * @param Project $project The Project model instance (resolved via slug)
     */
    public function store(Request $request, Project $project)
    {
        // 1. Authorization check
        if ($project->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized access'], 403);
        }

        // 2. Validation
        $request->validate([
            'name' => 'required|string|max:255',
            'due_date' => 'nullable|date',
            'column_id' => [
                'required',
                'integer',
                // Ensure the column belongs to this project
                Rule::exists('board_columns', 'id')->where(function ($query) use ($project) {
                    return $query->where('project_id', $project->id);
                }),
            ],
        ]);

        // 3. Determine the next order number within the specified column
        $maxOrder = Task::where('column_id', $request->column_id)->max('order') ?? 0;

        // 4. Create the task
        $task = $project->tasks()->create([
            'column_id' => $request->column_id,
            'name' => $request->name,
            'description' => $request->description,
            'due_date' => $request->due_date,
            'order' => $maxOrder + 1,
            // default priority is 1 (low)
        ]);

        return response()->json([
            'data' => $task,
            'message' => 'Task created successfully.'
        ], 201);
    }

    /**
     * Update the specified task. This handles both content updates AND drag-and-drop.
     * * @param Task $task The Task model instance
     */
    public function update(Request $request, Task $task)
    {
        // 1. Authorization check
        if ($task->project->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized access'], 403);
        }

        // 2. Validation for drag-and-drop and content updates
        $validatedData = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'sometimes|integer|min:1|max:3',

            // Drag-and-drop parameters
            'column_id' => 'sometimes|integer|exists:board_columns,id',
            'order' => 'sometimes|integer|min:1',
            // Note: Reordering logic is complex and best handled in a service or model method,
            // but for a basic update, we just take the new values.
        ]);

        // 3. Handle reordering/movement logic
        if ($request->has(['column_id', 'order'])) {
            // This is a drag-and-drop action (moving the card)
            $this->handleTaskReordering($task, $validatedData['column_id'], $validatedData['order']);

        } else {
            // Simple content update (name, description, etc.)
            $task->update($validatedData);
        }

        return response()->json([
            'data' => $task->fresh(), // Return the freshly updated task object
            'message' => 'Task updated successfully.'
        ]);
    }

    /**
     * Simple logic to update column_id and re-sequence the tasks in the old/new column.
     * * NOTE: This is a simplified version. For true, production-ready reordering,
     * you would use a package like spatie/eloquent-sortable inside a transaction.
     * We will keep it simple here.
     */
    protected function handleTaskReordering(Task $task, $newColumnId, $newOrder)
    {
        $oldColumnId = $task->column_id;

        // If the column changed, update the column_id
        if ($oldColumnId != $newColumnId) {
            $task->column_id = $newColumnId;
        }

        // Update the order
        $task->order = $newOrder;
        $task->save();

        // In a real app, you would now re-sequence all other tasks in the old and new columns
        // to close the gap and shift tasks down.
    }

    /**
     * Remove the specified task from storage.
     */
    public function destroy(Task $task)
    {
        // 1. Authorization check
        if ($task->project->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized access'], 403);
        }

        $task->delete();

        return response()->json(['message' => 'Task deleted successfully'], 204);
    }
}
