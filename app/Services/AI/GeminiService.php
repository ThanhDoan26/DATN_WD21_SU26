<?php   
namespace App\Services\AI;
use Illuminate\Support\Facades\Http;
use Exception;
class GeminiService
{
    protected string $apiKey;
    protected string $model;
    protected string $baseUrl;
    public function __construct(){
        $this->apiKey = config('ai.gemini.api_key');
        $this->model = config('ai.gemini.model');
        $this->baseUrl = config('ai.gemini.base_url');

        if(empty($this->apiKey)){
            throw new Exception('Gemini chưa được cấu hình');
        }
    }
    protected function makeRequest(array $payload): array
    {
        $url = "{$this->baseUrl}/models/{$this->model}:generateContent?key={$this->apiKey}";

        try {
            $response = Http::timeout(30)
                ->acceptJson()
                ->retry(3, 1000, function (\Exception $exception, \Illuminate\Http\Client\Request $request) {
                    if ($exception instanceof \Illuminate\Http\Client\RequestException && $exception->response->clientError()) {
                        return false;
                    }
                    return true;
                })
                ->post($url, $payload);

            if (!$response->successful()) {
                $status = $response->status();
                $message = match ($status) {
                    400 => 'Dữ liệu yêu cầu không hợp lệ.',
                    401 => 'Xác thực API thất bại.',
                    403 => 'Không có quyền truy cập dịch vụ AI.',
                    404 => 'Không tìm thấy mô hình AI.',
                    429 => 'Hệ thống AI đang quá tải, vui lòng thử lại sau.',
                    500 => 'Lỗi máy chủ nội bộ từ dịch vụ AI.',
                    default => 'Có lỗi xảy ra khi kết nối tới dịch vụ AI.',
                };
                throw new Exception($message);
            }

            return $response->json();
        } catch (Exception $e) {
            $safeMessage = $e->getMessage();
            if (str_contains($safeMessage, $this->apiKey)) {
                $safeMessage = 'Lỗi kết nối tới AI API.';
            }
            throw new Exception($safeMessage);
        }
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