<?php

namespace App\Mail;

use App\Models\Document;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Attachment;

class PaymentReceiptMail extends BaseMailable
{
  use Queueable, SerializesModels;

  /**
   * @var array<string, string>
   */
  public array $paymentLinks = [];

  /**
   * @param Document $document
   * @param Payment $payment
   * @param array<int, mixed> $otherPendingInvoices
   * @param array<string, mixed> $settings
   */
  public function __construct(public Document $document, public Payment $payment, public array $otherPendingInvoices = [], public array $settings = [])
  {
    $wallets = isset($settings["company.mobileWallets"]) ? json_decode($settings["company.mobileWallets"], true) : [];
    if (is_array($wallets) && $document->balance > 0) {
      foreach ($wallets as $w) {
        if (empty($w["_deleted"])) {
          $link = \App\Services\PaymentLinkService::generate($w["provider"], $w["value"], $document->balance, $document->document_number);
          if ($link) {
            $this->paymentLinks[$w["provider"]] = $link;
          }
          break;
        }
      }
    }
  }

  public function envelope(): Envelope
  {
    return $this->buildEnvelope("Payment Confirmation: " . $this->document->document_number, true);
  }

  public function content(): Content
  {
    return new Content(view: "emails.html.payment_receipt", text: "emails.text.payment_receipt_text");
  }

  /**
   * @return array<int, Attachment>|array
   */
  public function attachments(): array
  {
    $this->document->refresh();

    $attachments = [];
    $docAttachments = $this->document->attachments ?? [];

    if (is_array($docAttachments)) {
      foreach ($docAttachments as $attachment) {
        if (isset($attachment["label"]) && $attachment["label"] === "Invoice" && isset($attachment["path"])) {
          if (\Illuminate\Support\Facades\Storage::disk("local")->exists($attachment["path"])) {
            $attachments[] = Attachment::fromPath(\Illuminate\Support\Facades\Storage::disk("local")->path($attachment["path"]))
              ->as($this->document->document_number . ".pdf")
              ->withMime("application/pdf");
          }
          break;
        }
      }
    }

    return $attachments;
  }
}
