<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\BoardColumn;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BoardColumnController extends Controller
{
    public function index(Project $project)
    {
        // 1. Authorization check: Ensure the user owns this project
        if ($project->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized access'], 403);
        }

        // 2. Load columns with their tasks, ordered correctly
        $columns = $project->columns()
            ->with(['tasks']) // Eager load all tasks for each column
            ->get();

        // The response will be an array of columns, each containing a nested array of tasks.
        return response()->json([
            'data' => $columns,
        ]);
    }

    /**
     * Store a newly created column in storage.
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
            'title' => 'required|string|max:100',
        ]);

        // 3. Determine the next order number (max order + 1)
        $maxOrder = $project->columns()->max('order') ?? 0;

        // 4. Create the column
        $column = $project->columns()->create([
            'title' => $request->title,
            'order' => $maxOrder + 1,
        ]);

        return response()->json([
            'data' => $column,
            'message' => 'Column created successfully.'
        ], 201);
    }

    /**
     * Update the specified column (title or order).
     * * @param BoardColumn $boardColumn The BoardColumn model instance
     */
    public function update(Request $request, BoardColumn $boardColumn)
    {
        // 1. Authorization check: Ensure the column belongs to a project the user owns
        if ($boardColumn->project->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized access'], 403);
        }

        // 2. Validation
        $request->validate([
            'title' => 'sometimes|string|max:100',
            'order' => [
                'sometimes',
                'integer',
                'min:1',
                // Unique order constraint check (optional but good practice)
                Rule::unique('board_columns')->where(function ($query) use ($boardColumn) {
                    return $query->where('project_id', $boardColumn->project_id);
                })->ignore($boardColumn->id)
            ],
        ]);

        $boardColumn->update($request->only('title', 'order'));

        return response()->json([
            'data' => $boardColumn,
            'message' => 'Column updated successfully.'
        ]);
    }

    /**
     * Remove the specified column from storage.
     * * @param BoardColumn $boardColumn The BoardColumn model instance
     */
    public function destroy(BoardColumn $boardColumn)
    {
        // 1. Authorization check
        if ($boardColumn->project->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized access'], 403);
        }

        // The tasks associated with this column will be deleted automatically
        // due to the 'onDelete('cascade')' constraint in the migration.
        $boardColumn->delete();

        return response()->json(['message' => 'Column deleted successfully'], 204);
    }
}
