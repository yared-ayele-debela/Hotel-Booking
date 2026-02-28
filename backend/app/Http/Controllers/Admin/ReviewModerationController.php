<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewModerationController extends Controller
{
    public function index(Request $request): View
    {
        $query = Review::with(['booking.hotel', 'booking.customer', 'moderatedBy']);
        if ($request->filled('filter')) {
            match ($request->filter) {
                'pending' => $query->where('approved', false)->where('hidden', false),
                'approved' => $query->where('approved', true)->where('hidden', false),
                'rejected' => $query->where('approved', false),
                'hidden' => $query->where('hidden', true),
                default => null,
            };
        }
        $reviews = $query->latest()->paginate(15)->withQueryString();
        return view('admin.reviews.index', compact('reviews'));
    }

    public function show(Review $review): View
    {
        $review->load(['booking.hotel', 'booking.customer', 'moderatedBy']);
        return view('admin.reviews.show', compact('review'));
    }

    public function update(Request $request, Review $review): RedirectResponse
    {
        $validated = $request->validate([
            'action' => 'required|in:approve,reject,hide,unhide',
        ]);
        $data = ['moderated_at' => now(), 'moderated_by' => auth()->id()];
        switch ($validated['action']) {
            case 'approve':
                $data['approved'] = true;
                $data['hidden'] = false;
                break;
            case 'reject':
                $data['approved'] = false;
                $data['hidden'] = false;
                break;
            case 'hide':
                $data['hidden'] = true;
                break;
            case 'unhide':
                $data['hidden'] = false;
                break;
        }
        $review->update($data);
        return redirect()->route('admin.reviews.show', $review)->with('success', 'Review updated.');
    }
}
