<?php

namespace App\Services\Bookings;

class BookingService
{
    // Confirms booking, handles SetupIntent + optional deposit PaymentIntent
    public function confirm(array $data): array
    {
        // TODO: Persist booking and invoke payment flow
        return [
            'booking_id' => null,
            'deposit_captured' => false,
        ];
    }
}

