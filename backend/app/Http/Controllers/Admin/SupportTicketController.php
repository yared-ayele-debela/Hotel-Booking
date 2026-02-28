<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupportTicketController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', SupportTicket::class);
        $query = SupportTicket::with(['user', 'assignedTo']);
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        $tickets = $query->latest()->paginate(15)->withQueryString();
        return view('admin.support-tickets.index', compact('tickets'));
    }

    public function show(SupportTicket $supportTicket): View
    {
        $this->authorize('view', $supportTicket);
        $supportTicket->load(['user', 'assignedTo']);
        $staff = User::whereIn('role', [Role::ADMIN, Role::SUPER_ADMIN])->orderBy('name')->get();
        return view('admin.support-tickets.show', compact('supportTicket', 'staff'));
    }

    public function update(Request $request, SupportTicket $supportTicket): RedirectResponse
    {
        $this->authorize('update', $supportTicket);
        $request->merge(['assigned_to' => $request->input('assigned_to') ?: null]);
        $validated = $request->validate([
            'status' => 'required|in:open,assigned,in_progress,resolved,closed',
            'assigned_to' => 'nullable|exists:users,id',
            'priority' => 'required|in:low,normal,high',
        ]);
        $supportTicket->status = $validated['status'];
        $supportTicket->priority = $validated['priority'];
        $supportTicket->assigned_to = $validated['assigned_to'];
        if (in_array($validated['status'], ['resolved', 'closed'], true)) {
            $supportTicket->closed_at = now();
        }
        $supportTicket->save();
        return redirect()->route('admin.support-tickets.show', $supportTicket)->with('success', 'Ticket updated.');
    }
}
