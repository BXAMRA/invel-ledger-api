<x-email-layout>
  <x-slot:title>
    Payment Confirmation: {{ $document->document_number }}
  </x-slot>

  <p class="text-main" style="margin-bottom: 20px; font-size: 14px; color: #334155;">
    Dear {{ $document->customer->company_name ?: ($document->customer->name ?: 'Valued Client') }},
  </p>

  <p class="text-main" style="margin-bottom: 24px; font-size: 14px; color: #334155; line-height: 1.6;">
    This email is to confirm that we have successfully received and processed a payment of <strong>{{ $settings['invoice.currency'] ?? '₹' }}{{ number_format($payment->amount, 2) }}</strong> via <strong>{{ $payment->payment_method ?? 'Unknown' }}</strong>
    @if($payment->reference_number)
      (Ref <strong> #{{ strtoupper($payment->reference_number) }} </strong> )
    @endif
    towards Invoice <strong>{{ $document->document_number }}</strong>. Your account ledger has been updated accordingly.
  </p>

  <!-- PDF Attachment Notice -->
  <p class="notice-bg" style="margin-bottom: 24px; font-size: 14px; background-color: #f1f5f9; padding: 12px 16px; border-left: 4px solid #2563eb; border-radius: 4px; color: #1e293b;">
    <strong>NOTE:</strong> A PDF copy of the updated invoice is attached to this email for your records.
  </p>

  <!-- Dynamic Payment Status Box -->
  @if($document->balance > 0)
  <div class="notice-bg warning-bg" style="margin-bottom: 24px; font-size: 14px; background-color: #fffbeb; padding: 14px 16px; border-left: 4px solid #f59e0b; border-radius: 4px; color: #92400e;">
    <strong style="display: block; margin-bottom: 4px;">Partial Payment Recorded</strong>
    This invoice has a remaining pending balance of <strong>{{ $settings['invoice.currency'] ?? '₹' }}{{ number_format($document->balance, 2) }}</strong>. Please remit the remaining balance at your earliest convenience.
  </div>
  @else
  <div class="notice-bg success-bg" style="margin-bottom: 24px; font-size: 14px; background-color: #ecfdf5; padding: 14px 16px; border-left: 4px solid #10b981; border-radius: 4px; color: #065f46;">
    <strong style="display: block; margin-bottom: 4px;">Invoice Fully Paid</strong>
    Thank you! Invoice {{ $document->document_number }} is now fully settled and officially closed in our system.
  </div>
  @endif

  <!-- Dynamic Other Pending Invoices Section (Mobile Safe) -->
  @if(isset($otherPendingInvoices) && count($otherPendingInvoices) > 0)
  <hr style="border: none; border-top: 1px solid #e2e8f0; margin: 32px 0 24px 0;">

  <h2 class="text-dark" style="margin-top: 0; margin-bottom: 16px; font-size: 16px; color: #0f172a; font-weight: 600;">Other Pending Invoices</h2>
  <p class="text-main" style="margin-bottom: 16px; font-size: 14px; color: #334155;">
    For your records, here is a summary of other outstanding invoices currently registered against your account:
  </p>

  <!-- Stacked Mobile-Friendly Cards -->
  @foreach($otherPendingInvoices as $otherInvoice)
  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom: 12px; background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; overflow: hidden;">
    <tr>
      <td style="padding: 14px 16px; width: 70%;">
        <strong style="color: #0f172a; font-size: 14px; display: block; margin-bottom: 4px;">{{ $otherInvoice['invoice_number'] }}</strong>
        <span style="color: #64748b; font-size: 13px;">Total: {{ $settings['invoice.currency'] ?? '₹' }}{{ number_format($otherInvoice['total'], 2) }}</span><br>
        <span style="color: #dc2626; font-size: 13px; font-weight: 600;">Pending: {{ $settings['invoice.currency'] ?? '₹' }}{{ number_format($otherInvoice['pending'], 2) }}</span>
      </td>
      <td style="padding: 14px 16px; text-align: right; vertical-align: middle; width: 30%;">
        <a href="{{ $otherInvoice['link'] }}" style="display: inline-block; padding: 8px 16px; background-color: #2563eb; color: #ffffff; text-decoration: none; font-weight: 600; font-size: 12px; border-radius: 4px;">Pay UPI</a>
      </td>
    </tr>
  </table>
  @endforeach
  @endif

  <p class="text-muted" style="font-size: 13px; color: #64748b; margin-top: 24px; margin-bottom: 24px;">
    For payment discrepancies or balance reconciliation inquiries, reply to this email or send a new one to <a href="mailto:{{ $settings['company.accountsEmail'] ?? $settings['company.email'] }}" style="color: #2563eb; text-decoration: none;">{{ $settings['company.accountsEmail'] ?? $settings['company.email'] }}</a>.
  </p>

  <p class="text-main" style="margin-bottom: 0; font-size: 14px; color: #334155;">
    Best regards,<br>
    <strong>{{ $settings['company.name'] ?? 'Hope Society' }}</strong>
  </p>
</x-email-layout>
