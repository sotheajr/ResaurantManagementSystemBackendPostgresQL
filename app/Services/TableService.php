<?php

namespace App\Services;

use App\Repositories\Contracts\TableRepositoryInterface;
use App\Traits\UploadImageTrait;
use Illuminate\Http\UploadedFile;

class TableService
{
    use UploadImageTrait;

    protected $tableRepository;

    public function __construct(TableRepositoryInterface $tableRepository)
    {
        $this->tableRepository = $tableRepository;
    }

    public function getAllTables()
    {
        return $this->tableRepository->getAll();
    }

    public function getTableById(int $id)
    {
        return $this->tableRepository->findById($id);
    }

    public function getAvailableTables()
    {
        return $this->tableRepository->getAvailableTables();
    }

    public function createTable(array $data, ?UploadedFile $imageFile = null)
    {
        if ($imageFile) {
            $data['image'] = $this->uploadImage($imageFile, 'tables');
        }

        return $this->tableRepository->create($data);
    }

    public function updateTable(int $id, array $data, ?UploadedFile $imageFile = null)
    {
        $table = $this->tableRepository->findById($id);

        if ($imageFile) {
            if ($table->image) {
                $this->deleteImage($table->image);
            }
            $data['image'] = $this->uploadImage($imageFile, 'tables');
        }

        return $this->tableRepository->update($id, $data);
    }

    public function deleteTable(int $id)
    {
        $table = $this->tableRepository->findById($id);

        if ($table->image) {
            $this->deleteImage($table->image);
        }

        return $this->tableRepository->delete($id);
    }
}
