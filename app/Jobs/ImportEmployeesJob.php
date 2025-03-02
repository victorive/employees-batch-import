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
use App\Repositories\EmployeeRepository;
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
    public function handle(FileProcessorFactory $fileProcessorFactory, EmployeeRepository $employeeRepository): void
    {
        try {
            $processor = $fileProcessorFactory->getProcessor($this->format);
            $filePath = Storage::path($this->filePath);

            $processor->process($filePath, $this->delimiter)
                ->chunk(self::CHUNK_SIZE)
                ->each(function (LazyCollection $chunk) use ($processor, $employeeRepository) {
                    $this->processChunk($chunk, $processor, $employeeRepository);
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

    private function processChunk(LazyCollection $chunk, FileProcessor $processor, EmployeeRepository $employeeRepository): void
    {
        DB::beginTransaction();
        try {
            $chunk->each(function (array $row) use ($processor, $employeeRepository) {
                try {
                    $employee = EmployeeDto::fromArray($row);

                    $employeeRepository->updateOrCreate(
                        $employee->empId,
                        $employee->toDatabaseArray()
                    );
                } catch (Throwable $e) {
                    $this->logFailedRecord($row, $e->getMessage(), $processor);
                }
            });
            DB::commit();
        } catch (Throwable $exception) {
            DB::rollBack();

            Log::error("Chunk failed: {$exception->getMessage()}");
            throw $exception;
        }
    }

    private function logFailedRecord(array $row, string $errorMessage, FileProcessor $processor): void
    {
        $headers = array_keys(array_merge($row, ['error' => '']));

        $processor->writeErrorRow($this->failedFilePath, $headers, $row, $errorMessage);
    }
}
