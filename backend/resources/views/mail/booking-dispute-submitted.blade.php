A new booking dispute was submitted.

Dispute #{{ $dispute->id }}
Status: {{ $dispute->status }}

Booking reference: {{ $booking?->uuid ?? $dispute->booking_id }}
Hotel: {{ $booking?->hotel?->name ?? '—' }}
Customer: {{ $booking?->customer?->name ?? $booking?->guest_name ?? '—' }} ({{ $booking?->customer?->email ?? $booking?->guest_email ?? '—' }})

Contact on dispute:
Name: {{ $dispute->contact_name ?? '—' }}
Email: {{ $dispute->contact_email ?? '—' }}
Phone: {{ $dispute->contact_phone ?? '—' }}

Message:
{{ $dispute->customer_notes }}

Open in admin: {{ route('admin.disputes.show', $dispute) }}
