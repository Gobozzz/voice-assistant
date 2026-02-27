<?php

declare(strict_types=1);

namespace Gobozzz\VoiceAssistant\DTO\VoiceAssistant;

final readonly class VoiceAssistantResultDTO
{
    public function __construct(
        public string $question,
        public string $answer,
        public ?string $voice = null,
    ) {}
}
