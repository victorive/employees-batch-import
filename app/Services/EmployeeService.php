<?php

namespace App\Services;

use App\Jobs\ImportEmployeesJob;
use App\Repositories\EmployeeRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Models\Employee;
use App\Exceptions\EmployeeException;
use Symfony\Component\HttpFoundation\Response;

class EmployeeService
{
    public function __construct(
        private EmployeeRepository $repository,
    ) {
    }

    public function importEmployees(UploadedFile $file, ?string $delimiter = null): void
    {
        $filePath = Storage::putFile('imports', $file);

        ImportEmployeesJob::dispatch($filePath, $file->getClientOriginalExtension(), $delimiter);
    }

    public function findEmployee(int $id): Employee
    {
        $employee = $this->repository->find($id);

        if (!$employee) {
            throw new EmployeeException('Employee not found', Response::HTTP_NOT_FOUND);
        }

        return $employee;
    }

    public function deleteEmployee(int $id): void
    {
        $this->findEmployee($id);

        $this->repository->delete($id);
    }
}
