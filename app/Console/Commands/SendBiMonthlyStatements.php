<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Customer;
use App\Mail\BiMonthlyMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Attributes\Description;
use App\Models\Setting;

#[Signature("app:send-bi-monthly-statements")]
#[Description("Sends bi-monthly statement emails to customers with pending invoices")]
class SendBiMonthlyStatements extends Command
{
  public function handle()
  {
    $this->info("Starting to send bi-monthly statements...");
    Log::channel("mail")->info("Starting bi-monthly statement cron job.");

    $customers = Customer::with([
      "documents" => function ($query) {
        $query
          ->where("type", "invoice")
          ->where("balance", ">", 0)
          ->whereNotIn("status", ["draft", "cancelled", "paid"]);
      },
    ])->get();

    $sentCount = 0;

    foreach ($customers as $customer) {
      if ($customer->documents->isEmpty()) {
        continue;
      }

      $email = $customer->contact_email ?: $customer->email;
      if (!$email) {
        $this->warn("Skipping customer {$customer->company_name} - no email address.");
        Log::channel("mail")->warning("Skipped sending statement to {$customer->company_name} - no email address found.");
        continue;
      }

      $pdfPaths = [];
      $settings = Setting::query()->pluck("value", "key")->map(function ($value) {
        $decoded = json_decode($value, true);
        return json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
      })->toArray();

      foreach ($customer->documents as $doc) {
        $generatedPdf = collect($doc->attachments ?? [])->firstWhere("label", "Invoice");
        if ($generatedPdf) {
          $pdfPaths[$doc->document_number] = $generatedPdf["path"];
        }
      }

      $targetEmail = app()->environment("local") ? env("MAIL_TEST_EMAIL", $email) : $email;

      if (app()->environment("local")) {
        $this->info("Local environment detected. Rerouting email for {$customer->company_name} from {$email} to {$targetEmail}");
      }

      Mail::to($targetEmail)->send(new BiMonthlyMail($customer, $customer->documents, $settings, $pdfPaths));

      $logMsg = "Sent statement to {$targetEmail} for {$customer->company_name}";
      $this->info($logMsg);
      Log::channel("mail")->info($logMsg);

      $sentCount++;
    }

    $this->info("Completed sending {$sentCount} statements.");
    Log::channel("mail")->info("Completed bi-monthly statement cron job. Total sent: {$sentCount}");
  }
}
