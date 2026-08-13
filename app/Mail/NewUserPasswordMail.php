<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Attachment;

class NewUserPasswordMail extends BaseMailable
{
  use Queueable, SerializesModels;

  /**
   * @param User $user
   * @param string $password
   */
  public function __construct(public User $user, public string $password) {}

  public function envelope(): Envelope
  {
    return $this->buildEnvelope("Welcome to Invel LEDGER - Your Account Details", false);
  }

  public function content(): Content
  {
    return new Content(view: "emails.new_user_password");
  }

  /**
   * @return array<int, Attachment>|array
   */
  public function attachments(): array
  {
    return [];
  }
}
