<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Attachment;

class FeedbackRequestMail extends BaseMailable
{
  use Queueable, SerializesModels;

  /**
   * @param string $clientName
   * @param string $projectName
   * @param array<string, mixed> $settings
   * @param string|null $reviewLink
   */
  public function __construct(public string $clientName, public string $projectName, public array $settings = [], public ?string $reviewLink = null) {}

  public function envelope(): Envelope
  {
    return $this->buildEnvelope("How did we do on " . $this->projectName . "?", false);
  }

  public function content(): Content
  {
    return new Content(view: "emails.html.feedback_request");
  }

  /**
   * @return array<int, Attachment>|array
   */
  public function attachments(): array
  {
    return [];
  }
}
