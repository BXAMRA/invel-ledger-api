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
  /**
   * @return \Illuminate\Http\Response
   */
  public function export()
  {
    $timestamp = now()->toIso8601String();

    Setting::query()->updateOrCreate(["key" => "last_export_timestamp"], ["value" => $timestamp]);

    $backup = [
      "version" => "2.1",
      "timestamp" => $timestamp,
      "users" => User::all()
        ->makeVisible(["password", "remember_token"])
        ->toArray(),
      "customers" => Customer::all(),
      "services" => Service::all(),
      "service_bundles" => ServiceBundle::with("services")->get(),
      "documents" => Document::all(),
      "document_items" => DocumentItem::all(),
      "payments" => Payment::all(),
      "settings" => Setting::all(),
    ];

    return Response::make(json_encode($backup, JSON_PRETTY_PRINT), 200, [
      "Content-Type" => "application/json",
      "Content-Disposition" => 'attachment; filename="invel-ledger-backup-' . now()->format("Y-m-d") . '.json"',
    ]);
  }

  /**
   * @param \Illuminate\Http\Request $request
   * @return \Illuminate\Http\JsonResponse
   */
  public function import(Request $request): JsonResponse
  {
    $request->validate([
      "file" => "required|file|mimetypes:application/json,text/plain",
    ]);

    $data = json_decode(file_get_contents($request->file("file")->getRealPath()), true);

    if (!isset($data["version"]) || !in_array($data["version"], ["2.0", "2.1"])) {
      return $this->error("Invalid backup format. Only standard version 2.0 or 2.1 backups are supported.", 400);
    }

    DB::transaction(function () use ($data) {
      DB::statement("PRAGMA foreign_keys=OFF;");

      Payment::query()->delete();
      DocumentItem::query()->delete();
      Document::query()->delete();
      DB::table("service_bundle_services")->delete();
      ServiceBundle::query()->delete();
      Service::query()->delete();
      Customer::query()->delete();
      Setting::query()->delete();
      User::query()->delete();

      $prepare = function (&$rows) {
        if (!is_array($rows)) {
          return;
        }
        foreach ($rows as &$row) {
          foreach ($row as $key => &$val) {
            if (is_array($val)) {
              $val = json_encode($val);
            }
          }
        }
      };

      if (isset($data["users"])) {
        $prepare($data["users"]);
        foreach ($data["users"] as &$user) {
          if (!isset($user["password"])) {
            $user["password"] = Hash::make("password");
          }
        }
        User::query()->insert($data["users"]);
      }
      if (isset($data["customers"])) {
        $prepare($data["customers"]);
        Customer::query()->insert($data["customers"]);
      }
      if (isset($data["services"])) {
        $prepare($data["services"]);
        foreach ($data["services"] as &$service) {
          unset($service["unit"], $service["pricing_type"]);
        }
        Service::query()->insert($data["services"]);
      }

      if (isset($data["service_bundles"])) {
        $prepare($data["service_bundles"]);
        foreach ($data["service_bundles"] as $b) {
          $services = $b["services"] ?? [];
          unset($b["services"]);
          $bundle = ServiceBundle::query()->create($b);
          if (!empty($services)) {
            if (is_string($services)) {
              $services = json_decode($services, true);
            }
            $bundle->services()->sync(array_column($services, "id"));
          }
        }
      }

      if (isset($data["documents"])) {
        $prepare($data["documents"]);
        Document::query()->insert($data["documents"]);
      }
      if (isset($data["document_items"])) {
        $prepare($data["document_items"]);
        DocumentItem::query()->insert($data["document_items"]);
      }
      if (isset($data["payments"])) {
        $prepare($data["payments"]);
        Payment::query()->insert($data["payments"]);
      }
      if (isset($data["settings"])) {
        $prepare($data["settings"]);
        Setting::query()->insert($data["settings"]);
      }

      DB::statement("PRAGMA foreign_keys=ON;");
    });

    return $this->success(null, "Backup restored successfully");
  }
}
