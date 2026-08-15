<x-text-layout :settings="$settings">
Payment Overdue: {{ $document->document_number }}

Hi {{ $document->customer->contact_person ?: $document->customer->company_name }},

We hope you're having a great day!

We are reaching out because payment for invoice {{ $document->document_number }}, which was due on {{ $document->due_date?->format('F d, Y') ?? 'N/A' }} is now past due. We know how busy things can get, so we just wanted to float this to the top of your inbox.

NOTE: A PDF copy of the invoice is attached to this email for your convenience.

Outstanding Balance: ₹{{ number_format($document->balance, 2) }}
Please process this payment at your earliest convenience to bring your account up to date.

----------------------------------------
REMITTANCE OPTIONS

Bank Transfer:
A/C Name: {{ $settings['company.bank.accountName'] ?? '' }}
Bank: {{ $settings['company.bank.bankName'] ?? '' }} ({{ $settings['company.bank.branch'] ?? '' }})
Account: {{ $settings['company.bank.accountNumber'] ?? '' }}
IFSC: {{ $settings['company.bank.ifsc'] ?? '' }}
@if(!empty($settings['company.bank.swift']))
SWIFT: {{ $settings['company.bank.swift'] }}
@endif

@php
  $rawWallets = $settings['company.mobileWallets'] ?? [];
  if (is_string($rawWallets)) {
      $rawWallets = json_decode($rawWallets, true) ?? [];
  }
  $activeWallets = is_array($rawWallets) ? array_filter($rawWallets, function($w) {
      return empty($w['_deleted']);
  }) : [];
@endphp
@if(!empty($activeWallets) || !empty($paymentLinks))

Mobile Wallets & Online Payment:
@if(!empty($activeWallets))
@foreach($activeWallets as $wallet)
- {{ $wallet['provider'] ?? 'Wallet' }}: {{ $wallet['value'] ?? '' }}
@endforeach
@endif
@if(!empty($paymentLinks))
@foreach($paymentLinks as $provider => $link)
- Pay Now via {{ strtoupper($provider) }}: {!! $link !!}
@endforeach
@endif
@endif
----------------------------------------

Already completed your payment? Please reply to this email with your transaction reference number or a screenshot so we can promptly update your ledger account.

Got a question about this invoice? Just reply to this email or reach out to {{ $settings['company.accountsEmail'] ?? 'accounts@bxamra.dev' }}.

Best regards,
{{ $settings['company.name'] ?? 'BXAMRA IT Solutions' }}
</x-text-layout>
