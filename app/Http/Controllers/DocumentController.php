<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDocumentRequest;
use App\Http\Requests\UpdateDocumentRequest;
use App\Models\Document;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Document::query()->with('customer');

        if ($request->has('type')) {
            $query->where('type', $request->query('type'));
        }

        if ($request->has('issue_date')) {
            $query->whereDate('issue_date', Carbon::parse($request->query('issue_date'))->toDateString());
        }

        if ($request->has('search')) {
            $search = $request->query('search');
            $query->where('document_number', 'like', "%{$search}%")->orWhereHas('customer', function ($q) use ($search) {
                $q->where('company_name', 'like', "%{$search}%");
            });
        }

        if ($request->has('status')) {
            $status = $request->query('status');
            if ($status === 'all') {
                $query->whereNotIn('status', ['cancelled', 'paid']);
            } else {
                $query->where('status', $status);
            }
        }

        if ($request->has('sort_by')) {
            $sortBy = $request->query('sort_by');
            $sortDir = $request->query('sort_dir', 'desc');

            if ($sortBy === 'customer') {
                $query->join('customers', 'documents.customer_id', '=', 'customers.id')
                    ->select('documents.*')
                    ->orderBy('customers.company_name', $sortDir);
            } elseif (in_array($sortBy, ['issue_date', 'due_date'])) {
                $query->orderBy($sortBy, $sortDir);
            } else {
                $query->latest();
            }
        } else {
            $query->latest();
        }

        $documents = $query->paginate($request->query('per_page', 15));

        return $this->success($documents);
    }

    public function store(StoreDocumentRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $document = DB::transaction(function () use ($validated) {
            $documentNumber = $validated['document_number'] ?? null;
            if (! $documentNumber) {
                $prefix = 'INV';
                $digits = 4;

                if ($validated['type'] === 'invoice') {
                    $prefixSetting = Setting::where('key', 'invoice.prefix')->value('value');
                    if ($prefixSetting) {
                        $prefix = json_decode($prefixSetting, true) ?? $prefixSetting;
                    }
                    $digitsSetting = Setting::where('key', 'invoice.digits')->value('value');
                    if ($digitsSetting) {
                        $digits = (int) (json_decode($digitsSetting, true) ?? $digitsSetting);
                    }
                } elseif ($validated['type'] === 'quote' || $validated['type'] === 'quotation') {
                    $prefix = 'QT';
                }

                $count = Document::query()
                    ->where('type', $validated['type'])
                    ->whereDate('issue_date', Carbon::parse($validated['issue_date'])->toDateString())
                    ->count() + 1;
                $issueDateParsed = Carbon::parse($validated['issue_date'])->format('Ymd');
                $documentNumber = $prefix.'-'.$issueDateParsed.'-'.str_pad($count, $digits, '0', STR_PAD_LEFT);
            }

            $doc = Document::query()->create([
                'customer_id' => $validated['customer_id'],
                'type' => $validated['type'],
                'document_number' => $documentNumber,
                'issue_date' => $validated['issue_date'],
                'due_date' => $validated['due_date'] ?? null,
                'discount_flat' => $validated['discount_flat'] ?? 0,
                'discount_percentage' => $validated['discount_percentage'] ?? 0,
                'notes' => $validated['notes'] ?? null,
                'status' => $validated['status'] ?? 'draft',
                'subtotal' => 0,
                'tax_total' => 0,
                'grand_total' => 0,
                'paid_total' => 0,
                'balance' => 0,
            ]);

            $subtotal = 0;
            $taxTotal = 0;

            foreach ($validated['items'] as $itemData) {
                $unitPrice = $itemData['unit_price'] ?? 0;
                $itemTotalBase = $unitPrice;

                $discFlat = $itemData['discount_flat'] ?? 0;
                $discPerc = $itemData['discount_percentage'] ?? 0;

                $discountAmount = $discFlat + $itemTotalBase * ($discPerc / 100);
                $afterDiscount = $itemTotalBase - $discountAmount;
                if ($afterDiscount < 0) {
                    $afterDiscount = 0;
                }

                $taxAmount = $afterDiscount * (($itemData['tax_rate'] ?? 0) / 100);
                $finalTotal = $afterDiscount + $taxAmount;

                $doc->items()->create([
                    'service_id' => $itemData['service_id'] ?? null,
                    'name' => $itemData['name'],
                    'description' => $itemData['description'] ?? null,
                    'deliverables' => $itemData['deliverables'] ?? null,
                    'deliverables_heading' => $itemData['deliverables_heading'] ?? null,
                    'unit_price' => $unitPrice,
                    'discount_flat' => $discFlat,
                    'discount_percentage' => $discPerc,
                    'tax_rate' => $itemData['tax_rate'] ?? 0,
                    'total' => $finalTotal,
                    'pricing_type' => $itemData['pricing_type'] ?? 'standard',
                ]);

                $subtotal += $afterDiscount;
                $taxTotal += $taxAmount;
            }

            // Apply document level discount
            $docDiscount = $doc->discount_flat + $subtotal * ($doc->discount_percentage / 100);
            $finalSubtotal = $subtotal - $docDiscount;
            if ($finalSubtotal < 0) {
                $finalSubtotal = 0;
            }

            $grandTotal = $finalSubtotal + $taxTotal;

            $doc->update([
                'subtotal' => $subtotal, // Base sum of items after their individual discounts
                'tax_total' => $taxTotal,
                'grand_total' => $grandTotal,
                'balance' => $grandTotal,
            ]);

            return $doc;
        });

        $document->load('items', 'customer');

        return $this->success($document, 'Document created successfully', 201);
    }

    public function show(Document $document): JsonResponse
    {
        $document->load('items', 'customer', 'payments');

        return $this->success($document);
    }

    public function update(UpdateDocumentRequest $request, Document $document): JsonResponse
    {
        // For simplicity, document updates (like adding/removing items) often require
        // deleting old items and recreating them to ensure accurate calculations.
        $validated = $request->validated();

        DB::transaction(function () use ($validated, $document) {
            $document->update([
                'document_number' => $validated['document_number'] ?? $document->document_number,
                'customer_id' => $validated['customer_id'] ?? $document->customer_id,
                'type' => $validated['type'] ?? $document->type,
                'issue_date' => $validated['issue_date'] ?? $document->issue_date,
                'due_date' => array_key_exists('due_date', $validated) ? $validated['due_date'] : $document->due_date,
                'discount_flat' => $validated['discount_flat'] ?? $document->discount_flat,
                'discount_percentage' => $validated['discount_percentage'] ?? $document->discount_percentage,
                'notes' => array_key_exists('notes', $validated) ? $validated['notes'] : $document->notes,
                'status' => array_key_exists('status', $validated) ? $validated['status'] : $document->status,
            ]);

            if (isset($validated['items'])) {
                $submittedIds = collect($validated['items'])->pluck('id')->filter()->values();

                // Delete items that are no longer in the payload
                $document->items()->whereNotIn('id', $submittedIds)->delete();

                $subtotal = 0;
                $taxTotal = 0;

                foreach ($validated['items'] as $itemData) {
                    $unitPrice = $itemData['unit_price'] ?? 0;

                    $discFlat = $itemData['discount_flat'] ?? 0;
                    $discPerc = $itemData['discount_percentage'] ?? 0;

                    $discountAmount = $discFlat + $unitPrice * ($discPerc / 100);
                    $afterDiscount = max(0, $unitPrice - $discountAmount);

                    $taxAmount = $afterDiscount * (($itemData['tax_rate'] ?? 0) / 100);
                    $finalTotal = $afterDiscount + $taxAmount;

                    $attributes = [
                        'service_id' => $itemData['service_id'] ?? null,
                        'name' => $itemData['name'],
                        'description' => $itemData['description'] ?? null,
                        'deliverables' => $itemData['deliverables'] ?? null,
                        'deliverables_heading' => $itemData['deliverables_heading'] ?? null,
                        'unit_price' => $unitPrice,
                        'discount_flat' => $discFlat,
                        'discount_percentage' => $discPerc,
                        'tax_rate' => $itemData['tax_rate'] ?? 0,
                        'total' => $finalTotal,
                        'pricing_type' => $itemData['pricing_type'] ?? 'standard',
                    ];

                    if (! empty($itemData['id'])) {
                        // Update in-place — preserves the existing row ID
                        $document->items()->where('id', $itemData['id'])->update($attributes);
                    } else {
                        // New item — insert a fresh row
                        $document->items()->create($attributes);
                    }

                    $subtotal += $afterDiscount;
                    $taxTotal += $taxAmount;
                }

                $docDiscount = $document->discount_flat + $subtotal * ($document->discount_percentage / 100);
                $finalSubtotal = $subtotal - $docDiscount;
                if ($finalSubtotal < 0) {
                    $finalSubtotal = 0;
                }

                $grandTotal = $finalSubtotal + $taxTotal;

                $balance = $grandTotal - $document->paid_total;
                if ($balance < 0) {
                    $balance = 0;
                }

                $status = $document->status;
                if ($document->type === 'invoice') {
                    if ($balance <= 0 && $grandTotal > 0) {
                        $status = 'paid';
                    } elseif ($document->paid_total > 0 && $balance > 0) {
                        $status = 'partially_paid';
                    } elseif ($document->paid_total == 0 && in_array($status, ['paid', 'partially_paid'])) {
                        $status = 'sent'; // Downgrade if payments were removed (though usually payments aren't removed here, just items added)
                    }
                }

                $document->update([
                    'subtotal' => $subtotal,
                    'tax_total' => $taxTotal,
                    'grand_total' => $grandTotal,
                    'balance' => $balance,
                    'status' => $status,
                ]);
            }
        });

        $document->load('items', 'customer');

        return $this->success($document, 'Document updated successfully');
    }

    public function destroy(Document $document): JsonResponse
    {
        DB::transaction(function () use ($document) {
            $document->items()->delete();
            $document->payments()->delete();
            $document->delete();
        });

        return $this->success(null, 'Document deleted successfully');
    }

    public function uploadAttachment(Request $request, Document $document): JsonResponse
    {
        $request->validate([
            'label' => ['required', 'string', 'max:255'],
            'file' => ['required', 'file', 'max:51200'], // max 50MB
        ]);

        $file = $request->file('file');
        $label = $request->input('label');

        if ($label === 'Invoice') {
            $filename = $document->document_number.'.pdf';
        } else {
            $extension = $file->getClientOriginalExtension();
            $filename = Str::slug($label).'_'.time().'.'.$extension;
        }

        $path = $file->storeAs('documents', $filename, 'local');

        $attachments = $document->attachments ?? [];

        $existingIndex = collect($attachments)->search(fn ($att) => $att['label'] === $label);
        if ($existingIndex !== false) {
            Storage::disk('local')->delete($attachments[$existingIndex]['path']);
            $attachments[$existingIndex]['path'] = $path;
        } else {
            $attachments[] = ['label' => $label, 'path' => $path];
        }

        $document->update(['attachments' => $attachments]);

        return $this->success($document, 'Attachment uploaded successfully');
    }

    public function downloadAttachment(Document $document, int $index)
    {
        $attachments = $document->attachments ?? [];
        if (! isset($attachments[$index])) {
            return response()->json(['message' => 'Attachment not found'], 404);
        }
        $path = $attachments[$index]['path'];
        if (! Storage::disk('local')->exists($path)) {
            return response()->json(['message' => 'File not found'], 404);
        }

        return Storage::disk('local')->download($path);
    }

    public function deleteAttachment(Document $document, int $index): JsonResponse
    {
        $attachments = $document->attachments ?? [];
        if (! isset($attachments[$index])) {
            return response()->json(['message' => 'Attachment not found'], 404);
        }
        $path = $attachments[$index]['path'];
        Storage::disk('local')->delete($path);

        array_splice($attachments, $index, 1);
        $document->update(['attachments' => $attachments]);

        return $this->success($document, 'Attachment deleted successfully');
    }
}
