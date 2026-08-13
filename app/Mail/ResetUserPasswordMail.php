<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResetUserPasswordMail extends BaseMailable
{
  use Queueable, SerializesModels;

  /**
   * @param User $user
   * @param string $password
   */
  public function __construct(
    public User $user,
    public string $password,
  ) {}

  public function envelope(): Envelope
  {
    return $this->buildEnvelope('Your Password Has Been Reset - Invel LEDGER', false);
  }

  public function content(): Content
  {
    return new Content(view: 'emails.reset_user_password');
  }

  /**
   * @return array<int, \Illuminate\Mail\Mailables\Attachment>|array
   */
  public function attachments(): array
  {
    return [];
  }
}
