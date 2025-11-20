public function checkStatus($transaction_id)
{
    try {
        $order = Order::where('transaction_id', $transaction_id)
                     ->orWhere('order_reference', $transaction_id)
                     ->first();
        
        if (!$order) {
            return response()->json([
                'status' => 'error',
                'message' => 'Transaction not found'
            ], 404);
        }
        
        switch ($order->order_status_id) {
            case 1: // Completed
                if ($order->amount && $order->amount > 0 && $order->is_payment_received) {
                    return response()->json([
                        'status' => 'completed',
                        'message' => 'Payment successful',
                        'redirect_url' => route('showOrderDetails', [
                            'order_reference' => $order->order_reference
                        ])
                    ]);
                }
                // Falls through to default
                
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
        
    } catch (\Exception $e) {
        Log::error('Mobile payment status check error: ' . $e->getMessage());
        
        return response()->json([
            'status' => 'error',
            'message' => 'Unable to check payment status'
        ], 500);
    }
}