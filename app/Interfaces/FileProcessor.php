<?php

namespace App\Interfaces;

use Illuminate\Support\LazyCollection;

interface FileProcessor
{
    public function process(string $filePath, ?string $delimiter = null): LazyCollection;

    public function writeErrorRow(string $filePath, array $headers, array $row, string $errorMessage): void;
}
