<?php

namespace App\Services\Quotes;

class QuoteService
{
    // Computes price based on matrix rules (service/property/extras/first-visit factor)
    public function compute(array $payload): array
    {
        // TODO: Implement matrix evaluation logic
        return [
            'net_pence' => 0,
            'vat_pence' => 0,
            'gross_pence' => 0,
            'breakdown' => [],
        ];
    }
}

