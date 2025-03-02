<?php

namespace App\Services;

use App\Interfaces\FileProcessor;
use App\Utilities\CsvUtility;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Facades\Storage;
use SplFileObject;
use App\Enums\CsvDelimiter;

class CsvProcessorService implements FileProcessor
{
    public const DELIMITER_CHECK_THRESHOLD = 2;

    public function process(string $filePath, ?string $delimiter = null): LazyCollection
    {
        return LazyCollection::make(function () use ($filePath, $delimiter) {
            $handle = fopen($filePath, 'r');
            $headers = [];
            $delimiter ??= $this->getDelimiter($filePath);

            while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                if (empty($headers)) {
                    $headers = $row;
                    continue;
                }

                yield array_combine($headers, $row);
            }

            fclose($handle);
        });
    }

    public function writeErrorRow(string $filePath, array $headers, array $row, string $errorMessage): void
    {
        $rowWithError = array_merge($row, ['error' => $errorMessage]);

        if (!Storage::exists($filePath)) {
            $headerLine = $this->arrayToCsvLine($headers);
            Storage::put($filePath, $headerLine . "\n");
        }

        $rowLine = $this->arrayToCsvLine($rowWithError);
        Storage::append($filePath, $rowLine . "\n");
    }

    private function arrayToCsvLine(array $row): string
    {
        $escapedValues = array_map(function ($value) {
            if ($value === null) {
                return '';
            }
            $value = str_replace('"', '""', $value);
            if (preg_match('/[,"\n]/', $value)) {
                return '"' . $value . '"';
            }
            return $value;
        }, array_values($row));

        return implode(',', $escapedValues);
    }

    private function getDelimiter(string $filePath)
    {
        $file = new SplFileObject($filePath);

        $delimiters = CsvDelimiter::getAllAsArray();

        $delimiterCount = [];

        $lineCount = 0;

        while ($file->valid() && $lineCount <= self::DELIMITER_CHECK_THRESHOLD) {
            $line = $file->fgets();

            foreach ($delimiters as $delimiter) {
                $fields = explode($delimiter, $line);

                $totalFields = count($fields);

                if ($totalFields > 1) {
                    if (!empty($delimiterCount[$delimiter])) {
                        $delimiterCount[$delimiter] += $totalFields;
                    } else {
                        $delimiterCount[$delimiter] = $totalFields;
                    }
                }
            }

            $lineCount++;
        }

        if (!empty($delimiterCount)) {
            arsort($delimiterCount);

            return key($delimiterCount) ?? ',';
        }

        return ',';
    }
}
