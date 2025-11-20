<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Order;
use App\Models\PaymentGateway;
use App\Services\Order as OrderService;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Log;
use Validator;

class MobilePaymentController extends Controller
{
    /**
     * Process mobile money payments for Tanzania
     *
     * @param Request $request
     * @param $event_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function initiate(Request $request, $event_id)
    {
        try {
            // Get ticket order from session
            $ticket_order = session()->get('ticket_order_' . $event_id);
            
            if (!$ticket_order) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Session expired. Please start checkout again.'
                ]);
            }

            // Debug: Log incoming request
            Log::info('Mobile Payment Debug - Request Data:', [
                'payment_method' => $request->get('payment_method'),
                'mobile_number' => $request->get('mobile_number'),
                'event_id' => $event_id,
                'has_ticket_order' => !empty($ticket_order),
                'full_request' => $request->all()
            ]);

            $payment_method = $request->get('payment_method');
            $mobile_number = $request->get('mobile_number');
            
            // Check if these values exist
            if (empty($payment_method) || empty($mobile_number)) {
                Log::error('Mobile Payment Error: Missing required fields', [
                    'payment_method' => $payment_method,
                    'mobile_number' => $mobile_number
                ]);
                
                return response()->json([
                    'status' => 'error',
                    'message' => 'Payment method and mobile number are required.'
                ]);
            }
            
            // Format mobile number
            $formatted_mobile = '+255' . $mobile_number;
            
            // Validate input
            $validator = Validator::make($request->all(), [
                'payment_method' => 'required|in:mpesa,tigopesa,airtel',
                'mobile_number' => ['required', 'regex:/^[67][0-9]{8}$/']
            ]);

            if ($validator->fails()) {
                Log::error('Mobile Payment Validation Failed:', [
                    'errors' => $validator->errors()->toArray(),
                    'payment_method_received' => $payment_method,
                    'mobile_number_received' => $mobile_number
                ]);
                
                return response()->json([
                    'status'   => 'error',
                    'message'  => 'Invalid payment details.',
                    'errors'   => $validator->errors(),
                    'debug_info' => [
                        'received_payment_method' => $payment_method,
                        'expected_methods' => ['mpesa', 'tigopesa', 'airtel']
                    ]
                ]);
            }

            $event = Event::findOrFail($event_id);
            Log::info('Event found:', ['event_id' => $event->id, 'title' => $event->title]);

            // Validate ticket order structure
            if (!isset($ticket_order['order_total']) || !isset($ticket_order['total_booking_fee'])) {
                Log::error('Mobile Payment Error: Invalid ticket order structure', [
                    'ticket_order_keys' => array_keys($ticket_order ?? []),
                    'full_ticket_order' => $ticket_order
                ]);
                
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid order data. Please try again.'
                ]);
            }

            $order_service = new OrderService($ticket_order['order_total'], $ticket_order['total_booking_fee'], $event);
            $order_service->calculateFinalCosts();
            
            $order_total = $order_service->getGrandTotal();
            
            Log::info('Order totals calculated:', [
                'order_total' => $ticket_order['order_total'],
                'booking_fee' => $ticket_order['total_booking_fee'],
                'grand_total' => $order_total
            ]);
            
            // Store mobile payment data in session for order completion
            session()->push('ticket_order_' . $event_id . '.mobile_payment_data', [
                'payment_method' => $payment_method,
                'mobile_number' => $formatted_mobile,
                'amount' => $order_total,
                'initiated_at' => now(),
            ]);

            $stored_data = session()->get('ticket_order_' . $event_id . '.mobile_payment_data');
            Log::info('Session data stored:', ['stored_data' => $stored_data]);

            // Initiate mobile money payment
            $payment_result = $this->initiateMobilePayment($payment_method, $formatted_mobile, $order_total, $event_id);

            Log::info('Payment result:', $payment_result);

            if ($payment_result['success']) {
                // Store transaction reference
                session()->push('ticket_order_' . $event_id . '.transaction_id', $payment_result['transaction_id']);
                
                // Store transaction to event mapping for later retrieval
                session()->put("transaction_event_map.{$payment_result['transaction_id']}", $event_id);
                
                // Generate redirect URL
                try {
                    $redirect_url = route('showMobilePaymentStatus', [
                        'event_id' => $event_id,
                        'transaction_id' => $payment_result['transaction_id']
                    ]);
                    
                    Log::info('Redirect URL generated successfully:', [
                        'url' => $redirect_url,
                        'route_exists' => true
                    ]);
                    
                } catch (\Exception $route_error) {
                    Log::error('Route generation error:', [
                        'error' => $route_error->getMessage(),
                        'trying_fallback' => true
                    ]);
                    
                    // Fallback redirect
                    $redirect_url = url("/e/{$event_id}/payment/status/{$payment_result['transaction_id']}");
                    
                    Log::info('Using fallback URL:', ['url' => $redirect_url]);
                }
                
                $response_data = [
                    'status'  => 'success',
                    'message' => $payment_result['message'],
                    'payment_method' => $payment_method,
                    'mobile_number' => $formatted_mobile,
                    'transaction_id' => $payment_result['transaction_id'],
                    'redirectUrl' => $redirect_url
                ];
                
                Log::info('Returning success response:', $response_data);
                
                return response()->json($response_data);
            } else {
                Log::error('Payment initiation failed:', $payment_result);
                
                return response()->json([
                    'status'  => 'error',
                    'message' => $payment_result['message']
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Mobile Payment Error Details:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
                'event_id' => $event_id ?? null,
                'session_data' => session()->get('ticket_order_' . $event_id ?? null)
            ]);
            
            return response()->json([
                'status'  => 'error',
                'message' => 'Error processing mobile payment. Please try again.',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ]);
        }
    }

    /**
     * Initiate mobile money payment with provider
     */
    private function initiateMobilePayment($payment_method, $mobile_number, $amount, $event_id)
    {
        try {
            $transaction_id = 'TXN' . time() . rand(1000, 9999);
            
            Log::info('Initiating mobile payment:', [
                'payment_method' => $payment_method,
                'mobile_number' => $mobile_number,
                'amount' => $amount,
                'event_id' => $event_id,
                'transaction_id' => $transaction_id
            ]);
            
            // Payment gateway mapping
            $gateway_mapping = [
                'mpesa' => 'MPesa',
                'tigopesa' => 'TigoPesa', 
                'airtel' => 'AirtelMoney',
            ];
            
            $gateway_name = $gateway_mapping[$payment_method] ?? null;
            if (!$gateway_name) {
                throw new \Exception("Unsupported payment method: {$payment_method}");
            }
            
            // Try to get payment gateway ID
            try {
                $payment_gateway_id = DB::table('payment_gateways')
                                   ->where('name', $gateway_name)
                                   ->value('id');
                                   
                if ($payment_gateway_id) {
                    Log::info('Payment gateway found:', ['gateway_id' => $payment_gateway_id, 'name' => $gateway_name]);
                } else {
                    Log::info('Payment gateway not found in database:', ['name' => $gateway_name]);
                }
            } catch (\Exception $db_error) {
                Log::warning('Could not find payment gateway in database:', ['error' => $db_error->getMessage()]);
                $payment_gateway_id = null;
            }
            
            // Provider names mapping
            $provider_names = [
                'mpesa' => 'M-Pesa',
                'tigopesa' => 'Tigo Pesa', 
                'airtel' => 'Airtel Money',
            ];
            
            $provider_name = $provider_names[$payment_method] ?? 'Mobile Money';
            
            // TODO: Replace with actual API integration
            // Example for M-Pesa:
            // if ($payment_method === 'mpesa') {
            //     $mpesa_api = new VodacomMPesaApi();
            //     $result = $mpesa_api->initiateSTKPush($mobile_number, $amount);
            //     return [
            //         'success' => $result->success,
            //         'transaction_id' => $result->transaction_id,
            //         'message' => $result->message
            //     ];
            // }
            
            Log::info("Mobile Payment Simulation Successful", [
                'payment_method' => $payment_method,
                'mobile_number' => $mobile_number,
                'amount' => $amount,
                'transaction_id' => $transaction_id,
                'provider_name' => $provider_name
            ]);
            
            return [
                'success' => true,
                'transaction_id' => $transaction_id,
                'message' => "Payment request sent to {$mobile_number} via {$provider_name}. Please check your phone and follow the prompts to complete the payment."
            ];
            
        } catch (\Exception $e) {
            Log::error('initiateMobilePayment Error:', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'payment_method' => $payment_method ?? 'unknown'
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to initiate mobile payment: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Show payment status page
     */
    public function showStatus(Request $request, $event_id, $transaction_id)
    {
        $event = Event::findOrFail($event_id);
        
        $ticket_order = session()->get('ticket_order_' . $event_id, []);
        $orderService = null;
        
        if (isset($ticket_order['order_total']) && isset($ticket_order['total_booking_fee'])) {
            $orderService = new OrderService($ticket_order['order_total'], $ticket_order['total_booking_fee'], $event);
            $orderService->calculateFinalCosts();
        }
        
        return view('Public.ViewEvent.MobilePaymentStatus', [
            'event' => $event,
            'transaction_id' => $transaction_id,
            'orderService' => $orderService,
            'status' => 'processing'
        ]);
    }

    /**
     * API endpoint to check mobile payment status via AJAX
     *
     * @param $transaction_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkStatus($transaction_id)
    {
        try {
            Log::info('Mobile Payment Status Check Started:', [
                'transaction_id' => $transaction_id,
                'session_id' => session()->getId()
            ]);

            // First check if order already exists in database
            $order = Order::where('transaction_id', $transaction_id)
                         ->orWhere('order_reference', $transaction_id)
                         ->first();
            
            if ($order) {
                // Order exists, check its status
                Log::info('Order found in database:', [
                    'order_id' => $order->id,
                    'order_reference' => $order->order_reference,
                    'status_id' => $order->order_status_id,
                    'is_payment_received' => $order->is_payment_received
                ]);
                
                switch ($order->order_status_id) {
                    case 1: // Completed
                        if ($order->is_payment_received) {
                            $redirectUrl = route('showOrderDetails', [
                                'order_reference' => $order->order_reference
                            ]);
                            
                            Log::info('Payment completed successfully:', [
                                'order_reference' => $order->order_reference,
                                'redirect_url' => $redirectUrl
                            ]);
                            
                            return response()->json([
                                'status' => 'completed',
                                'message' => 'Payment successful',
                                'redirect_url' => $redirectUrl,
                                'order_reference' => $order->order_reference
                            ]);
                        }
                        
                        Log::warning('Order exists but payment not received:', [
                            'order_id' => $order->id
                        ]);
                        
                        return response()->json([
                            'status' => 'pending',
                            'message' => 'Processing payment...'
                        ]);
                        
                    case 4: // Cancelled
                        return response()->json([
                            'status' => 'failed',
                            'message' => 'Payment was cancelled or failed'
                        ]);
                        
                    default:
                        if ($order->created_at->diffInMinutes(now()) > 5) {
                            return response()->json([
                                'status' => 'timeout',
                                'message' => 'Payment request timed out'
                            ]);
                        }
                        
                        return response()->json([
                            'status' => 'pending',
                            'message' => 'Waiting for payment confirmation'
                        ]);
                }
            }

            // Order doesn't exist yet, get event_id from session
            $event_id = session()->get("transaction_event_map.{$transaction_id}");
            
            if (!$event_id) {
                // Try to find from all ticket orders
                foreach (session()->all() as $key => $value) {
                    if (strpos($key, 'ticket_order_') === 0 && is_array($value)) {
                        $stored_transactions = $value['transaction_id'] ?? [];
                        if (is_array($stored_transactions) && in_array($transaction_id, $stored_transactions)) {
                            $event_id = str_replace('ticket_order_', '', $key);
                            break;
                        }
                    }
                }
            }

            Log::info('Event ID Resolution:', [
                'event_id' => $event_id,
                'transaction_id' => $transaction_id
            ]);

            if (!$event_id) {
                Log::error('Event ID not found for transaction:', [
                    'transaction_id' => $transaction_id
                ]);
                
                return response()->json([
                    'status' => 'error',
                    'message' => 'Session expired. Please start checkout again.'
                ]);
            }

            // Simulate payment status check (replace with actual API call)
            $status = $this->simulatePaymentStatusCheck($transaction_id);
            $message = $this->getPaymentStatusMessage($status);
            $redirect_url = null;

            if ($status === 'completed') {
                // Payment completed, create order
                $completion_result = $this->completeOrder($event_id, $transaction_id);
                
                if ($completion_result['success']) {
                    Log::info('Mobile Payment Order Completed Successfully:', [
                        'event_id' => $event_id,
                        'transaction_id' => $transaction_id,
                        'redirect_url' => $completion_result['redirect_url']
                    ]);
                    
                    $redirect_url = $completion_result['redirect_url'];
                    
                    return response()->json([
                        'status' => 'completed',
                        'message' => $message,
                        'redirect_url' => $redirect_url,
                        'transaction_id' => $transaction_id
                    ]);
                } else {
                    $status = 'failed';
                    $message = $completion_result['message'];
                }
            }

            return response()->json([
                'status' => $status,
                'message' => $message,
                'redirect_url' => $redirect_url,
                'transaction_id' => $transaction_id
            ]);

        } catch (\Exception $e) {
            Log::error('Mobile Payment Status Check Error:', [
                'transaction_id' => $transaction_id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Unable to check payment status. Please try again.'
            ], 500);
        }
    }

    /**
     * Complete order when payment is successful
     */
    private function completeOrder($event_id, $transaction_id)
    {
        try {
            Log::info('Starting mobile order completion', [
                'event_id' => $event_id,
                'transaction_id' => $transaction_id
            ]);

            // Check if order already exists (prevent duplicate)
            $existingOrder = Order::where('transaction_id', $transaction_id)->first();
            if ($existingOrder) {
                Log::info('Order already exists:', [
                    'order_reference' => $existingOrder->order_reference
                ]);
                
                $redirectUrl = route('showOrderDetails', [
                    'order_reference' => $existingOrder->order_reference
                ]);
                
                return [
                    'success' => true,
                    'redirect_url' => $redirectUrl
                ];
            }

            // Get the order data from session
            $ticket_order = session()->get("ticket_order_{$event_id}");
            
            if (!$ticket_order) {
                Log::error('No ticket order found in session', ['event_id' => $event_id]);
                return [
                    'success' => false,
                    'message' => 'Session expired. Please start checkout again.'
                ];
            }

            // Ensure transaction_id is in session
            $stored_transaction = $ticket_order['transaction_id'][0] ?? null;
            
            if ($stored_transaction !== $transaction_id) {
                Log::error('Transaction ID mismatch', [
                    'expected' => $transaction_id,
                    'found' => $stored_transaction
                ]);
                return [
                    'success' => false,
                    'message' => 'Transaction verification failed'
                ];
            }

            // Call the main EventCheckoutController's completeOrder method
            $checkoutController = app(EventCheckoutController::class);
            $response = $checkoutController->completeOrder($event_id, true);
            
            if ($response instanceof \Illuminate\Http\JsonResponse) {
                $responseData = $response->getData(true);
                
                if (isset($responseData['redirectUrl'])) {
                    Log::info('Mobile order completed successfully', [
                        'redirect_url' => $responseData['redirectUrl']
                    ]);
                    
                    return [
                        'success' => true,
                        'redirect_url' => $responseData['redirectUrl']
                    ];
                }
            }
            
            Log::error('Unexpected response from completeOrder', [
                'response_type' => get_class($response)
            ]);
            
            return [
                'success' => false,
                'message' => 'Order processing failed'
            ];
            
        } catch (\Exception $e) {
            Log::error('Mobile order completion error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'message' => 'An error occurred while processing your order'
            ];
        }
    }

    /**
     * Simulate payment status for testing purposes
     */
    private function simulatePaymentStatusCheck($transaction_id)
    {
        $session_key = 'payment_simulation_' . $transaction_id;
        $simulation_data = session($session_key, [
            'created_at' => now(),
            'checks' => 0,
            'final_status' => null
        ]);

        $simulation_data['checks']++;

        Log::info('Payment Simulation Check:', [
            'transaction_id' => $transaction_id,
            'check_number' => $simulation_data['checks']
        ]);

        // Complete after 2nd check for testing
        $status = $simulation_data['checks'] >= 2 ? 'completed' : 'processing';

        $simulation_data['final_status'] = $status;
        session([$session_key => $simulation_data]);

        return $status;
    }

    /**
     * Get user-friendly payment status messages
     */
    private function getPaymentStatusMessage($status)
    {
        $messages = [
            'processing' => 'Payment is being processed. Please check your phone for prompts.',
            'completed' => 'Payment completed successfully! Redirecting...',
            'failed' => 'Payment failed. Please try again.',
            'timeout' => 'Payment request timed out. Please try again.',
            'error' => 'Unable to process payment. Please contact support.'
        ];

        return $messages[$status] ?? 'Unknown payment status';
    }
}