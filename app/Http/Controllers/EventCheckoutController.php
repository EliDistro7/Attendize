<?php

namespace App\Http\Controllers;

use App\Attendize\PaymentUtils;
use App\Jobs\SendOrderNotificationJob;
use App\Jobs\SendOrderConfirmationJob;
use App\Jobs\SendOrderAttendeeTicketJob;
use App\Models\Account;
use App\Models\AccountPaymentGateway;
use App\Models\Affiliate;
use App\Models\Attendee;
use App\Models\Event;
use App\Models\EventStats;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentGateway;
use App\Models\QuestionAnswer;
use App\Models\ReservedTickets;
use App\Models\Ticket;
use App\Services\Order as OrderService;
use Services\PaymentGateway\Factory as PaymentGatewayFactory;
use Carbon\Carbon;
use Config;
use Cookie;
use DB;
use Illuminate\Http\Request;
use Log;
use Mail;
use Omnipay;
use PDF;
use PhpSpec\Exception\Exception;
use Validator;
use Spatie\Browsershot\Browsershot;
use Spatie\Browsershot\Exceptions\CouldNotTakeBrowsershot;

class EventCheckoutController extends Controller
{
    /**
     * Is the checkout in an embedded Iframe?
     *
     * @var bool
     */
    protected $is_embedded;

    /**
     * EventCheckoutController constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        /*
         * See if the checkout is being called from an embedded iframe.
         */
        $this->is_embedded = $request->get('is_embedded') == '1';
    }

    /**
     * Validate a ticket request. If successful reserve the tickets and redirect to checkout
     *
     * @param Request $request
     * @param $event_id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function postValidateTickets(Request $request, $event_id)
    {
        /*
         * Order expires after X min
         */
        $order_expires_time = Carbon::now()->addMinutes(config('attendize.checkout_timeout_after'));

        $event = Event::findOrFail($event_id);

        if (!$request->has('tickets')) {
            return response()->json([
                'status'  => 'error',
                'message' => 'No tickets selected',
            ]);
        }

        $ticket_ids = $request->get('tickets');

        /*
         * Remove any tickets the user has reserved
         */
        ReservedTickets::where('session_id', '=', session()->getId())->delete();

        /*
         * Go though the selected tickets and check if they're available
         * , tot up the price and reserve them to prevent over selling.
         */

        $validation_rules = [];
        $validation_messages = [];
        $tickets = [];
        $order_total = 0;
        $total_ticket_quantity = 0;
        $booking_fee = 0;
        $organiser_booking_fee = 0;
        $quantity_available_validation_rules = [];

        foreach ($ticket_ids as $ticket_id) {
            $current_ticket_quantity = (int)$request->get('ticket_' . $ticket_id);

            if ($current_ticket_quantity < 1) {
                continue;
            }

            $total_ticket_quantity = $total_ticket_quantity + $current_ticket_quantity;
            $ticket = Ticket::find($ticket_id);
            $max_per_person = min($ticket->quantity_remaining, $ticket->max_per_person);

            $quantity_available_validation_rules['ticket_' . $ticket_id] = [
                'numeric',
                'min:' . $ticket->min_per_person,
                'max:' . $max_per_person
            ];

            $quantity_available_validation_messages = [
                'ticket_' . $ticket_id . '.max' => 'The maximum number of tickets you can register is ' . $max_per_person,
                'ticket_' . $ticket_id . '.min' => 'You must select at least ' . $ticket->min_per_person . ' tickets.',
            ];

            $validator = Validator::make(['ticket_' . $ticket_id => (int)$request->get('ticket_' . $ticket_id)],
                $quantity_available_validation_rules, $quantity_available_validation_messages);

            if ($validator->fails()) {
                return response()->json([
                    'status'   => 'error',
                    'messages' => $validator->messages()->toArray(),
                ]);
            }

            $order_total = $order_total + ($current_ticket_quantity * $ticket->price);
            $booking_fee = $booking_fee + ($current_ticket_quantity * $ticket->booking_fee);
            $organiser_booking_fee = $organiser_booking_fee + ($current_ticket_quantity * $ticket->organiser_booking_fee);

            $tickets[] = [
                'ticket'                => $ticket,
                'qty'                   => $current_ticket_quantity,
                'price'                 => ($current_ticket_quantity * $ticket->price),
                'booking_fee'           => ($current_ticket_quantity * $ticket->booking_fee),
                'organiser_booking_fee' => ($current_ticket_quantity * $ticket->organiser_booking_fee),
                'full_price'            => $ticket->price + $ticket->total_booking_fee,
            ];

            /*
             * Reserve the tickets for X amount of minutes
             */
            $reservedTickets = new ReservedTickets();
            $reservedTickets->ticket_id = $ticket_id;
            $reservedTickets->event_id = $event_id;
            $reservedTickets->quantity_reserved = $current_ticket_quantity;
            $reservedTickets->expires = $order_expires_time;
            $reservedTickets->session_id = session()->getId();
            $reservedTickets->save();

            for ($i = 0; $i < $current_ticket_quantity; $i++) {
                /*
                 * Create our validation rules here
                 */
                $validation_rules['ticket_holder_first_name.' . $i . '.' . $ticket_id] = ['required'];
                $validation_rules['ticket_holder_last_name.' . $i . '.' . $ticket_id] = ['required'];
                $validation_rules['ticket_holder_email.' . $i . '.' . $ticket_id] = ['required', 'email'];

                $validation_messages['ticket_holder_first_name.' . $i . '.' . $ticket_id . '.required'] = 'Ticket holder ' . ($i + 1) . '\'s first name is required';
                $validation_messages['ticket_holder_last_name.' . $i . '.' . $ticket_id . '.required'] = 'Ticket holder ' . ($i + 1) . '\'s last name is required';
                $validation_messages['ticket_holder_email.' . $i . '.' . $ticket_id . '.required'] = 'Ticket holder ' . ($i + 1) . '\'s email is required';
                $validation_messages['ticket_holder_email.' . $i . '.' . $ticket_id . '.email'] = 'Ticket holder ' . ($i + 1) . '\'s email appears to be invalid';

                /*
                 * Validation rules for custom questions
                 */
                foreach ($ticket->questions as $question) {
                    if ($question->is_required && $question->is_enabled) {
                        $validation_rules['ticket_holder_questions.' . $ticket_id . '.' . $i . '.' . $question->id] = ['required'];
                        $validation_messages['ticket_holder_questions.' . $ticket_id . '.' . $i . '.' . $question->id . '.required'] = "This question is required";
                    }
                }
            }
        }

        if (empty($tickets)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'No tickets selected.',
            ]);
        }

        $activeAccountPaymentGateway = $event->account->getGateway($event->account->payment_gateway_id);
        //if no payment gateway configured and no offline pay, don't go to the next step and show user error
        if (empty($activeAccountPaymentGateway) && !$event->enable_offline_payments) {
            return response()->json([
                'status'  => 'error',
                'message' => 'No payment gateway configured',
            ]);
        }

        $paymentGateway = $activeAccountPaymentGateway ? $activeAccountPaymentGateway->payment_gateway : false;

        /*
         * The 'ticket_order_{event_id}' session stores everything we need to complete the transaction.
         */
        session()->put('ticket_order_' . $event->id, [
            'validation_rules'        => $validation_rules,
            'validation_messages'     => $validation_messages,
            'event_id'                => $event->id,
            'tickets'                 => $tickets,
            'total_ticket_quantity'   => $total_ticket_quantity,
            'order_started'           => time(),
            'expires'                 => $order_expires_time,
            'reserved_tickets_id'     => $reservedTickets->id,
            'order_total'             => $order_total,
            'booking_fee'             => $booking_fee,
            'organiser_booking_fee'   => $organiser_booking_fee,
            'total_booking_fee'       => $booking_fee + $organiser_booking_fee,
            'order_requires_payment'  => PaymentUtils::requiresPayment($order_total),
            'account_id'              => $event->account->id,
            'affiliate_referral'      => Cookie::get('affiliate_' . $event_id),
            'account_payment_gateway' => $activeAccountPaymentGateway,
            'payment_gateway'         => $paymentGateway
        ]);

        /*
         * If we're this far assume everything is OK and redirect them
         * to the the checkout page.
         */
        if ($request->ajax()) {
            return response()->json([
                'status'      => 'success',
                'isEmbedded' => $this->is_embedded,
                'redirectUrl' => route('showEventCheckout', [
                        'event_id'    => $event_id,
                    ]) . '#order_form',
            ]);
        }

        /*
         * Maybe display something prettier than this?
         */
        exit('Please enable Javascript in your browser.');
    }

    /**
     * Show the checkout page
     *
     * @param Request $request
     * @param $event_id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
 /**
 * Show the checkout page
 *
 * @param Request $request
 * @param $event_id
 * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
 */
public function showEventCheckout(Request $request, $event_id)
{
    // Log the start of the checkout process
    \Log::info('=== CHECKOUT DEBUG START ===', [
        'event_id' => $event_id,
        'request_data' => $request->all(),
        'session_id' => session()->getId()
    ]);

    $session_key = 'ticket_order_' . $event_id;
    \Log::info('Looking for session key: ' . $session_key);

    $order_session = session()->get($session_key);
    
    // Log session retrieval results
    if (!$order_session) {
        \Log::warning('No order session found', [
            'session_key' => $session_key,
            'all_session_data' => session()->all() // Be careful with this in production
        ]);
    } else {
        \Log::info('Order session found', [
            'session_key' => $session_key,
            'session_data' => $order_session,
            'expires_at' => $order_session['expires'] ?? 'not set',
            'current_time' => Carbon::now()->toDateTimeString()
        ]);
    }

    // Check expiration
    if (!$order_session || $order_session['expires'] < Carbon::now()) {
        $reason = !$order_session ? 'session_not_found' : 'session_expired';
        
        \Log::warning('Redirecting due to invalid/expired session', [
            'reason' => $reason,
            'event_id' => $event_id,
            'session_expires' => $order_session['expires'] ?? null,
            'current_time' => Carbon::now()->toDateTimeString()
        ]);

        $route_name = $this->is_embedded ? 'showEmbeddedEventPage' : 'showEventPage';
        return redirect()->route($route_name, ['event_id' => $event_id]);
    }

    $secondsToExpire = Carbon::now()->diffInSeconds($order_session['expires']);
    \Log::info('Session timing', [
        'seconds_to_expire' => $secondsToExpire,
        'expires_at' => $order_session['expires']->toDateTimeString(),
        'current_time' => Carbon::now()->toDateTimeString()
    ]);

    try {
        $event = Event::findorFail($order_session['event_id']);
        \Log::info('Event loaded successfully', [
            'event_id' => $event->id,
            'event_title' => $event->title ?? 'No title'
        ]);
    } catch (\Exception $e) {
        \Log::error('Failed to load event', [
            'event_id' => $order_session['event_id'],
            'error' => $e->getMessage()
        ]);
        throw $e;
    }

    try {
        $orderService = new OrderService($order_session['order_total'], $order_session['total_booking_fee'], $event);
        \Log::info('OrderService created', [
            'order_total' => $order_session['order_total'],
            'total_booking_fee' => $order_session['total_booking_fee']
        ]);

        $orderService->calculateFinalCosts();
        \Log::info('Final costs calculated', [
            'final_costs' => method_exists($orderService, 'getFinalTotal') ? $orderService->getGrandTotal() : 'Cannot retrieve'
        ]);
    } catch (\Exception $e) {
        \Log::error('OrderService error', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        throw $e;
    }

    $data = $order_session + [
        'event'           => $event,
        'secondsToExpire' => $secondsToExpire,
        'is_embedded'     => $this->is_embedded,
        'orderService'    => $orderService
    ];

    \Log::info('Data prepared for view', [
        'data_keys' => array_keys($data),
        'is_embedded' => $this->is_embedded
    ]);

    if ($this->is_embedded) {
        \Log::info('Rendering embedded checkout view');
        return view('Public.ViewEvent.Embedded.EventPageCheckout', $data);
    }

    \Log::info('Rendering standard checkout view');
    return view('Public.ViewEvent.EventPageCheckout', $data);
}

    public function postValidateOrder(Request $request, $event_id)
    {
        //If there's no session kill the request and redirect back to the event homepage.
        if (!session()->get('ticket_order_' . $event_id)) {
            return response()->json([
                'status'      => 'error',
                'message'     => 'Your session has expired.',
                'redirectUrl' => route('showEventPage', [
                    'event_id' => $event_id,
                ])
            ]);
        }

        $request_data = session()->get('ticket_order_' . $event_id . ".request_data");
        $request_data = (!empty($request_data[0])) ? array_merge($request_data[0], $request->all())
                                                   : $request->all();

        session()->remove('ticket_order_' . $event_id . '.request_data');
        session()->push('ticket_order_' . $event_id . '.request_data', $request_data);

        $event = Event::findOrFail($event_id);
        $order = new Order();
        $ticket_order = session()->get('ticket_order_' . $event_id);

        $validation_rules = $ticket_order['validation_rules'];
        $validation_messages = $ticket_order['validation_messages'];

        $order->rules = $order->rules + $validation_rules;
        $order->messages = $order->messages + $validation_messages;

        if ($request->has('is_business') && $request->get('is_business')) {
            // Dynamic validation on the new business fields, only gets validated if business selected
            $businessRules = [
                'business_name' => 'required',
                'business_tax_number' => 'required',
                'business_address_line1' => 'required',
                'business_address_city' => 'required',
                'business_address_code' => 'required',
            ];

            $businessMessages = [
                'business_name.required' => 'Please enter a valid business name',
                'business_tax_number.required' => 'Please enter a valid business tax number',
                'business_address_line1.required' => 'Please enter a valid street address',
                'business_address_city.required' => 'Please enter a valid city',
                'business_address_code.required' => 'Please enter a valid code',
            ];

            $order->rules = $order->rules + $businessRules;
            $order->messages = $order->messages + $businessMessages;
        }

        if (!$order->validate($request->all())) {
            return response()->json([
                'status'   => 'error',
                'messages' => $order->errors(),
            ]);
        }

        return response()->json([
            'status'      => 'success',
            'redirectUrl' => route('showEventPayment', [
                    'event_id'    => $event_id,
                    'is_embedded' => $this->is_embedded
                ])
        ]);

    }

public function showEventPayment(Request $request, $event_id)
{
    $order_session = session()->get('ticket_order_' . $event_id);
    $event = Event::findOrFail($event_id);

    // Check if order session exists and has required data
    if (!$order_session || !is_array($order_session)) {
        $route_name = $this->is_embedded ? 'showEmbeddedEventPage' : 'showEventPage';
        return redirect()->route($route_name, ['event_id' => $event_id])
            ->with('error', 'Your session has expired. Please start the checkout process again.');
    }

    // Check if session has expired
    if (isset($order_session['expires']) && $order_session['expires'] < Carbon::now()) {
        $route_name = $this->is_embedded ? 'showEmbeddedEventPage' : 'showEventPage';
        return redirect()->route($route_name, ['event_id' => $event_id])
            ->with('error', 'Your session has expired. Please start the checkout process again.');
    }

    // Safely extract required data with fallbacks
    $payment_gateway = $order_session['payment_gateway'] ?? null;
    $order_total = $order_session['order_total'] ?? 0;
    $account_payment_gateway = $order_session['account_payment_gateway'] ?? null;

    // Fix config decoding issue from session serialization
    if ($account_payment_gateway && is_string($account_payment_gateway->config)) {
        $account_payment_gateway->config = json_decode($account_payment_gateway->config, true);
    }

    // Validate that essential payment data exists
    if (!$payment_gateway && !$event->enable_offline_payments) {
        return redirect()->route('showEventCheckout', ['event_id' => $event_id])
            ->with('error', 'Payment gateway information is missing. Please restart the checkout process.');
    }

    $orderService = new OrderService($order_session['order_total'], $order_session['total_booking_fee'], $event);
    $orderService->calculateFinalCosts();

    $payment_failed = $request->get('is_payment_failed') ? 1 : 0;

    $secondsToExpire = isset($order_session['expires']) ? Carbon::now()->diffInSeconds($order_session['expires']) : 0;

    $viewData = [
        'event' => $event,
        'tickets' => $order_session['tickets'] ?? [],
        'order_total' => $order_total,
        'orderService' => $orderService,
        'order_requires_payment' => PaymentUtils::requiresPayment($order_total),
        'account_payment_gateway' => $account_payment_gateway,
        'payment_gateway' => $payment_gateway,
        'secondsToExpire' => $secondsToExpire,
        'payment_failed' => $payment_failed
    ];

    return view('Public.ViewEvent.EventPagePayment', $viewData);
}
    /**
     * 
     * Create the order and start the payment for the order via Omnipay
     *
     *
     * @param Request $request
     * @param $event_id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function postCreateOrder(Request $request, $event_id)
    {
        $request_data = $ticket_order = session()->get('ticket_order_' . $event_id . ".request_data",[0 => []]);
        $request_data = array_merge($request_data[0], $request->except(['cardnumber', 'cvc']));

        session()->remove('ticket_order_' . $event_id . '.request_data');
        session()->push('ticket_order_' . $event_id . '.request_data', $request_data);

        $ticket_order = session()->get('ticket_order_' . $event_id);

        $event = Event::findOrFail($event_id);

        $order_requires_payment = $ticket_order['order_requires_payment'];

        if ($order_requires_payment && $request->get('pay_offline') && $event->enable_offline_payments) {
            return $this->completeOrder($event_id);
        }

        if (!$order_requires_payment) {
            return $this->completeOrder($event_id);
        }

        try {

            $order_service = new OrderService($ticket_order['order_total'], $ticket_order['total_booking_fee'], $event);
            $order_service->calculateFinalCosts();

            $payment_gateway_config = $ticket_order['account_payment_gateway']->config + [
                                                    'testMode' => config('attendize.enable_test_payments')];

            $payment_gateway_factory = new PaymentGatewayFactory();
            $gateway = $payment_gateway_factory->create($ticket_order['payment_gateway']->name, $payment_gateway_config);
            //certain payment gateways require an extra parameter here and there so this method takes care of that
            //and sets certain options for the gateway that can be used when the transaction is started
            $gateway->extractRequestParameters($request);

            //generic data that is needed for most orders
            $order_total = $order_service->getGrandTotal();
            $order_email = $ticket_order['request_data'][0]['order_email'];

            $response = $gateway->startTransaction($order_total, $order_email, $event);

            if ($response->isSuccessful()) {

                session()->push('ticket_order_' . $event_id . '.transaction_id',
                    $response->getTransactionReference());

                $additionalData = ($gateway->storeAdditionalData()) ? $gateway->getAdditionalData($response) : array();

                session()->push('ticket_order_' . $event_id . '.transaction_data',
                                $gateway->getTransactionData() + $additionalData);

                $gateway->completeTransaction($additionalData);

                return $this->completeOrder($event_id);

            } elseif ($response->isRedirect()) {

                $additionalData = ($gateway->storeAdditionalData()) ? $gateway->getAdditionalData($response) : array();

                session()->push('ticket_order_' . $event_id . '.transaction_data',
                                $gateway->getTransactionData() + $additionalData);

                Log::info("Redirect url: " . $response->getRedirectUrl());

                $return = [
                    'status'       => 'success',
                    'redirectUrl'  => $response->getRedirectUrl(),
                    'message'      => 'Redirecting to ' . $ticket_order['payment_gateway']->provider_name
                ];

                // GET method requests should not have redirectData on the JSON return string
                if($response->getRedirectMethod() == 'POST') {
                    $return['redirectData'] = $response->getRedirectData();
                }

                return response()->json($return);

            } else {
                // display error to customer
                return response()->json([
                    'status'  => 'error',
                    'message' => $response->getMessage(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error($e);
            $error = 'Sorry, there was an error processing your payment. Please try again.';
        }

        if ($error) {
            return response()->json([
                'status'  => 'error',
                'message' => $error,
            ]);
        }

    }







private function getNetworkProvider($payment_method)
{
    $providers = [
        'mpesa' => 'Vodacom',
        'tigopesa' => 'Tigo',
        'airtelmoney' => 'Airtel',
        'halopesa' => 'HaloPesa'
    ];
    
    return $providers[$payment_method] ?? 'Unknown';
}




/**
 * API endpoint to check mobile payment status via AJAX
 *
 * @param $transaction_id
 * @return \Illuminate\Http\JsonResponse
 */



/**
 * Simulate payment status for testing purposes
 */


/**
 * Handle successful payment completion
 */
private function handleSuccessfulPayment($event_id, $transaction_id)
{
    try {
        // Complete the order
        $result = $this->completeOrder($event_id);
        
        if ($result instanceof \Illuminate\Http\RedirectResponse) {
            return $result->getTargetUrl();
        }
        
        // Fallback redirect
        return route('showEventCheckoutPaymentReturn', ['event_id' => $event_id]);
        
    } catch (\Exception $e) {
        Log::error('Error completing order after successful payment:', [
            'event_id' => $event_id,
            'transaction_id' => $transaction_id,
            'error' => $e->getMessage()
        ]);
        
        return route('showEventPage', ['event_id' => $event_id]);
    }
}

/**
 * Get user-friendly payment status messages
 */
private function getPaymentStatusMessage($status, $payment_record = null)
{
    $messages = [
        'processing' => 'Payment is being processed. Please check your phone for prompts.',
        'completed' => 'Payment completed successfully! Redirecting...',
        'failed' => 'Payment failed. Please try again.',
        'timeout' => 'Payment request timed out. Please try again.',
        'error' => 'Unable to process payment. Please contact support.'
    ];

    $message = $messages[$status] ?? 'Unknown payment status';

    // Add specific error details from payment record if available
    if ($payment_record && $status === 'failed' && !empty($payment_record->provider_response)) {
        $message .= ' Reason: ' . $payment_record->provider_response;
    }

    return $message;
}

public function postCreateOrderMobile(Request $request, $event_id)
{
    $request_data = $ticket_order = session()->get('ticket_order_' . $event_id . ".request_data",[0 => []]);
    $request_data = array_merge($request_data[0], $request->except(['cardnumber', 'cvc']));

    session()->remove('ticket_order_' . $event_id . '.request_data');
    session()->push('ticket_order_' . $event_id . '.request_data', $request_data);

    $ticket_order = session()->get('ticket_order_' . $event_id);
    $event = Event::findOrFail($event_id);
    $order_requires_payment = $ticket_order['order_requires_payment'];

    // Handle offline payments
    if ($order_requires_payment && $request->get('pay_offline') && $event->enable_offline_payments) {
        return $this->completeOrder($event_id);
    }

    // Handle free orders
    if (!$order_requires_payment) {
        return $this->completeOrder($event_id);
    }

    // Handle Mobile Money payments - delegate to MobilePaymentController
    if ($request->has('payment_method') && $request->has('mobile_number')) {
        $mobileController = app(MobilePaymentController::class);
        return $mobileController->initiate($request, $event_id);
    }

    // Original Stripe/Gateway payment processing continues below...
    try {
        $order_service = new OrderService($ticket_order['order_total'], $ticket_order['total_booking_fee'], $event);
        $order_service->calculateFinalCosts();

        if (!isset($ticket_order['account_payment_gateway']) || !$ticket_order['account_payment_gateway']) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Payment gateway not configured.',
            ]);
        }

        $payment_gateway_config = $ticket_order['account_payment_gateway']->config + [
                                                'testMode' => config('attendize.enable_test_payments')];

        $payment_gateway_factory = new PaymentGatewayFactory();
        $gateway = $payment_gateway_factory->create($ticket_order['payment_gateway']->name, $payment_gateway_config);
        
        $gateway->extractRequestParameters($request);

        $order_total = $order_service->getGrandTotal();
        $order_email = $ticket_order['request_data'][0]['order_email'];

        $response = $gateway->startTransaction($order_total, $order_email, $event);

        if ($response->isSuccessful()) {
            session()->push('ticket_order_' . $event_id . '.transaction_id',
                $response->getTransactionReference());

            $additionalData = ($gateway->storeAdditionalData()) ? $gateway->getAdditionalData($response) : array();

            session()->push('ticket_order_' . $event_id . '.transaction_data',
                            $gateway->getTransactionData() + $additionalData);

            $gateway->completeTransaction($additionalData);

            return $this->completeOrder($event_id);

        } elseif ($response->isRedirect()) {
            $additionalData = ($gateway->storeAdditionalData()) ? $gateway->getAdditionalData($response) : array();

            session()->push('ticket_order_' . $event_id . '.transaction_data',
                            $gateway->getTransactionData() + $additionalData);

            Log::info("Redirect url: " . $response->getRedirectUrl());

            $return = [
                'status'       => 'success',
                'redirectUrl'  => $response->getRedirectUrl(),
                'message'      => 'Redirecting to ' . $ticket_order['payment_gateway']->provider_name
            ];

            if($response->getRedirectMethod() == 'POST') {
                $return['redirectData'] = $response->getRedirectData();
            }

            return response()->json($return);

        } else {
            return response()->json([
                'status'  => 'error',
                'message' => $response->getMessage(),
            ]);
        }
    } catch (\Exception $e) {
        Log::error($e);
        $error = 'Sorry, there was an error processing your payment. Please try again.';
        
        return response()->json([
            'status'  => 'error',
            'message' => $error,
        ]);
    }
}

private function getFailureMessage($scenario)
{
    $messages = [
        'insufficient_funds' => 'Insufficient balance in mobile money account',
        'network_error' => 'Network connectivity issues with mobile money provider',
        'timeout' => 'User did not complete payment within the specified time limit'
    ];
    
    return $messages[$scenario] ?? 'Payment processing failed';
}

public function showEventCheckoutPaymentReturn(Request $request, $event_id)
{
    $ticket_order = session()->get('ticket_order_' . $event_id);

    // Check if ticket order exists
    if (!$ticket_order) {
        session()->flash('message', 'Order session expired. Please start over.');
        return response()->redirectToRoute('showEventCheckout', ['event_id' => $event_id]);
    }

    // Check if transaction data exists
    if (empty($ticket_order['transaction_data']) || !isset($ticket_order['transaction_data'][0])) {
        session()->flash('message', 'Transaction data is missing. Please try again.');
        return response()->redirectToRoute('showEventCheckout', ['event_id' => $event_id]);
    }

    $payment_gateway_config = $ticket_order['account_payment_gateway']->config + [
            'testMode' => config('attendize.enable_test_payments')];

    $payment_gateway_factory = new PaymentGatewayFactory();
    $gateway = $payment_gateway_factory->create($ticket_order['payment_gateway']->name, $payment_gateway_config);
    $gateway->extractRequestParameters($request);
    $response = $gateway->completeTransaction($ticket_order['transaction_data'][0]);

    if ($response->isSuccessful()) {
        session()->push('ticket_order_' . $event_id . '.transaction_id', $response->getTransactionReference());
        return $this->completeOrder($event_id, false);
    } else {
        session()->flash('message', $response->getMessage());
        return response()->redirectToRoute('showEventPayment', [
            'event_id'          => $event_id,
            'is_payment_failed' => 1,
        ]);
    }
}

   
  /**
 * Complete an order
 *
 * @param $event_id
 * @param bool|true $return_json
 * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
 */
public function completeOrder($event_id, $return_json = true)
{
    DB::beginTransaction();

    try {

        $order = new Order();
        $ticket_order = session()->get('ticket_order_' . $event_id);

        $request_data = $ticket_order['request_data'][0];
        $event = Event::findOrFail($ticket_order['event_id']);
        $attendee_increment = 1;
        $ticket_questions = isset($request_data['ticket_holder_questions']) ? $request_data['ticket_holder_questions'] : [];

        /*
         * Create the order
         */
        if (isset($ticket_order['transaction_id'])) {
            $order->transaction_id = $ticket_order['transaction_id'][0];
        }

        if (isset($ticket_order['transaction_data'][0]['payment_intent'])) {
            $order->payment_intent = $ticket_order['transaction_data'][0]['payment_intent'];
        }

        if ($ticket_order['order_requires_payment'] && !isset($request_data['pay_offline'])) {
            $order->payment_gateway_id = $ticket_order['payment_gateway']->id;
        }
        
        $order->first_name = sanitise($request_data['order_first_name']);
        $order->last_name = sanitise($request_data['order_last_name']);
        $order->email = sanitise($request_data['order_email']);
        
        // Use OrderStatus helper to determine status based on testing mode
        $order->order_status_id = isset($request_data['pay_offline']) 
            ? config('attendize.order.awaiting_payment') 
            : \App\Models\OrderStatus::getDefaultStatus();
            
        $order->amount = $ticket_order['order_total'];
        $order->booking_fee = $ticket_order['booking_fee'];
        $order->organiser_booking_fee = $ticket_order['organiser_booking_fee'];
        $order->discount = 0.00;
        $order->account_id = $event->account->id;
        $order->event_id = $ticket_order['event_id'];
        
        // Use OrderStatus helper to determine payment received status
        $order->is_payment_received = isset($request_data['pay_offline']) 
            ? 0 
            : \App\Models\OrderStatus::isPaymentReceived();

        // Business details is selected, we need to save the business details
        if (isset($request_data['is_business']) && (bool)$request_data['is_business']) {
            $order->is_business = $request_data['is_business'];
            $order->business_name = sanitise($request_data['business_name']);
            $order->business_tax_number = sanitise($request_data['business_tax_number']);
            $order->business_address_line_one = sanitise($request_data['business_address_line1']);
            $order->business_address_line_two  = sanitise($request_data['business_address_line2']);
            $order->business_address_state_province  = sanitise($request_data['business_address_state']);
            $order->business_address_city = sanitise($request_data['business_address_city']);
            $order->business_address_code = sanitise($request_data['business_address_code']);
        }

        // Calculating grand total including tax
        $orderService = new OrderService($ticket_order['order_total'], $ticket_order['total_booking_fee'], $event);
        $orderService->calculateFinalCosts();

        $order->taxamt = $orderService->getTaxAmount();
        $order->save();

        /**
         * We need to attach the ticket ID to an order. There is a case where multiple tickets
         * can be bought in the same order.
         */
        collect($ticket_order['tickets'])->map(function($ticketDetail) use ($order) {
            $order->tickets()->attach($ticketDetail['ticket']['id']);
        });

        /*
         * Update affiliates stats stats
         */
        if ($ticket_order['affiliate_referral']) {
            $affiliate = Affiliate::where('name', '=', $ticket_order['affiliate_referral'])
                ->where('event_id', '=', $event_id)->first();
            $affiliate->increment('sales_volume', $order->amount + $order->organiser_booking_fee);
            $affiliate->increment('tickets_sold', $ticket_order['total_ticket_quantity']);
        }

        /*
         * Update the event stats
         */
        $event_stats = EventStats::updateOrCreate([
            'event_id' => $event_id,
            'date'     => DB::raw('CURRENT_DATE'),
        ]);
        $event_stats->increment('tickets_sold', $ticket_order['total_ticket_quantity']);

        if ($ticket_order['order_requires_payment']) {
            $event_stats->increment('sales_volume', $order->amount);
            $event_stats->increment('organiser_fees_volume', $order->organiser_booking_fee);
        }

        /*
         * Add the attendees
         */
        foreach ($ticket_order['tickets'] as $attendee_details) {
            /*
             * Update ticket's quantity sold
             */
            $ticket = Ticket::findOrFail($attendee_details['ticket']['id']);

            /*
             * Update some ticket info
             */
            $ticket->increment('quantity_sold', $attendee_details['qty']);
            $ticket->increment('sales_volume', ($attendee_details['ticket']['price'] * $attendee_details['qty']));
            $ticket->increment('organiser_fees_volume',
                ($attendee_details['ticket']['organiser_booking_fee'] * $attendee_details['qty']));

            /*
             * Insert order items (for use in generating invoices)
             */
            $orderItem = new OrderItem();
            $orderItem->title = $attendee_details['ticket']['title'];
            $orderItem->quantity = $attendee_details['qty'];
            $orderItem->order_id = $order->id;
            $orderItem->unit_price = $attendee_details['ticket']['price'];
            $orderItem->unit_booking_fee = $attendee_details['ticket']['booking_fee'] + $attendee_details['ticket']['organiser_booking_fee'];
            $orderItem->save();

            /*
             * Create the attendees
             */
            for ($i = 0; $i < $attendee_details['qty']; $i++) {

                $attendee = new Attendee();
                $attendee->first_name = sanitise($request_data["ticket_holder_first_name"][$i][$attendee_details['ticket']['id']]);
                $attendee->last_name = sanitise($request_data["ticket_holder_last_name"][$i][$attendee_details['ticket']['id']]);
                $attendee->email = sanitise($request_data["ticket_holder_email"][$i][$attendee_details['ticket']['id']]);
                $attendee->event_id = $event_id;
                $attendee->order_id = $order->id;
                $attendee->ticket_id = $attendee_details['ticket']['id'];
                $attendee->account_id = $event->account->id;
                $attendee->reference_index = $attendee_increment;
                $attendee->save();

                /*
                 * Save the attendee's questions
                 */
                foreach ($attendee_details['ticket']->questions as $question) {
                    $ticket_answer = isset($ticket_questions[$attendee_details['ticket']->id][$i][$question->id])
                        ? $ticket_questions[$attendee_details['ticket']->id][$i][$question->id]
                        : null;

                    if (is_null($ticket_answer)) {
                        continue;
                    }

                    /*
                     * If there are multiple answers to a question then join them with a comma
                     * and treat them as a single answer.
                     */
                    $ticket_answer = is_array($ticket_answer) ? implode(', ', $ticket_answer) : $ticket_answer;

                    if (!empty($ticket_answer)) {
                        QuestionAnswer::create([
                            'answer_text' => $ticket_answer,
                            'attendee_id' => $attendee->id,
                            'event_id'    => $event->id,
                            'account_id'  => $event->account->id,
                            'question_id' => $question->id
                        ]);
                    }
                }

                /* Keep track of total number of attendees */
                $attendee_increment++;
            }
        }

    } catch (Exception $e) {
        Log::error('Order completion error: ' . $e->getMessage(), [
            'event_id' => $event_id,
            'trace' => $e->getTraceAsString()
        ]);
        DB::rollBack();

        return response()->json([
            'status'  => 'error',
            'message' => 'Whoops! There was a problem processing your order. Please try again.'
        ]);
    }
    
    //save the order to the database
    DB::commit();
    //forget the order in the session
    session()->forget('ticket_order_' . $event->id);

    /*
     * Remove any tickets the user has reserved after they have been ordered for the user
     */
    ReservedTickets::where('session_id', '=', session()->getId())->delete();

    // Queue up some tasks - Emails to be sent, PDFs etc.
    // Send order notification to organizer
    Log::debug('Queueing Order Notification Job');
    SendOrderNotificationJob::dispatch($order, $orderService);
    // Send order confirmation to ticket buyer
    Log::debug('Queueing Order Tickets Job');
    SendOrderConfirmationJob::dispatch($order, $orderService);
    // Send tickets to attendees
    Log::debug('Queueing Attendee Ticket Jobs');
    foreach ($order->attendees as $attendee) {
        SendOrderAttendeeTicketJob::dispatch($attendee);
        Log::debug('Queueing Attendee Ticket Job Done');
    }

    if ($return_json) {
        return response()->json([
            'status'      => 'success',
            'redirectUrl' => route('showOrderDetails', [
                'is_embedded'     => $this->is_embedded,
                'order_reference' => $order->order_reference,
            ]),
        ]);
    }

    return response()->redirectToRoute('showOrderDetails', [
        'is_embedded'     => $this->is_embedded,
        'order_reference' => $order->order_reference,
    ]);
}
    /**
     * Show the order details page
     *
     * @param Request $request
     * @param $order_reference
     * @return \Illuminate\View\View
     */

    
    public function showOrderDetails(Request $request, $order_reference)
    {
        $order = Order::where('order_reference', '=', $order_reference)->first();

        if (!$order) {
            abort(404);
        }

        $orderService = new OrderService($order->amount, $order->organiser_booking_fee, $order->event);
        $orderService->calculateFinalCosts();

        $data = [
            'order'        => $order,
            'orderService' => $orderService,
            'event'        => $order->event,
            'tickets'      => $order->event->tickets,
            'is_embedded'  => $this->is_embedded,
        ];

        if ($this->is_embedded) {
            return view('Public.ViewEvent.Embedded.EventPageViewOrder', $data);
        }

        return view('Public.ViewEvent.EventPageViewOrder', $data);
    }

/**
 * Shows the tickets for an order - either HTML or PDF
 *
 * @param Request $request
 * @param $order_reference
 * @return \Illuminate\View\View
 */

public function showOrderTickets(Request $request, $order_reference)
{
    $order = Order::where('order_reference', '=', $order_reference)->first();
    
    if (!$order) {
        abort(404);
    }

    $images = [];
    $imgs = $order->event->images;
    
    foreach ($imgs as $img) {
        $imagePath = public_path($img->image_path);
        if (file_exists($imagePath)) {
            $images[] = base64_encode(file_get_contents($imagePath));
        } else {
            Log::warning('Event image not found', [
                'path' => $imagePath,
                'image_id' => $img->id
            ]);
        }
    }

    $logoPath = public_path($order->event->organiser->full_logo_path);
    $logo = null;
    
    if (file_exists($logoPath)) {
        $logo = base64_encode(file_get_contents($logoPath));
    } else {
        Log::warning('Organiser logo not found', [
            'path' => $logoPath,
            'organiser_id' => $order->event->organiser->id
        ]);
    }

    $data = [
        'order' => $order,
        'event' => $order->event,
        'tickets' => $order->event->tickets,
        'attendees' => $order->attendees,
        'css' => file_get_contents(public_path('assets/stylesheet/ticket.css')),
        'image' => $logo,
        'images' => $images,
    ];

    // No more ?download=1 logic needed! Client-side handles it
    return view('Public.ViewEvent.Partials.PDFTicket', $data);
}
private function getStatusMessage($status)
{
    $messages = [
        'pending' => 'Payment is being processed',
        'processing' => 'Payment is being processed',
        'completed' => 'Payment completed successfully',
        'failed' => 'Payment failed',
        'cancelled' => 'Payment was cancelled',
        'timeout' => 'Payment timed out'
    ];
    
    return $messages[$status] ?? 'Unknown status';
}

}
