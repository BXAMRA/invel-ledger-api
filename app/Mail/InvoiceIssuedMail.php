<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use App\Models\Document;

class InvoiceIssuedMail extends BaseMailable
{
  use Queueable, SerializesModels;

  /**
   * @param Document $document
   * @param string $invoiceLink
   * @param array<string, mixed> $settings
   * @param string|null $pdfAttachmentPath
   */
  public function __construct(public Document $document, public string $invoiceLink, public array $settings = [], public ?string $pdfAttachmentPath = null) {}

  public function envelope(): Envelope
  {
    return $this->buildEnvelope("New Invoice Issued: " . $this->document->document_number, true);
  }

  public function content(): Content
  {
    return new Content(view: "emails.html.invoice_issued");
  }

  /**
   * @return array<int, Attachment>|array
   */
  public function attachments(): array
  {
    if ($this->pdfAttachmentPath) {
      return [
        Attachment::fromPath($this->pdfAttachmentPath)
          ->as("Invoice_" . $this->document->document_number . ".pdf")
          ->withMime("application/pdf"),
      ];
    }

    return [];
  }
}
