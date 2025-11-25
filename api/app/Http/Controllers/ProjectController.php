<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = auth()->user()->projects()->latest()->get();
        return response()->json([
            'data' => $projects,
        ]);
    }

    public function store(Request $request) {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        // 1. Create the project (Slug generation is handled by the Project Model's boot method)
        $project = Project::create([
            'user_id' => auth()->id(),
            'name'    => $request->name,
            // 'slug' property is safe to omit here if it's handled in the model
        ]);

        // 2. Initialize the default Kanban columns
        $this->initializeDefaultColumns($project);

        return response()->json([
            'data' => $project,
            'message' => 'Project created successfully.'
        ], 201);
    }

    public function show(Project $project){
        // 1. Authorization check
        if ($project->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized access'], 403);
        }

        // 2. Load the entire Kanban board structure
        // Eager load columns, and nested tasks for efficiency.
        $project->load([
            'columns' => function ($query) {
                // Load the tasks for each column, ordered correctly
                $query->with(['tasks']);
            }
        ]);

        return response()->json([
            'data' => $project,
        ]);
    }

    protected function initializeDefaultColumns(Project $project): void {
        $defaultColumns = ['To Do', 'In Progress', 'Review', 'Done'];
        $order = 1;

        foreach ($defaultColumns as $title) {
            $project->columns()->create([
                'title' => $title,
                'order' => $order++,
            ]);
        }
    }
}
