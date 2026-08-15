<?php

namespace App\Mail;

use App\Models\Customer;
use App\Services\PaymentLinkService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BiMonthlyMail extends BaseMailable
{
  use Queueable, SerializesModels;

  /** @var array<string, array<string, string>> All generated payment links keyed by document_number then provider name. */
  public array $paymentLinks = [];

  /**
   * @param Customer $customer
   * @param \Illuminate\Database\Eloquent\Collection|array $documents
   * @param array<string, mixed> $settings
   * @param array<string, string> $pdfPaths
   */
  public function __construct(public Customer $customer, public $documents, public array $settings = [], public array $pdfPaths = [])
  {
    $wallets = $settings["company.mobileWallets"] ?? [];

    if (is_string($wallets)) {
      $wallets = json_decode($wallets, true) ?? [];
    }

    foreach ($this->documents as $doc) {
      $this->paymentLinks[$doc->document_number] = [];
      foreach ($wallets as $wallet) {
        if (!empty($wallet["_deleted"])) {
          continue;
        }

        $provider = strtolower(trim($wallet["provider"] ?? ""));
        $receiverId = trim($wallet["value"] ?? "");

        if (!$provider || !$receiverId) {
          continue;
        }

        $link = PaymentLinkService::generate(provider: $provider, receiverId: $receiverId, amount: (float) $doc->balance, note: $doc->document_number);

        if ($link) {
          $this->paymentLinks[$doc->document_number][$provider] = $link;
        }
      }
    }
  }

  public function envelope(): Envelope
  {
    return $this->buildEnvelope("Bi-Monthly Statement & Pending Invoice Reminder", true);
  }

  public function content(): Content
  {
    return new Content(
      view: "emails.html.bi_monthly",
      text: "emails.text.bi_monthly_text",
      with: [
        "paymentLinks" => $this->paymentLinks,
      ],
    );
  }

  /**
   * @return array<int, Attachment>|array
   */
  public function attachments(): array
  {
    $attachments = [];

    foreach ($this->pdfPaths as $invoiceNumber => $path) {
      $attachments[] = Attachment::fromStorageDisk("local", $path)
        ->as($invoiceNumber . ".pdf")
        ->withMime("application/pdf");
    }

    return $attachments;
  }
}
