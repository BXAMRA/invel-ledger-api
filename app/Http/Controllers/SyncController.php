<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Document;
use App\Models\DocumentItem;
use App\Models\Payment;
use App\Models\Service;
use App\Models\ServiceBundle;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SyncController extends Controller
{
    public function sync(Request $request)
    {
        $lastSync = $request->input('last_sync');
        $changes = $request->input('changes', []);

        // 1. Gather all data updated since last_sync BEFORE applying incoming changes
        // This prevents "echoing" the data back to the client immediately
        $pullData = $this->gatherPullData($lastSync);

        // 2. Apply incoming changes
        if (! empty($changes)) {
            DB::transaction(function () use ($changes) {
                foreach ($changes as $change) {
                    $this->applyChange($change['action'] ?? '', $change['payload'] ?? []);
                }
            });
        }

        // 3. Return the pulled data
        return response()->json([
            'changes' => $pullData,
        ]);
    }

    private function gatherPullData(?string $lastSync): array
    {
        $query = function ($modelClass) use ($lastSync) {
            $q = $modelClass::withTrashed(); // Include soft deleted records
            if ($lastSync) {
                $q->where('updated_at', '>', Carbon::parse($lastSync));
            }

            return $q->get()->toArray();
        };

        // Handle service bundles specifically to include pivot data
        $bundlesQuery = ServiceBundle::withTrashed()->with('services');
        if ($lastSync) {
            $bundlesQuery->where('updated_at', '>', Carbon::parse($lastSync));
        }
        $formattedBundles = $bundlesQuery->get()->map(function ($bundle) {
            $b = $bundle->toArray();
            $b['services'] = $bundle->services->pluck('id')->toArray();

            return $b;
        })->toArray();

        return [
            'customers' => $query(Customer::class),
            'services' => $query(Service::class),
            'service_bundles' => $formattedBundles,
            'documents' => $query(Document::class),
            'document_items' => $query(DocumentItem::class),
            'payments' => $query(Payment::class),
            'settings' => Setting::query()->when($lastSync, fn($q) => $q->where('updated_at', '>', Carbon::parse($lastSync)))->get()->toArray(),
        ];
    }

    private function applyChange(string $action, array $payload)
    {
        $modelMap = [
            'upsert_customer' => Customer::class,
            'upsert_service' => Service::class,
            'upsert_service_bundle' => ServiceBundle::class,
            'upsert_document' => Document::class,
            'upsert_document_item' => DocumentItem::class,
            'upsert_payment' => Payment::class,
            'upsert_setting' => Setting::class,
        ];

        if (array_key_exists($action, $modelMap)) {
            $modelClass = $modelMap[$action];
            $id = $payload['id'] ?? null;

            // Handle service bundle relationships specifically
            $services = null;
            if ($action === 'upsert_service_bundle' && isset($payload['services'])) {
                $services = $payload['services'];
                unset($payload['services']);
            }

            $hasSoftDeletes = in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive($modelClass));

            $query = $hasSoftDeletes ? $modelClass::withTrashed() : $modelClass::query();

            // Handle Settings specifically which might not have UUID 'id'
            if ($action === 'upsert_setting' && isset($payload['key'])) {
                $model = $query->updateOrCreate(
                    ['key' => $payload['key']],
                    $payload
                );
            } else {
                $model = $query->updateOrCreate(
                    ['id' => $id],
                    $payload
                );
            }

            // Sync pivot if needed
            if ($action === 'upsert_service_bundle' && is_array($services)) {
                // Determine if services array contains objects or strings
                $serviceIds = [];
                foreach ($services as $service) {
                    $serviceIds[] = is_array($service) ? $service['id'] : $service;
                }
                $model->services()->sync($serviceIds);
            }

            // Restore if it was soft deleted but now upserted
            if ($hasSoftDeletes && $model->trashed() && ! isset($payload['deleted_at'])) {
                $model->restore();
            }

        } elseif (str_starts_with($action, 'delete_')) {
            $target = str_replace('delete_', 'upsert_', $action);
            if (array_key_exists($target, $modelMap)) {
                $modelClass = $modelMap[$target];
                $modelClass::where('id', $payload['id'])->delete();
            }
        }
    }
}
