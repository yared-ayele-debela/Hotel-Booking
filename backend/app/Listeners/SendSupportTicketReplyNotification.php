<?php

namespace App\Listeners;

use App\Events\SupportTicketReplyCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendSupportTicketReplyNotification implements ShouldQueue
{
    public function handle(SupportTicketReplyCreated $event): void
    {
        $reply = $event->reply;
        $ticket = $reply->supportTicket;
        $recipient = $ticket->user;
        if (! $recipient || ! $recipient->email) {
            return;
        }
        $replierName = $reply->user->name ?? $reply->user->email ?? 'Support';

        try {
            Mail::raw(
                "Hello {$recipient->name},\n\nA new reply was added to your support ticket #{$ticket->id} ({$ticket->subject}):\n\n---\n{$reply->body}\n---\n\nReplied by: {$replierName}\n\nYou can view the ticket in your account.",
                function ($message) use ($recipient, $ticket) {
                    $message->to($recipient->email)
                        ->subject("Reply on support ticket #{$ticket->id}: {$ticket->subject}");
                }
            );
        } catch (\Throwable $e) {
            Log::warning('Support ticket reply notification failed (reply saved; email not sent).', [
                'ticket_id' => $ticket->id,
                'recipient' => $recipient->email,
                'exception' => $e->getMessage(),
            ]);
        }
    }
}
