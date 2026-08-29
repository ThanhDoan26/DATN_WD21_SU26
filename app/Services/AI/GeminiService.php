<?php   
namespace App\Services\AI;
use Illuminate\Support\Facades\Http;
use Exception;
class GeminiService
{
    protected string $apiKey;
    protected string $model;
    protected array $fallbackModels;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('ai.gemini.api_key') ?? '';
        $this->model = config('ai.gemini.model', 'gemini-3.5-flash');
        $this->fallbackModels = config('ai.gemini.fallback_models', [
            'gemini-3.5-flash',
            'gemini-3.5-flash-lite',
            'gemini-flash-lite-latest',
            'gemini-3.1-flash-lite',
            'gemini-3-flash-preview',
        ]);
        $this->baseUrl = config('ai.gemini.base_url', 'https://generativelanguage.googleapis.com/v1beta');

        if (empty($this->apiKey)) {
            throw new Exception('Gemini API Key chưa được cấu hình');
        }
    }

    protected function makeRequest(array $payload): array
    {
        // Build list of models to try in sequence: primary model first, followed by fallbacks
        $modelsToTry = array_values(array_unique(array_merge([$this->model], $this->fallbackModels)));
        $lastErrorMessage = null;

        foreach ($modelsToTry as $modelName) {
            $url = "{$this->baseUrl}/models/{$modelName}:generateContent?key={$this->apiKey}";

            try {
                $response = Http::timeout(25)
                    ->acceptJson()
                    ->post($url, $payload);

                if ($response->successful()) {
                    return $response->json();
                }

                $status = $response->status();
                $body = $response->json();
                $lastErrorMessage = $body['error']['message'] ?? "HTTP {$status}";
                \Illuminate\Support\Facades\Log::warning("Gemini model [{$modelName}] failed with status {$status}: {$lastErrorMessage}");

                // Critical auth errors where retrying other models won't help
                if ($status === 401 || $status === 403) {
                    throw new Exception('Xác thực API AI thất bại hoặc không có quyền truy cập.');
                }

                // If 503, 429, 404, 500, etc., continue to next fallback model
                continue;
            } catch (Exception $e) {
                if ($e->getMessage() === 'Xác thực API AI thất bại hoặc không có quyền truy cập.') {
                    throw $e;
                }
                $lastErrorMessage = $e->getMessage();
                \Illuminate\Support\Facades\Log::warning("Gemini request error for model [{$modelName}]: " . $lastErrorMessage);
                continue;
            }
        }

        \Illuminate\Support\Facades\Log::error("All Gemini fallback models failed. Last error: " . ($lastErrorMessage ?? 'unknown'));
        throw new Exception('Hệ thống AI đang quá tải hoặc tạm thời gián đoạn. Vui lòng thử lại sau giây lát.');
    }

    public function generate(string $prompt, ?string $systemInstruction = null, array $history = []): string
    {
        $payload = [
            'contents' => $history
        ];
        
        $payload['contents'][] = [
            'role' => 'user',
            'parts' => [
                ['text' => $prompt]
            ]
        ];

        if ($systemInstruction) {
            $payload['system_instruction'] = [
                'parts' => [
                    ['text' => $systemInstruction]
                ]
            ];
        }

        $data = $this->makeRequest($payload);

        return $data['candidates'][0]['content']['parts'][0]['text']
            ?? 'Xin lỗi, tôi chưa thể trả lời câu hỏi này.';
    }

    public function generateJson(string $prompt, ?string $systemInstruction = null): array
    {
        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'responseMimeType' => 'application/json'
            ]
        ];

        if ($systemInstruction) {
            $payload['system_instruction'] = [
                'parts' => [
                    ['text' => $systemInstruction]
                ]
            ];
        }

        $data = $this->makeRequest($payload);

        $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '{}';
        return json_decode($text, true) ?? [];
    }
}