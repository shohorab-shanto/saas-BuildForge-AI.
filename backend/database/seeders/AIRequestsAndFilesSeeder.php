<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\AIRequest;
use App\Models\GeneratedFile;

class AIRequestsAndFilesSeeder extends Seeder
{
    public function run(): void
    {
        $projects = Project::all();

        foreach ($projects as $project) {
            // 1. Create AI Requests for each project
            $requestTypes = ['analysis', 'architecture', 'schema', 'api_generation', 'frontend_generation'];

            foreach ($requestTypes as $type) {
                AIRequest::create([
                    'project_id' => $project->id,
                    'type' => $type,
                    'prompt' => 'Generate ' . $type . ' for ' . $project->name,
                    'response_json' => [
                        'status' => 'success',
                        'timestamp' => now()->toIso8601String(),
                        'details' => 'Generated ' . $type . ' for ' . $project->name
                    ],
                    'status' => 'completed',
                ]);
            }

            // 2. Create Generated Files for each project
            $fileData = [
                ['path' => 'database/migrations/create_users_table.php', 'type' => 'migration', 'content' => "<?php\n\nSchema::create('users', function (Blueprint \$table) {\n    \$table->id();\n    \$table->string('name');\n    \$table->timestamps();\n});"],
                ['path' => 'app/Models/User.php', 'type' => 'model', 'content' => "<?php\n\nnamespace App\Models;\n\nclass User extends Model {}"],
                ['path' => 'app/Http/Controllers/UserController.php', 'type' => 'controller', 'content' => "<?php\n\nnamespace App\Http\Controllers;\n\nclass UserController extends Controller {}"],
                ['path' => 'src/app/users/page.tsx', 'type' => 'frontend_page', 'content' => "'use client';\n\nexport default function UsersPage() { return <div>Users Page</div>; }"],
                ['path' => 'src/components/UserList.tsx', 'type' => 'frontend_component', 'content' => "'use client';\n\nexport function UserList() { return <div>User List</div>; }"],
            ];

            foreach ($fileData as $file) {
                GeneratedFile::create([
                    'project_id' => $project->id,
                    'file_path' => $file['path'],
                    'content' => $file['content'],
                    'file_type' => $file['type'],
                ]);
            }
        }
    }
}
