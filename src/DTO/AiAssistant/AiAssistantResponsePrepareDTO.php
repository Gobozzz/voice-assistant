<?php

declare(strict_types=1);

namespace Gobozzz\VoiceAssistant\DTO\AiAssistant;

final readonly class AiAssistantResponsePrepareDTO
{
    public function __construct(
        public string $content,
        public ?string $url = null,
    ) {}
}
