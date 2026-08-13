<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

use App\Models\Setting;
use App\Models\Payment;
use App\Models\Document;
use App\Models\Customer;

use App\Mail\BiMonthlyMail;
use App\Mail\PaymentReceiptMail;
use App\Mail\PaymentDueMail;
use App\Mail\ProjectQuotationMail;
use App\Mail\FeedbackRequestMail;
use App\Mail\InvoiceIssuedMail;
use App\Mail\PaymentOverdueMail;

use App\Services\PaymentLinkService;

if (app()->environment("local")) {
  Route::get("/email-bi-monthly", function () {
    $invoices = [
      [
        "invoice_number" => "#INV-001",
        "total" => 50000,
        "paid" => 20000,
        "pending" => 30000,
        "link" => "upi://pay?pa=YOUR_UPI_ID@BANK&pn=BXAMRA%20IT%20Solutions&tr=INV-001&am=50000.00&cu=INR",
      ],
      [
        "invoice_number" => "#INV-002",
        "total" => 15000,
        "paid" => 5000,
        "pending" => 10000,
        "link" => "upi://pay?pa=YOUR_UPI_ID@BANK&pn=BXAMRA%20IT%20Solutions&tr=INV-001&am=50000.00&cu=INR",
      ],
    ];

    return new BiMonthlyMail($invoices);
  });

  // New Payment Receipt test route
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
      ->map(function (Document $d) use ($primaryWallet) {
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
    $document = new Document([
      "document_number" => "#INV-004",
      "due_date" => "2026-08-05",
      "balance" => 25000,
    ]);
    $document->setRelation("customer", new Customer(["company_name" => "Acme Corp"]));
    $invoiceLink = "https://bxamra.dev/invoices/INV-004";

    // Dummy Settings Array
    $settings = [
      "company.name" => "BXAMRA IT Solutions",
      "company.addressLine1" => "Khurla Kingra",
      "company.city" => "Jalandhar",
      "company.pincode" => "144014",
      "company.phone" => "+91 8427 430 011",
      "company.email" => "admin@bxamra.dev",
      "company.accountsEmail" => "accounts@bxamra.dev",
      "company.website" => "https://bxamra.dev",
      // 'company.gst' => '03AAACA1234A1Z5', // Uncomment to test tax display
    ];

    return new PaymentDueMail($document, $invoiceLink, $settings);
  });

  Route::get("/preview-invoice-issued", function () {
    $document = new Document([
      "document_number" => "#INV-005",
      "due_date" => "2026-08-25",
      "grand_total" => 45000,
      "balance" => 45000,
    ]);
    $document->setRelation("customer", new Customer(["company_name" => "Acme Corp"]));
    $invoiceLink = "https://bxamra.dev/invoices/INV-005";

    // Dummy Settings Array
    $settings = [
      "company.name" => "BXAMRA IT Solutions",
      "company.addressLine1" => "Khurla Kingra",
      "company.city" => "Jalandhar",
      "company.pincode" => "144014",
      "company.phone" => "+91 8427 430 011",
      "company.email" => "admin@bxamra.dev",
      "company.accountsEmail" => "accounts@bxamra.dev",
      "company.website" => "https://bxamra.dev",
    ];

    // We leave the pdfAttachmentPath null for the preview route so it doesn't try to attach a missing file
    return new InvoiceIssuedMail($document, $invoiceLink, $settings);
  });

  Route::get("/preview-project-quotation", function () {
    $clientName = "Acme Corp";
    $projectName = "E-Commerce Platform Refactor";
    $estimatedTimeline = "6-8 Weeks";
    $quotedAmount = 125000;
    $scopeLink = "https://docs.bxamra.dev/projects/acme-corp-ecommerce";

    // Dummy Settings Array
    $settings = [
      "company.name" => "BXAMRA IT Solutions",
      "company.addressLine1" => "Khurla Kingra",
      "company.city" => "Jalandhar",
      "company.pincode" => "144014",
      "company.phone" => "+91 8427 430 011",
      "company.email" => "admin@bxamra.dev",
      "company.accountsEmail" => "accounts@bxamra.dev",
      "company.website" => "https://bxamra.dev",
    ];

    return new ProjectQuotationMail($clientName, $projectName, $estimatedTimeline, $quotedAmount, $scopeLink, $settings);
  });

  Route::get("/preview-feedback-request", function () {
    $clientName = "Acme Corp";
    $projectName = "E-Commerce Platform Refactor";
    $reviewLink = "https://g.page/r/CeWAnbjVbNLOEBM/review"; // Your specific review link

    // Dummy Settings Array
    $settings = [
      "company.name" => "BXAMRA IT Solutions",
      "company.addressLine1" => "Khurla Kingra",
      "company.city" => "Jalandhar",
      "company.pincode" => "144014",
      "company.phone" => "+91 8427 430 011",
      "company.email" => "admin@bxamra.dev",
      "company.accountsEmail" => "accounts@bxamra.dev",
      "company.website" => "https://bxamra.dev",
    ];

    return new FeedbackRequestMail($clientName, $projectName, $settings, $reviewLink);
  });

  Route::get("/preview-payment-overdue", function () {
    $document = Document::with("customer")->first();

    $settings = Setting::query()->pluck("value", "key")->toArray();

    return new PaymentOverdueMail($document, $settings);
  });
}

Route::get("/", function () {
  $migrationsRun = Schema::hasTable("users");

  return view("welcome", compact("migrationsRun"));
});

Route::post("/setup/migrate", function () {
  Artisan::call("migrate", ["--force" => true]);

  return response()->json(["message" => "Migrations ran successfully", "output" => Artisan::output()]);
});
