<?php

namespace App\Http\Controllers;

use App\Models\Benefit;
use Illuminate\Http\Request;

class BenefitController extends Controller
{
    public function create()
    {
        return view('benefit-interest'); // Ensure the view file matches this name and location.
    }

    /**
     * Store a newly created benefit in the database.
     */
    public function store(Request $request)
    {
        // Validate input
        $request->validate([
            'company_name' => 'required|string|max:255',
            'company_email' => 'email|max:255|unique:benefits',
            'company_phone' => 'nullable|string|max:15|unique:benefits',
            'number_of_staff' => 'nullable|integer',
            'address' => 'nullable|string|max:255',
        ]);

        // Save to the database
        Benefit::create([
            'company_name' => $request->company_name,
            'company_email' => $request->company_email,
            'company_phone' => $request->company_phone,
            'number_of_staff' => $request->number_of_staff,
            'address' => $request->address,
        ]);

        // Redirect with success message
        return redirect()->route('benefit.create')->with('success', 'Thank you! Your information has been submitted.');
    }
}
