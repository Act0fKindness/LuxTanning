<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Support\JobPresenter;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PublicTrackingController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $defaults = [
            'booking_number' => trim((string) $request->query('booking_number', '')),
            'customer_name' => trim((string) $request->query('customer_name', '')),
            'postal_code' => trim((string) $request->query('postal_code', '')),
        ];

        $hasQuery = collect($defaults)->filter(fn ($value) => $value !== '')->isNotEmpty();
        $form = $defaults;
        $result = null;
        $message = null;

        if ($hasQuery) {
            $form = $request->validate([
                'booking_number' => ['required', 'string', 'max:60'],
                'customer_name' => ['required', 'string', 'max:120'],
                'postal_code' => ['required', 'string', 'max:32'],
            ]);

            [$result, $message] = $this->lookupBooking($form);
        }

        return Inertia::render('Public/TrackBooking', [
            'form' => $form,
            'result' => $result,
            'hasSearched' => $hasQuery,
            'notFoundMessage' => $message,
            'googleMapsKey' => config('services.google.maps_key'),
        ]);
    }

    private function lookupBooking(array $filters): array
    {
        $booking = Booking::query()
            ->with(['tenant:id,name', 'customer:id,name', 'address'])
            ->whereRaw('LOWER(booking_number) = ?', [Str::lower($filters['booking_number'])])
            ->whereHas('customer', function ($query) use ($filters) {
                $name = Str::lower($filters['customer_name']);
                $query->whereRaw('LOWER(name) LIKE ?', ['%' . $name . '%']);
            })
            ->whereHas('address', function ($query) use ($filters) {
                $postcode = $this->normalizedPostcode($filters['postal_code']);
                $query->whereRaw("REPLACE(LOWER(postcode), ' ', '') = ?", [$postcode]);
            })
            ->first();

        if (!$booking) {
            return [null, 'We could not find a booking that matches those details.'];
        }

        $job = $booking->jobs()
            ->with('staff')
            ->orderByRaw("CASE WHEN status IN ('scheduled','en_route','arrived','started') THEN 0 WHEN status = 'completed' THEN 1 ELSE 2 END")
            ->orderBy('date')
            ->orderBy('sequence')
            ->first();

        $jobPayload = $job ? JobPresenter::make($job, ['address' => $booking->address]) : null;

        return [$this->buildPayload($booking, $jobPayload), null];
    }

    private function buildPayload(Booking $booking, ?array $job): array
    {
        $address = $booking->address;
        $jobData = is_array($job) ? $job : [];
        $destination = $jobData['location'] ?? null;

        if (!$destination && $address) {
            $destination = [
                'lat' => $address->lat ? (float) $address->lat : null,
                'lng' => $address->lng ? (float) $address->lng : null,
                'label' => $address->line1,
            ];
        }

        if ($destination) {
            $destination['label'] = $destination['label'] ?? ($address->line1 ?? 'Home');
            $destination['detail'] = trim(collect([$address->city, $address->postcode])->filter()->join(', '));
        }

        return [
            'bookingNumber' => $booking->booking_number,
            'customer' => [
                'name' => $booking->customer?->name,
                'address' => [
                    'line1' => $address->line1 ?? null,
                    'city' => $address->city ?? null,
                    'postcode' => $address->postcode ?? null,
                ],
            ],
            'tenant' => [
                'name' => $booking->tenant?->name ?? 'Your cleaners',
            ],
            'job' => $jobData,
            'status' => $jobData['status'] ?? null,
            'statusLabel' => $jobData['status_label'] ?? null,
            'etaWindow' => $jobData['eta_window'] ?? null,
            'window' => [
                'start' => $jobData['start_time'] ?? null,
                'end' => $jobData['end_time'] ?? null,
                'date' => $jobData['date'] ?? null,
            ],
            'map' => [
                'destination' => $destination,
                'latestPing' => $jobData['last_location'] ?? null,
            ],
            'timeline' => $this->buildTimeline($jobData),
        ];
    }

    private function buildTimeline(?array $job): array
    {
        $phases = [
            'scheduled' => 'Scheduled',
            'en_route' => 'Cleaner en-route',
            'arrived' => 'Arrived on street',
            'started' => 'Cleaning in progress',
            'completed' => 'Finished',
        ];

        $current = $job['status'] ?? 'scheduled';
        $recorded = $job['last_location']['recorded_at'] ?? null;

        $events = [];
        foreach ($phases as $key => $label) {
            $events[] = [
                'key' => $key,
                'label' => $label,
                'active' => $this->phaseIsActive($current, $key),
                'complete' => $this->phaseIsComplete($current, $key),
                'timestamp' => $key === 'en_route' ? $recorded : null,
            ];
        }

        return $events;
    }

    private function phaseIsActive(?string $current, string $candidate): bool
    {
        if (!$current) {
            return $candidate === 'scheduled';
        }

        $order = array_flip(['scheduled','en_route','arrived','started','completed']);
        if (!isset($order[$current], $order[$candidate])) {
            return $candidate === 'scheduled';
        }

        return $order[$current] === $order[$candidate];
    }

    private function phaseIsComplete(?string $current, string $candidate): bool
    {
        $order = array_flip(['scheduled','en_route','arrived','started','completed']);
        if (!$current || !isset($order[$current], $order[$candidate])) {
            return false;
        }

        return $order[$current] > $order[$candidate];
    }

    private function normalizedPostcode(string $value): string
    {
        return preg_replace('/\s+/', '', Str::lower($value));
    }
}
