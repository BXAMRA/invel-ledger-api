<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Document;
use App\Models\Setting;
use App\Mail\PaymentOverdueMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Attributes\Description;
use Illuminate\Support\Facades\Storage;

#[Signature("app:send-overdue-reminders {--force : Send to all overdue invoices regardless of the 5-day interval}")]
#[Description("Sends overdue reminders every 5 days after the due date")]
class SendOverdueReminders extends Command
{
  public function handle(): void
  {
    $force = $this->option("force");
    $this->info("Starting to send overdue reminders..." . ($force ? " (FORCE MODE)" : ""));
    Log::channel("mail")->info("Starting overdue reminders cron job." . ($force ? " (FORCE MODE)" : ""));

    $today = Carbon::today();

    $documents = Document::with("customer")
      ->where("type", "invoice")
      ->where("balance", ">", 0)
      ->whereNotNull("due_date")
      ->where("due_date", "<", $today->format("Y-m-d"))
      ->whereNotIn("status", ["draft", "cancelled", "paid"])
      ->get();

    $sentCount = 0;

    $settings = Setting::query()->pluck("value", "key")->toArray();
    $companyName = $settings["company.name"] ?? "BXAMRA IT Solutions";
    $companyNameEncoded = rawurlencode($companyName);

    foreach ($documents as $doc) {
      $dueDate = Carbon::parse($doc->due_date);
      // Calculate days overdue
      $daysOverdue = (int) $dueDate->diffInDays($today);

      if (($daysOverdue > 0 && $daysOverdue % 5 === 0) || ($force && $daysOverdue > 0)) {
        $customer = $doc->customer;
        if (!$customer) {
          continue;
        }

        $email = $customer->contact_email ?: $customer->email;
        if (!$email) {
          $this->warn("Skipping document {$doc->document_number} - no email address.");
          continue;
        }

        $pdfPath = null;
        $attachments = $doc->attachments ?? [];
        if (is_array($attachments)) {
          foreach ($attachments as $attachment) {
            if (isset($attachment["label"]) && $attachment["label"] === "Invoice" && isset($attachment["path"])) {
              if (Storage::disk("local")->exists($attachment["path"])) {
                $pdfPath = Storage::disk("local")->path($attachment["path"]);
              }
              break;
            }
          }
        }

        $invoiceLink = "https://bxamra.dev/invoices/" . $customer->slug . "/" . $doc->document_number;

        $mail = new PaymentOverdueMail($doc, $settings, $pdfPath);
        $targetEmail = app()->environment("local") ? env("MAIL_TEST_EMAIL", $email) : $email;

        Mail::to($targetEmail)->send($mail);

        $logMsg = "Sent overdue reminder for {$doc->document_number} to {$targetEmail} (Overdue by {$daysOverdue} days)";
        $this->info($logMsg);
        Log::channel("mail")->info($logMsg);

        $sentCount++;
      }
    }

    $this->info("Completed sending {$sentCount} overdue reminders.");
    Log::channel("mail")->info("Completed overdue reminders cron job. Total sent: {$sentCount}");
  }
}
