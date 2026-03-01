<?php

namespace App\Http\Controllers\Admin\Vendor;

use App\Enums\TicketCategory;
use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupportTicketController extends Controller
{
    public function index(Request $request): View
    {
        $query = SupportTicket::where('user_id', $request->user()->id)
            ->withCount('replies')
            ->latest();
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        $tickets = $query->paginate(15)->withQueryString();
        return view('admin.vendor.support-tickets.index', compact('tickets'));
    }

    public function create(): View
    {
        $this->authorize('create', SupportTicket::class);
        return view('admin.vendor.support-tickets.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', SupportTicket::class);
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'body' => 'required|string|max:10000',
            'category' => 'required|in:'.implode(',', array_column(TicketCategory::cases(), 'value')),
            'priority' => 'nullable|in:low,normal,high',
        ]);
        SupportTicket::create([
            'user_id' => $request->user()->id,
            'subject' => $validated['subject'],
            'body' => $validated['body'],
            'category' => $validated['category'],
            'priority' => $validated['priority'] ?? 'normal',
            'status' => 'open',
        ]);
        return redirect()->route('admin.vendor.support-tickets.index')->with('success', 'Support ticket created.');
    }

    public function show(Request $request, SupportTicket $supportTicket): View|RedirectResponse
    {
        $this->authorize('view', $supportTicket);
        if ((int) $supportTicket->user_id !== (int) $request->user()->id) {
            abort(403);
        }
        $supportTicket->load(['replies.user']);
        return view('admin.vendor.support-tickets.show', compact('supportTicket'));
    }
}
