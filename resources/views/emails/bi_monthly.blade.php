<x-email-layout>
  <x-slot:title>
    Pending Invoice Reminder
  </x-slot>

  <h1 class="text-dark" style="margin-top: 0; font-size: 20px; color: #0f172a; font-weight: 600;">Statement of Account & Pending Invoice Reminder</h1>
  <p class="text-main" style="margin-bottom: 20px; font-size: 14px; color: #334155;">
    Dear {{ $clientName ?? 'Valued Client' }},
  </p>

  <p class="text-main" style="margin-bottom: 20px; font-size: 14px; color: #334155;">
    This is a bi-monthly account balance reminder regarding pending invoices against your account as of the close of the business yesterday.
  </p>

  <!-- PDF Attachment Notice -->
  <p class="notice-bg" style="margin-bottom: 24px; font-size: 14px; background-color: #f1f5f9; padding: 12px 16px; border-left: 4px solid #2563eb; border-radius: 4px; color: #1e293b;">
    📎 PDF copies for the pending invoices listed below have been attached to this email.
  </p>

  <!-- Invoice Summary Table -->
  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="border-collapse: collapse; margin-bottom: 24px; font-size: 13px;">
    <thead>
      <tr class="table-header" style="background-color: #f1f5f9; text-align: right;">
        <th style="padding: 10px 12px; border: 0; color: #475569; text-align: left;">Invoice #</th>
        <th style="padding: 10px 12px; border: 0; color: #475569;">Total</th>
        <th style="padding: 10px 12px; border: 0; color: #475569;">Pending</th>
        <th style="padding: 10px 12px; border: 0; color: #475569; text-align: center;">Pay Now</th>
      </tr>
    </thead>
    <tbody>
      @foreach($invoices as $invoice)
      <tr style="text-align: right;" >
        <td class="text-dark" style="padding: 10px 12px; border: 0; font-weight: 600; color: #0f172a; text-align: left;">{{ $invoice['invoice_number'] }}</td>
        <td class="text-main" style="padding: 10px 12px; border: 0; color: #334155;">{{ $settings['invoice.currency'] ?? '₹' }}{{ number_format($invoice['total'], 2) }}</td>
        <td style="padding: 10px 12px; border: 0; color: #dc2626; font-weight: 600;">{{ $settings['invoice.currency'] ?? '₹' }}{{ number_format($invoice['pending'], 2) }}</td>
        <td style="padding: 10px 12px; border: 0; text-align: center;">
          <a href="{{ $invoice['link'] }}" style="color: #2563eb; text-decoration: none; font-weight: 600;">UPI</a>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>

  <!-- Payment Request & Confirmation Notice -->
  <p class="text-main" style="margin-bottom: 24px; font-size: 13px; color: #334155;">
    Please remit the pending amount at your earliest convenience. If you have already processed the payment, kindly reply to this email with the transaction reference or a screenshot of the UPI payment so we can promptly update your account ledger.
  </p>

  <p class="text-muted" style="font-size: 13px; color: #64748b; margin-bottom: 24px;">
    For invoice discrepancies or balance reconciliation inquiries, reply to this email or send a new one to <a href="mailto:{{ $settings['company.accountsEmail'] ?? 'accounts@bxamra.dev' }}" style="color: #2563eb; text-decoration: none;">{{ $settings['company.accountsEmail'] ?? 'accounts@bxamra.dev' }}</a>.
  </p>

  <p class="text-main" style="margin-bottom: 0; font-size: 14px; color: #334155;">Best regards,<br><strong>{{ $settings['company.name'] ?? 'BXAMRA IT Solutions' }}</strong></p>
</x-email-layout>
