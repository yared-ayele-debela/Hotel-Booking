<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookingDispute;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DisputeController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', BookingDispute::class);
        $query = BookingDispute::with(['booking.customer', 'booking.hotel']);
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        $disputes = $query->latest()->paginate(15)->withQueryString();
        return view('admin.disputes.index', compact('disputes'));
    }

    public function show(BookingDispute $dispute): View
    {
        $this->authorize('view', $dispute);
        $dispute->load(['booking.customer', 'booking.hotel', 'resolvedBy']);
        return view('admin.disputes.show', compact('dispute'));
    }

    public function update(Request $request, BookingDispute $dispute): RedirectResponse
    {
        $this->authorize('update', $dispute);
        $validated = $request->validate([
            'status' => 'required|in:open,in_review,resolved,closed',
            'internal_notes' => 'nullable|string|max:5000',
        ]);
        if (in_array($validated['status'], ['resolved', 'closed'], true)) {
            $validated['resolved_at'] = now();
            $validated['resolved_by'] = auth()->id();
        }
        $dispute->update($validated);
        return redirect()->route('admin.disputes.show', $dispute)->with('success', 'Dispute updated.');
    }
}
