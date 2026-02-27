<?php

declare(strict_types=1);

namespace Gobozzz\VoiceAssistant\Services\TranscriberService;

use Gobozzz\VoiceAssistant\Exceptions\Audio\InvalidConversionAudioException;
use Gobozzz\VoiceAssistant\Exceptions\Audio\InvalidDetectedDurationAudioException;
use Gobozzz\VoiceAssistant\Exceptions\Audio\InvalidDetectedMimeTypeAudioException;
use Gobozzz\VoiceAssistant\Exceptions\Audio\InvalidDurationAudioException;
use Gobozzz\VoiceAssistant\Exceptions\Audio\InvalidMimeTypeAudioException;
use Gobozzz\VoiceAssistant\Exceptions\Audio\InvalidSizeAudioException;
use Gobozzz\VoiceAssistant\Exceptions\Transcriber\ErrorGetAccessTokenException;
use Gobozzz\VoiceAssistant\Exceptions\Transcriber\InvalidResponseException;
use Gobozzz\VoiceAssistant\Helpers\AudioHelper;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

final class SaluteSpeechTranscriberService implements TranscriberServiceContract
{
    private const VALID_MIME_TYPES = ['audio/mpeg', 'video/mpeg', 'audio/webm', 'video/webm', 'audio/x-wav', 'audio/wav', 'audio/mp3', 'audio/ogg'];

    private const MAX_SIZE_FILE = 2048;

    private const MAX_DURATION_FILE = 10;

    private const MIN_DURATION_FILE = 1;

    /**
     * @throws InvalidConversionAudioException
     * @throws InvalidSizeAudioException
     * @throws InvalidDetectedDurationAudioException
     * @throws InvalidMimeTypeAudioException
     * @throws InvalidDetectedMimeTypeAudioException
     * @throws InvalidResponseException
     * @throws InvalidDurationAudioException
     * @throws ConnectionException
     * @throws ErrorGetAccessTokenException
     */
    public function getTextFromAudio(string $pathToAudio): string
    {
        $this->ensureValidMimeType($pathToAudio);

        $this->ensureValidSize($pathToAudio);

        $this->ensureValidDuration($pathToAudio);

        $mp3AudioPath = AudioHelper::convertToMp3($pathToAudio);

        $mp3AudioBinary = file_get_contents($mp3AudioPath);

        if ($mp3AudioBinary === false) {
            throw new InvalidConversionAudioException;
        }

        unlink($mp3AudioPath);

        return $this->transcribeText($mp3AudioBinary);
    }

    /**
     * @throws InvalidResponseException
     * @throws ConnectionException
     * @throws ErrorGetAccessTokenException
     */
    private function transcribeText(string $audioMP3Binary): string
    {
        $token = $this->fetchAccessToken();

        $response = Http::timeout(config('voice-assistant.transcriber.timeout_request_seconds'))
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ])
            ->withOptions([
                'verify' => $this->getCertificatePath(),
            ])
            ->withBody($audioMP3Binary, 'audio/mpeg')
            ->post(config('voice-assistant.transcriber.api_url'));

        if ($response->successful()) {
            $data = $response->json();

            return $this->parseResponseSpeechToText($data);
        }

        throw new InvalidResponseException($response->body());
    }

    /**
     * @throws InvalidResponseException
     */
    private function parseResponseSpeechToText(mixed $data): string
    {
        if (isset($data['result']) && is_array($data['result'])) {
            return implode('.', $data['result']);
        }

        throw new InvalidResponseException;
    }

    /**
     * @throws InvalidResponseException
     * @throws ErrorGetAccessTokenException
     * @throws ConnectionException
     */
    private function fetchAccessToken(): string
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Accept' => 'application/json',
            'RqUID' => Str::uuid()->toString(),
            'Authorization' => 'Basic '.config('voice-assistant.transcriber.api_key'),
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

    /**
     * @throws InvalidDetectedMimeTypeAudioException
     * @throws InvalidMimeTypeAudioException
     */
    private function ensureValidMimeType(string $pathToAudio): void
    {
        $mimeType = AudioHelper::getMimeType($pathToAudio);

        if (! in_array($mimeType, self::VALID_MIME_TYPES)) {
            throw new InvalidMimeTypeAudioException("Invalid mime type '{$mimeType}'");
        }
    }

    /**
     * @throws InvalidSizeAudioException
     */
    private function ensureValidSize(string $pathToAudio): void
    {
        $size = AudioHelper::getSize($pathToAudio);
        if ($size > self::MAX_SIZE_FILE) {
            throw new InvalidSizeAudioException("Invalid size of {$size} Kb");
        }
    }

    /**
     * @throws InvalidDurationAudioException
     * @throws InvalidDetectedDurationAudioException
     */
    private function ensureValidDuration(string $pathToAudio): void
    {
        $duration = AudioHelper::getDuration($pathToAudio);
        if ($duration > self::MAX_DURATION_FILE || $duration < self::MIN_DURATION_FILE) {
            throw new InvalidDurationAudioException("Invalid duration of {$duration} seconds");
        }
    }

    private function getCertificatePath(): string
    {
        return storage_path(config('voice-assistant.transcriber.ssl_cert_path'));
    }
}
