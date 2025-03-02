<?php

namespace App\Jobs;

use App\Models\Employee;
use App\Dto\EmployeeDto;
use InvalidArgumentException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\FileProcessorFactory;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Throwable;
use App\Interfaces\FileProcessor;
use App\Utilities\CsvUtility;

class ImportEmployeesJob implements ShouldQueue
{
    use Queueable;

    public const FAILED_IMPORTS_DIRECTORY = 'employees/failed_imports';
    public const FAILED_FILE_PREFIX = 'failed_employees_';
    public const CHUNK_SIZE = 1000;

    private string $failedFilePath;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $filePath,
        public string $format,
        public ?string $delimiter
    ) {
        $this->failedFilePath = self::FAILED_IMPORTS_DIRECTORY . '/' .
            self::FAILED_FILE_PREFIX . date('Ymd_His') . '.' . $this->format;
    }

    /**
     * Execute the job.
     */
    public function handle(FileProcessorFactory $fileProcessorFactory): void
    {
        try {
            $processor = $fileProcessorFactory->getProcessor($this->format);
            $filePath = Storage::path($this->filePath);

            $processor->process($filePath, $this->delimiter)
                ->chunk(self::CHUNK_SIZE)
                ->each(function (LazyCollection $chunk) use ($processor) {
                    $this->processChunk($chunk, $processor);
                });

            Storage::delete($this->filePath);
        } catch (Throwable $exception) {
            Log::error("Import job failed: {$exception->getMessage()}", [
                'file' => $this->filePath,
                'trace' => $exception->getTraceAsString()
            ]);

            $failedPath = self::FAILED_IMPORTS_DIRECTORY . '/' . basename($this->filePath);
            Storage::move($this->filePath, $failedPath);
        }
    }

    private function processChunk(LazyCollection $chunk, FileProcessor $processor): void
    {
        DB::beginTransaction();
        try {
            $chunk->each(function (array $row) use ($processor) {
                try {
                    $employee = EmployeeDto::fromArray($row);
                    Employee::updateOrCreate(
                        ['emp_id' => $employee->empId],
                        $employee->toDatabaseArray()
                    );
                } catch (InvalidArgumentException $e) {
                    $this->logFailedRecord($row, $e->getMessage(), $processor);
                }
            });
            DB::commit();
        } catch (Throwable $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

    private function logFailedRecord(array $row, string $errorMessage, FileProcessor $processor): void
    {
        $headers = array_keys(array_merge($row, ['error' => '']));

        $processor->writeErrorRow($this->failedFilePath, $headers, $row, $errorMessage);
    }
}
