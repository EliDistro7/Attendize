<?php

use Illuminate\Database\Seeder;



class PaymentGatewaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $dummyGateway = DB::table('payment_gateways')->where('name', '=', 'Dummy')->first();

        if ($dummyGateway === null) {
            // user doesn't exist
            DB::table('payment_gateways')->insert(
                [
                    'provider_name' => 'Dummy/Test Gateway',
                    'provider_url' => 'none',
                    'is_on_site' => 1,
                    'can_refund' => 1,
                    'name' => 'Dummy',
                    'default' => 1, // Make Dummy default for testing
                    'admin_blade_template' => '',
                    'checkout_blade_template' => 'Public.ViewEvent.Partials.Dummy'
                ]
            );
        }

        $stripe = DB::table('payment_gateways')->where('name', '=', 'Stripe')->first();
        if ($stripe === null) {
            DB::table('payment_gateways')->insert(
                [
                    'name' => 'Stripe',
                    'provider_name' => 'Stripe',
                    'provider_url' => 'https://www.stripe.com',
                    'is_on_site' => 1,
                    'can_refund' => 1,
                    'default' => 0,
                    'admin_blade_template' => 'ManageAccount.Partials.Stripe',
                    'checkout_blade_template' => 'Public.ViewEvent.Partials.PaymentStripe'
                ]
            );
        }

        $stripePaymentIntents = DB::table('payment_gateways')->where('name', '=', 'Stripe\PaymentIntents')->first();
        if ($stripePaymentIntents === null) {
            DB::table('payment_gateways')->insert(
                [
                    'provider_name' => 'Stripe SCA',
                    'provider_url' => 'https://www.stripe.com',
                    'is_on_site' => 0,
                    'can_refund' => 1,
                    'name' => 'Stripe\PaymentIntents',
                    'default' => 0,
                    'admin_blade_template' => 'ManageAccount.Partials.StripeSCA',
                    'checkout_blade_template' => 'Public.ViewEvent.Partials.PaymentStripeSCA'
                ]
            );
            
        }

        // Tanzania Mobile Money Gateways
        $mpesa = DB::table('payment_gateways')->where('name', '=', 'MPesa')->first();
        if ($mpesa === null) {
            DB::table('payment_gateways')->insert([
                'provider_name' => 'M-Pesa (Vodacom)',
                'provider_url' => 'https://www.vodacom.co.tz',
                'is_on_site' => 1,
                'can_refund' => 0,
                'name' => 'MPesa',
                'default' => 0,
                'admin_blade_template' => 'ManageAccount.Partials.MPesa',
                'checkout_blade_template' => 'Public.ViewEvent.Partials.MobilePayment'
            ]);
        }

        $tigoPesa = DB::table('payment_gateways')->where('name', '=', 'TigoPesa')->first();
        if ($tigoPesa === null) {
            DB::table('payment_gateways')->insert([
                'provider_name' => 'Tigo Pesa',
                'provider_url' => 'https://www.tigo.co.tz',
                'is_on_site' => 1,
                'can_refund' => 0,
                'name' => 'TigoPesa',
                'default' => 0,
                'admin_blade_template' => 'ManageAccount.Partials.TigoPesa',
                'checkout_blade_template' => 'Public.ViewEvent.Partials.MobilePayment'
            ]);
        }

        $airtelMoney = DB::table('payment_gateways')->where('name', '=', 'AirtelMoney')->first();
        if ($airtelMoney === null) {
            DB::table('payment_gateways')->insert([
                'provider_name' => 'Airtel Money',
                'provider_url' => 'https://www.airtel.co.tz',
                'is_on_site' => 1,
                'can_refund' => 0,
                'name' => 'AirtelMoney',
                'default' => 0,
                'admin_blade_template' => 'ManageAccount.Partials.AirtelMoney',
                'checkout_blade_template' => 'Public.ViewEvent.Partials.MobilePayment'
            ]);
        }

        // Combined Mobile Payment Gateway (All-in-one)
        $mobileMoney = DB::table('payment_gateways')->where('name', '=', 'MobileMoney')->first();
        if ($mobileMoney === null) {
            DB::table('payment_gateways')->insert([
                'provider_name' => 'Mobile Money (M-Pesa, Tigo Pesa, Airtel)',
                'provider_url' => 'https://tanzania-mobile-money.com',
                'is_on_site' => 1,
                'can_refund' => 0,
                'name' => 'MobileMoney',
                'default' => 0,
                'admin_blade_template' => 'ManageAccount.Partials.MobileMoney',
                'checkout_blade_template' => 'Public.ViewEvent.Partials.MobilePayment'
            ]);
        }

        // Other East African Mobile Money Services (for future expansion)
        $mtn = DB::table('payment_gateways')->where('name', '=', 'MTNMobileMoney')->first();
        if ($mtn === null) {
            DB::table('payment_gateways')->insert([
                'provider_name' => 'MTN Mobile Money',
                'provider_url' => 'https://www.mtn.com',
                'is_on_site' => 1,
                'can_refund' => 0,
                'name' => 'MTNMobileMoney',
                'default' => 0,
                'admin_blade_template' => 'ManageAccount.Partials.MTNMobileMoney',
                'checkout_blade_template' => 'Public.ViewEvent.Partials.MobilePayment'
            ]);
        }

        // Kenya - Safaricom M-Pesa (different from Tanzania M-Pesa)
        $safaricomMpesa = DB::table('payment_gateways')->where('name', '=', 'SafaricomMPesa')->first();
        if ($safaricomMpesa === null) {
            DB::table('payment_gateways')->insert([
                'provider_name' => 'Safaricom M-Pesa (Kenya)',
                'provider_url' => 'https://www.safaricom.co.ke',
                'is_on_site' => 1,
                'can_refund' => 0,
                'name' => 'SafaricomMPesa',
                'default' => 0,
                'admin_blade_template' => 'ManageAccount.Partials.SafaricomMPesa',
                'checkout_blade_template' => 'Public.ViewEvent.Partials.MobilePayment'
            ]);
        }

        // Uganda - MTN Mobile Money
        $mtnUganda = DB::table('payment_gateways')->where('name', '=', 'MTNUganda')->first();
        if ($mtnUganda === null) {
            DB::table('payment_gateways')->insert([
                'provider_name' => 'MTN Mobile Money (Uganda)',
                'provider_url' => 'https://www.mtn.co.ug',
                'is_on_site' => 1,
                'can_refund' => 0,
                'name' => 'MTNUganda',
                'default' => 0,
                'admin_blade_template' => 'ManageAccount.Partials.MTNUganda',
                'checkout_blade_template' => 'Public.ViewEvent.Partials.MobilePayment'
            ]);
        }

        // Rwanda - MTN Mobile Money
        $mtnRwanda = DB::table('payment_gateways')->where('name', '=', 'MTNRwanda')->first();
        if ($mtnRwanda === null) {
            DB::table('payment_gateways')->insert([
                'provider_name' => 'MTN MoMo (Rwanda)',
                'provider_url' => 'https://www.mtn.rw',
                'is_on_site' => 1,
                'can_refund' => 0,
                'name' => 'MTNRwanda',
                'default' => 0,
                'admin_blade_template' => 'ManageAccount.Partials.MTNRwanda',
                'checkout_blade_template' => 'Public.ViewEvent.Partials.MobilePayment'
            ]);
        }

        // Ghana - MTN Mobile Money
        $mtnGhana = DB::table('payment_gateways')->where('name', '=', 'MTNGhana')->first();
        if ($mtnGhana === null) {
            DB::table('payment_gateways')->insert([
                'provider_name' => 'MTN Mobile Money (Ghana)',
                'provider_url' => 'https://www.mtn.com.gh',
                'is_on_site' => 1,
                'can_refund' => 0,
                'name' => 'MTNGhana',
                'default' => 0,
                'admin_blade_template' => 'ManageAccount.Partials.MTNGhana',
                'checkout_blade_template' => 'Public.ViewEvent.Partials.MobilePayment'
            ]);
        }

        // Nigeria - Different providers
        $paystack = DB::table('payment_gateways')->where('name', '=', 'Paystack')->first();
        if ($paystack === null) {
            DB::table('payment_gateways')->insert([
                'provider_name' => 'Paystack (Nigeria)',
                'provider_url' => 'https://paystack.com',
                'is_on_site' => 1,
                'can_refund' => 1,
                'name' => 'Paystack',
                'default' => 0,
                'admin_blade_template' => 'ManageAccount.Partials.Paystack',
                'checkout_blade_template' => 'Public.ViewEvent.Partials.Paystack'
            ]);
        }

        $flutterwave = DB::table('payment_gateways')->where('name', '=', 'Flutterwave')->first();
        if ($flutterwave === null) {
            DB::table('payment_gateways')->insert([
                'provider_name' => 'Flutterwave (Multi-African)',
                'provider_url' => 'https://flutterwave.com',
                'is_on_site' => 0,
                'can_refund' => 1,
                'name' => 'Flutterwave',
                'default' => 0,
                'admin_blade_template' => 'ManageAccount.Partials.Flutterwave',
                'checkout_blade_template' => 'Public.ViewEvent.Partials.Flutterwave'
            ]);
        }

    }
}