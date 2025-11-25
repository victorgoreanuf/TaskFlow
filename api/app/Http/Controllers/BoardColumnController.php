<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\BoardColumn;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BoardColumnController extends Controller
{
    // ... index and store are fine ...

    public function store(Request $request, Project $project)
    {
        if ($project->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized access'], 403);
        }

        $request->validate([
            'title' => 'required|string|max:100',
        ]);

        $maxOrder = $project->columns()->max('order') ?? 0;

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
     * Update the specified column.
     * 🚨 NOTE: Added "Project $project" to signature to match the URL structure
     */
    public function update(Request $request, Project $project, BoardColumn $boardColumn)
    {
        // 1. Authorization
        if ($project->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized access'], 403);
        }

        // Ensure column belongs to project (security check)
        if($boardColumn->project_id !== $project->id) {
            return response()->json(['message' => 'Column does not belong to this project'], 403);
        }

        // 2. Validation
        $request->validate([
            'title' => 'sometimes|string|max:100',
            // ... existing validation ...
        ]);

        $boardColumn->update($request->only('title', 'order'));

        return response()->json([
            'data' => $boardColumn,
            'message' => 'Column updated successfully.'
        ]);
    }

    /**
     * Remove the specified column.
     * 🚨 NOTE: Added "Project $project" to signature
     */
    public function destroy(Project $project, BoardColumn $boardColumn)
    {
        // 1. Authorization
        if ($project->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized access'], 403);
        }

        if($boardColumn->project_id !== $project->id) {
            return response()->json(['message' => 'Column does not belong to this project'], 403);
        }

        $boardColumn->delete();

        return response()->json(['message' => 'Column deleted successfully'], 204);
    }
}