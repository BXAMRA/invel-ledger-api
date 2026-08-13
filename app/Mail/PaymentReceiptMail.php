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
   * @param Document $document
   * @param Payment $payment
   * @param array<int, mixed> $otherPendingInvoices
   * @param array<string, mixed> $settings
   */
  public function __construct(public Document $document, public Payment $payment, public array $otherPendingInvoices = [], public array $settings = []) {}

  public function envelope(): Envelope
  {
    return $this->buildEnvelope("Payment Confirmation: " . $this->document->document_number, true);
  }

  public function content(): Content
  {
    return new Content(view: "emails.payment_receipt");
  }

  /**
   * @return array<int, Attachment>|array
   */
  public function attachments(): array
  {
    return [];
  }
}
