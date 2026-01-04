<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@lang('MobilePayment.mobile_payment_status') - {{$event->title}}</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .payment-status-container {
            margin-top: 50px;
        }

        .status-indicator {
            margin-bottom: 20px;
        }

        .status-indicator.processing i {
            color: #f39c12;
        }

        .status-indicator.success i {
            color: #27ae60;
        }

        .status-indicator.failed i {
            color: #e74c3c;
        }

        .payment-details {
            background: #ffffff;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border: 1px solid #e9ecef;
        }

        .status-actions {
            margin: 20px 0;
        }

        .status-actions .btn {
            margin: 0 10px;
        }

        .help-section {
            margin-top: 30px;
            padding: 20px;
            background: #ffffff;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }

        .help-section h6 {
            color: #495057;
            margin-bottom: 10px;
        }

        .panel {
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .panel-heading {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-bottom: none;
        }

        .panel-heading h3 {
            margin: 0;
            font-weight: 500;
        }

        @media (max-width: 768px) {
            .status-actions .btn {
                display: block;
                margin: 10px 0;
                width: 100%;
            }
            
            .payment-status-container {
                margin-top: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="payment-status-container">
                    <div class="panel panel-default">
                        <div class="panel-heading text-center">
                            <h3><i class="fa fa-mobile"></i> @lang('MobilePayment.mobile_payment_status')</h3>
                        </div>
                        
                        <div class="panel-body text-center">
                            <div class="payment-status-content">
                                <div class="status-indicator processing">
                                    <i class="fa fa-spinner fa-spin fa-3x"></i>
                                </div>
                                
                                <h4>@lang('MobilePayment.processing_your_payment')</h4>
                                <p class="lead">@lang('MobilePayment.transaction_id'): <strong>{{$transaction_id}}</strong></p>
                                
                                <div class="alert alert-info">
                                    <h5><i class="fa fa-info-circle"></i> @lang('MobilePayment.what_happens_next')</h5>
                                    <ul class="text-left" style="display: inline-block;">
                                        <li>@lang('MobilePayment.check_phone_for_prompts')</li>
                                        <li>@lang('MobilePayment.follow_instructions')</li>
                                        <li>@lang('MobilePayment.enter_pin_when_prompted')</li>
                                        <li>@lang('MobilePayment.receive_sms_confirmation')</li>
                                    </ul>
                                </div>
                                
                                <div class="payment-details">
                                    <p><strong>@lang('MobilePayment.event'):</strong> {{$event->title}}</p>
                                    <p><strong>@lang('MobilePayment.amount'):</strong> {{ $orderService ? $orderService->getGrandTotal(true) : 'TZS 0' }}</p>
                                </div>
                                
                                <div class="status-actions">
                                    <button class="btn btn-primary" onclick="checkStatus()">
                                        <i class="fa fa-refresh"></i> @lang('MobilePayment.check_status')
                                    </button>
                                    <a href="{{route('showEventPage', ['event_id' => $event->id])}}" class="btn btn-default">
                                        <i class="fa fa-arrow-left"></i> @lang('MobilePayment.back_to_event')
                                    </a>
                                </div>
                                
                                <div id="status-message" class="alert" style="display: none; margin-top: 20px;">
                                    <!-- Status updates will appear here -->
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="help-section">
                        <h5><i class="fa fa-question-circle"></i> @lang('MobilePayment.having_trouble')</h5>
                        <div class="row">
                            <div class="col-sm-6">
                                <h6><i class="fa fa-exclamation-triangle"></i> @lang('MobilePayment.payment_not_working')</h6>
                                <ul class="small">
                                    <li>@lang('MobilePayment.check_balance')</li>
                                    <li>@lang('MobilePayment.ensure_connectivity')</li>
                                    <li>@lang('MobilePayment.try_again_later')</li>
                                </ul>
                            </div>
                            <div class="col-sm-6">
                                <h6><i class="fa fa-phone"></i> @lang('MobilePayment.need_help')</h6>
                                <p class="small">
                                    @lang('MobilePayment.contact_support')
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

 <script>
    let statusCheckInterval;
    let checkCount = 0;
    const maxChecks = 40; // Keep reasonable number of checks

    // Translation strings for JavaScript
    const translations = {
        checking: "@lang('MobilePayment.checking_payment_status')",
        success: "@lang('MobilePayment.payment_successful_redirecting')",
        failed: "@lang('MobilePayment.payment_failed_message')",
        unableToCheck: "@lang('MobilePayment.unable_to_check_status')",
        timeout: "@lang('MobilePayment.status_check_timeout')",
        orderCompletedRedirectFailed: "@lang('MobilePayment.order_completed_redirect_failed')",
        viewOrders: "@lang('MobilePayment.view_your_orders')"
    };

    function checkStatus() {
        console.log('started checking status - attempt ' + (checkCount + 1));
        const statusMessage = document.getElementById('status-message');
        const transactionId = '{{$transaction_id}}';
        const eventId = '{{$event->id}}';
        
        statusMessage.className = 'alert alert-info';
        statusMessage.innerHTML = '<i class="fa fa-spinner fa-spin"></i> ' + translations.checking;
        statusMessage.style.display = 'block';
        
        fetch(`/api/mobile-payment-status/${transactionId}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Status check response:', data);
            updateStatusDisplay(data);
        })
        .catch(error => {
            console.error('Status check error:', error);
            statusMessage.className = 'alert alert-warning';
            statusMessage.innerHTML = '<i class="fa fa-exclamation-triangle"></i> ' + translations.unableToCheck;
        });
    }

    function updateStatusDisplay(data) {
        const statusMessage = document.getElementById('status-message');
        const statusIndicator = document.querySelector('.status-indicator');
        
        console.log('Full API response:', data);
        console.log('Status:', data.status);
        console.log('Redirect URL:', data.redirect_url);
        
        if (data.status === 'completed') {
            statusIndicator.className = 'status-indicator success';
            statusIndicator.innerHTML = '<i class="fa fa-check-circle fa-3x"></i>';
            
            statusMessage.className = 'alert alert-success';
            statusMessage.innerHTML = '<i class="fa fa-check"></i> ' + translations.success;
            
            if (statusCheckInterval) {
                clearInterval(statusCheckInterval);
            }
            
            setTimeout(() => {
                if (data.redirect_url) {
                    console.log('Redirecting to:', data.redirect_url);
                    window.location.href = data.redirect_url;
                } else {
                    console.error('No redirect_url in response:', data);
                    statusMessage.className = 'alert alert-warning';
                    
                    if (data.order_reference) {
                        const fallbackUrl = `/order/${data.order_reference}/tickets`;
                        console.log('Using fallback URL:', fallbackUrl);
                        window.location.href = fallbackUrl;
                    } else {
                        statusMessage.innerHTML = translations.orderCompletedRedirectFailed + ' <a href="/order">' + translations.viewOrders + '</a>';
                    }
                }
            }, 5000);
            
        } else if (data.status === 'failed') {
            statusIndicator.className = 'status-indicator failed';
            statusIndicator.innerHTML = '<i class="fa fa-times-circle fa-3x"></i>';
            
            statusMessage.className = 'alert alert-danger';
            statusMessage.innerHTML = '<i class="fa fa-times"></i> ' + translations.failed;
            
            if (statusCheckInterval) {
                clearInterval(statusCheckInterval);
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        // INCREASED: Check every 15 seconds instead of 6 (better for Render free tier)
        // 40 checks × 15 seconds = 10 minutes total
        statusCheckInterval = setInterval(() => {
            if (checkCount < maxChecks) {
                checkStatus();
                checkCount++;
                
                // Show progress to user
                const elapsed = Math.floor((checkCount * 15) / 60);
                const remaining = Math.floor(((maxChecks - checkCount) * 15) / 60);
                console.log(`Check ${checkCount}/${maxChecks} - ${elapsed}m elapsed, ~${remaining}m remaining`);
                
            } else {
                clearInterval(statusCheckInterval);
                const statusMessage = document.getElementById('status-message');
                statusMessage.className = 'alert alert-warning';
                statusMessage.innerHTML = '<i class="fa fa-exclamation-triangle"></i> ' + translations.timeout;
                statusMessage.style.display = 'block';
            }
        }, 15000); // CHANGED: 15 seconds instead of 6 seconds
        
        // INCREASED: Initial check after 5 seconds instead of 3 (give server time to process)
        setTimeout(() => {
            checkStatus();
        }, 5000);
    });

    window.addEventListener('beforeunload', function() {
        if (statusCheckInterval) {
            clearInterval(statusCheckInterval);
        }
    });
</script>
</body>
</html>