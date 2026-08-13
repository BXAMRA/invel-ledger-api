<?php

namespace App\Services;

class PaymentLinkService
{
  /**
   * Generate the payment URI/link for the specified gateway.
   *
   * @param string $provider The payment gateway/provider name (e.g. UPI, Venmo).
   * @param string $receiverId The receiver's ID (e.g. UPI ID, Venmo username, CashApp $cashtag).
   * @param float $amount The amount to request.
   * @param string|null $note Optional note or message, typically the document/invoice number.
   * @return string|null The formatted URI/link, or null if the provider is unsupported.
   */
  public static function generate(string $provider, string $receiverId, float $amount, ?string $note = null): ?string
  {
    $provider = strtolower(trim($provider));

    switch ($provider) {
      case "upi":
        // UPI deep link format
        $url = "upi://pay?pa=" . urlencode($receiverId) . "&pn=" . urlencode($receiverId) . "&am=" . number_format($amount, 2, ".", "");
        if ($note) {
          $url .= "&tn=" . urlencode($note) . "&tr=" . urlencode($note);
        }
        return $url;

      case "venmo":
        // Venmo deep link format
        $url = "venmo://paycharge?txn=pay&recipients=" . urlencode($receiverId) . "&amount=" . number_format($amount, 2, ".", "");
        if ($note) {
          $url .= "&note=" . urlencode($note);
        }
        return $url;

      case "cashapp":
        // Cash App format: https://cash.app/$cashtag/amount
        return "https://cash.app/" . urlencode($receiverId) . "/" . number_format($amount, 2, ".", "");

      case "paypal":
        // PayPal.Me format: https://paypal.me/username/amount
        return "https://paypal.me/" . urlencode($receiverId) . "/" . number_format($amount, 2, ".", "");

      default:
        // Unsupported provider
        return null;
    }
  }
}
