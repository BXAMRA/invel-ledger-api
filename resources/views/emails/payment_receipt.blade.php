<x-email-layout>
  <x-slot:title>
    Payment Confirmation
  </x-slot>

  <h1 class="text-dark" style="margin-top: 0; font-size: 20px; color: #0f172a; font-weight: 600;">Payment Confirmation & Receipt</h1>

  <p class="text-main" style="margin-bottom: 20px; font-size: 14px; color: #334155;">
    Dear {{ $document->customer->company_name ?: $document->customer->name }},
  </p>

  <p class="text-main" style="margin-bottom: 24px; font-size: 14px; color: #334155;">
    This email is to confirm that we have successfully received and processed a payment of <strong>{{ $settings['invoice.currency'] ?? '₹' }}{{ number_format($payment->amount, 2) }}</strong> via <strong>{{ $payment->payment_method ?? 'Unknown' }}</strong>
    @if($payment->reference_number)
      Ref #{{ $payment->reference_number }}
    @endif
    towards Invoice <strong>{{ $document->document_number }}</strong>. Your account ledger has been updated accordingly.
  </p>

  <!-- Dynamic Payment Status Box -->
  @if($document->balance > 0)
  <p class="notice-bg warning-bg" style="margin-bottom: 24px; font-size: 14px; background-color: #fffbeb; padding: 12px 16px; border-left: 4px solid #f59e0b; border-radius: 4px; color: #92400e;">
    <strong>Partial Payment Recorded:</strong> This invoice has a remaining pending balance of <strong>{{ $settings['invoice.currency'] ?? '₹' }}{{ number_format($document->balance, 2) }}</strong>. Please remit the remaining balance at your earliest convenience.
  </p>
  @else
  <p class="notice-bg success-bg" style="margin-bottom: 24px; font-size: 14px; background-color: #ecfdf5; padding: 12px 16px; border-left: 4px solid #10b981; border-radius: 4px; color: #065f46;">
    <strong>Invoice Fully Paid:</strong> Thank you! Invoice {{ $document->document_number }} is now fully settled and officially closed.
  </p>
  @endif

  <!-- Dynamic Other Pending Invoices Section -->
  @if(isset($otherPendingInvoices) && count($otherPendingInvoices) > 0)
  <hr style="border: none; border-top: 1px solid #e2e8f0; margin: 32px 0 24px 0;">

  <h2 class="text-dark" style="margin-top: 0; margin-bottom: 16px; font-size: 16px; color: #0f172a; font-weight: 600;">Other Pending Invoices on Your Account</h2>
  <p class="text-main" style="margin-bottom: 16px; font-size: 14px; color: #334155;">
    For your records, here is a summary of other outstanding invoices currently registered against your account:
  </p>

  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="border-collapse: collapse; margin-bottom: 24px; font-size: 13px;">
    <thead>
      <tr class="table-header" style="background-color: #f1f5f9; text-align: left;">
        <th style="padding: 10px 12px; border: 0; color: #475569;">Invoice #</th>
        <th style="padding: 10px 12px; border: 0; color: #475569;">Total</th>
        <th style="padding: 10px 12px; border: 0; color: #475569;">Pending</th>
        <th style="padding: 10px 12px; border: 0; color: #475569; text-align: center;">Pay Now</th>
      </tr>
    </thead>
    <tbody>
      @foreach($otherPendingInvoices as $otherInvoice)
      <tr>
        <td class="text-dark" style="padding: 10px 12px; border: 0; font-weight: 600; color: #0f172a;">{{ $otherInvoice['invoice_number'] }}</td>
        <td class="text-main" style="padding: 10px 12px; border: 0; color: #334155;">{{ $settings['invoice.currency'] ?? '₹' }}{{ number_format($otherInvoice['total'], 2) }}</td>
        <td style="padding: 10px 12px; border: 0; color: #dc2626; font-weight: 600;">{{ $settings['invoice.currency'] ?? '₹' }}{{ number_format($otherInvoice['pending'], 2) }}</td>
        <td style="padding: 10px 12px; border: 0; text-align: center;">
          <a href="{{ $otherInvoice['link'] }}" style="color: #2563eb; text-decoration: none; font-weight: 600;">UPI</a>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
  @endif

  <p class="text-muted" style="font-size: 13px; color: #64748b; margin-top: 24px; margin-bottom: 24px;">
    For payment discrepancies or balance reconciliation inquiries, reply to this email or send a new one to <a href="mailto:{{ $settings['company.accountsEmail'] ?? $settings['company.email'] }}" style="color: #2563eb; text-decoration: none;">{{ $settings['company.accountsEmail'] ?? $settings['company.email'] }}</a>.
  </p>

  <p class="text-main" style="margin-bottom: 0; font-size: 14px; color: #334155;">Best regards,
    <br>
    <strong>{{ $settings['company.name'] ?? 'BXAMRA IT Solutions' }}</strong>
  </p>
</x-email-layout>
