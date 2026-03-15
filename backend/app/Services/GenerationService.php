<?php

namespace App\Services;

use App\Models\Project;
use App\Models\GeneratedFile;
use Illuminate\Support\Facades\Log;

class GenerationService
{
    protected $aiService;

    public function __construct(AIService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Run the full AI generation pipeline for a project.
     */
    public function processProject(Project $project)
    {
        try {
            // 1. Idea Analysis
            $project->update(['status' => 'analyzing']);
            $analysis = $this->aiService->analyzeIdea($project, $project->idea_url ?: $project->description);
            
            if (!$analysis) {
                $project->update(['status' => 'failed']);
                Log::warning("Project generation aborted: Gemini is disabled.");
                return;
            }

            $project->update(['status' => 'analyzed']);

            // 2. System Architecture
            $project->update(['status' => 'architecting']);
            $architecture = $this->aiService->generateArchitecture($project, $analysis);
            
            if (!$architecture) {
                $project->update(['status' => 'failed']);
                return;
            }

            $project->update([
                'architecture_json' => $architecture,
                'status' => 'architected'
            ]);

            // 3. Database Schema (MySQL)
            $project->update(['status' => 'generating_schema']);
            $schema = $this->aiService->generateSchema($project, $architecture, $analysis['entities']);
            
            if (!$schema) {
                $project->update(['status' => 'failed']);
                return;
            }

            $project->update([
                'schema_json' => $schema,
                'status' => 'schema_generated'
            ]);

            // 4. API Generation (Laravel)
            $project->update(['status' => 'generating_api']);
            foreach ($schema['tables'] as $table) {
                $apiCode = $this->aiService->generateApiCode($project, $table);
                if ($apiCode) {
                    $this->saveApiFiles($project, $table['name'], $apiCode);
                }
            }

            // 5. Frontend Generation (Next.js)
            $project->update(['status' => 'generating_frontend']);
            foreach ($analysis['modules'] as $module) {
                $frontendCode = $this->aiService->generateFrontendCode($project, $module, $analysis['entities']);
                if ($frontendCode) {
                    $this->saveFrontendFiles($project, $module, $frontendCode);
                }
            }

            $project->update(['status' => 'completed']);

        } catch (\Exception $e) {
            Log::error("Project Generation Failed: " . $e->getMessage());
            $project->update(['status' => 'failed']);
            throw $e;
        }
    }

    /**
     * Save generated Laravel files to database.
     */
    protected function saveApiFiles(Project $project, string $tableName, array $code)
    {
        $files = [
            "database/migrations/create_{$tableName}_table.php" => ['content' => $code['migration'], 'type' => 'migration'],
            "app/Models/" . ucfirst($tableName) . ".php" => ['content' => $code['model'], 'type' => 'model'],
            "app/Http/Controllers/" . ucfirst($tableName) . "Controller.php" => ['content' => $code['controller'], 'type' => 'controller'],
            "app/Services/" . ucfirst($tableName) . "Service.php" => ['content' => $code['service'], 'type' => 'service'],
            "app/Repositories/" . ucfirst($tableName) . "Repository.php" => ['content' => $code['repository'], 'type' => 'repository'],
        ];

        foreach ($files as $path => $data) {
            GeneratedFile::create([
                'project_id' => $project->id,
                'file_path' => $path,
                'content' => $data['content'],
                'file_type' => $data['type'],
            ]);
        }
    }

    /**
     * Save generated Next.js files to database.
     */
    protected function saveFrontendFiles(Project $project, string $module, array $code)
    {
        // Save main page
        GeneratedFile::create([
            'project_id' => $project->id,
            'file_path' => "src/app/" . strtolower($module) . "/page.tsx",
            'content' => $code['page'],
            'file_type' => 'frontend_page',
        ]);

        // Save components
        foreach ($code['components'] as $component) {
            GeneratedFile::create([
                'project_id' => $project->id,
                'file_path' => "src/components/" . $component['name'],
                'content' => $component['content'],
                'file_type' => 'frontend_component',
            ]);
        }
    }
}
