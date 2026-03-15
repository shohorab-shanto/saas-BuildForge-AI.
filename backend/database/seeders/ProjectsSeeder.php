<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\Organization;

class ProjectsSeeder extends Seeder
{
    public function run(): void
    {
        $organizations = Organization::all();

        $projectIdeas = [
            'SaaS ERP System',
            'Furniture E-commerce Platform',
            'Personal Training Management App',
            'AI Image Generator Dashboard',
            'Real Estate Marketplace',
            'Healthcare Patient Portal',
            'Legal Case Management Tool',
            'Portfolio Site for Designers',
            'Fitness Tracking API',
            'Task Management for Freelancers',
        ];

        foreach ($organizations as $org) {
            // Generate 2-3 projects for each organization
            for ($i = 0; $i < fake()->numberBetween(2, 4); $i++) {
                $name = fake()->randomElement($projectIdeas);
                
                Project::create([
                    'name' => $name . ' (' . ($i+1) . ')',
                    'description' => 'A production-ready ' . strtolower($name) . ' built with BuildForge AI.',
                    'idea_url' => fake()->boolean(50) ? fake()->url() : null,
                    'organization_id' => $org->id,
                    'status' => fake()->randomElement(['pending', 'analyzing', 'analyzed', 'architecting', 'architected', 'generating_schema', 'schema_generated', 'generating_api', 'generating_frontend', 'completed']),
                    'architecture_json' => [
                        'services' => ['Auth Service', 'Product Service', 'Payment Service'],
                        'architecture' => 'Modular Monolith',
                        'scaling_strategy' => 'Horizontal scaling with Redis',
                        'caching_layer' => 'Redis',
                        'queue_workers' => ['Order processing', 'Email notifications']
                    ],
                    'schema_json' => [
                        'tables' => [
                            [
                                'name' => 'users',
                                'columns' => [['name' => 'id', 'type' => 'bigIncrements'], ['name' => 'name', 'type' => 'string']]
                            ],
                            [
                                'name' => 'products',
                                'columns' => [['name' => 'id', 'type' => 'bigIncrements'], ['name' => 'name', 'type' => 'string'], ['name' => 'price', 'type' => 'decimal']]
                            ]
                        ]
                    ]
                ]);
            }
        }
    }
}
