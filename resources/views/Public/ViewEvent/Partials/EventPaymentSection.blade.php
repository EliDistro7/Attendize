<section id='order_form' class="container">
    <div class="row">
        <h1 class="section_head">
            @lang("Public_ViewEvent.payment_information")
        </h1>
    </div>
    @if($payment_failed)
    <div class="row">
        <div class="col-md-8 alert-danger" style="text-align: left; padding: 10px">
            @lang("Order.payment_failed")
        </div>
    </div>
    @endif
    <div class="row">
        <div class="col-md-12" style="text-align: center">
            @lang("Public_ViewEvent.below_order_details_header")
        </div>
        <div class="col-md-4 col-md-push-8">
            <div class="panel">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        <i class="ico-cart mr5"></i>
                        @lang("Public_ViewEvent.order_summary")
                    </h3>
                </div>

                <div class="panel-body pt0">
                    <table class="table mb0 table-condensed">
                        @foreach($tickets as $ticket)
                        <tr>
                            <td class="pl0">{{{$ticket['ticket']['title']}}} X <b>{{$ticket['qty']}}</b></td>
                            <td style="text-align: right;">
                                @isFree($ticket['full_price'])
                                @lang("Public_ViewEvent.free")
                                @else
                                {{ money($ticket['full_price'], $event->currency) }}
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </table>
                </div>
                @if($order_total > 0)
                <div class="panel-footer">
                    <h5>
                        @lang("Public_ViewEvent.total"): <span style="float: right;"><b>{{ $orderService->getOrderTotalWithBookingFee(true) }}</b></span>
                    </h5>
                    @if($event->organiser->charge_tax)
                    <h5>
                        {{ $event->organiser->tax_name }} ({{ $event->organiser->tax_value }}%):
                        <span style="float: right;"><b>{{ $orderService->getTaxAmount(true) }}</b></span>
                    </h5>
                    <h5>
                        <strong>@lang("Public_ViewEvent.grand_total")</strong>
                        <span style="float: right;"><b>{{  $orderService->getGrandTotal(true) }}</b></span>
                    </h5>
                    @endif
                </div>
                @endif
            </div>
            <div class="help-block">
                {!! @trans("Public_ViewEvent.time", ["time"=>"<span id='countdown'></span>"]) !!}
            </div>
        </div>
        <div class="col-md-8 col-md-pull-4">
            <div class="row">

                @if($order_requires_payment)
                
                <!-- Payment Method Selection -->
                <div class="col-md-12">
                    <div class="payment-methods-container">
                        <h3>@lang("Public_ViewEvent.select_payment_method")</h3>
                        
                        <div class="payment-options-list">
                            <!-- Mobile Money Option (Default) -->
                            <div class="payment-option-card active" data-payment="mobile">
                                <div class="payment-option-header">
                                    <input type="radio" name="payment_choice" value="mobile" id="mobile_choice" checked>
                                    <label for="mobile_choice">
                                        <i class="fa fa-mobile"></i>
                                        @lang("Public_ViewEvent.mobile_money")
                                    </label>
                                </div>
                                <div class="payment-option-content">
                                    <p class="text-muted">@lang("Public_ViewEvent.mobile_money_description")</p>
                                </div>
                            </div>

                            <!-- Offline Payment Option -->
                            @if($event->enable_offline_payments)
                            <div class="payment-option-card" data-payment="offline">
                                <div class="payment-option-header">
                                    <input type="radio" name="payment_choice" value="offline" id="offline_choice">
                                    <label for="offline_choice">
                                        <i class="fa fa-money"></i>
                                        @lang("Public_ViewEvent.pay_offline")
                                    </label>
                                </div>
                                <div class="payment-option-content">
                                    <p class="text-muted">@lang("Public_ViewEvent.offline_payment_description")</p>
                                </div>
                            </div>
                            @endif

                            <!-- Card Payment Option -->
                            @if($payment_gateway && View::exists($payment_gateway['checkout_blade_template']))
                            <div class="payment-option-card" data-payment="card">
                                <div class="payment-option-header">
                                    <input type="radio" name="payment_choice" value="card" id="card_choice">
                                    <label for="card_choice">
                                        <i class="fa fa-credit-card"></i>
                                        @lang("Public_ViewEvent.credit_debit_card")
                                    </label>
                                </div>
                                <div class="payment-option-content">
                                    <p class="text-muted">@lang("Public_ViewEvent.card_payment_description")</p>
                                </div>
                            </div>
                            @endif
                        </div>

                        <!-- Mobile Money Payment Form -->
                        <div id="mobile_payment_form" class="payment-form-section">
                            <form class="online_payment mobile-payment-form" action="{{ route('postCreateOrderMobile', ['event_id' => $event->id]) }}" method="post" id="mobile-payment-form">
                                <h4>@lang("Public_ViewEvent.mobile_money_details")</h4>
                                
                                <div class="form-group">
                                    <label>@lang("Public_ViewEvent.select_mobile_provider")</label>
                                    <div class="mobile-providers">
                                        <div class="provider-option">
                                            <input type="radio" name="payment_method" value="mpesa" id="mpesa">
                                            <label for="mpesa" class="provider-label">
                                                <div class="provider-content">
                                                    <img src="/images/mpesa.png" alt="M-Pesa" class="provider-logo">
                                                    <span class="provider-name">M-Pesa</span>
                                                    <small class="provider-desc">Vodacom</small>
                                                </div>
                                            </label>
                                        </div>
                                        <div class="provider-option">
                                            <input type="radio" name="payment_method" value="tigopesa" id="tigopesa">
                                            <label for="tigopesa" class="provider-label">
                                                <div class="provider-content">
                                                    <img src="/images/tigopesa.png" alt="Tigo Pesa" class="provider-logo">
                                                    <span class="provider-name">Tigo Pesa</span>
                                                    <small class="provider-desc">Tigo</small>
                                                </div>
                                            </label>
                                        </div>
                                        <div class="provider-option">
                                            <input type="radio" name="payment_method" value="airtel" id="airtel">
                                            <label for="airtel" class="provider-label">
                                                <div class="provider-content">
                                                    <img src="/images/airtel.png" alt="Airtel Money" class="provider-logo">
                                                    <span class="provider-name">Airtel Money</span>
                                                    <small class="provider-desc">Airtel</small>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                    <div id="provider-error" class="text-danger" style="display:none;">
                                        @lang("Public_ViewEvent.please_select_provider")
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="mobile_number">
                                        @lang("Public_ViewEvent.mobile_phone_number")
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="tel" 
                                           id="mobile_number" 
                                           name="mobile_number" 
                                           class="form-control" 
                                           placeholder="754123456" 
                                           maxlength="9"
                                           pattern="[67][0-9]{8}"
                                           required>
                                    <small class="help-block">Format: 7XXXXXXXX or 6XXXXXXXX (9 digits, without country code)</small>
                                    <div id="mobile-errors" class="text-danger"></div>
                                </div>

                                <div class="payment-instructions" id="payment-instructions" style="display:none;">
                                    <div class="alert alert-info">
                                        <h5><i class="fa fa-info-circle"></i> @lang("Public_ViewEvent.payment_instructions")</h5>
                                        <div id="instruction-content"></div>
                                    </div>
                                </div>

                                {!! Form::token() !!}
                                <button class="btn btn-lg btn-success mobile-submit" 
                                        style="width:100%;" 
                                        type="submit" 
                                        disabled>
                                    <i class="fa fa-mobile"></i>
                                    @lang("Public_ViewEvent.pay_with_mobile_money")
                                </button>
                            </form>
                        </div>

                        <!-- Offline Payment Form -->
                        @if($event->enable_offline_payments)
                        <div id="offline_payment_form" class="payment-form-section" style="display:none;">
                            @include('Public.ViewEvent.Partials.OfflinePayments')
                        </div>
                        @endif

                        <!-- Card Payment Form -->
                        @if($payment_gateway && View::exists($payment_gateway['checkout_blade_template']))
                        <div id="card_payment_form" class="payment-form-section" style="display:none;">
                            @include($payment_gateway['checkout_blade_template'])
                        </div>
                        @endif
                    </div>
                </div>

                @endif

                @if(!$order_requires_payment)
                @include('Public.ViewEvent.Partials.PaymentFree')
                @endif
            </div>
        </div>
    </div>
    <img src="https://cdn.attendize.com/lg.png" />
</section>

<style>
.payment-methods-container {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.payment-options-list {
    margin-bottom: 30px;
}

.payment-option-card {
    border: 2px solid #e9ecef;
    border-radius: 8px;
    margin-bottom: 15px;
    background: white;
    transition: all 0.3s ease;
    cursor: pointer;
}

.payment-option-card:hover {
    border-color: #007bff;
    box-shadow: 0 2px 8px rgba(0,123,255,0.15);
}

.payment-option-card.active {
    border-color: #28a745;
    box-shadow: 0 2px 8px rgba(40,167,69,0.15);
}

.payment-option-header {
    padding: 15px 20px;
    display: flex;
    align-items: center;
}

.payment-option-header input[type="radio"] {
    margin-right: 15px;
    transform: scale(1.2);
}

.payment-option-header label {
    margin: 0;
    font-weight: 500;
    font-size: 16px;
    cursor: pointer;
    flex-grow: 1;
}

.payment-option-header i {
    margin-right: 8px;
    color: #6c757d;
    width: 20px;
}

.payment-option-content {
    padding: 0 55px 15px 55px;
}

.mobile-providers {
    display: flex;
    gap: 15px;
    margin: 15px 0;
    flex-wrap: wrap;
}

.provider-option {
    flex: 1;
    min-width: 120px;
}

.provider-option input[type="radio"] {
    display: none;
}

.provider-label {
    display: block;
    padding: 15px 10px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    background: white;
}

.provider-label:hover {
    border-color: #007bff;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.provider-option input:checked + .provider-label {
    border-color: #28a745;
    background-color: #f8fff9;
    box-shadow: 0 4px 12px rgba(40,167,69,0.2);
}

.provider-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
}

.provider-logo {
    height: 40px;
    width: auto;
    object-fit: contain;
}

.provider-name {
    font-weight: 600;
    color: #495057;
    font-size: 14px;
}

.provider-desc {
    color: #6c757d;
    font-size: 12px;
}

.mobile-submit:disabled {
    background-color: #6c757d !important;
    border-color: #6c757d !important;
    cursor: not-allowed;
}

.payment-form-section {
    background: white;
    padding: 25px;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.payment-instructions {
    margin: 20px 0;
}

#mobile-errors {
    margin-top: 5px;
    font-size: 14px;
}

@media (max-width: 768px) {
    .mobile-providers {
        flex-direction: column;
    }
    
    .provider-option {
        min-width: auto;
    }
    
    .provider-label {
        padding: 12px;
    }
    
    .provider-logo {
        height: 35px;
    }
}
</style>

<script>
// Common validation and formatting functions
function validateMobileNumber(number) {
    const cleanNumber = number.replace(/[\s\-\(\)]/g, '');
    
    let validationNumber = cleanNumber;
    if (validationNumber.startsWith('255')) {
        validationNumber = validationNumber.substring(3);
    }
    if (validationNumber.startsWith('0')) {
        validationNumber = validationNumber.substring(1);
    }
    
    const standardPattern = /^[67][0-9]{8}$/;
    return standardPattern.test(validationNumber);
}

function formatMobileNumber(number) {
    let cleanNumber = number.replace(/\D/g, '');
    
    if (cleanNumber.startsWith('0')) {
        cleanNumber = cleanNumber.substring(1);
    }
    
    if (cleanNumber.startsWith('255')) {
        cleanNumber = cleanNumber.substring(3);
    }
    
    if (cleanNumber.length > 0 && !cleanNumber.startsWith('6') && !cleanNumber.startsWith('7')) {
        if (cleanNumber.startsWith('5')) {
            cleanNumber = '7' + cleanNumber.substring(1);
        } else if (cleanNumber.startsWith('2')) {
            cleanNumber = '6' + cleanNumber.substring(1);
        }
    }
    
    return cleanNumber.substring(0, 9);
}

document.addEventListener('DOMContentLoaded', function() {
    let selectedPaymentMethod = null;
    
    // Payment option switching
    const paymentCards = document.querySelectorAll('.payment-option-card');
    const paymentSections = {
        'mobile': document.getElementById('mobile_payment_form'),
        'offline': document.getElementById('offline_payment_form'),
        'card': document.getElementById('card_payment_form')
    };

    paymentCards.forEach(card => {
        card.addEventListener('click', function() {
            const paymentType = this.dataset.payment;
            const radio = this.querySelector('input[type="radio"]');
            
            radio.checked = true;
            
            paymentCards.forEach(c => c.classList.remove('active'));
            this.classList.add('active');
            
            Object.entries(paymentSections).forEach(([type, section]) => {
                if (section) {
                    section.style.display = type === paymentType ? 'block' : 'none';
                }
            });
        });
    });

    // Mobile provider selection
    const providerOptions = document.querySelectorAll('input[name="payment_method"]');
    const mobileNumber = document.getElementById('mobile_number');
    const submitButton = document.querySelector('.mobile-submit');
    const instructionsDiv = document.getElementById('payment-instructions');
    const instructionContent = document.getElementById('instruction-content');

    const instructions = {
        'mpesa': '@lang("Public_ViewEvent.mpesa_instructions")',
        'tigopesa': '@lang("Public_ViewEvent.tigopesa_instructions")',
        'airtel': '@lang("Public_ViewEvent.airtel_instructions")'
    };

    const placeholders = {
        'mpesa': '754123456 (Vodacom/M-Pesa)',
        'tigopesa': '652123456 (Tigo)',
        'airtel': '782123456 (Airtel)'
    };

    providerOptions.forEach(option => {
        option.addEventListener('change', function() {
            selectedPaymentMethod = this.value;
            
            if (mobileNumber && placeholders[this.value]) {
                mobileNumber.placeholder = placeholders[this.value];
            }
            
            if (instructionsDiv && instructionContent) {
                instructionContent.innerHTML = instructions[this.value] || '';
                instructionsDiv.style.display = 'block';
            }
            
            validateForm();
        });
    });

    function validateForm() {
        const mobileNum = mobileNumber ? mobileNumber.value.trim() : '';
        const providerError = document.getElementById('provider-error');
        const mobileErrors = document.getElementById('mobile-errors');
        
        let isValid = true;
        
        if (!selectedPaymentMethod) {
            if (providerError) providerError.style.display = 'block';
            isValid = false;
        } else {
            if (providerError) providerError.style.display = 'none';
        }
        
        if (!mobileNum) {
            if (mobileErrors) mobileErrors.textContent = 'Mobile number is required';
            isValid = false;
        } else if (!validateMobileNumber(mobileNum)) {
            if (mobileErrors) mobileErrors.textContent = 'Please enter a valid Tanzania mobile number (7XXXXXXXX or 6XXXXXXXX)';
            isValid = false;
        } else {
            if (mobileErrors) mobileErrors.textContent = '';
            if (mobileNumber) mobileNumber.classList.remove('is-invalid');
        }
        
        if (submitButton) {
            submitButton.disabled = !isValid;
        }
        
        return isValid;
    }

    if (mobileNumber) {
        mobileNumber.addEventListener('input', function() {
            const cursorPosition = this.selectionStart;
            const oldLength = this.value.length;
            
            const formattedNumber = formatMobileNumber(this.value);
            this.value = formattedNumber;
            
            const newLength = this.value.length;
            const newCursorPosition = cursorPosition + (newLength - oldLength);
            this.setSelectionRange(newCursorPosition, newCursorPosition);
            
            validateForm();
        });

        mobileNumber.addEventListener('blur', validateForm);
        
        mobileNumber.addEventListener('paste', function(e) {
            setTimeout(() => {
                this.value = formatMobileNumber(this.value);
                validateForm();
            }, 10);
        });
    }

 // Add this JavaScript to your blade template, replacing the current form submit handler

const mobileForm = document.getElementById('mobile-payment-form');
if (mobileForm) {
    mobileForm.addEventListener('submit', function(e) {
        e.preventDefault(); // Prevent normal form submission
        
        if (!validateForm()) {
            if (!selectedPaymentMethod) {
                alert('Please select a payment method');
            } else {
                alert('Please enter a valid Tanzania mobile number (7XXXXXXXX or 6XXXXXXXX)');
            }
            return false;
        }
        
        if (mobileNumber) {
            const finalNumber = formatMobileNumber(mobileNumber.value);
            if (!validateMobileNumber(finalNumber)) {
                alert('Invalid mobile number format. Please use: 7XXXXXXXX or 6XXXXXXXX');
                return false;
            }
            mobileNumber.value = finalNumber;
        }
        
        if (submitButton) {
            submitButton.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Processing Payment...';
            submitButton.disabled = true;
        }
        
        // Get form data
        const formData = new FormData(this);
        
        // Make AJAX request
        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if(!response.ok) {
                throw new Error('Network response was not ok');
            }
            console.log('Payment Response Status:', response);
            return response.json();
        })
        .then(data => {
            console.log('Payment Response:', data);
            
            if (data.status === 'success') {
                if (data.redirectUrl) {
                    // Show success message first
                    if (data.message) {
                     //   alert(data.message);
                    }
                    console.log('Redirecting to...:', data.redirectUrl);
                    // Redirect to status page
                    window.location.href = data.redirectUrl;

                } else {
                    // Handle case where there's no redirect URL
                    alert(data.message || 'Payment initiated successfully');
                }
            } else {
                // Handle error response
                alert('Error: ' + (data.message || 'Payment failed'));
                
                // Reset button
                if (submitButton) {
                    submitButton.innerHTML = '<i class="fa fa-mobile"></i> @lang("Public_ViewEvent.pay_with_mobile_money")';
                    submitButton.disabled = false;
                }
            }
        })
        .catch(error => {
            console.error('Payment Error:', error);
            alert('An error occurred while processing your payment. Please try again.');
            
            // Reset button
            if (submitButton) {
                submitButton.innerHTML = '<i class="fa fa-mobile"></i> @lang("Public_ViewEvent.pay_with_mobile_money")';
                submitButton.disabled = false;
            }
        });
    });
}
});


</script>

@if(session()->get('message'))
<script>showMessage("{{session()->get('message')}}");</script>
@endif