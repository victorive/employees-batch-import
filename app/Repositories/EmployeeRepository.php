<?php

namespace App\Repositories;

use App\Models\Employee;

class EmployeeRepository
{
    public function __construct(private Employee $model)
    {
    }

    public function find(int $id): ?Employee
    {
        return $this->model->find($id);
    }

    public function delete(int $id): bool
    {
        return $this->model->destroy($id);
    }

    public function updateOrCreate(int $empId, array $attributes): Employee
    {
        return $this->model->updateOrCreate(
            ['emp_id' => $empId],
            $attributes
        );
    }
}
