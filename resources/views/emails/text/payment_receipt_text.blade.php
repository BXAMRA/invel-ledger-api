<x-text-layout :settings="$settings">
Payment Confirmation & Receipt

Dear {{ $document->customer->company_name ?: ($document->customer->name ?: 'Valued Client') }},

This email is to confirm that we have successfully received and processed a payment of {{ $settings['invoice.currency'] ?? '₹' }}{{ number_format($payment->amount, 2) }} via {{ $payment->payment_method ?? 'Unknown' }} @if($payment->reference_number)(Ref #{{ strtoupper($payment->reference_number) }})@endif towards Invoice {{ $document->document_number }}. Your account ledger has been updated accordingly.

NOTE: A PDF copy of the updated invoice is attached to this email for your records.

@if($document->balance > 0)
Partial Payment Recorded
This invoice has a remaining pending balance of {{ $settings['invoice.currency'] ?? '₹' }}{{ number_format($document->balance, 2) }}. Please remit the remaining balance at your earliest convenience.
@else
Invoice Fully Paid
Thank you! Invoice {{ $document->document_number }} is now fully settled and officially closed in our system.
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

@if($document->balance > 0 || (isset($otherPendingInvoices) && count($otherPendingInvoices) > 0))
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
@endif

@if(isset($otherPendingInvoices) && count($otherPendingInvoices) > 0)
Other Pending Invoices
For your records, here is a summary of other outstanding invoices currently registered against your account:

@foreach($otherPendingInvoices as $otherInvoice)
Invoice {{ $otherInvoice['invoice_number'] }}
@if(!empty($otherInvoice['due']))
Due: {{ \Carbon\Carbon::parse($otherInvoice['due'])->format('F d, Y') }}
@endif
Total: {{ $settings['invoice.currency'] ?? '₹' }}{{ number_format($otherInvoice['total'], 2) }}
Pending: {{ $settings['invoice.currency'] ?? '₹' }}{{ number_format($otherInvoice['pending'], 2) }}
@if(!empty($otherInvoice['link']))
@php $provider = count($activeWallets) > 0 ? $activeWallets[array_key_first($activeWallets)]['provider'] : 'WALLET'; @endphp
- Pay Now via {{ strtoupper($provider) }}: {!! $otherInvoice['link'] !!}
@endif

@endforeach
@endif

For payment discrepancies or balance reconciliation inquiries, reply to this email or send a new one to {{ $settings['company.accountsEmail'] ?? $settings['company.email'] }}.

Best regards,
{{ $settings['company.name'] ?? 'BXAMRA IT Solutions' }}
</x-text-layout>
