<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payout;
use App\Services\PayoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PayoutController extends Controller
{
    public function __construct(
        protected PayoutService $payoutService
    ) {}

    public function index(Request $request): View
    {
        $query = Payout::with('vendor')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }

        $payouts = $query->paginate(20)->withQueryString();
        $vendors = \App\Models\User::where('role', \App\Enums\Role::VENDOR)->orderBy('name')->get(['id', 'name', 'email']);

        return view('admin.payouts.index', compact('payouts', 'vendors'));
    }

    public function create(): View
    {
        return view('admin.payouts.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
        ]);

        $created = $this->payoutService->generateForPeriod(
            $request->period_start,
            $request->period_end
        );

        $count = count($created);
        $message = $count > 0
            ? "Generated {$count} payout(s) for the period."
            : 'No unpaid bookings found for this period.';

        return redirect()->route('admin.payouts.index')->with('success', $message);
    }

    public function show(Payout $payout): View
    {
        $payout->load(['vendor', 'bookings.hotel']);
        return view('admin.payouts.show', compact('payout'));
    }

    public function update(Request $request, Payout $payout): RedirectResponse
    {
        $request->validate([
            'status' => 'required|in:pending,processing,paid',
            'reference' => 'nullable|string|max:255',
        ]);

        $data = ['status' => $request->status];
        if ($request->filled('reference')) {
            $data['reference'] = $request->reference;
        }
        if ($request->status === 'paid') {
            $data['paid_at'] = now();
        } else {
            $data['paid_at'] = null;
        }

        $payout->update($data);

        return redirect()->route('admin.payouts.index')->with('success', 'Payout updated.');
    }

    public function markPaid(Request $request, Payout $payout): RedirectResponse
    {
        $request->validate([
            'reference' => 'nullable|string|max:255',
        ]);

        $payout->update([
            'status' => Payout::STATUS_PAID,
            'reference' => $request->reference,
            'paid_at' => now(),
        ]);

        return redirect()->route('admin.payouts.index')->with('success', 'Payout marked as paid.');
    }

    public function export(Request $request): StreamedResponse|Response
    {
        $format = $request->get('format', 'csv');
        $query = Payout::with('vendor')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }

        $payouts = $query->get();

        if ($format === 'csv') {
            return response()->streamDownload(function () use ($payouts): void {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['ID', 'Vendor', 'Email', 'Period Start', 'Period End', 'Gross', 'Commission', 'Net', 'Status', 'Reference', 'Paid At']);
                foreach ($payouts as $p) {
                    fputcsv($handle, [
                        $p->id,
                        $p->vendor->name ?? $p->vendor_id,
                        $p->vendor->email ?? '',
                        $p->period_start->format('Y-m-d'),
                        $p->period_end->format('Y-m-d'),
                        $p->amount,
                        $p->commission,
                        $p->net,
                        $p->status,
                        $p->reference ?? '',
                        $p->paid_at?->format('Y-m-d H:i') ?? '',
                    ]);
                }
                fclose($handle);
            }, 'payouts-' . now()->format('Y-m-d') . '.csv', [
                'Content-Type' => 'text/csv',
            ]);
        }

        return response('PDF export not implemented. Use CSV.', 400);
    }
}
