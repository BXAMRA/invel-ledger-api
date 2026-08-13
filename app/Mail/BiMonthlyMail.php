<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Attachment;
use App\Models\Setting;

class BiMonthlyMail extends BaseMailable
{
  use Queueable, SerializesModels;

  /**
   * @param array<int, mixed> $invoices
   * @param array<string, string> $pdfPaths
   */
  public function __construct(public array $invoices, public array $pdfPaths = []) {}

  public function envelope(): Envelope
  {
    return $this->buildEnvelope("Bi-Monthly Statement & Pending Invoice Reminder", true);
  }

  public function content(): Content
  {
    $settings = Setting::query()
      ->pluck("value", "key")
      ->map(function ($value) {
        $decoded = json_decode($value, true);
        return json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
      })
      ->toArray();

    return new Content(
      view: "emails.bi_monthly",
      with: [
        "settings" => $settings,
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
