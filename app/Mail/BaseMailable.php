<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Queue\SerializesModels;
use App\Models\Setting;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\Middleware\RateLimited;

class BaseMailable extends Mailable implements ShouldQueue
{
  use Queueable, SerializesModels;

  public $tries = 0; // Infinite retries for rate limiting
  public $maxExceptions = 3; // Fail after 3 actual errors

  /**
   * @return array<int, RateLimited>
   */
  public function middleware()
  {
    return [new RateLimited("emails")];
  }

  protected function buildEnvelope(string $subject, bool $useAccountsEmail = false): Envelope
  {
    $settings = Setting::query()
      ->whereIn("key", ["company.email", "company.name", "company.accountsEmail", "company.senderEmail"])
      ->pluck("value", "key")
      ->toArray();

    $companyEmail = !empty($settings["company.email"]) ? $settings["company.email"] : "hello@example.dev";
    $companyName = !empty($settings["company.name"]) ? $settings["company.name"] : "Developer";

    if ($useAccountsEmail) {
      $fromEmail = !empty($settings["company.accountsEmail"]) ? $settings["company.accountsEmail"] : $companyEmail;
      $fromName = (!empty($settings["company.emailHeaderName"]) ? $settings["company.emailHeaderName"] : $companyName) . " Accounts";
    } else {
      $fromEmail = !empty($settings["company.senderEmail"]) ? $settings["company.senderEmail"] : $companyEmail;
      $fromName = $companyName;
    }

    $bcc = [];
    $to = [];

    if (app()->environment("local")) {
      $testEmail = env("MAIL_TEST_EMAIL", $companyEmail);
      $to[] = new Address($testEmail, "Local Test");
      $this->to = [];
      $this->cc = [];
      $this->bcc = [];
    } else {
      if ($companyEmail) {
        $bcc[] = new Address($companyEmail, $companyName);
      }
    }

    return new Envelope(from: new Address($fromEmail, $fromName), to: $to, bcc: $bcc, subject: $subject);
  }
}
