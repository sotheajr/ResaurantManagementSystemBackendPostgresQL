<?php

namespace App\Services;

use App\Repositories\Contracts\OrderRepositoryInterface;

class OrderService
{
    protected $orderRepository;

    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function getAllOrders(string $type = 'history', array $filters = [])
    {
        return $this->orderRepository->getAll($type, $filters);
    }

    public function getOrderById(int $id)
    {
        return $this->orderRepository->findById($id);
    }

    public function createOrder(array $data, array $items = [])
    {
        return $this->orderRepository->create($data, $items);
    }

    public function updateOrder(int $id, array $data)
    {
        return $this->orderRepository->update($id, $data);
    }

    public function updateOrderStatus(int $id, string $status)
    {
        return $this->orderRepository->updateStatus($id, $status);
    }

    public function deleteOrder(int $id)
    {
        return $this->orderRepository->delete($id);
    }
}
