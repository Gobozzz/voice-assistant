<?php

declare(strict_types=1);

namespace Gobozzz\VoiceAssistant\DTO\AiAssistant;

use Gobozzz\VoiceAssistant\Enums\AiMessageRole;

final readonly class AiMessage
{
    public function __construct(
        public string $content,
        public AiMessageRole $role,
    ) {}

    public function toArray(): array
    {
        return [
            'content' => $this->content,
            'role' => $this->role->value,
        ];
    }
}
