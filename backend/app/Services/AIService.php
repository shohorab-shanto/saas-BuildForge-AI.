<?php

namespace App\Services;

use App\Models\Project;
use App\Models\AIRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIService
{
    /**
     * Analyze a startup idea or website URL.
     */
    public function analyzeIdea(Project $project, string $input)
    {
        $prompt = "Analyze the following startup idea or URL and provide a structured JSON response.
        Input: {$input}
        
        Output format:
        {
            \"product_type\": \"e.g., E-commerce, SaaS, Portfolio\",
            \"modules\": [\"Authentication\", \"Product Management\", \"Order Processing\", \"User Dashboard\"],
            \"user_roles\": [\"Admin\", \"Customer\", \"Vendor\"],
            \"entities\": [
                { \"name\": \"Product\", \"fields\": [\"name\", \"description\", \"price\", \"stock_quantity\"] },
                { \"name\": \"Order\", \"fields\": [\"user_id\", \"total_price\", \"status\"] }
            ],
            \"workflows\": [\"User signs up\", \"Admin adds product\", \"Customer places order\"]
        }";

        return $this->generateResponse($project, 'analysis', $prompt);
    }

    /**
     * Generate system architecture based on analysis.
     */
    public function generateArchitecture(Project $project, array $analysis)
    {
        $analysisJson = json_encode($analysis);
        $prompt = "Based on this business analysis: {$analysisJson}, generate a production-ready system architecture.
        
        Output format:
        {
            \"services\": [\"Auth Service\", \"Product Service\", \"Order Service\"],
            \"architecture\": \"Modular Monolith / Microservices\",
            \"scaling_strategy\": \"Horizontal scaling with Redis caching\",
            \"caching_layer\": \"Redis for session and query caching\",
            \"queue_workers\": [\"Email notifications\", \"Order processing\", \"Image optimization\"],
            \"api_routes\": [\"POST /api/auth/register\", \"GET /api/products\", \"POST /api/orders\"]
        }";

        return $this->generateResponse($project, 'architecture', $prompt);
    }

    /**
     * Generate MySQL database schema based on architecture and entities.
     */
    public function generateSchema(Project $project, array $architecture, array $entities)
    {
        $context = json_encode(['architecture' => $architecture, 'entities' => $entities]);
        $prompt = "Based on this system context: {$context}, generate a detailed MySQL database schema.
        
        Output format:
        {
            \"tables\": [
                {
                    \"name\": \"table_name\",
                    \"columns\": [
                        { \"name\": \"id\", \"type\": \"bigIncrements\" },
                        { \"name\": \"name\", \"type\": \"string\", \"length\": 255 },
                        { \"name\": \"user_id\", \"type\": \"foreignId\", \"constrained\": \"users\" },
                        { \"name\": \"created_at\", \"type\": \"timestamps\" }
                    ],
                    \"relationships\": [
                        { \"type\": \"belongsTo\", \"model\": \"User\" },
                        { \"type\": \"hasMany\", \"model\": \"OrderItem\" }
                    ]
                }
            ]
        }";

        return $this->generateResponse($project, 'schema', $prompt);
    }

    /**
     * Generate Laravel API code (Controllers, Models, Services) for a table.
     */
    public function generateApiCode(Project $project, array $table)
    {
        $tableJson = json_encode($table);
        $prompt = "Generate full Laravel 11 API code for the following table structure: {$tableJson}.
        Return a JSON object containing the code for Model, Controller, Service, Repository, and Migration.
        Be concise but complete. Ensure all backslashes in PHP namespaces are properly escaped for JSON (e.g., App\\\\Models).
        
        Output format:
        {
            \"model\": \"<?php ... ?>\",
            \"controller\": \"<?php ... ?>\",
            \"service\": \"<?php ... ?>\",
            \"repository\": \"<?php ... ?>\",
            \"migration\": \"<?php ... ?>\"
        }";

        return $this->generateResponse($project, 'api_generation', $prompt);
    }

    /**
     * Generate Next.js frontend pages and components.
     */
    public function generateFrontendCode(Project $project, string $module, array $entities)
    {
        $context = json_encode(['module' => $module, 'entities' => $entities]);
        $prompt = "Generate Next.js 14 (App Router) frontend code using TailwindCSS and ShadCN UI for the module: {$module}.
        Context: {$context}.
        Return a JSON object containing the code for Page and multiple Components.
        
        Output format:
        {
            \"page\": \"'use client'; ...\",
            \"components\": [
                { \"name\": \"ComponentName.tsx\", \"content\": \"'use client'; ...\" }
            ]
        }";

        return $this->generateResponse($project, 'frontend_generation', $prompt);
    }

    /**
     * Internal method to call Gemini and handle AIRequest logging.
     */
    protected function generateResponse(Project $project, string $type, string $prompt)
    {
        if (!config('services.gemini.enabled')) {
            Log::info("Gemini AI generation skipped because it is disabled in config.");
            return null;
        }

        $aiRequest = AIRequest::create([
            'project_id' => $project->id,
            'type' => $type,
            'prompt' => $prompt,
            'status' => 'processing',
        ]);

        try {
            $content = $this->callGemini($prompt);

            $aiRequest->update([
                'response_json' => $content,
                'status' => 'completed',
            ]);

            return $content;
        } catch (\Exception $e) {
            Log::error("Gemini API Error for Project {$project->id} ({$type}): " . $e->getMessage());
            $aiRequest->update([
                'status' => 'failed',
            ]);
            throw $e;
        }
    }

    /**
     * Actual API call to Gemini.
     */
    protected function callGemini(string $prompt)
    {
        $apiKey = config('services.gemini.key');
        $model = config('services.gemini.model', 'gemini-2.5-flash');
        $timeout = (int) config('services.gemini.timeout', 120);
        $maxTokens = (int) config('services.gemini.max_tokens', 4096);
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

        // Attempt the request with retries for transient connection issues
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->timeout($timeout)
          ->retry(3, 2000) // Retry up to 3 times with 2s delay
          ->post($url, [
            'contents' => [
                [
                    'parts' => [
                        ['text' => "You are a Senior Software Architect and Product Designer. Your goal is to provide technical specifications and code in a STRICT JSON format. Do NOT include any markdown code blocks, preamble, or postamble. Return ONLY the raw JSON object.\n\n" . $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'response_mime_type' => 'application/json',
                'max_output_tokens' => $maxTokens,
                'temperature' => 0.2,
            ]
        ]);

        if ($response->failed()) {
            throw new \Exception("Gemini API call failed: " . $response->body());
        }

        $result = $response->json();
        $contentStr = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';
        
        // Check if the response seems truncated
        $finishReason = $result['candidates'][0]['finishReason'] ?? '';
        if ($finishReason === 'MAX_TOKENS') {
            Log::error("Gemini response was truncated due to token limit.");
        }

        // Find the first '{' and the last '}' to extract valid JSON object
        $firstBrace = strpos($contentStr, '{');
        $lastBrace = strrpos($contentStr, '}');
        
        if ($firstBrace !== false && $lastBrace !== false) {
            $contentStr = substr($contentStr, $firstBrace, $lastBrace - $firstBrace + 1);
        } else {
            Log::error("No JSON object found in Gemini response: " . substr($contentStr, 0, 500));
            throw new \Exception("Invalid JSON response from Gemini: No object found.");
        }
        
        $decoded = json_decode($contentStr, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error("Failed to decode Gemini response. Error: " . json_last_error_msg());
            Log::error("Raw content attempt: " . $contentStr);
            throw new \Exception("Invalid JSON response from Gemini: " . json_last_error_msg() . ". The response might be too large or contain unescaped characters.");
        }

        return $decoded;
    }
}
