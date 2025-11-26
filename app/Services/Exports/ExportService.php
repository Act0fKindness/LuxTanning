<?php

namespace App\Services\Exports;

class ExportService
{
    public function create(string $type, array $options = []): array
    {
        // TODO: Queue export job and return handle
        return ['export_id' => null, 'status' => 'pending'];
    }
}

