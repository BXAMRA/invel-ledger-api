<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProjectQuotationMail extends BaseMailable
{
  use Queueable, SerializesModels;

  /**
   * @param string $clientName
   * @param string $projectName
   * @param string $estimatedTimeline
   * @param float $quotedAmount
   * @param string $scopeLink
   * @param array<string, mixed> $settings
   * @param string|null $pdfAttachmentPath
   */
  public function __construct(
    public string $clientName,
    public string $projectName,
    public string $estimatedTimeline,
    public float $quotedAmount,
    public string $scopeLink,
    public array $settings = [],
    public ?string $pdfAttachmentPath = null,
  ) {
    // Optional PDF for a formal quote document
  }

  public function envelope(): Envelope
  {
    return $this->buildEnvelope("Project Quotation & Technical Proposal: " . $this->projectName);
  }

  public function content(): Content
  {
    return new Content(view: "emails.project_quotation");
  }

  /**
   * @return array<int, \Illuminate\Mail\Mailables\Attachment>|array
   */
  public function attachments(): array
  {
    if ($this->pdfAttachmentPath) {
      return [Attachment::fromPath($this->pdfAttachmentPath)->as("Quotation_" . preg_replace("/[^A-Za-z0-9\-]/", "", $this->projectName) . ".pdf")];
    }

    return [];
  }
}
