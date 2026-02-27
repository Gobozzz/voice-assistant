<?php

declare(strict_types=1);

namespace Gobozzz\VoiceAssistant\Enums;

enum AiMessageRole: string
{
    case SYSTEM = 'system';
    case USER = 'user';
    case ASSISTANT = 'assistant';
}
