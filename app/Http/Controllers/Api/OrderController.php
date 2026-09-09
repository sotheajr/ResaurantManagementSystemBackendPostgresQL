<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\StoreOrderRequest;
use App\Http\Requests\Order\UpdateOrderStatusRequest;
use App\Services\OrderService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    use ApiResponseTrait;

    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Display a listing of orders filtered by type.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $type = $request->query('type', 'history');
            $filters = $request->only(['start_date', 'end_date', 'search', 'status', 'payment_status', 'per_page']);

            if ($type === 'completed' && empty($filters['status']) && empty($filters['payment_status'])) {
                $filters['status'] = 'completed';
                $filters['payment_status'] = 'unpaid';
            }

            $orders = $this->orderService->getAllOrders($type, $filters);
            return $this->successResponse($orders);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Store a newly created order.
     *
     * @param StoreOrderRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreOrderRequest $request)
    {
        try {
            $data = $request->only(['table_id', 'customer_id', 'user_id', 'notes']);
            $items = $request->input('items', []);

            $order = $this->orderService->createOrder($data, $items);
            return $this->successResponse($order, 'Order created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Display the specified order.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $order = $this->orderService->getOrderById((int) $id);
            return $this->successResponse($order);
        } catch (\Exception $e) {
            return $this->errorResponse('Order not found', 404);
        }
    }

    /**
     * Update the specified order status.
     *
     * @param UpdateOrderStatusRequest $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatus(UpdateOrderStatusRequest $request, $id)
    {
        try {
            $status = $request->input('status');
            $paymentStatus = strtolower((string) $request->input('payment_status', ''));

            if ($paymentStatus === 'paid' || strtolower((string) $status) === 'paid' || strtolower((string) $status) === 'Paid') {
                return $this->errorResponse('Payment completion must happen through the checkout/payment flow. Manual status changes to paid are not allowed.', 422);
            }

            $order = $this->orderService->updateOrderStatus((int) $id, $status);
            return $this->successResponse($order, 'Order status updated successfully');
        } catch (\InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified order.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $this->orderService->deleteOrder((int) $id);
            return $this->successResponse(null, 'Order deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Order not found', 404);
        }
    }
}
