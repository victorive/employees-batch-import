<?php

namespace App\Services;

use App\Interfaces\FileProcessor;
use App\Services\CsvProcessorService;
use App\Exceptions\UnsupportedFileException;

class FileProcessorFactory
{
    public function getProcessor(string $extension): FileProcessor
    {
        return match ($extension) {
            'csv' => new CsvProcessorService,
            default => throw new UnsupportedFileException("Unsupported file type: $extension"),
        };
    }
}
