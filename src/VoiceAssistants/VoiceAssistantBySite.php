<?php

declare(strict_types=1);

namespace Gobozzz\VoiceAssistant\VoiceAssistants;

final class VoiceAssistantBySite extends BaseVoiceAssistant
{
    protected function getSystemPrompt(): string
    {
        $urlSite = config('voice-assistant.voice-assistants.site.url_site', '');
        $aboutCompany = config('voice-assistant.voice-assistants.site.about_company', '');
        $linksPages = '';
        foreach (config('voice-assistant.voice-assistants.site.links_pages', '') as $linksPage) {
            $linksPages .= '- '.$linksPage['title'].': '.$urlSite.$linksPage['link']."\n";
        }
        $contacts = '';
        foreach (config('voice-assistant.voice-assistants.site.contacts', '') as $contact) {
            $contacts .= '- '.$contact['title'].': '.$contact['link']."\n";
        }
        $nameAssistant = config('voice-assistant.voice-assistants.site.name');
        $rulesAnswer = config('voice-assistant.voice-assistants.site.rules_answer');
        $partingWords = config('voice-assistant.voice-assistants.site.parting_words');
        $info = config('voice-assistant.voice-assistants.site.info');

        return <<<PROMPT
Ты — ассистент сайта $urlSite.
Тебя зовут $nameAssistant.
$partingWords

Справочная информация для тебя:
$info

О компании:
$aboutCompany

Справочные ссылки по сайту:
$linksPages

Контакты:
$contacts

Правила ответа:
$rulesAnswer
PROMPT;
    }
}
