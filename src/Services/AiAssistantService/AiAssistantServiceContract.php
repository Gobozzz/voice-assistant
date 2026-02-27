<?php

declare(strict_types=1);

namespace Gobozzz\VoiceAssistant\Services\AiAssistantService;

use Gobozzz\VoiceAssistant\DTO\AiAssistant\AiMessage;

interface AiAssistantServiceContract
{
    /**
     * @param  AiMessage[]  $messages
     */
    public function sendRequest(array $messages): AiMessage;
}
