<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\TicketCategory;
use App\Http\Resources\SupportTicketResource;
use App\Models\SupportTicket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupportTicketController extends BaseApiController
{
    /**
     * List own support tickets (customer or vendor). Paginated.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = SupportTicket::where('user_id', $user->id)
            ->withCount('replies')
            ->latest();
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        $perPage = min((int) $request->input('per_page', 15), 50);
        $tickets = $query->paginate($perPage);

        return $this->success([
            'data' => SupportTicketResource::collection($tickets->items()),
            'meta' => [
                'current_page' => $tickets->currentPage(),
                'last_page' => $tickets->lastPage(),
                'per_page' => $tickets->perPage(),
                'total' => $tickets->total(),
            ],
        ]);
    }

    /**
     * Create a support ticket (customer or vendor).
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', SupportTicket::class);
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'body' => 'required|string|max:10000',
            'category' => 'required|in:'.implode(',', array_column(TicketCategory::cases(), 'value')),
            'priority' => 'nullable|in:low,normal,high',
        ]);
        $ticket = SupportTicket::create([
            'user_id' => $request->user()->id,
            'subject' => $validated['subject'],
            'body' => $validated['body'],
            'category' => $validated['category'],
            'priority' => $validated['priority'] ?? 'normal',
            'status' => 'open',
        ]);
        return $this->success(new SupportTicketResource($ticket->loadCount('replies')), 201);
    }

    /**
     * Show own ticket with replies.
     */
    public function show(Request $request, SupportTicket $supportTicket): JsonResponse
    {
        $this->authorize('view', $supportTicket);
        $supportTicket->load(['replies.user']);
        return $this->success(new SupportTicketResource($supportTicket));
    }

    /**
     * Add a reply to own ticket (customer/vendor).
     */
    public function storeReply(Request $request, SupportTicket $supportTicket): JsonResponse
    {
        $this->authorize('reply', $supportTicket);
        $validated = $request->validate(['body' => 'required|string|max:10000']);
        $reply = \App\Models\SupportTicketReply::create([
            'support_ticket_id' => $supportTicket->id,
            'user_id' => $request->user()->id,
            'body' => $validated['body'],
        ]);
        $reply->load('user');
        return $this->success(new \App\Http\Resources\SupportTicketReplyResource($reply), 201);
    }
}
