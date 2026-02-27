<?php

declare(strict_types=1);

namespace Gobozzz\VoiceAssistant\Services\AiAssistantService;

use Gobozzz\VoiceAssistant\DTO\AiAssistant\AiMessage;
use Gobozzz\VoiceAssistant\Enums\AiMessageRole;
use Gobozzz\VoiceAssistant\Exceptions\AiAssistant\AiAssistantAuthenticationException;
use Gobozzz\VoiceAssistant\Exceptions\AiAssistant\AiAssistantInvalidDataResponseException;
use Gobozzz\VoiceAssistant\Exceptions\AiAssistant\AiAssistantInvalidResponseException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

final class GigaChatAssistantServiceContract implements AiAssistantServiceContract
{
    /**
     * @throws AiAssistantAuthenticationException
     * @throws AiAssistantInvalidDataResponseException
     * @throws ConnectionException
     * @throws AiAssistantInvalidResponseException
     */
    public function sendRequest(array $messages): AiMessage
    {
        $messages = array_map(fn (AiMessage $message) => $message->toArray(), $messages);

        $token = $this->fetchAccessToken();

        $response = Http::timeout(config('voice-assistant.ai-assistant.timeout_request_seconds'))
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ])
            ->withOptions([
                'verify' => $this->getCertificatePath(),
            ])->post(config('voice-assistant.ai-assistant.api_url'), [
                'model' => config('voice-assistant.ai-assistant.model'),
                'messages' => $messages,
            ]);

        if ($response->successful()) {
            return $this->parseResponseForRequest($response->json());
        }

        throw new AiAssistantInvalidResponseException($response->body());
    }

    private function getCertificatePath(): string
    {
        return storage_path(config('voice-assistant.ai-assistant.ssl_cert_path'));
    }

    /**
     * @throws AiAssistantInvalidDataResponseException
     */
    private function parseResponseForRequest(mixed $data): AiMessage
    {
        if (
            is_array($data) &&
            isset($data['choices'][0]['message']['content']) &&
            is_string($data['choices'][0]['message']['content'])
        ) {
            return new AiMessage(content: $data['choices'][0]['message']['content'], role: AiMessageRole::ASSISTANT);
        }

        throw new AiAssistantInvalidDataResponseException;
    }

    /**
     * @throws AiAssistantAuthenticationException
     * @throws AiAssistantInvalidDataResponseException
     * @throws ConnectionException
     */
    private function fetchAccessToken(): string
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Accept' => 'application/json',
            'RqUID' => Str::uuid()->toString(),
            'Authorization' => 'Basic '.config('voice-assistant.ai-assistant.api_key'),
        ])
            ->asForm()
            ->withOptions([
                'verify' => $this->getCertificatePath(),
            ])->post(config('voice-assistant.ai-assistant.auth_url'), [
                'scope' => config('voice-assistant.ai-assistant.scope'),
            ]);
        if ($response->successful()) {
            $data = $response->json();

            if (! isset($data['access_token'])) {
                throw new AiAssistantInvalidDataResponseException;
            }

            return $data['access_token'];
        }
        throw new AiAssistantAuthenticationException($response->body());
    }
}
