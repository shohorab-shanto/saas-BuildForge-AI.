<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Services\GenerationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    protected $generationService;

    public function __construct(GenerationService $generationService)
    {
        $this->generationService = $generationService;
    }

    public function index()
    {
        return Auth::user()->organizations()->first()->projects()->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'idea_url' => 'nullable|url',
        ]);

        $organization = Auth::user()->organizations()->first();

        $project = $organization->projects()->create([
            'name' => $request->name,
            'description' => $request->description,
            'idea_url' => $request->idea_url,
            'status' => 'pending',
        ]);

        // Start processing in background (dispatch a job later)
        // For now, call synchronously (demo purposes)
        $this->generationService->processProject($project);

        return response()->json($project, 201);
    }

    public function show(Project $project)
    {
        $this->authorize('view', $project);
        return $project->load(['aiRequests', 'generatedFiles']);
    }

    public function update(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'idea_url' => 'nullable|url',
        ]);

        $project->update($request->only(['name', 'description', 'idea_url']));

        return response()->json($project);
    }

    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);
        $project->delete();
        return response()->json(null, 204);
    }

    public function analyze(Project $project)
    {
        $this->authorize('update', $project);
        // TODO: Call AI Agent to analyze project
        return response()->json(['message' => 'Analysis started']);
    }

    public function generateArchitecture(Project $project)
    {
        $this->authorize('update', $project);
        // TODO: Call AI Agent to generate architecture
        return response()->json(['message' => 'Architecture generation started']);
    }

    public function generateSchema(Project $project)
    {
        $this->authorize('update', $project);
        // TODO: Call AI Agent to generate schema
        return response()->json(['message' => 'Schema generation started']);
    }

    public function generateApi(Project $project)
    {
        $this->authorize('update', $project);
        // TODO: Call AI Agent to generate API
        return response()->json(['message' => 'API generation started']);
    }

    public function generateFrontend(Project $project)
    {
        $this->authorize('update', $project);
        // TODO: Call AI Agent to generate Frontend
        return response()->json(['message' => 'Frontend generation started']);
    }

    public function retry(Project $project)
    {
        $this->authorize('update', $project);
        
        // Reset status
        $project->update(['status' => 'pending']);
        
        // Start processing again
        $this->generationService->processProject($project);

        return response()->json($project);
    }
}
