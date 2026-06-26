<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\Employee;
use App\Models\Task;
use App\Models\Project;
use App\Models\Team;

class AIChatService
{
    public function getResponse($message, $context, $history)
    {
        $systemPrompt = "You are AI Manager Assistant, a helpful, intelligent copilot for a management platform. You can analyze data, summarize reports, and answer questions about employees, tasks, projects, meetings, teams, and gitlab commits.\n";
        $systemPrompt .= "Current Page Context: $context\n";

        // Add some basic stats to the prompt as context
        try {
            $empCount = Employee::count();
            $taskCount = Task::count();
            $projectCount = Project::count();
            $teamCount = Team::count();
            $systemPrompt .= "System Stats: $empCount Employees, $taskCount Tasks, $projectCount Projects, $teamCount Teams.\n";
        } catch (\Exception $e) {
            // Ignore DB errors if tables missing
        }
        
        $systemPrompt .= "Always answer concisely and professionally. Format text with Markdown. Emphasize key data.\n";

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt]
        ];

        // Keep last 6 messages
        $recentHistory = array_slice($history, -6);
        foreach ($recentHistory as $msg) {
            $messages[] = $msg;
        }

        $messages[] = ['role' => 'user', 'content' => $message];

        $apiKey = env('NVIDIA_API_KEY');
        
        if (!$apiKey) {
            return "I am the AI Manager Assistant. I understand you are asking about '$message' from the context of $context. Please configure the NVIDIA API KEY to get full analytical responses.";
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json'
            ])->post('https://integrate.api.nvidia.com/v1/chat/completions', [
                'model' => 'meta/llama-3.3-70b-instruct',
                'messages' => $messages,
                'max_tokens' => 1024,
                'temperature' => 0.7,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['choices'][0]['message']['content'] ?? "I'm sorry, I couldn't generate a response at this time.";
            }

            return "API Error: " . $response->status() . " - " . $response->body();
        } catch (\Exception $e) {
            return "Connection error: " . $e->getMessage();
        }
    }
}
