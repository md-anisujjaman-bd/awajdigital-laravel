<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Modules\Voices\DataTransferObjects;

use Illuminate\Http\UploadedFile;
use MdAnisujjamanBd\AwajdigitalLaravel\Exceptions\ClientValidationException;

final readonly class UploadVoiceData
{
    private const int MAX_FILE_SIZE_BYTES = 10 * 1024 * 1024; // 10MB

    private const array ALLOWED_EXTENSIONS = ['mp3', 'wav', 'ogg', 'm4a', 'aac', 'webm', 'flac'];

    /**
     * @param  resource|string  $contents
     */
    public function __construct(
        public string $name,
        public mixed $contents,
        public string $filename,
    ) {
        $trimmedName = trim($name);
        $nameLen = mb_strlen($trimmedName);
        if ($nameLen < 1 || $nameLen > 255) {
            throw new ClientValidationException("Voice name must be between 1 and 255 characters, {$nameLen} provided.");
        }
    }

    public static function fromPath(string $filePath, ?string $name = null): self
    {
        if (! file_exists($filePath) || ! is_readable($filePath)) {
            throw new ClientValidationException("Audio file at [{$filePath}] does not exist or is not readable.");
        }

        $size = filesize($filePath);
        if ($size === false || $size > self::MAX_FILE_SIZE_BYTES) {
            throw new ClientValidationException('Audio file exceeds the maximum allowed size of 10MB.');
        }

        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        if (! in_array($ext, self::ALLOWED_EXTENSIONS, true)) {
            throw new ClientValidationException("Unsupported audio format [{$ext}]. Allowed formats: ".implode(', ', self::ALLOWED_EXTENSIONS).'.');
        }

        $filename = basename($filePath);
        $voiceName = $name !== null && trim($name) !== '' ? trim($name) : pathinfo($filePath, PATHINFO_FILENAME);

        $resource = fopen($filePath, 'r');
        if ($resource === false) {
            throw new ClientValidationException("Could not open file resource for [{$filePath}].");
        }

        return new self(
            name: $voiceName,
            contents: $resource,
            filename: $filename,
        );
    }

    public static function fromUploadedFile(UploadedFile $file, ?string $name = null): self
    {
        $size = $file->getSize();
        if ($size > self::MAX_FILE_SIZE_BYTES) {
            throw new ClientValidationException('Audio file exceeds the maximum allowed size of 10MB.');
        }

        $ext = strtolower($file->getClientOriginalExtension());
        if (! in_array($ext, self::ALLOWED_EXTENSIONS, true)) {
            throw new ClientValidationException("Unsupported audio format [{$ext}]. Allowed formats: ".implode(', ', self::ALLOWED_EXTENSIONS).'.');
        }

        $filename = $file->getClientOriginalName();
        $voiceName = $name !== null && trim($name) !== '' ? trim($name) : pathinfo($filename, PATHINFO_FILENAME);

        $resource = fopen($file->getRealPath(), 'r');
        if ($resource === false) {
            throw new ClientValidationException('Could not read uploaded file content.');
        }

        return new self(
            name: $voiceName,
            contents: $resource,
            filename: $filename,
        );
    }
}
