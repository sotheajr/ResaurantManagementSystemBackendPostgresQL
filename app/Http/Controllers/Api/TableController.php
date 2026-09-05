<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Table\StoreTableRequest;
use App\Http\Requests\Table\UpdateTableRequest;
use App\Services\TableService;
use App\Traits\ApiResponseTrait;

class TableController extends Controller
{
    use ApiResponseTrait;

    protected $tableService;

    public function __construct(TableService $tableService)
    {
        $this->tableService = $tableService;
    }

    public function index()
    {
        try {
            $tables = $this->tableService->getAllTables();
            return $this->successResponse($tables);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function available()
    {
        try {
            $tables = $this->tableService->getAvailableTables();
            return $this->successResponse($tables);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function store(StoreTableRequest $request)
    {
        try {
            $imageFile = $request->file('image');
            $table = $this->tableService->createTable($request->validated(), $imageFile);
            return $this->successResponse($table, 'Table created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function show($id)
    {
        try {
            $table = $this->tableService->getTableById((int) $id);
            return $this->successResponse($table);
        } catch (\Exception $e) {
            return $this->errorResponse('Table not found', 404);
        }
    }

    public function update(UpdateTableRequest $request, $id)
    {
        try {
            $imageFile = $request->file('image');
            $table = $this->tableService->updateTable((int) $id, $request->validated(), $imageFile);
            return $this->successResponse($table, 'Table updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        try {
            $this->tableService->deleteTable((int) $id);
            return $this->successResponse(null, 'Table deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Table not found', 404);
        }
    }
}
