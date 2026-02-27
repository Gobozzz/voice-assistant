<?php

declare(strict_types=1);

namespace Gobozzz\VoiceAssistant\Services\SynthesizerService;

interface SynthesizerServiceContract
{
    /**
     * @return string Файл аудио в бинарном виде
     */
    public function synthesize(string $text): string;
}
