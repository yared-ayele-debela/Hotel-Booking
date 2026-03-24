<?php

namespace App\Http\Controllers\Admin\Vendor;

use App\Http\Controllers\Controller;
use App\Models\VendorBankAccount;
use App\Models\VendorProfile;
use App\Services\VendorBusinessDocumentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProfileController extends Controller
{
    public function __construct(
        protected VendorBusinessDocumentService $documents
    ) {}

    public function edit(): View
    {
        $user = auth()->user();
        $profile = $user->vendorProfile ?? VendorProfile::create([
            'user_id' => $user->id,
            'status' => VendorProfile::STATUS_APPROVED,
        ]);
        $bankAccounts = $user->bankAccounts;
        $businessDocuments = $this->documents->list($profile);

        return view('admin.vendor.profile.edit', compact('profile', 'bankAccounts', 'businessDocuments'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'business_name' => 'nullable|string|max:255',
            'business_address' => 'nullable|string|max:500',
            'business_phone' => 'nullable|string|max:50',
            'business_website' => 'nullable|string|max:255',
            'tax_id' => 'nullable|string|max:100',
            'business_details' => 'nullable|string|max:2000',
        ]);

        $profile = auth()->user()->vendorProfile ?? VendorProfile::create([
            'user_id' => auth()->id(),
            'status' => VendorProfile::STATUS_APPROVED,
        ]);

        $profile->update($validated);

        return redirect()->route('admin.vendor.profile.edit')
            ->with('success', 'Business details updated.');
    }

    public function storeBankAccount(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'account_holder_name' => 'required|string|max:255',
            'bank_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:50',
            'routing_number' => 'nullable|string|max:50',
            'swift_code' => 'nullable|string|max:20',
            'currency' => 'nullable|string|size:3',
            'is_default' => 'nullable|boolean',
        ]);

        $vendorId = auth()->id();
        $validated['vendor_id'] = $vendorId;
        $validated['currency'] = $validated['currency'] ?? 'USD';
        $validated['is_default'] = (bool) ($validated['is_default'] ?? false);

        if ($validated['is_default']) {
            VendorBankAccount::where('vendor_id', $vendorId)->update(['is_default' => false]);
        }

        $maxOrder = VendorBankAccount::where('vendor_id', $vendorId)->max('sort_order') ?? 0;
        $validated['sort_order'] = $maxOrder + 1;

        VendorBankAccount::create($validated);

        return redirect()->route('admin.vendor.profile.edit')
            ->with('success', 'Bank account added.');
    }

    public function updateBankAccount(Request $request, VendorBankAccount $bankAccount): RedirectResponse
    {
        if ($bankAccount->vendor_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'account_holder_name' => 'required|string|max:255',
            'bank_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:50',
            'routing_number' => 'nullable|string|max:50',
            'swift_code' => 'nullable|string|max:20',
            'currency' => 'nullable|string|size:3',
            'is_default' => 'nullable|boolean',
        ]);

        $validated['currency'] = $validated['currency'] ?? 'USD';
        $validated['is_default'] = (bool) ($validated['is_default'] ?? false);

        if ($validated['is_default']) {
            VendorBankAccount::where('vendor_id', auth()->id())->update(['is_default' => false]);
        }

        $bankAccount->update($validated);

        return redirect()->route('admin.vendor.profile.edit')
            ->with('success', 'Bank account updated.');
    }

    public function destroyBankAccount(VendorBankAccount $bankAccount): RedirectResponse
    {
        if ($bankAccount->vendor_id !== auth()->id()) {
            abort(403);
        }

        $bankAccount->delete();

        return redirect()->route('admin.vendor.profile.edit')
            ->with('success', 'Bank account removed.');
    }

    public function storeDocuments(Request $request): RedirectResponse
    {
        $request->validate([
            'document_files' => 'required|array|min:1|max:10',
            'document_files.*' => 'file|max:'.VendorBusinessDocumentService::MAX_FILE_KB.'|mimes:pdf,jpg,jpeg,png,webp,doc,docx',
        ]);

        $profile = auth()->user()->vendorProfile ?? VendorProfile::create([
            'user_id' => auth()->id(),
            'status' => VendorProfile::STATUS_APPROVED,
        ]);

        $files = $request->file('document_files', []);
        $existing = count($this->documents->list($profile));
        if ($existing + count($files) > VendorBusinessDocumentService::MAX_FILES) {
            return redirect()->route('admin.vendor.profile.edit')
                ->withErrors(['document_files' => 'You can upload at most '.VendorBusinessDocumentService::MAX_FILES.' files in total.'])
                ->withInput();
        }

        $this->documents->append($profile, $files);

        return redirect()->route('admin.vendor.profile.edit')
            ->with('success', count($files).' file(s) uploaded.');
    }

    public function destroyDocument(string $documentId): RedirectResponse
    {
        $profile = auth()->user()->vendorProfile;
        if (! $profile) {
            abort(404);
        }
        if (! $this->documents->deleteById($profile, $documentId)) {
            return redirect()->route('admin.vendor.profile.edit')
                ->with('error', 'Document not found.');
        }

        return redirect()->route('admin.vendor.profile.edit')
            ->with('success', 'Document removed.');
    }

    public function downloadDocument(string $documentId): StreamedResponse|RedirectResponse
    {
        $profile = auth()->user()->vendorProfile;
        if (! $profile) {
            abort(404);
        }
        $doc = $this->documents->findById($profile, $documentId);
        if (! $doc) {
            abort(404);
        }
        $path = $doc['path'] ?? null;
        if (! $path || ! Storage::disk('public')->exists($path)) {
            abort(404);
        }

        return Storage::disk('public')->download($path, $doc['original_name'] ?? basename($path));
    }
}
