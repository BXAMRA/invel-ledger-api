<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Document;
use App\Models\DocumentItem;
use App\Models\Payment;
use App\Models\Service;
use App\Models\ServiceBundle;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;

class BackupController extends Controller
{
    public function export()
    {
        $timestamp = now()->toIso8601String();

        Setting::updateOrCreate(['key' => 'last_export_timestamp'], ['value' => $timestamp]);

        $backup = [
            'version' => '2.0',
            'timestamp' => $timestamp,
            'users' => User::all()
                ->makeVisible(['password', 'remember_token'])
                ->toArray(),
            'customers' => Customer::all(),
            'services' => Service::all(),
            'service_bundles' => ServiceBundle::with('services')->get(),
            'documents' => Document::all(),
            'document_items' => DocumentItem::all(),
            'payments' => Payment::all(),
            'settings' => Setting::all(),
        ];

        return Response::make(json_encode($backup, JSON_PRETTY_PRINT), 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="invel-ledger-backup-'.now()->format('Y-m-d').'.json"',
        ]);
    }

    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimetypes:application/json,text/plain',
        ]);

        $data = json_decode(file_get_contents($request->file('file')->getRealPath()), true);

        if (! isset($data['version']) || $data['version'] !== '2.0') {
            return $this->error('Invalid backup format. Only standard version 2.0 backups are supported.', 400);
        }

        DB::transaction(function () use ($data) {
            DB::statement('PRAGMA foreign_keys=OFF;');

            Payment::query()->delete();
            DocumentItem::query()->delete();
            Document::query()->delete();
            DB::table('service_bundle_services')->delete();
            ServiceBundle::query()->delete();
            Service::query()->delete();
            Customer::query()->delete();
            Setting::query()->delete();
            User::query()->delete();

            if (isset($data['users'])) {
                foreach ($data['users'] as &$user) {
                    if (! isset($user['password'])) {
                        $user['password'] = Hash::make('password');
                    }
                }
                User::query()->insert($data['users']);
            }
            if (isset($data['customers'])) {
                Customer::query()->insert($data['customers']);
            }
            if (isset($data['services'])) {
                Service::query()->insert($data['services']);
            }

            if (isset($data['service_bundles'])) {
                foreach ($data['service_bundles'] as $b) {
                    $services = $b['services'] ?? [];
                    unset($b['services']);
                    $bundle = ServiceBundle::create($b);
                    if (! empty($services)) {
                        $bundle->services()->sync(array_column($services, 'id'));
                    }
                }
            }

            if (isset($data['documents'])) {
                Document::query()->insert($data['documents']);
            }
            if (isset($data['document_items'])) {
                DocumentItem::query()->insert($data['document_items']);
            }
            if (isset($data['payments'])) {
                Payment::query()->insert($data['payments']);
            }
            if (isset($data['settings'])) {
                Setting::query()->insert($data['settings']);
            }

            DB::statement('PRAGMA foreign_keys=ON;');
        });

        return $this->success(null, 'Backup restored successfully');
    }
}
