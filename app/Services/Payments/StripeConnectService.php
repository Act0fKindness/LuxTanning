<?php

namespace App\Services\Payments;

class StripeConnectService
{
    // Creates a customer SetupIntent to save card
    public function createSetupIntent(array $context = []): array
    {
        // TODO: Integrate with Stripe SDK
        return ['client_secret' => null];
    }

    // Creates a PaymentIntent (deposit / one-off)
    public function createPaymentIntent(int $amountPence, array $context = []): array
    {
        // TODO: Integrate with Stripe SDK
        return ['id' => null, 'status' => 'requires_confirmation'];
    }

    // Off-session charge on job completion (with application_fee_amount)
    public function chargeOffSession(int $amountPence, array $context = []): array
    {
        // TODO: Integrate with Stripe SDK
        return ['id' => null, 'status' => 'pending'];
    }
}

