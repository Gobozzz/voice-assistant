<?php

declare(strict_types=1);

namespace Gobozzz\VoiceAssistant\Helpers;

final class MarkDownToSpeechTextHelper
{
    /**
     * Преобразует Markdown в чистый текст для синтезатора речи
     * Удаляет эмодзи, спецсимволы и лишние переносы строк
     */
    public static function convert(string $markdown): string
    {
        $text = $markdown;

        // 1. Удаляем HTML-теги (если есть)
        $text = strip_tags($text);

        // 2. Удаляем изображения ![alt](url)
        $text = preg_replace('/!\[.*?\]\(.*?\)/', '', $text);

        // 3. Удаляем ссылки [text](url) - оставляем только текст ссылки
        $text = preg_replace('/\[([^\]]+)\]\([^\)]+\)/', '$1', $text);

        // 4. Удаляем Markdown-ссылки в формате <http://...> или <https://...>
        $text = preg_replace('/<https?:\/\/[^>]+>/', '', $text);

        // 5. Удаляем сырые URL (http, https, ftp и т.д.)
        $text = preg_replace('/https?:\/\/[^\s]+/', '', $text);
        $text = preg_replace('/ftp:\/\/[^\s]+/', '', $text);
        $text = preg_replace('/www\.[^\s]+/', '', $text);

        // 6. Удаляем таблицы (в Markdown таблицы содержат | и ---)
        // Сначала удаляем строки с разделителями (|---|)
        $text = preg_replace('/^\|.*\|$/m', '', $text);
        // Удаляем строки, которые выглядят как заголовки таблиц (| --- | --- |)
        $text = preg_replace('/^\|[\s\-:]+\|$/m', '', $text);

        // 7. Удаляем Markdown-форматирование

        // Жирный текст **text** или __text__ -> text
        $text = preg_replace('/\*\*(.*?)\*\*/', '$1', $text);
        $text = preg_replace('/__(.*?)__/', '$1', $text);

        // Курсив *text* или _text_ -> text
        $text = preg_replace('/\*(.*?)\*/', '$1', $text);
        $text = preg_replace('/_(.*?)_/', '$1', $text);

        // Зачеркнутый ~~text~~ -> text
        $text = preg_replace('/~~(.*?)~~/', '$1', $text);

        // Код `code` -> code
        $text = preg_replace('/`(.*?)`/', '$1', $text);

        // 8. Удаляем блоки кода (```code```)
        $text = preg_replace('/```.*?```/s', '', $text);

        // 9. Удаляем заголовки Markdown (#, ##, ### и т.д.), но оставляем текст
        $text = preg_replace('/^#{1,6}\s+(.*?)$/m', '$1', $text);

        // 10. Удаляем горизонтальные линии (---, ***, ___)
        $text = preg_replace('/^-{3,}$/m', '', $text);
        $text = preg_replace('/^\*{3,}$/m', '', $text);
        $text = preg_replace('/^_{3,}$/m', '', $text);

        // 11. Удаляем цитаты (> text), оставляем текст
        $text = preg_replace('/^>\s+(.*?)$/m', '$1', $text);

        // 12. Удаляем маркированные списки (* text, - text, + text), оставляем текст
        $text = preg_replace('/^[\*\-\+]\s+(.*?)$/m', '$1', $text);

        // 13. Удаляем нумерованные списки (1. text), оставляем текст
        $text = preg_replace('/^\d+\.\s+(.*?)$/m', '$1', $text);

        // 14. Очищаем лишние пробелы и переносы строк
        $text = preg_replace('/\n{3,}/', "\n\n", $text); // Убираем множественные переносы
        $text = preg_replace('/[ \t]+/', ' ', $text); // Убираем лишние пробелы

        // 15. Удаляем пустые строки в начале и конце
        return trim($text);
    }
}
