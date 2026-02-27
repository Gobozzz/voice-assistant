<?php

declare(strict_types=1);

namespace Gobozzz\VoiceAssistant\Services\SynthesizerService;

use Gobozzz\VoiceAssistant\Exceptions\Synthesizer\ErrorGetAccessTokenException;
use Gobozzz\VoiceAssistant\Exceptions\Synthesizer\InvalidResponseException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

final class SaluteSpeechSynthesizer implements SynthesizerServiceContract
{
    private const MAX_LENGTH_TEXT = 4000;

    /**
     * @throws InvalidResponseException
     * @throws ConnectionException
     * @throws ErrorGetAccessTokenException
     */
    public function synthesize(string $text): string
    {
        $this->ensureTextValid($text);

        $token = $this->fetchAccessToken();

        $response = Http::timeout(config('voice-assistant.synthesizer.timeout_request_seconds'))
            ->withHeaders([
                'Content-Type' => 'application/text',
                'Accept' => config('voice-assistant.synthesizer.mimetype_file_returned'),
                'Authorization' => "Bearer {$token}",
            ])
            ->withOptions([
                'verify' => $this->getCertificatePath(),
            ])
            ->withBody($text, 'application/text')
            ->post(config('voice-assistant.synthesizer.api_url').'?voice='.config('voice-assistant.synthesizer.voice'));

        if ($response->successful()) {
            return $response->body();
        }

        throw new InvalidResponseException($response->body());
    }

    private function ensureTextValid(string $text): void
    {
        if (mb_strlen($text) > self::MAX_LENGTH_TEXT) {
            throw new \InvalidArgumentException('Text for synthesize is too long.');
        }
    }

    /**
     * @throws InvalidResponseException
     * @throws ConnectionException
     * @throws ErrorGetAccessTokenException
     */
    private function fetchAccessToken(): string
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Accept' => 'application/json',
            'RqUID' => Str::uuid()->toString(),
            'Authorization' => 'Basic '.config('voice-assistant.synthesizer.api_key'),
        ])
            ->asForm()
            ->withOptions([
                'verify' => $this->getCertificatePath(),
            ])
            ->post(config('voice-assistant.transcriber.auth_url'), [
                'scope' => config('voice-assistant.transcriber.scope'),
            ]);
        if ($response->successful()) {
            $data = $response->json();

            if (! isset($data['access_token']) || ! is_string($data['access_token'])) {
                throw new InvalidResponseException;
            }

            return $data['access_token'];
        }

        throw new ErrorGetAccessTokenException($response->body());
    }

    private function getCertificatePath(): string
    {
        return storage_path(config('voice-assistant.synthesizer.ssl_cert_path'));
    }
}
