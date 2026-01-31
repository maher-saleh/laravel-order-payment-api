<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/**
 * Order Controller.
 * 
 * Handles all order-related API endpoints.
 * Follows RESTful conventions and uses dependency injection.
 */
class OrderController extends Controller
{

    use AuthorizesRequests;
    /**
     * Display a listing of orders.
     * 
     * GET /api/orders
     * Query params: status, per_page
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Order::with(['items', 'payments'])
            ->where('user_id', $request->user()->id);

        // Filter by status if provided
        if($request->has('status')){
            $query->status($request->status);
        }

        // Pagination
        $perPage = $request->input('per_page', 15);
        $orders = $query->latest()->paginate($perPage);

        return OrderResource::collection($orders);
    }



    /**
     * Store a newly created order.
     * 
     * POST /api/orders
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        $order = DB::transaction(function () use ($request) {
            // Create order
            $order = Order::create([
                'user_id' => $request->user()->id,
                'status' => Order::STATUS_PENDING,
                'total_amount' => 0, // Will be calculated
            ]);

            // Create order items
            foreach ($request->items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_name' => $item['product_name'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);
            }

            // Calculate and update total
            $order->refresh();
            $total = $order->calculateTotal();
            $order->update(['total_amount' => $total]);

            return $order->load(['items', 'payments']);
        });

        return response()->json([
            'message' => 'Order created successfully',
            'data' => new OrderResource($order),
        ], 201);
    }



    /**
     * Display the specified order.
     * 
     * GET /api/orders/{order}
     */
    public function show(Order $order): JsonResponse
    {
        // Authorization: user can only view their own orders
        $this->authorize('view', $order);

        $order->load(['items', 'payments', 'user']);

        return response()->json([
            'data' => new OrderResource($order),
        ]);
    }



    /**
     * Update the specified order.
     * 
     * PUT/PATCH /api/orders/{order}
     */
    public function update(UpdateOrderRequest $request, Order $order): JsonResponse
    {
        DB::transaction(function () use ($request, $order) {
            // Update status if provided
            if($request->has('status')){
                $order->update(['status' => $request->status]);
            }

            // Update items if provided
            if($request->has('items')){
                // Delete existing items
                $order->items()->delete();

                // Create new items
                foreach ($request->items as $item) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_name' => $item['product_name'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                    ]);
                }

                // Recalculate total
                $order->refresh();
                $total = $order->calculateTotal();
                $order->update(['total_amount' => $total]);
            }
        });

        $order->refresh()->load(['items', 'payments']);

        return response()->json([
            'message' => 'Order updated successfully',
            'data' => new OrderResource($order),
        ]);
    }



    /**
     * Remove the specified order.
     * 
     * DELETE /api/orders/{order}
     */
    public function destroy(Order $order): JsonResponse
    {
        $this->authorize('delete', $order);

        try{
            $order->delete();

            return response()->json([
                'message' => 'Order deleted successfully',
            ]);
        }catch(\Exception $e){
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }



    /**
     * Confirm an order.
     * 
     * POST /api/orders/{order}/confirm
     */
    public function confirm(Order $order): JsonResponse
    {

        $this->authorize('update', $order);

        $order->confirm();

        return response()->json([
            'message' => 'Order confirmed successfully',
            'data' => new OrderResource($order->load(['items', 'payments'])),
        ]);
    }



    /**
     * Cancel an order.
     * 
     * POST /api/orders/{order}/cancel
     */
    public function cancel(Order $order): JsonResponse
    {
        $this->authorize('update', $order);

        $order->cancel();

        return response()->json([
            'message' => 'Order cancelled successfully',
            'data' => new OrderResource($order->load(['items', 'payments'])),
        ]);
    }
}