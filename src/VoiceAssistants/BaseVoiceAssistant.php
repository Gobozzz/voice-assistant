<?php

declare(strict_types=1);

namespace Gobozzz\VoiceAssistant\VoiceAssistants;

use Gobozzz\VoiceAssistant\DTO\AiAssistant\AiMessage;
use Gobozzz\VoiceAssistant\DTO\VoiceAssistant\VoiceAssistantResultDTO;
use Gobozzz\VoiceAssistant\Enums\AiMessageRole;
use Gobozzz\VoiceAssistant\Exceptions\Synthesizer\ErrorSaveSynthesizeAudioException;
use Gobozzz\VoiceAssistant\Helpers\MarkDownToSpeechTextHelper;
use Gobozzz\VoiceAssistant\Services\AiAssistantService\AiAssistantServiceContract;
use Gobozzz\VoiceAssistant\Services\SynthesizerService\SynthesizerServiceContract;
use Gobozzz\VoiceAssistant\Services\TranscriberService\TranscriberServiceContract;
use Illuminate\Support\Facades\Storage;

abstract class BaseVoiceAssistant
{
    public function __construct(
        private readonly TranscriberServiceContract $transcriber,
        private readonly AiAssistantServiceContract $aiAssistant,
        private readonly SynthesizerServiceContract $synthesizer,
    ) {}

    public function getAnswer(?string $pathToAudioQuestion = null, ?string $textQuestion = null, array $previousMessages = []): VoiceAssistantResultDTO
    {
        if ($pathToAudioQuestion === null && $textQuestion === null) {
            throw new \InvalidArgumentException('Question is required');
        }
        if ($pathToAudioQuestion !== null) {
            $transcribeText = $this->transcriber->getTextFromAudio($pathToAudioQuestion);
        } else {
            $transcribeText = $textQuestion;
        }

        $preparedPreviouslyMessages = $this->preparePreviouslyMessages($previousMessages);

        $context = [
            new AiMessage(content: $this->getSystemPrompt(), role: AiMessageRole::SYSTEM),
            ...$preparedPreviouslyMessages,
            new AiMessage(content: $transcribeText, role: AiMessageRole::USER),
        ];

        $aiAnswer = $this->aiAssistant->sendRequest($context);

        $aiAnswerTextForSpeech = MarkDownToSpeechTextHelper::convert($aiAnswer->content);

        try {
            $audioBinary = $this->synthesizer->synthesize($aiAnswerTextForSpeech);
            $urlAudio = $this->saveSynthesizeAudio($audioBinary);
        } catch (\Exception $e) {
            $urlAudio = null;
        }

        return new VoiceAssistantResultDTO(question: $transcribeText, answer: $aiAnswer->content, voice: $urlAudio);
    }

    /**
     * @return string Абсолютный URL аудио файла
     *
     * @throws ErrorSaveSynthesizeAudioException
     */
    private function saveSynthesizeAudio(string $audioBinary): string
    {
        $fileName = config('voice-assistant.synthesizer.dir_for_save_audio').'/'.uniqid().'.'.config('voice-assistant.synthesizer.extension_file_returned');
        $isSaveFile = Storage::disk(config('voice-assistant.synthesizer.disk_for_save_audio'))->put($fileName, $audioBinary);

        if (! $isSaveFile) {
            throw new ErrorSaveSynthesizeAudioException;
        }

        return Storage::disk(config('voice-assistant.synthesizer.disk_for_save_audio'))->url($fileName);
    }

    /**
     * @return AiMessage[]
     */
    protected function preparePreviouslyMessages(array $previousMessages): array
    {
        $result = [];
        foreach ($previousMessages as $message) {
            if ($this->isValidPreviousMessage($message)) {
                $result[] = new AiMessage(content: $message['content'], role: AiMessageRole::tryFrom($message['role']));
            }
        }

        $offset = config('voice-assistant.count_previous_messages_for_context') * (-1);

        return array_slice($result, $offset);
    }

    protected function isValidPreviousMessage(mixed $message): bool
    {
        if (! is_array($message)) {
            return false;
        }

        if (! isset($message['role']) || AiMessageRole::tryFrom($message['role']) === null || $message['role'] === AiMessageRole::SYSTEM) {
            return false;
        }

        if (! isset($message['content']) || ! is_string($message['content'])) {
            return false;
        }

        return true;
    }

    abstract protected function getSystemPrompt(): string;
}
