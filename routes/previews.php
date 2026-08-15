<?php

use App\Models\Setting;
use App\Models\Payment;
use App\Models\Document;
use App\Models\Customer;
use Illuminate\Support\Facades\Route;

use App\Mail\BiMonthlyMail;
use App\Mail\PaymentReceiptMail;
use App\Mail\PaymentDueMail;
use App\Mail\ProjectQuotationMail;
use App\Mail\FeedbackRequestMail;
use App\Mail\InvoiceIssuedMail;
use App\Mail\PaymentOverdueMail;

use App\Services\PaymentLinkService;

Route::get("/preview-bi-monthly", function () {
  $customer = Customer::with([
    "documents" => function ($query) {
      $query
        ->where("type", "invoice")
        ->where("balance", ">", 0)
        ->whereNotIn("status", ["draft", "cancelled", "paid"]);
    },
  ])->whereHas("documents", function ($query) {
      $query
        ->where("type", "invoice")
        ->where("balance", ">", 0)
        ->whereNotIn("status", ["draft", "cancelled", "paid"]);
  })->first();

  if (!$customer) return "No customer with pending invoices found in the database.";

  $invoicesData = [];
  $pdfPaths = [];
  
  $settings = Setting::query()->pluck("value", "key")->toArray();
  $wallets = isset($settings["company.mobileWallets"]) ? json_decode($settings["company.mobileWallets"], true) : [];
  $primaryWallet = null;
  if (is_array($wallets)) {
    foreach ($wallets as $w) {
      if (empty($w["_deleted"])) {
        $primaryWallet = $w;
        break;
      }
    }
  }

  foreach ($customer->documents as $doc) {
      $link = null;
      if ($primaryWallet) {
          $link = PaymentLinkService::generate($primaryWallet["provider"], $primaryWallet["value"], $doc->balance, $doc->document_number);
      }

      $invoicesData[] = [
        "invoice_number" => $doc->document_number,
        "total" => $doc->grand_total,
        "pending" => $doc->balance,
        "link" => $link,
      ];

      $generatedPdf = collect($doc->attachments ?? [])->firstWhere("label", "Invoice");
      if ($generatedPdf) {
        $pdfPaths[$doc->document_number] = $generatedPdf["path"];
      }
  }

  return new BiMonthlyMail($invoicesData, $pdfPaths);
});

Route::get("/preview-payment-receipt", function () {
  /** @var \App\Models\Payment $payment */
  $payment = Payment::with("document.customer")->first();

  if (!$payment) {
    return "No payments found in the database to preview.";
  }

  /** @var \App\Models\Document $document */
  $document = $payment->document;
  $customer = $document->customer;

  $settings = Setting::query()->pluck("value", "key")->toArray();

  $wallets = isset($settings["company.mobileWallets"]) ? json_decode($settings["company.mobileWallets"], true) : [];
  $primaryWallet = null;
  if (is_array($wallets)) {
    foreach ($wallets as $w) {
      if (empty($w["_deleted"])) {
        $primaryWallet = $w;
        break;
      }
    }
  }

  $otherPendingInvoices = Document::query()
    ->where("customer_id", $customer->id)
    ->where("type", "invoice")
    ->where("id", "!=", $document->id)
    ->where("balance", ">", 0)
    ->whereNotIn("status", ["draft", "cancelled", "paid"])
    ->get()
    ->map(function (\App\Models\Document $d) use ($primaryWallet) {
      $link = null;
      if ($primaryWallet) {
        $link = PaymentLinkService::generate($primaryWallet["provider"], $primaryWallet["value"], $d->balance, $d->document_number);
      }
      return [
        "invoice_number" => $d->document_number,
        "total" => $d->grand_total,
        "pending" => $d->balance,
        "due" => $d->due_date,
        "link" => $link,
      ];
    })
    ->all();

  return new PaymentReceiptMail($document, $payment, $otherPendingInvoices, $settings);
});

Route::get("/preview-payment-due", function () {
  $document = Document::with("customer")
    ->where("type", "invoice")
    ->where("balance", ">", 0)
    ->whereNotIn("status", ["draft", "cancelled", "paid"])
    ->first();
    
  if (!$document) return "No pending invoices found.";
  
  $settings = Setting::query()->pluck("value", "key")->toArray();
  $invoiceLink = env("FRONTEND_URL", "http://localhost:1420") . "/documents/" . $document->uuid;
  
  return new PaymentDueMail($document, $invoiceLink, $settings);
});

Route::get("/preview-invoice-issued", function () {
  $document = Document::with("customer")->where("type", "invoice")->first();
  if (!$document) return "No invoices found.";
  
  $settings = Setting::query()->pluck("value", "key")->toArray();
  $invoiceLink = env("FRONTEND_URL", "http://localhost:1420") . "/documents/" . $document->uuid;
  
  return new InvoiceIssuedMail($document, $invoiceLink, $settings);
});

Route::get("/preview-project-quotation", function () {
  $document = Document::with("customer")->where("type", "quote")->first();
  if (!$document) return "No quotes found.";
  
  $clientName = $document->customer->company_name ?? "Unknown Client";
  $projectName = $document->document_number; // Fallback since specific project name might not be separated
  $estimatedTimeline = "TBD";
  $quotedAmount = $document->grand_total;
  $scopeLink = env("FRONTEND_URL", "http://localhost:1420") . "/documents/" . $document->uuid;
  
  $settings = Setting::query()->pluck("value", "key")->toArray();
  
  return new ProjectQuotationMail($clientName, $projectName, $estimatedTimeline, $quotedAmount, $scopeLink, $settings);
});

Route::get("/preview-feedback-request", function () {
  $customer = Customer::first();
  if (!$customer) return "No customers found.";
  
  $clientName = $customer->company_name ?? "Unknown Client";
  $projectName = "Recent Project";
  
  $settings = Setting::query()->pluck("value", "key")->toArray();
  $reviewLink = $settings['company.website'] ?? "https://example.com/review";
  
  return new FeedbackRequestMail($clientName, $projectName, $settings, $reviewLink);
});

Route::get("/preview-payment-overdue", function () {
  $document = Document::with("customer")
    ->where("type", "invoice")
    ->where("balance", ">", 0)
    ->whereNotNull("due_date")
    ->where("due_date", "<", now()->format("Y-m-d"))
    ->first();
    
  if (!$document) {
    // Fallback if no genuinely overdue document exists
    $document = Document::with("customer")->where("type", "invoice")->first();
    if (!$document) return "No invoices found.";
  }
  
  $settings = Setting::query()->pluck("value", "key")->toArray();
  
  return new PaymentOverdueMail($document, $settings);
});
