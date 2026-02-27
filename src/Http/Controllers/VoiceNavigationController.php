<?php

declare(strict_types=1);

namespace Gobozzz\VoiceAssistant\Http\Controllers;

use App\Http\Controllers\Controller;
use Gobozzz\VoiceAssistant\Enums\AiMessageRole;
use Gobozzz\VoiceAssistant\VoiceAssistants\BaseVoiceAssistant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

final class VoiceNavigationController extends Controller
{
    private readonly BaseVoiceAssistant $assistantBySite;

    public function __construct()
    {
        $this->assistantBySite = app(config('voice-assistant.voice-assistants.site.realization'));
    }

    public function __invoke(Request $request)
    {
        $data = $request->validate([
            'audio' => ['required_without:text_question', 'file'],
            'text_question' => ['required_without:audio', 'string', 'max:500'],
            'messages' => ['nullable', 'array'],
            'messages.*' => ['array'],
            'messages.*.content' => ['nullable', 'string'],
            'messages.*.role' => ['required', new Enum(AiMessageRole::class)],
        ]);

        $audioFile = $request->file('audio');
        $messages = $data['messages'] ?? [];

        try {
            if ($audioFile !== null) {
                $assistantData = $this->assistantBySite->getAnswer(pathToAudioQuestion: $audioFile->getRealPath(), previousMessages: $messages);
            } else {
                $assistantData = $this->assistantBySite->getAnswer(textQuestion: $data['text_question'], previousMessages: $messages);
            }
            if (empty($assistantData->answer)) {
                throw new \Exception('Empty answer');
            }
        } catch (\Exception $exception) {
            return response()->json(['error' => $exception->getMessage()], 400);
        }

        return response()->json([
            'question' => $assistantData->question,
            'answer' => $assistantData->answer,
            'voice' => $assistantData->voice,
        ]);
    }
}
