<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\ImportEmployeesRequest;
use App\Models\Employee;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\EmployeeService;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\JsonResponse;

class EmployeeController extends Controller
{

    public function __construct(public EmployeeService $employeeService)
    {

    }

    public function importEmployees(ImportEmployeesRequest $request)
    {
        $this->employeeService->importEmployees(
            $request->file('file'),
            $request->input('delimiter')
        );

        return response()->json([
            'status' => true,
            'message' => 'Employees import batched for processing.',
        ], Response::HTTP_ACCEPTED);
    }

    public function getEmployee(int $id): JsonResponse
    {
        $employee = $this->employeeService->findEmployee($id);

        return response()->json([
            'status' => true,
            'message' => 'Employee retrieved successfully',
            'data' => $employee,
        ], Response::HTTP_OK);
    }

    /**
     * Delete an employee by ID.
     */
    public function deleteEmployee(int $id): JsonResponse
    {
        $this->employeeService->deleteEmployee($id);

        return response()->json([
            'status' => true,
            'message' => 'Employee deleted successfully',
        ], Response::HTTP_NO_CONTENT);
    }
}
