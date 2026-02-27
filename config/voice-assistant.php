<?php

return [
    'web_route_enabled' => true,
    'transcriber' => [
        'realization' => \Gobozzz\VoiceAssistant\Services\TranscriberService\SaluteSpeechTranscriberService::class,
        'api_key' => env('VOICE_ASSISTANT_TRANSCRIBER_API_KEY'),
        'auth_url' => 'https://ngw.devices.sberbank.ru:9443/api/v2/oauth',
        'api_url' => 'https://smartspeech.sber.ru/rest/v1/speech:recognize',
        'scope' => 'SALUTE_SPEECH_PERS',
        'ssl_cert_path' => env('VOICE_ASSISTANT_TRANSCRIBER_CERT_PATH'),
        'timeout_request_seconds' => 60,
    ],
    'synthesizer' => [
        'realization' => \Gobozzz\VoiceAssistant\Services\SynthesizerService\SaluteSpeechSynthesizer::class,
        'api_key' => env('VOICE_ASSISTANT_SYNTHESIZER_API_KEY'),
        'auth_url' => 'https://ngw.devices.sberbank.ru:9443/api/v2/oauth',
        'api_url' => 'https://smartspeech.sber.ru/rest/v1/text:synthesize',
        'scope' => 'SALUTE_SPEECH_PERS',
        'ssl_cert_path' => env('VOICE_ASSISTANT_SYNTHESIZER_CERT_PATH'),
        'timeout_request_seconds' => 60,
        'mimetype_file_returned' => 'audio/x-wav',
        'extension_file_returned' => 'mp3',
        'dir_for_save_audio' => 'audios',
        'disk_for_save_audio' => 'public',
        'voice' => 'Bys_24000',
    ],
    'ai-assistant' => [
        'realization' => \Gobozzz\VoiceAssistant\Services\AiAssistantService\GigaChatAssistantServiceContract::class,
        'api_key' => env('VOICE_ASSISTANT_AI_ASSISTANT_API_KEY'),
        'auth_url' => 'https://ngw.devices.sberbank.ru:9443/api/v2/oauth',
        'api_url' => 'https://gigachat.devices.sberbank.ru/api/v1/chat/completions',
        'scope' => 'GIGACHAT_API_PERS',
        'timeout_request_seconds' => 60,
        'ssl_cert_path' => env('VOICE_ASSISTANT_AI_ASSISTANT_CERT_PATH'),
        'model' => 'GigaChat-2',
    ],
    'voice-assistants' => [
        'site' => [
            'realization' => \Gobozzz\VoiceAssistant\VoiceAssistants\VoiceAssistantBySite::class,
            'name' => 'Goboz',
            'url_site' => 'https://example.com',
            'info' => 'Наши услуги: строительство домов, внутренняя отделка помещений, ...',
            'about_company' => 'Наша компания на рынке уже 20 лет, мы специализируемся на...',
            'links_pages' => [
                ['title' => 'Главная', 'link' => '/'],
                ['title' => 'О компании', 'link' => '/about'],
            ],
            'contacts' => [
                ['title' => 'Телефон', 'link' => '+77777777777'],
                ['title' => 'WhatsApp', 'link' => 'https://wa.me/+77777777777'],
            ],
            'parting_words' => 'Отвечай так, чтобы завлечь клиентов на наш сайт, но не ври и не сочиняй.',
            'rules_answer' => 'Ответ должен быть не больше 700 символов!
Развернутый ответ ты должен отдать в MD формате, используй все возможности MarkDown.
Ответ должен быть сжатый, но в тоже время информативный, и грамотно написанный.
Если не нашел информация тут, скажи - Нет информации по данному вопросу',
        ],
    ],
];
