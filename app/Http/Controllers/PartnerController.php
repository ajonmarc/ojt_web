<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PartnerController extends Controller
{
    public function index()
    {
        $partners = Partner::all();
        return Inertia::render('Admin/Partner', [
            'partners' => $partners
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'partnerName' => 'required|string|max:255',
            'partnerAddress' => 'required|string',
            'partnerPhone' => 'required|string',
            'partnerEmail' => 'required|email',
            'partnerContact' => 'required|string',
            'status' => 'required|string|in:Active,Inactive',
        ]);

        Partner::create($validated);

        return redirect()->back();
    }

    public function update(Request $request, Partner $partner)
    {
        $validated = $request->validate([
            'partnerName' => 'required|string|max:255',
            'partnerAddress' => 'required|string',
            'partnerPhone' => 'required|string',
            'partnerEmail' => 'required|email',
            'partnerContact' => 'required|string',
            'status' => 'required|string|in:Active,Inactive',
        ]);

        $partner->update($validated);

        return redirect()->back();
    }

    public function destroy(Partner $partner)
    {
        $partner->delete();
        return redirect()->back();
    }
}
