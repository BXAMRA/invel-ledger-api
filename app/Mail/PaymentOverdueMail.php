<?php

namespace App\Mail;

use App\Models\Document;
use App\Services\PaymentLinkService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentOverdueMail extends BaseMailable
{
  use Queueable, SerializesModels;

  /** @var string Convenience alias for the UPI link (kept for blade template compatibility). */
  public string $upiLink = "";

  /** @var array<string, string> All generated payment links keyed by provider name. */
  public array $paymentLinks = [];

  /**
   * @param Document $document
   * @param array<string, mixed> $settings
   * @param string|null $pdfAttachmentPath
   */
  public function __construct(public Document $document, public array $settings = [], public ?string $pdfAttachmentPath = null)
  {
    $wallets = $settings["company.mobileWallets"] ?? [];

    // company.mobileWallets may be stored as a JSON string
    if (is_string($wallets)) {
      $wallets = json_decode($wallets, true) ?? [];
    }

    foreach ($wallets as $wallet) {
      if (!empty($wallet["_deleted"])) {
        continue;
      }

      $provider = strtolower(trim($wallet["provider"] ?? ""));
      $receiverId = trim($wallet["value"] ?? "");

      if (!$provider || !$receiverId) {
        continue;
      }

      $link = PaymentLinkService::generate(provider: $provider, receiverId: $receiverId, amount: (float) $this->document->balance, note: $this->document->document_number);

      if ($link) {
        $this->paymentLinks[$provider] = $link;
      }
    }

    $this->upiLink = $this->paymentLinks["upi"] ?? "";
  }

  public function envelope(): Envelope
  {
    return $this->buildEnvelope("URGENT: Payment Overdue for " . $this->document->document_number, true);
  }

  public function content(): Content
  {
    return new Content(
      view: "emails.html.payment_overdue", text: "emails.text.payment_overdue_text",
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
    if ($this->pdfAttachmentPath) {
      return [
        Attachment::fromPath($this->pdfAttachmentPath)
          ->as($this->document->document_number . ".pdf")
          ->withMime("application/pdf"),
      ];
    }

    return [];
  }
}
