<?php

declare(strict_types=1);

namespace Gobozzz\VoiceAssistant\Helpers;

use FFMpeg\FFMpeg;
use FFMpeg\Format\Audio\Mp3;
use Gobozzz\VoiceAssistant\Exceptions\Audio\InvalidConversionAudioException;
use Gobozzz\VoiceAssistant\Exceptions\Audio\InvalidDetectedDurationAudioException;
use Gobozzz\VoiceAssistant\Exceptions\Audio\InvalidDetectedMimeTypeAudioException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

final class AudioHelper
{
    /**
     * @throws InvalidDetectedMimeTypeAudioException
     */
    public static function getMimeType(string $pathToAudio): string
    {
        try {
            $fileContent = file_get_contents($pathToAudio);

            $tempFile = tempnam(sys_get_temp_dir(), 'file_');
            file_put_contents($tempFile, $fileContent);

            $mimeType = File::mimeType($tempFile);

            unlink($tempFile);

            return $mimeType;
        } catch (\Throwable $exception) {
            throw new InvalidDetectedMimeTypeAudioException($exception->getMessage());
        }
    }

    /**
     * @return float Длительность аудио в секундах
     *
     * @throws InvalidDetectedDurationAudioException
     */
    public static function getDuration(string $pathToAudio): float
    {
        $result = Process::run([
            'ffprobe', '-v', 'error',
            '-show_entries', 'format=duration',
            '-of', 'default=noprint_wrappers=1:nokey=1',
            $pathToAudio,
        ]);

        if ($result->failed()) {
            throw new InvalidDetectedDurationAudioException;
        }

        return (float) trim($result->output());
    }

    /**
     * @return float Вес файла в килобайтах
     */
    public static function getSize(string $pathToAudio): float
    {
        return round(filesize($pathToAudio) / 1024, 2);
    }

    /**
     * @return string Путь до файла MP3
     *
     * @throws InvalidConversionAudioException
     */
    public static function convertToMp3(string $inputPath): string
    {
        $outputPath = null;
        try {
            $ffmpeg = FFMpeg::create();

            $pathInfo = pathinfo($inputPath);
            $outputPath = $pathInfo['dirname'].'/'.$pathInfo['filename'].'_converted.mp3';

            $audio = $ffmpeg->open($inputPath);

            $format = new Mp3;
            $format->setAudioKiloBitrate(128);
            $format->setAudioChannels(2);

            $audio->save($format, $outputPath);

            return $outputPath;
        } catch (\Exception $e) {
            if ($outputPath !== null) {
                unlink($outputPath);
            }
            throw new InvalidConversionAudioException('Cannot convert to MP3: '.$e->getMessage());
        }
    }
}
