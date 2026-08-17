<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class ExportUuidData extends Command
{
    protected $signature = 'data:export-uuids {--file=database_uuid_backup.json}';
    protected $description = 'Export database to JSON while generating UUIDs and mapping foreign keys.';

    public function handle()
    {
        $this->info('Starting data extraction and UUID mapping...');

        $tables = [
            'users' => [],
            'customers' => [],
            'services' => [],
            'service_bundles' => [],
            'service_bundle_services' => [],
            'documents' => [],
            'document_items' => [],
            'payments' => [],
            'settings' => []
        ];

        $mappings = [
            'users' => [],
            'customers' => [],
            'services' => [],
            'service_bundles' => [],
            'documents' => [],
        ];

        // 1. Extract and Map Users
        $users = DB::table('users')->get();
        foreach ($users as $user) {
            $uuid = (string) Str::uuid();
            $mappings['users'][$user->id] = $uuid;
            $user->id = $uuid;
            $tables['users'][] = (array) $user;
        }

        // 2. Extract and Map Customers
        $customers = DB::table('customers')->get();
        foreach ($customers as $customer) {
            $uuid = (string) Str::uuid();
            $mappings['customers'][$customer->id] = $uuid;
            $customer->id = $uuid;
            $tables['customers'][] = (array) $customer;
        }

        // 3. Extract and Map Services
        $services = DB::table('services')->get();
        foreach ($services as $service) {
            $uuid = (string) Str::uuid();
            $mappings['services'][$service->id] = $uuid;
            $service->id = $uuid;
            $tables['services'][] = (array) $service;
        }

        // 4. Extract and Map Service Bundles
        $bundles = DB::table('service_bundles')->get();
        foreach ($bundles as $bundle) {
            $uuid = (string) Str::uuid();
            $mappings['service_bundles'][$bundle->id] = $uuid;
            $bundle->id = $uuid;
            $tables['service_bundles'][] = (array) $bundle;
        }

        // 5. Extract and Map Service Bundle Services (Pivot)
        $bundleServices = DB::table('service_bundle_services')->get();
        foreach ($bundleServices as $pivot) {
            $uuid = (string) Str::uuid();
            $pivot->id = $uuid;
            $pivot->service_bundle_id = $mappings['service_bundles'][$pivot->service_bundle_id] ?? $pivot->service_bundle_id;
            $pivot->service_id = $mappings['services'][$pivot->service_id] ?? $pivot->service_id;
            $tables['service_bundle_services'][] = (array) $pivot;
        }

        // 6. Extract and Map Documents
        $documents = DB::table('documents')->get();
        foreach ($documents as $document) {
            $uuid = (string) Str::uuid();
            $mappings['documents'][$document->id] = $uuid;
            $document->id = $uuid;
            $document->customer_id = $mappings['customers'][$document->customer_id] ?? $document->customer_id;
            $tables['documents'][] = (array) $document;
        }

        // 7. Extract and Map Document Items
        $docItems = DB::table('document_items')->get();
        foreach ($docItems as $item) {
            $uuid = (string) Str::uuid();
            $item->id = $uuid;
            $item->document_id = $mappings['documents'][$item->document_id] ?? $item->document_id;
            $item->service_id = $item->service_id ? ($mappings['services'][$item->service_id] ?? $item->service_id) : null;
            $tables['document_items'][] = (array) $item;
        }

        // 8. Extract and Map Payments
        $payments = DB::table('payments')->get();
        foreach ($payments as $payment) {
            $uuid = (string) Str::uuid();
            $payment->id = $uuid;
            $payment->document_id = $mappings['documents'][$payment->document_id] ?? $payment->document_id;
            $tables['payments'][] = (array) $payment;
        }

        // Settings
        $settings = DB::table('settings')->get();
        $tables['settings'] = $settings->map(fn($s) => (array) $s)->toArray();

        $file = $this->option('file');
        File::put(storage_path('app/' . $file), json_encode($tables, JSON_PRETTY_PRINT));

        $this->info("Successfully exported mapped data to storage/app/{$file}");
    }
}
