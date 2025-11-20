<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Mobile Payment Status - {{$event->title}}</title>
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
                            <h3><i class="fa fa-mobile"></i> Mobile Payment Status</h3>
                        </div>
                        
                        <div class="panel-body text-center">
                            <div class="payment-status-content">
                                <div class="status-indicator processing">
                                    <i class="fa fa-spinner fa-spin fa-3x"></i>
                                </div>
                                
                                <h4>Processing Your Payment</h4>
                                <p class="lead">Transaction ID: <strong>{{$transaction_id}}</strong></p>
                                
                                <div class="alert alert-info">
                                    <h5><i class="fa fa-info-circle"></i> What happens next?</h5>
                                    <ul class="text-left" style="display: inline-block;">
                                        <li>Check your mobile phone for payment prompts</li>
                                        <li>Follow the instructions on your mobile money service</li>
                                        <li>Enter your mobile money PIN when prompted</li>
                                        <li>You will receive an SMS confirmation</li>
                                    </ul>
                                </div>
                                
                                <div class="payment-details">
                                    <p><strong>Event:</strong> {{$event->title}}</p>
                                    <p><strong>Amount:</strong> {{ $orderService ? $orderService->getGrandTotal(true) : 'TZS 0' }}</p>
                                </div>
                                
                                <div class="status-actions">
                                    <button class="btn btn-primary" onclick="checkStatus()">
                                        <i class="fa fa-refresh"></i> Check Status
                                    </button>
                                    <a href="{{route('showEventPage', ['event_id' => $event->id])}}" class="btn btn-default">
                                        <i class="fa fa-arrow-left"></i> Back to Event
                                    </a>
                                </div>
                                
                                <div id="status-message" class="alert" style="display: none; margin-top: 20px;">
                                    <!-- Status updates will appear here -->
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="help-section">
                        <h5><i class="fa fa-question-circle"></i> Having trouble?</h5>
                        <div class="row">
                            <div class="col-sm-6">
                                <h6><i class="fa fa-exclamation-triangle"></i> Payment not working?</h6>
                                <ul class="small">
                                    <li>Check your mobile money account balance</li>
                                    <li>Ensure you have network connectivity</li>
                                    <li>Try again in a few minutes</li>
                                </ul>
                            </div>
                            <div class="col-sm-6">
                                <h6><i class="fa fa-phone"></i> Need help?</h6>
                                <p class="small">
                                    Contact event organizer or customer support if payment issues persist.
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
        const maxChecks = 20; // Check for about 2 minutes (6 seconds * 20)

        function checkStatus() {
            console.log('started checking status');
            const statusMessage = document.getElementById('status-message');
            const transactionId = '{{$transaction_id}}';
            const eventId = '{{$event->id}}';
            
            // Show checking message
            statusMessage.className = 'alert alert-info';
            statusMessage.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Checking payment status...';
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
                statusMessage.innerHTML = '<i class="fa fa-exclamation-triangle"></i> Unable to check status. Please try again or refresh the page.';
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
        statusMessage.innerHTML = '<i class="fa fa-check"></i> Payment successful! Redirecting to order confirmation...';
        
        // Clear the interval
        if (statusCheckInterval) {
            clearInterval(statusCheckInterval);
        }
        
        setTimeout(() => {
            // Check if redirect_url exists
            if (data.redirect_url) {
                console.log('Redirecting to:', data.redirect_url);
                window.location.href = data.redirect_url;
            } else {
                console.error('No redirect_url in response:', data);
                statusMessage.className = 'alert alert-warning';
                
                // Try to build fallback URL using order_reference if available
                if (data.order_reference) {
                    const fallbackUrl = `/order/${data.order_reference}`;
                    console.log('Using fallback URL:', fallbackUrl);
                    window.location.href = fallbackUrl;
                } else {
                    statusMessage.innerHTML = 'Order completed, but redirect failed. <a href="/order">View your orders</a>';
                }
            }
        }, 5000);
        
    } else if (data.status === 'failed') {
        // ... rest of your code
    }
}

        // Auto-check status every 6 seconds
        document.addEventListener('DOMContentLoaded', function() {
            statusCheckInterval = setInterval(() => {
                if (checkCount < maxChecks) {
                    checkStatus();
                    checkCount++;
                } else {
                    clearInterval(statusCheckInterval);
                    const statusMessage = document.getElementById('status-message');
                    statusMessage.className = 'alert alert-warning';
                    statusMessage.innerHTML = '<i class="fa fa-exclamation-triangle"></i> Status check timeout. Please refresh the page or contact support.';
                    statusMessage.style.display = 'block';
                }
            }, 6000);
            
            // Initial status check after 3 seconds
            setTimeout(() => {
                checkStatus();
            }, 3000);
        });

        // Clean up interval when page unloads
        window.addEventListener('beforeunload', function() {
            if (statusCheckInterval) {
                clearInterval(statusCheckInterval);
            }
        });
    </script>
</body>
</html>