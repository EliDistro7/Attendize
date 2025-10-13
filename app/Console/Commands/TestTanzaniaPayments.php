<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TanzaniaPaymentTestService;
use Illuminate\Support\Facades\DB;

class TestTanzaniaPayments extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'test:tanzania-payments 
                            {--network= : Specific network to test (mpesa, tigopesa, airtelmoney, mobilemoney)}
                            {--amount= : Test amount in TZS}
                            {--phone= : Phone number to test}
                            {--scenario= : Test scenario (success, insufficient_funds, timeout, invalid_phone)}
                            {--suite : Run full test suite}
                            {--stats : Show payment statistics}';

    /**
     * The console command description.
     */
    protected $description = 'Test Tanzania mobile money payment integrations';

    protected $testService;

    public function __construct()
    {
        parent::__construct();
        $this->testService = new TanzaniaPaymentTestService();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🇹🇿 Tanzania Mobile Money Payment Testing');
        $this->line('=====================================');

        // Show statistics
        if ($this->option('stats')) {
            $this->showStatistics();
            return;
        }

        // Run full test suite
        if ($this->option('suite')) {
            $this->runFullTestSuite();
            return;
        }

        // Run specific test
        $this->runSpecificTest();
    }

    protected function runFullTestSuite()
    {
        $this->info('Running comprehensive Tanzania payment test suite...');
        $this->newLine();

        $results = $this->testService->runTanzaniaTestSuite();

        $this->displayResults($results);
    }

    protected function runSpecificTest()
    {
        $network = $this->option('network') ?: $this->choice(
            'Which network would you like to test?',
            ['mpesa', 'tigopesa', 'airtelmoney', 'mobilemoney'],
            'mpesa'
        );

        $amount = $this->option('amount') ?: $this->ask('Enter amount in TZS', '10000');
        $phone = $this->option('phone') ?: $this->ask('Enter phone number', '0754123456');
        $scenario = $this->option('scenario') ?: $this->choice(
            'Select test scenario',
            ['success', 'insufficient_funds', 'timeout', 'invalid_phone'],
            'success'
        );

        $this->info("Testing {$network} payment...");
        $this->line("Amount: {$amount} TZS");
        $this->line("Phone: {$phone}");
        $this->line("Scenario: {$scenario}");
        $this->newLine();

        try {
            $result = null;
            
            switch ($network) {
                case 'mpesa':
                    $result = $this->testService->testMPesaPayment($phone, $amount, $scenario);
                    break;
                case 'tigopesa':
                    $result = $this->testService->testTigoPesaPayment($phone, $amount, $scenario);
                    break;
                case 'airtelmoney':
                    $result = $this->testService->testAirtelMoneyPayment($phone, $amount, $scenario);
                    break;
                case 'mobilemoney':
                    $result = $this->testService->testMobileMoneyPayment($phone, $amount, $scenario);
                    break;
            }

            if ($result) {
                $this->displaySingleResult($result);
            }

        } catch (\Exception $e) {
            $this->error('Test failed: ' . $e->getMessage());
        }
    }

    protected function displayResults($results)
    {
        $this->table(
            ['Network', 'Scenario', 'Status', 'Transaction ID', 'Result'],
            collect($results)->map(function ($result) {
                return [
                    $result['network'],
                    $result['scenario'],
                    $result['status'],
                    $result['transaction_id'] ?? 'N/A',
                    $result['result'] ?? $result['error'] ?? 'Unknown'
                ];
            })->toArray()
        );

        // Summary
        $successful = collect($results)->where('result', 'completed')->count();
        $total = count($results);
        $successRate = $total > 0 ? round(($successful / $total) * 100, 2) : 0;

        $this->newLine();
        $this->info("Test Summary:");
        $this->line("Total Tests: {$total}");
        $this->line("Successful: {$successful}");
        $this->line("Success Rate: {$successRate}%");
    }

    protected function displaySingleResult($result)
    {
        $status = $result['status'];
        $color = $status === 'completed' ? 'info' : 'error';

        $this->{$color}("Transaction ID: {$result['transaction_id']}");
        $this->{$color}("Status: {$status}");
        $this->line("Response: {$result['response']}");
        
        if ($status === 'completed') {
            $this->info('✅ Payment test completed successfully!');
        } else {
            $this->error('❌ Payment test failed');
        }
    }

    protected function showStatistics()
    {
        $this->info('Tanzania Payment Test Statistics');
        $this->line('================================');

        try {
            $stats = $this->testService->getTanzaniaStats();

            $this->line("Total Tests Run: {$stats['total_tests']}");
            $this->newLine();

            if ($stats['by_network']->count() > 0) {
                $this->info('By Network:');
                $networkData = $stats['by_network']->map(function ($item) {
                    return [
                        'Network' => ucfirst($item->payment_method),
                        'Tests' => $item->count,
                        'Avg Amount' => number_format($item->avg_amount, 2) . ' TZS'
                    ];
                })->toArray();

                $this->table(['Network', 'Tests', 'Avg Amount'], $networkData);
            }

            if ($stats['success_rate']) {
                $this->newLine();
                $this->info('Overall Success Rate:');
                $this->line("Successful: {$stats['success_rate']->successful}/{$stats['success_rate']->total}");
                $this->line("Success Rate: {$stats['success_rate']->success_rate}%");
            }

        } catch (\Exception $e) {
            $this->error('Could not retrieve statistics: ' . $e->getMessage());
            $this->line('Make sure the payment_test_records table exists and run some tests first.');
        }
    }
}