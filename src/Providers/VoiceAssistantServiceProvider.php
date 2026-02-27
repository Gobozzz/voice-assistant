<?php

declare(strict_types=1);

namespace Gobozzz\VoiceAssistant\Providers;

use Gobozzz\VoiceAssistant\Services\AiAssistantService\AiAssistantServiceContract;
use Gobozzz\VoiceAssistant\Services\SynthesizerService\SynthesizerServiceContract;
use Gobozzz\VoiceAssistant\Services\TranscriberService\TranscriberServiceContract;
use Illuminate\Support\ServiceProvider;

final class VoiceAssistantServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(TranscriberServiceContract::class, config('voice-assistant.transcriber.realization'));
        $this->app->bind(AiAssistantServiceContract::class, config('voice-assistant.ai-assistant.realization'));
        $this->app->bind(SynthesizerServiceContract::class, config('voice-assistant.synthesizer.realization'));
    }

    public function boot(): void
    {
        if (config('voice-assistant.web_route_enabled')) {
            $this->loadRoutesFrom(__DIR__.'/../../routes/web.php');
        }

        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'voice-assistant');

        $this->publishes([
            __DIR__.'/../../config/voice-assistant.php' => config_path('voice-assistant.php'),
        ], 'config');

        $this->publishes([
            __DIR__.'/../../public' => public_path('voice-assistant'),
        ], 'public');

        $this->publishes([
            __DIR__.'/../../resources/views' => resource_path('views/vendor/voice-assistant'),
        ], 'views');

    }
}
