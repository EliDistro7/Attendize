<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TanzaniaPaymentTestService
{
    protected $testPhoneNumbers = [
        'mpesa' => [
            'valid' => ['0754123456', '0764123456', '0774123456'],
            'invalid' => ['0712123456', '0782123456'], // Wrong network
            'insufficient_funds' => '0754999999',
            'timeout' => '0754888888'
        ],
        'tigopesa' => [
            'valid' => ['0715123456', '0784123456', '0655123456'],
            'invalid' => ['0754123456', '0764123456'], // Wrong network
            'insufficient_funds' => '0715999999',
            'timeout' => '0715888888'
        ],
        'airtelmoney' => [
            'valid' => ['0782123456', '0786123456', '0689123456'],
            'invalid' => ['0754123456', '0715123456'], // Wrong network
            'insufficient_funds' => '0782999999',
            'timeout' => '0782888888'
        ]
    ];

    protected $testAmounts = [
        'small' => 1000, // 1,000 TZS
        'medium' => 50000, // 50,000 TZS
        'large' => 500000, // 500,000 TZS
        'max' => 3000000 // 3,000,000 TZS (typical daily limit)
    ];

    /**
     * Test M-Pesa payment
     */
    public function testMPesaPayment($phoneNumber, $amount, $testScenario = 'success')
    {
        $gateway = DB::table('payment_gateways')->where('name', 'MPesa')->first();
        
        if (!$gateway) {
            throw new \Exception('M-Pesa gateway not found. Run PaymentGatewaySeeder first.');
        }

        $testData = [
            'transaction_id' => $this->generateTransactionId('MP'),
            'payment_gateway_id' => $gateway->id,
            'amount' => $amount,
            'currency' => 'TZS',
            'payment_method' => 'mpesa',
            'phone_number' => $this->formatTanzanianPhone($phoneNumber),
            'test_scenario' => $testScenario,
            'metadata' => [
                'network' => 'Vodacom',
                'country' => 'Tanzania',
                'test_type' => 'mpesa_simulation',
                'initiated_at' => now()->toISOString()
            ]
        ];

        return $this->processTestPayment($testData, 'M-Pesa');
    }

    /**
     * Test Tigo Pesa payment
     */
    public function testTigoPesaPayment($phoneNumber, $amount, $testScenario = 'success')
    {
        $gateway = DB::table('payment_gateways')->where('name', 'TigoPesa')->first();
        
        if (!$gateway) {
            throw new \Exception('Tigo Pesa gateway not found. Run PaymentGatewaySeeder first.');
        }

        $testData = [
            'transaction_id' => $this->generateTransactionId('TP'),
            'payment_gateway_id' => $gateway->id,
            'amount' => $amount,
            'currency' => 'TZS',
            'payment_method' => 'tigopesa',
            'phone_number' => $this->formatTanzanianPhone($phoneNumber),
            'test_scenario' => $testScenario,
            'metadata' => [
                'network' => 'Tigo',
                'country' => 'Tanzania',
                'test_type' => 'tigopesa_simulation',
                'initiated_at' => now()->toISOString()
            ]
        ];

        return $this->processTestPayment($testData, 'Tigo Pesa');
    }

    /**
     * Test Airtel Money payment
     */
    public function testAirtelMoneyPayment($phoneNumber, $amount, $testScenario = 'success')
    {
        $gateway = DB::table('payment_gateways')->where('name', 'AirtelMoney')->first();
        
        if (!$gateway) {
            throw new \Exception('Airtel Money gateway not found. Run PaymentGatewaySeeder first.');
        }

        $testData = [
            'transaction_id' => $this->generateTransactionId('AM'),
            'payment_gateway_id' => $gateway->id,
            'amount' => $amount,
            'currency' => 'TZS',
            'payment_method' => 'airtelmoney',
            'phone_number' => $this->formatTanzanianPhone($phoneNumber),
            'test_scenario' => $testScenario,
            'metadata' => [
                'network' => 'Airtel',
                'country' => 'Tanzania',
                'test_type' => 'airtelmoney_simulation',
                'initiated_at' => now()->toISOString()
            ]
        ];

        return $this->processTestPayment($testData, 'Airtel Money');
    }

    /**
     * Test Mobile Money combined gateway
     */
    public function testMobileMoneyPayment($phoneNumber, $amount, $testScenario = 'success')
    {
        $gateway = DB::table('payment_gateways')->where('name', 'MobileMoney')->first();
        
        if (!$gateway) {
            throw new \Exception('Mobile Money gateway not found. Run PaymentGatewaySeeder first.');
        }

        // Auto-detect network from phone number
        $network = $this->detectNetworkFromPhone($phoneNumber);

        $testData = [
            'transaction_id' => $this->generateTransactionId('MM'),
            'payment_gateway_id' => $gateway->id,
            'amount' => $amount,
            'currency' => 'TZS',
            'payment_method' => 'mobilemoney',
            'phone_number' => $this->formatTanzanianPhone($phoneNumber),
            'test_scenario' => $testScenario,
            'metadata' => [
                'detected_network' => $network,
                'country' => 'Tanzania',
                'test_type' => 'mobilemoney_combined',
                'auto_detection' => true,
                'initiated_at' => now()->toISOString()
            ]
        ];

        return $this->processTestPayment($testData, 'Mobile Money (' . $network . ')');
    }

    /**
     * Run comprehensive Tanzania payment tests
     */
    public function runTanzaniaTestSuite()
    {
        $results = [];
        
        Log::info('Starting Tanzania Payment Test Suite');

        // Test all three networks with different scenarios
        $networks = ['mpesa', 'tigopesa', 'airtelmoney'];
        $scenarios = ['success', 'insufficient_funds', 'timeout', 'invalid_phone'];
        
        foreach ($networks as $network) {
            foreach ($scenarios as $scenario) {
                $phoneNumber = $this->getTestPhoneNumber($network, $scenario);
                $amount = $this->testAmounts['medium'];
                
                try {
                    switch ($network) {
                        case 'mpesa':
                            $result = $this->testMPesaPayment($phoneNumber, $amount, $scenario);
                            break;
                        case 'tigopesa':
                            $result = $this->testTigoPesaPayment($phoneNumber, $amount, $scenario);
                            break;
                        case 'airtelmoney':
                            $result = $this->testAirtelMoneyPayment($phoneNumber, $amount, $scenario);
                            break;
                    }
                    
                    $results[] = [
                        'network' => $network,
                        'scenario' => $scenario,
                        'status' => 'completed',
                        'transaction_id' => $result['transaction_id'],
                        'result' => $result['status']
                    ];
                    
                } catch (\Exception $e) {
                    $results[] = [
                        'network' => $network,
                        'scenario' => $scenario,
                        'status' => 'error',
                        'error' => $e->getMessage()
                    ];
                }
                
                // Small delay between tests
                sleep(1);
            }
        }
        
        Log::info('Tanzania Payment Test Suite completed', ['results' => $results]);
        return $results;
    }

    /**
     * Process test payment
     */
    protected function processTestPayment($testData, $gatewayName)
    {
        // Insert test record
        $testRecordId = DB::table('payment_test_records')->insertGetId([
            'transaction_id' => $testData['transaction_id'],
            'payment_gateway_id' => $testData['payment_gateway_id'],
            'amount' => $testData['amount'],
            'currency' => $testData['currency'],
            'payment_method' => $testData['payment_method'],
            'phone_number' => $testData['phone_number'],
            'status' => 'pending',
            'metadata' => json_encode($testData['metadata']),
            'is_test' => true,
            'test_scenario' => $testData['test_scenario'],
            'initiated_at' => now(),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        Log::info("Tanzania {$gatewayName} payment test initiated", [
            'transaction_id' => $testData['transaction_id'],
            'amount' => $testData['amount'],
            'phone' => $testData['phone_number'],
            'scenario' => $testData['test_scenario']
        ]);

        // Simulate payment processing
        $result = $this->simulateTanzanianPayment($testRecordId, $testData);
        
        return $result;
    }

    /**
     * Simulate Tanzanian mobile money payment processing
     */
    protected function simulateTanzanianPayment($testRecordId, $testData)
    {
        sleep(rand(1, 3)); // Simulate processing delay

        $scenario = $testData['test_scenario'];
        $status = 'pending';
        $response = '';

        switch ($scenario) {
            case 'success':
                $status = 'completed';
                $response = 'Payment completed successfully. Reference: ' . strtoupper(substr($testData['transaction_id'], -8));
                break;
                
            case 'insufficient_funds':
                $status = 'failed';
                $response = 'Transaction failed: Insufficient balance in mobile money account';
                break;
                
            case 'timeout':
                $status = 'failed';
                $response = 'Transaction timeout: Customer did not complete payment within time limit';
                break;
                
            case 'invalid_phone':
                $status = 'failed';
                $response = 'Invalid phone number or number not registered for mobile money service';
                break;
                
            default:
                $status = 'completed';
                $response = 'Payment processed successfully';
        }

        // Update test record
        DB::table('payment_test_records')->where('id', $testRecordId)->update([
            'status' => $status,
            'provider_response' => $response,
            'completed_at' => $status === 'completed' ? now() : null,
            'failed_at' => $status === 'failed' ? now() : null,
            'updated_at' => now()
        ]);

        Log::info("Tanzania payment test {$status}", [
            'transaction_id' => $testData['transaction_id'],
            'response' => $response
        ]);

        return [
            'transaction_id' => $testData['transaction_id'],
            'status' => $status,
            'response' => $response,
            'test_record_id' => $testRecordId
        ];
    }

    /**
     * Format Tanzanian phone number
     */
    protected function formatTanzanianPhone($phone)
    {
        // Remove any non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Convert to standard format (0XXXXXXXXX)
        if (strlen($phone) === 9) {
            $phone = '0' . $phone;
        } elseif (strlen($phone) === 12 && substr($phone, 0, 3) === '255') {
            $phone = '0' . substr($phone, 3);
        }
        
        return $phone;
    }

    /**
     * Detect network from phone number
     */
    protected function detectNetworkFromPhone($phone)
    {
        $phone = $this->formatTanzanianPhone($phone);
        $prefix = substr($phone, 0, 4);
        
        // Vodacom M-Pesa prefixes
        if (in_array($prefix, ['0754', '0755', '0764', '0765', '0774', '0775'])) {
            return 'Vodacom M-Pesa';
        }
        
        // Tigo Pesa prefixes
        if (in_array($prefix, ['0715', '0784', '0655', '0656'])) {
            return 'Tigo Pesa';
        }
        
        // Airtel Money prefixes
        if (in_array($prefix, ['0782', '0786', '0789', '0689', '0679'])) {
            return 'Airtel Money';
        }
        
        return 'Unknown';
    }

    /**
     * Get test phone number for scenario
     */
    protected function getTestPhoneNumber($network, $scenario)
    {
        if (isset($this->testPhoneNumbers[$network][$scenario])) {
            $numbers = $this->testPhoneNumbers[$network][$scenario];
            return is_array($numbers) ? $numbers[0] : $numbers;
        }
        
        return $this->testPhoneNumbers[$network]['valid'][0];
    }

    /**
     * Generate transaction ID
     */
    protected function generateTransactionId($prefix)
    {
        return $prefix . '_' . time() . '_' . rand(1000, 9999);
    }

    /**
     * Get Tanzania payment statistics
     */
    public function getTanzaniaStats()
    {
        return [
            'total_tests' => DB::table('payment_test_records')
                ->whereIn('payment_method', ['mpesa', 'tigopesa', 'airtelmoney', 'mobilemoney'])
                ->count(),
            'by_network' => DB::table('payment_test_records')
                ->whereIn('payment_method', ['mpesa', 'tigopesa', 'airtelmoney', 'mobilemoney'])
                ->groupBy('payment_method')
                ->selectRaw('payment_method, count(*) as count, avg(amount) as avg_amount')
                ->get(),
            'success_rate' => DB::table('payment_test_records')
                ->whereIn('payment_method', ['mpesa', 'tigopesa', 'airtelmoney', 'mobilemoney'])
                ->selectRaw('
                    count(*) as total,
                    sum(case when status = "completed" then 1 else 0 end) as successful,
                    round(sum(case when status = "completed" then 1 else 0 end) * 100.0 / count(*), 2) as success_rate
                ')
                ->first()
        ];
    }
}