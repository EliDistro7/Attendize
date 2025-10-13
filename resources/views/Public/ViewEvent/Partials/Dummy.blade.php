<form class="online_payment ajax" action="<?php echo route('postCreateOrderMobile', ['event_id' => $event->id]); ?>" method="post" id="mobile-payment-form">
    <div class="online_payment">
        <!-- Payment Method Selection -->
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="payment_method">
                        @lang("Public_ViewEvent.select_payment_method")
                    </label>
                    <div class="payment-methods">
                        <div class="payment-option">
                            <input type="radio" id="mpesa" name="payment_method" value="mpesa" required>
                            <label for="mpesa" class="payment-label">
                                <div class="payment-logo mpesa-logo">M-Pesa</div>
                                <div class="payment-info">
                                    <span class="provider-name">M-Pesa</span>
                                    <small>Vodacom Tanzania</small>
                                </div>
                            </label>
                        </div>
                        
                        <div class="payment-option">
                            <input type="radio" id="tigopesa" name="payment_method" value="tigopesa" required>
                            <label for="tigopesa" class="payment-label">
                                <div class="payment-logo tigo-logo">Tigo</div>
                                <div class="payment-info">
                                    <span class="provider-name">Tigo Pesa</span>
                                    <small>Tigo Tanzania</small>
                                </div>
                            </label>
                        </div>
                        
                        <div class="payment-option">
                            <input type="radio" id="airtelmoney" name="payment_method" value="airtelmoney" required>
                            <label for="airtelmoney" class="payment-label">
                                <div class="payment-logo airtel-logo">Airtel</div>
                                <div class="payment-info">
                                    <span class="provider-name">Airtel Money</span>
                                    <small>Airtel Tanzania</small>
                                </div>
                            </label>
                        </div>
                        
                        <div class="payment-option">
                            <input type="radio" id="halopesa" name="payment_method" value="halopesa" required>
                            <label for="halopesa" class="payment-label">
                                <div class="payment-logo halo-logo">Halo</div>
                                <div class="payment-info">
                                    <span class="provider-name">HaloPesa</span>
                                    <small>Halotel Tanzania</small>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Number Input -->
        <div class="row" id="mobile-number-section" style="display: none;">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="mobile_number">
                        @lang("Public_ViewEvent.mobile_number")
                    </label>
                    <div class="mobile-input-group">
                        <span class="country-code">+255</span>
                        <input type="tel" id="mobile_number" name="mobile_number" 
                               placeholder="7XXXXXXXX" maxlength="9" 
                               pattern="[67][0-9]{8}" class="form-control mobile-input" 
                               autocomplete="tel" required>
                    </div>
                    <small class="help-text">
                        @lang("Public_ViewEvent.mobile_number_help", [], 'Enter your mobile number without the country code (+255)')
                    </small>
                    <div id="mobile-errors" class="error-message"></div>
                </div>
            </div>
        </div>

        <!-- Payment Summary -->
        <div class="payment-summary" id="payment-summary" style="display: none;">
            <h4>@lang("Public_ViewEvent.payment_summary", [], 'Payment Summary')</h4>
            <div class="summary-content">
                <div class="summary-row">
                    <span>@lang("Public_ViewEvent.total_amount", [], 'Total Amount'):</span>
                    <span class="amount">TSh {{ number_format($orderService->getGrandTotal(), 2) }}</span>
                </div>
                <div class="summary-row">
                    <span>@lang("Public_ViewEvent.payment_method", [], 'Payment Method'):</span>
                    <span id="selected-method">-</span>
                </div>
                <div class="summary-row">
                    <span>@lang("Public_ViewEvent.mobile_number", [], 'Mobile Number'):</span>
                    <span id="selected-number">-</span>
                </div>
            </div>
            
            <div class="payment-instructions">
                <div class="alert alert-info">
                    <i class="fa fa-info-circle"></i>
                    <p id="payment-instruction-text">
                        @lang("Public_ViewEvent.select_payment_method_instruction", [], 'Select a payment method to see instructions')
                    </p>
                </div>
            </div>
        </div>

        {!! Form::token() !!}

        <div class="row">
            <div class="col-md-12">
                <input class="btn btn-lg btn-success mobile-submit" style="width:100%;" 
                       type="submit" value="@lang('Public_ViewEvent.complete_payment')" disabled>
            </div>
        </div>
    </div>
</form>

<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function() {
    const paymentMethods = document.querySelectorAll('input[name="payment_method"]');
    const phoneSection = document.getElementById('mobile-number-section');
    const paymentSummary = document.getElementById('payment-summary');
    const mobileInput = document.getElementById('mobile_number');
    const submitButton = document.querySelector('.mobile-submit');
    const mobileErrors = document.getElementById('mobile-errors');
    
    // Payment method instructions in multiple languages
    const instructions = {
        'mpesa': '@lang("Public_ViewEvent.mpesa_instruction", [], "You will receive an SMS prompt on your M-Pesa registered number to complete the payment.")',
        'tigopesa': '@lang("Public_ViewEvent.tigopesa_instruction", [], "You will receive a USSD prompt on your Tigo Pesa number to authorize the payment.")',
        'airtelmoney': '@lang("Public_ViewEvent.airtelmoney_instruction", [], "You will receive a notification on your Airtel Money number to confirm the payment.")',
        'halopesa': '@lang("Public_ViewEvent.halopesa_instruction", [], "You will receive a prompt on your HaloPesa number to complete the transaction.")'
    };

    const providerNames = {
        'mpesa': 'M-Pesa (Vodacom)',
        'tigopesa': 'Tigo Pesa',
        'airtelmoney': 'Airtel Money', 
        'halopesa': 'HaloPesa (Halotel)'
    };

    // Handle payment method selection
    paymentMethods.forEach(method => {
        method.addEventListener('change', function() {
            if (this.checked) {
                phoneSection.style.display = 'block';
                paymentSummary.style.display = 'block';
                
                // Update selected method display
                document.getElementById('selected-method').textContent = providerNames[this.value];
                
                // Update instructions
                document.getElementById('payment-instruction-text').textContent = instructions[this.value];
                
                // Focus on mobile number input
                setTimeout(() => {
                    mobileInput.focus();
                }, 100);
                
                validateForm();
            }
        });
    });

    // Handle mobile number input
    mobileInput.addEventListener('input', function() {
        // Only allow numbers and limit to 9 digits
        this.value = this.value.replace(/[^0-9]/g, '').substring(0, 9);
        
        // Update summary display
        const fullNumber = this.value ? `+255${this.value}` : '-';
        document.getElementById('selected-number').textContent = fullNumber;
        
        validateMobileNumber();
        validateForm();
    });

    // Real-time validation as user types
    mobileInput.addEventListener('keyup', function() {
        validateMobileNumber();
        validateForm();
    });

    // Validate mobile number format
    function validateMobileNumber() {
        const number = mobileInput.value;
        mobileErrors.textContent = '';
        mobileErrors.style.display = 'none';
        
        if (number.length === 0) {
            return false;
        }
        
        if (number.length > 0 && number.length < 9) {
            showError('@lang("Public_ViewEvent.mobile_number_length_error", [], "Mobile number must be 9 digits long")');
            return false;
        }
        
        if (number.length === 9 && !/^[67][0-9]{8}$/.test(number)) {
            showError('@lang("Public_ViewEvent.mobile_number_format_error", [], "Mobile number must start with 6 or 7")');
            return false;
        }
        
        return number.length === 9;
    }

    function showError(message) {
        mobileErrors.textContent = message;
        mobileErrors.style.display = 'block';
    }

    // Validate entire form
    function validateForm() {
        const selectedMethod = document.querySelector('input[name="payment_method"]:checked');
        const isValidNumber = validateMobileNumber();
        
        if (selectedMethod && isValidNumber) {
            submitButton.disabled = false;
            submitButton.style.opacity = '1';
            submitButton.classList.remove('btn-secondary');
            submitButton.classList.add('btn-success');
        } else {
            submitButton.disabled = true;
            submitButton.style.opacity = '0.6';
            submitButton.classList.remove('btn-success');
            submitButton.classList.add('btn-secondary');
        }
    }

    // Handle form submission
    document.getElementById('mobile-payment-form').addEventListener('submit', function(e) {
        const selectedMethod = document.querySelector('input[name="payment_method"]:checked');
        const mobileNumber = mobileInput.value;
        
        if (!selectedMethod || !validateMobileNumber()) {
            e.preventDefault();
            showError('@lang("Public_ViewEvent.payment_validation_error", [], "Please select a payment method and enter a valid mobile number")');
            return false;
        }

        // Show loading state
        submitButton.value = '@lang("Public_ViewEvent.processing_payment", [], "Processing...")';
        submitButton.disabled = true;
        submitButton.style.opacity = '0.7';
        
        // Add spinner if available
        const spinner = '<i class="fa fa-spinner fa-spin"></i> ';
        if (submitButton.innerHTML.indexOf('fa-spinner') === -1) {
            submitButton.value = spinner + submitButton.value;
        }
    });
});
</script>

<style type="text/css">
.payment-methods {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
    margin-bottom: 20px;
}

@media (max-width: 768px) {
    .payment-methods {
        grid-template-columns: 1fr;
        gap: 10px;
    }
}

.payment-option {
    position: relative;
}

.payment-option input[type="radio"] {
    display: none;
}

.payment-label {
    display: flex;
    align-items: center;
    padding: 15px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    background: white;
    min-height: 70px;
}

.payment-label:hover {
    border-color: #007bff;
    background: #f8f9fa;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.payment-option input[type="radio"]:checked + .payment-label {
    border-color: #28a745;
    background: #e8f5e8;
    box-shadow: 0 2px 12px rgba(40, 167, 69, 0.3);
}

.payment-logo {
    width: 50px;
    height: 35px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    border-radius: 4px;
    margin-right: 12px;
    font-size: 11px;
    flex-shrink: 0;
}

.payment-info {
    display: flex;
    flex-direction: column;
}

.provider-name {
    font-weight: 600;
    font-size: 14px;
    color: #333;
}

.payment-info small {
    color: #666;
    font-size: 12px;
    margin-top: 2px;
}

.mpesa-logo {
    background: linear-gradient(135deg, #00a651, #008c44);
    color: white;
}

.tigo-logo {
    background: linear-gradient(135deg, #00aeef, #0088cc);
    color: white;
}

.airtel-logo {
    background: linear-gradient(135deg, #ff0000, #cc0000);
    color: white;
}

.halo-logo {
    background: linear-gradient(135deg, #ff6b35, #e55a2b);
    color: white;
}

.mobile-input-group {
    display: flex;
    border: 1px solid #e0e0e0;
    border-radius: 4px;
    overflow: hidden;
    transition: border-color 0.3s ease;
}

.mobile-input-group:focus-within {
    border-color: #28a745;
    box-shadow: 0 0 0 2px rgba(40, 167, 69, 0.25);
}

.country-code {
    background: #f8f9fa;
    padding: 12px 15px;
    border-right: 1px solid #e0e0e0;
    font-weight: 600;
    color: #495057;
    font-size: 16px;
}

.mobile-input {
    border: none !important;
    flex: 1;
    padding: 12px 15px;
    font-size: 16px;
    box-shadow: none !important;
}

.mobile-input:focus {
    outline: none;
}

.help-text {
    color: #6c757d;
    font-size: 13px;
    margin-top: 5px;
    display: block;
}

.error-message {
    color: #dc3545;
    font-size: 13px;
    margin-top: 5px;
    display: none;
    background: #f8d7da;
    padding: 8px 12px;
    border-radius: 4px;
    border: 1px solid #f5c6cb;
}

.payment-summary {
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    padding: 20px;
    border-radius: 8px;
    margin: 20px 0;
    border: 1px solid #dee2e6;
}

.payment-summary h4 {
    margin: 0 0 15px 0;
    color: #495057;
    font-size: 18px;
    border-bottom: 2px solid #28a745;
    padding-bottom: 8px;
}

.summary-content {
    margin-bottom: 15px;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
    padding: 8px 0;
    border-bottom: 1px solid #e9ecef;
    font-size: 14px;
}

.summary-row:last-child {
    border-bottom: none;
    margin-bottom: 0;
}

.amount {
    font-weight: bold;
    color: #28a745;
    font-size: 18px;
}

.payment-instructions .alert {
    margin-bottom: 0;
    padding: 12px 15px;
    border-radius: 6px;
    font-size: 14px;
    line-height: 1.4;
}

.mobile-submit {
    transition: all 0.3s ease;
    font-weight: 600;
    font-size: 16px;
    padding: 12px 20px;
}

.mobile-submit:disabled {
    cursor: not-allowed;
}

.alert-info {
    color: #0c5460;
    background-color: #d1ecf1;
    border-color: #bee5eb;
}

.alert-info i {
    margin-right: 8px;
}
</style>