<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CompanyWaitlist;

class CompanyWaitlistController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'company_name'   => 'required|string|max:255',
            'contact_name'   => 'nullable|string|max:255',
            'email'          => 'required|email|max:255',
            'phone'          => 'nullable|string|max:255',
            'website'        => 'nullable|string|max:255',
            'team_size'      => 'nullable|string|max:255',
            'service_area'   => 'nullable|string|max:255',
            'domain_request' => 'nullable|string|max:255',
            'notes'          => 'nullable|string',
        ]);

        // Upsert by email+company to avoid excessive duplicates
        CompanyWaitlist::create($data);

        return redirect('/register')->with('status', 'Thanks! You\'re on the waiting list. We\'ll be in touch shortly.');
    }
}

