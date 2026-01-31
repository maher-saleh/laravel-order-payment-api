<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProcessPaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Order;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/**
 * Payment Controller.
 * 
 * Handles payment processing and payment-related endpoints.
 */
class PaymentController extends Controller
{

    use AuthorizesRequests;
    public function __construct(
        protected PaymentService $paymentService
    ) {}



    /**
     * Display all payments or filter by order.
     * 
     * GET /api/payments
     * GET /api/orders/{order}/payments
     */
    public function index(Request $request, ?Order $order = null): AnonymousResourceCollection
    {
        $query = Payment::with('order');

        if($order){
            // Get payments for specific order
            $this->authorize('view', $order);
            $query->where('order_id', $order->id);
        }else{
            // Get all payments for authenticated user's orders
            $query->whereHas('order', function ($q) use ($request) {
                $q->where('user_id', $request->user()->id);
            });
        }

        // Filter by status if provided
        if($request->has('status')){
            $query->where('status', $request->status);
        }

        $perPage = $request->input('per_page', 15);
        $payments = $query->latest()->paginate($perPage);

        return PaymentResource::collection($payments);
    }



    /**
     * Process a payment for an order.
     * 
     * POST /api/orders/{order}/payments
     */
    public function store(ProcessPaymentRequest $request, Order $order): JsonResponse
    {
        try{
            $payment = $this->paymentService->processPayment(
                order: $order,
                paymentMethod: $request->payment_method,
                paymentDetails: $request->validated()
            );

            return response()->json([
                'message' => 'Payment processed successfully',
                'data' => new PaymentResource($payment->load('order')),
            ], 201);
        }catch(\Exception $e){
            return response()->json([
                'message' => 'Payment processing failed',
                'error' => $e->getMessage(),
            ], 422);
        }
    }



    /**
     * Display a specific payment.
     * 
     * GET /api/payments/{payment}
     */
    public function show(Payment $payment): JsonResponse
    {
        // Ensure user owns the order
        $this->authorize('view', $payment->order);

        return response()->json([
            'data' => new PaymentResource($payment->load('order')),
        ]);
    }



    /**
     * Get payment statistics for an order.
     * 
     * GET /api/orders/{order}/payments/stats
     */
    public function stats(Order $order): JsonResponse
    {
        $this->authorize('view', $order);

        $stats = $this->paymentService->getOrderPaymentStats($order);

        return response()->json([
            'data' => $stats,
        ]);
    }


    
    /**
     * Refund a payment.
     * 
     * POST /api/payments/{payment}/refund
     */
    public function refund(Request $request, Payment $payment): JsonResponse
    {
        $this->authorize('view', $payment->order);

        $request->validate([
            'amount' => ['sometimes', 'numeric', 'min:0.01', 'max:' . $payment->amount],
        ]);

        try{
            $result = $this->paymentService->refundPayment(
                payment: $payment,
                amount: $request->input('amount')
            );

            return response()->json([
                'message' => 'Refund processed successfully',
                'data' => $result->toArray(),
            ]);
        }catch(\Exception $e){
            return response()->json([
                'message' => 'Refund processing failed',
                'error' => $e->getMessage(),
            ], 422);
        }
    }
}