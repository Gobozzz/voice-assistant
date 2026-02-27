<?php

declare(strict_types=1);

namespace Gobozzz\VoiceAssistant\Services\TranscriberService;

interface TranscriberServiceContract
{
    /**
     * @param  string  $audio  Путь до аудио файла.
     */
    public function getTextFromAudio(string $pathToAudio): string;
}
