<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index()
    {
        return Project::with('client', 'timeEntries')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'in:active,inactive,completed'
        ]);

        $project = Project::create($validated);

        return response()->json($project->load('client'), 201);
    }

    public function show(Project $project)
    {
        return $project->load('client', 'timeEntries');
    }

    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'client_id' => 'exists:clients,id',
            'name' => 'string|max:255',
            'description' => 'nullable|string',
            'status' => 'in:active,inactive,completed'
        ]);

        $project->update($validated);

        return response()->json($project->load('client'));
    }

    public function destroy(Project $project)
    {
        $project->delete();

        return response()->json(['message' => 'Project deleted successfully']);
    }
}
