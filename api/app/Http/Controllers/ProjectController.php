<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use Illuminate\Support\Facades\Log;

class ProjectController extends Controller
{
    // ProjectController@store
    public function store(Request $request) {


        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $project = Project::create([
            'user_id' => auth()->id(),
            'name'    => $request->name,
        ]);

        return response()->json([
            'message' => 'Project created!',
            'project' => $project
        ]);
    }

    public function index()
    {
        $projects = auth()->user()->projects()->latest()->get();
        Log::debug($projects);
        Log::info($projects);
        return response()->json($projects);
    }
}
