<form class="online_payment mobile-payment-form" action="<?php echo route('postCreateOrderMobile', ['event_id' => $event->id]); ?>" method="post" id="mobile-payment-form">
    <div class="form-row">
        <label for="payment-method">
            @lang("Public_ViewEvent.select_payment_method")
        </label>
        <div class="payment-methods">
            <button type="button" class="payment-method-btn" data-method="mpesa" onclick="selectPaymentMethod('mpesa')">
                <img src="/images/mpesa-logo.png" alt="M-Pesa" style="height: 30px;">
                M-Pesa
            </button>
            <button type="button" class="payment-method-btn" data-method="tigopesa" onclick="selectPaymentMethod('tigopesa')">
                <img src="/images/tigopesa-logo.png" alt="Tigo Pesa" style="height: 30px;">
                Tigo Pesa
            </button>
            <button type="button" class="payment-method-btn" data-method="airtelmoney" onclick="selectPaymentMethod('airtelmoney')">
                <img src="/images/airtel-logo.png" alt="Airtel Money" style="height: 30px;">
                Airtel Money
            </button>
        </div>
        <input type="hidden" name="payment_method" id="payment_method" required>
    </div>

    <div class="form-row">
        <label for="mobile_number">
            @lang("Public_ViewEvent.mobile_phone_number")
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
        <div id="mobile-errors" role="alert"></div>
    </div>

    {!! Form::token() !!}
    <input class="btn btn-lg btn-success mobile-submit" 
           style="width:100%;" 
           type="submit" 
           value="@lang('Public_ViewEvent.complete_payment')" 
           disabled>
</form>

<script type="text/javascript">
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

    let selectedMethod = null;
    
    function selectPaymentMethod(method) {
        document.querySelectorAll('.payment-method-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        
        document.querySelector(`[data-method="${method}"]`).classList.add('active');
        document.getElementById('payment_method').value = method;
        selectedMethod = method;
        
        validateFormTwo();
        updateMobilePlaceholder(method);
    }
    
    function updateMobilePlaceholder(method) {
        const mobileInput = document.getElementById('mobile_number');
        const placeholders = {
            'mpesa': '754123456 (Vodacom/M-Pesa)',
            'tigopesa': '652123456 (Tigo)',
            'airtelmoney': '782123456 (Airtel)'
        };
        if (mobileInput && placeholders[method]) {
            mobileInput.placeholder = placeholders[method];
        }
    }
    
    function validateFormTwo() {
        const mobileInput = document.getElementById('mobile_number');
        const mobileNumber = mobileInput.value.trim();
        const submitBtn = document.querySelector('.mobile-submit');
        const mobileErrors = document.getElementById('mobile-errors');
        
        let isValid = true;
        
        if (!selectedMethod) {
            isValid = false;
        }
        
        if (!mobileNumber) {
            mobileErrors.textContent = 'Mobile number is required';
            mobileInput.classList.add('invalid');
            isValid = false;
        } else if (!validateMobileNumber(mobileNumber)) {
            mobileErrors.textContent = 'Please enter a valid Tanzania mobile number (7XXXXXXXX or 6XXXXXXXX)';
            mobileInput.classList.add('invalid');
            isValid = false;
        } else {
            mobileErrors.textContent = '';
            mobileInput.classList.remove('invalid');
        }
        
        submitBtn.disabled = !isValid;
        return isValid;
    }
    
    // Add event listeners when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        const mobileInput = document.getElementById('mobile_number');
        
        if (mobileInput) {
            // Auto-format as user types
            mobileInput.addEventListener('input', function() {
                const cursorPosition = this.selectionStart;
                const oldLength = this.value.length;
                
                const formattedNumber = formatMobileNumber(this.value);
                this.value = formattedNumber;
                
                const newLength = this.value.length;
                const newCursorPosition = cursorPosition + (newLength - oldLength);
                this.setSelectionRange(newCursorPosition, newCursorPosition);
                
                validateFormTwo();
            });
            
            mobileInput.addEventListener('blur', validateFormTwo);
            
            // Handle paste events
            mobileInput.addEventListener('paste', function(e) {
                setTimeout(() => {
                    this.value = formatMobileNumber(this.value);
                    validateFormTwo();
                }, 10);
            });
        }
        
        // Form submission handler
        const form = document.getElementById('mobile-payment-form');
        if (form) {
            form.addEventListener('submit', function(e) {
                if (!validateFormTwo()) {
                    e.preventDefault();
                    
                    if (!selectedMethod) {
                        alert('Please select a payment method');
                    } else {
                        alert('Please enter a valid Tanzania mobile number (7XXXXXXXX or 6XXXXXXXX)');
                    }
                    return false;
                }
                
                if (mobileInput) {
                    const finalNumber = formatMobileNumber(mobileInput.value);
                    if (!validateMobileNumber(finalNumber)) {
                        e.preventDefault();
                        alert('Invalid mobile number format. Please use: 7XXXXXXXX or 6XXXXXXXX');
                        return false;
                    }
                    mobileInput.value = finalNumber;
                }
                
                const submitBtn = document.querySelector('.mobile-submit');
                if (submitBtn) {
                    submitBtn.value = 'Processing Payment...';
                    submitBtn.disabled = true;
                }
            });
        }
    });
</script>

<style type="text/css">
    .mobile-payment-form .form-row {
        margin-bottom: 20px;
    }
    
    .mobile-payment-form label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: #32325d;
        font-size: 14px;
    }
    
    .payment-methods {
        display: flex;
        gap: 10px;
        margin-bottom: 15px;
    }
    
    .payment-method-btn {
        flex: 1;
        padding: 12px 8px;
        border: 2px solid #e0e0e0;
        border-radius: 6px;
        background: white;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 5px;
        font-size: 12px;
        font-weight: 500;
    }
    
    .payment-method-btn:hover {
        border-color: #4f8ef7;
        transform: translateY(-1px);
    }
    
    .payment-method-btn.active {
        border-color: #28a745;
        background-color: #f8fff9;
        box-shadow: 0 2px 8px rgba(40, 167, 69, 0.2);
    }
    
    .form-control {
        width: 100%;
        height: 40px;
        padding: 10px 12px;
        border: 1px solid #e0e0e0;
        border-radius: 4px;
        background-color: white;
        box-shadow: 0 1px 3px 0 #e6ebf1;
        transition: box-shadow 150ms ease, border-color 150ms ease;
        font-size: 16px;
        box-sizing: border-box;
    }
    
    .form-control:focus {
        outline: none;
        box-shadow: 0 1px 3px 0 #cfd7df;
        border-color: #4f8ef7;
    }
    
    .form-control.invalid {
        border-color: #fa755a;
    }
    
    .mobile-submit:disabled {
        background-color: #6c757d !important;
        border-color: #6c757d !important;
        cursor: not-allowed;
    }
    
    .help-block {
        color: #6c757d;
        font-size: 12px;
        margin-top: 5px;
    }
    
    #mobile-errors {
        color: #fa755a;
        font-size: 12px;
        margin-top: 5px;
    }
    
    @media (max-width: 768px) {
        .payment-methods {
            flex-direction: column;
        }
        
        .payment-method-btn {
            padding: 10px;
        }
    }
</style>