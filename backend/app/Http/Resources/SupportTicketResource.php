<?php

namespace App\Http\Resources;

use App\Enums\TicketCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupportTicketResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'subject' => $this->subject,
            'body' => $this->body,
            'category' => $this->category,
            'category_label' => TicketCategory::tryFrom($this->category)?->label() ?? $this->category,
            'status' => $this->status,
            'priority' => $this->priority,
            'created_at' => $this->created_at?->toIso8601String(),
            'closed_at' => $this->closed_at?->toIso8601String(),
            'replies' => SupportTicketReplyResource::collection($this->whenLoaded('replies')),
            'replies_count' => $this->when(isset($this->replies_count), fn () => $this->replies_count),
        ];
    }
}
