<x-text-layout :settings="$settings">
Statement of Account

Hi {{ $customer->contact_person ?: $customer->company_name ?: 'Valued Client' }},

We hope you're having a great day!

We are reaching out to provide you with a quick bi-monthly account balance reminder regarding pending invoices against your account as of the close of business yesterday.

NOTE: PDF copies for the pending invoices listed below have been attached to this email for your convenience.

PENDING INVOICES
@foreach($documents as $doc)
@php
  $dueDate = \Carbon\Carbon::parse($doc->due_date);
  $daysDiff = now()->startOfDay()->diffInDays($dueDate->startOfDay(), false);
  if ($daysDiff < 0) {
      $statusText = "Overdue by " . abs($daysDiff) . " days";
  } elseif ($daysDiff == 0) {
      $statusText = "Due today";
  } else {
      $statusText = "Due in $daysDiff days";
  }
@endphp

--- Invoice {{ $doc->document_number }} ---
Status: {{ $statusText }} ({{ $dueDate->format('M d, Y') }})
Total: {{ $settings['invoice.currency'] ?? '₹' }}{{ number_format($doc->grand_total, 2) }}
Pending: {{ $settings['invoice.currency'] ?? '₹' }}{{ number_format($doc->balance, 2) }}

@if(!empty($paymentLinks[$doc->document_number]))
Pay Online:
@foreach($paymentLinks[$doc->document_number] as $provider => $link)
- Pay Now via {{ strtoupper($provider) }}: {!! $link !!}
@endforeach
@endif

@endforeach

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
@if(!empty($activeWallets))

Mobile Wallets:
@foreach($activeWallets as $wallet)
- {{ $wallet['provider'] ?? 'Wallet' }}: {{ $wallet['value'] ?? '' }}
@endforeach
@endif
----------------------------------------

Already completed your payment? Please reply to this email with your transaction reference number or a screenshot so we can promptly update your ledger account.

Got a question about this statement? Just reply to this email or reach out to {{ $settings['company.accountsEmail'] ?? 'accounts@bxamra.dev' }}.

Best regards,
{{ $settings['company.name'] ?? 'BXAMRA IT Solutions' }}
</x-text-layout>
