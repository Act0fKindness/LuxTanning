<?php

namespace App\Support;

class QuoteCalculator
{
    public static function windowCleaning(int $windows, int $storeys, string $frequency, bool $frames = false, bool $sills = false, bool $gutters = false): array
    {
        $windowPrice = 2.5;
        $extrasPrice = ['frames' => 4, 'sills' => 3, 'gutters' => 25];
        $storeyModifiers = [1 => 0.00, 2 => 0.10, 3 => 0.20];
        $frequencyModifiers = ['one_off' => 0.20, 'four_week' => -0.05, 'six_week' => 0.00, 'eight_week' => 0.05];
        $firstVisitFactor = 1.3;
        $vatRate = 0.2;
        $minCallout = 15;
        $rounding = 0.5;
        $deposit = ['enabled' => true, 'percent' => 0.30, 'min' => 1000, 'max' => 5000];
        $timePerWindow = 2; // minutes
        $gutterTime = 20;

        $base = $windows * $windowPrice;
        if ($frames) $base += $extrasPrice['frames'];
        if ($sills) $base += $extrasPrice['sills'];
        if ($gutters) $base += $extrasPrice['gutters'];

        $storeyAdj = $base * ($storeyModifiers[$storeys] ?? 0);
        $freqAdj = $base * ($frequencyModifiers[$frequency] ?? 0);
        $firstAdj = $base * ($firstVisitFactor - 1);

        $exVat = $base + $storeyAdj + $freqAdj + $firstAdj;
        $exVat = max($exVat, $minCallout);
        $exVat = round($exVat / $rounding) * $rounding;
        $total = $exVat * (1 + $vatRate);
        $totalPence = (int) round($total * 100);

        $depositPence = 0;
        if ($deposit['enabled']) {
            $calc = (int) round($totalPence * $deposit['percent']);
            $depositPence = min(max($calc, $deposit['min']), $deposit['max']);
        }

        $minutes = $windows * $timePerWindow + ($gutters ? $gutterTime : 0);

        return [
            'total_pence' => $totalPence,
            'deposit_pence' => $depositPence,
            'estimate_minutes' => (int) round($minutes),
        ];
    }
}
