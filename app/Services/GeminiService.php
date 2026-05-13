<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected string $apiKey;
    protected string $modelName;
    protected string $systemPrompt;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
        $this->modelName = 'gemini-2.0-flash-exp';
        $this->systemPrompt = "You are a helpful productivity assistant embedded in TaskFlow, a task management app. " .
            "You have access to the user's tasks and can help them manage their workload. " .
            "Help users manage their tasks better, prioritize work, overcome procrastination, and stay productive. " .
            "Keep answers concise and practical (2–4 sentences unless a longer answer is clearly needed). " .
            "You may ask clarifying questions. Be warm but efficient.";
    }

    public function chatWithContext(string $message, array $history, array $userTasks, ?int $userId): string
    {
        try {
            $prompt = $this->systemPrompt . "\n\n";

            if (!empty($userTasks)) {
                $prompt .= "User's current tasks:\n";
                $activeTasks = array_filter($userTasks, function ($task) {
                    return $task['status'] !== 'DONE';
                });
                $activeTasks = array_slice($activeTasks, 0, 5);
                
                foreach ($activeTasks as $task) {
                    $prompt .= sprintf("- %s (%s priority, %s)\n",
                        $task['title'],
                        $task['priority'],
                        $task['status']
                    );
                }
                $prompt .= "\n";
            }

            if (!empty($history)) {
                foreach ($history as $msg) {
                    $role = $msg['role'] ?? '';
                    $content = $msg['content'] ?? '';
                    if ($role === 'user') {
                        $prompt .= "User: " . $content . "\n";
                    } elseif ($role === 'assistant') {
                        $prompt .= "Assistant: " . $content . "\n";
                    }
                }
            }

            $prompt .= "User: " . $message . "\n";
            $prompt .= "Assistant:";

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("https://generativelanguage.googleapis.com/v1beta/models/{$this->modelName}:generateContent?key={$this->apiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'maxOutputTokens' => 500,
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $responseText = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
                return $responseText ?: "I'm not sure how to respond to that.";
            }

            Log::error('Gemini API error: ' . $response->body());
            return "I'm having trouble connecting right now. Please check if the Gemini API is properly configured.";

        } catch (\Exception $e) {
            Log::error('Gemini API exception: ' . $e->getMessage());
            return "I'm having trouble connecting right now. Error: " . $e->getMessage();
        }
    }

    public function generateProductivityTip(?array $context): string
    {
        try {
            $prompt = "You are a concise productivity coach. " .
                "Give ONE short, specific, actionable productivity tip (max 2 sentences). " .
                "No preamble, no bullet points. Just the tip itself.\n\n";

            if ($context && isset($context['stats'])) {
                $stats = $context['stats'];
                $prompt .= sprintf(
                    "User stats: %d total tasks (%d todo, %d in progress, %d done, %.0f%% complete). ",
                    $stats['total'] ?? 0,
                    $stats['todo'] ?? 0,
                    $stats['inProgress'] ?? 0,
                    $stats['done'] ?? 0,
                    $stats['completionRate'] ?? 0
                );
            }

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("https://generativelanguage.googleapis.com/v1beta/models/{$this->modelName}:generateContent?key={$this->apiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.8,
                    'maxOutputTokens' => 100,
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $tip = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
                return $tip ?: "Try breaking down your largest task into smaller steps!";
            }

            return "Try breaking down your largest task into smaller steps!";

        } catch (\Exception $e) {
            Log::error('Gemini tip generation error: ' . $e->getMessage());
            return "Try breaking down your largest task into smaller steps!";
        }
    }
}