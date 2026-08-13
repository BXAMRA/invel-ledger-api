<?php

namespace App\Mail;

use App\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Attachment;

class PaymentDueMail extends BaseMailable
{
  use Queueable, SerializesModels;

  public string $upiLink = "";

  /**
   * @param Document $document
   * @param string $invoiceLink
   * @param array<string, mixed> $settings
   */
  public function __construct(public Document $document, public string $invoiceLink, public array $settings = [])
  {
    $upiId = $settings["company.bank.upiId"] ?? "";
    $companyName = $settings["company.name"] ?? "BXAMRA IT Solutions";
    $companyNameEncoded = rawurlencode($companyName);

    if ($upiId) {
      $this->upiLink = "upi://pay?pa={$upiId}&pn={$companyNameEncoded}&tr={$this->document->document_number}&am=" . number_format($this->document->balance, 2, ".", "") . "&cu=INR";
    }
  }

  public function envelope(): Envelope
  {
    return $this->buildEnvelope("Action Required: Payment Due Tomorrow for " . $this->document->document_number, true);
  }

  public function content(): Content
  {
    return new Content(view: "emails.payment_due");
  }

  /**
   * @return array<int, Attachment>|array
   */
  public function attachments(): array
  {
    return [];
  }
}
