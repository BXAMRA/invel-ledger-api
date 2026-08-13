<x-email-layout>
  <x-slot:title>
    Payment Due Reminder
  </x-slot>

  <p class="text-main" style="margin-bottom: 20px; font-size: 14px; color: #334155;">Dear {{ $document->customer->company_name ?: $document->customer->name }},</p>

  <p class="text-main" style="margin-bottom: 20px; font-size: 14px; color: #334155;">This is a payment notification regarding invoice <strong>{{ $document->document_number }}</strong>, which is scheduled for settlement tomorrow. Please arrange for payment at your earliest convenience.</p>

  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="border-collapse: collapse; margin-bottom: 24px; font-size: 13px;">
    <thead>
      <tr class="table-header" style="background-color: #f1f5f9; text-align: left;">
        <th style="padding: 10px 12px; border: 0; color: #475569;">Invoice #</th>
        <th style="padding: 10px 12px; border: 0; color: #475569;">Total Amount</th>
        <th style="padding: 10px 12px; border: 0; color: #475569; text-align: center;">Action</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td class="text-dark" style="padding: 10px 12px; border: 0; font-weight: 600; color: #0f172a;">{{ $document->document_number }}</td>
        <td class="text-main" style="padding: 10px 12px; border: 0; color: #334155;">₹{{ number_format($document->balance, 2) }}</td>
        <td style="padding: 10px 12px; border: 0; text-align: center;">
          <a href="{{ $invoiceLink }}" style="color: #2563eb; text-decoration: none; font-weight: 600;">View Online</a>
        </td>
      </tr>
    </tbody>
  </table>

  <!-- Payment Details Block -->
  <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; margin-bottom: 24px;">
    <h3 style="margin-top: 0; margin-bottom: 16px; font-size: 14px; color: #0f172a; text-transform: uppercase; letter-spacing: 0.5px;">Payment Options</h3>

    <table width="100%" cellspacing="0" cellpadding="0" border="0">
      <tr>
        <td width="65%" valign="top" style="font-size: 13px; color: #475569; padding-right: 10px;">
          <strong style="color: #0f172a;">Bank Transfer</strong><br>
          Bank: {{ $settings['company.bank.bankName'] ?? '' }}<br>
          A/C Name: {{ $settings['company.bank.accountName'] ?? '' }}<br>
          A/C No: {{ $settings['company.bank.accountNumber'] ?? '' }}<br>
          IFSC: {{ $settings['company.bank.ifsc'] ?? '' }}
        </td>
        <td width="35%" valign="top" style="font-size: 13px; color: #475569; border-left: 1px solid #cbd5e1; padding-left: 15px;">
          <strong style="color: #0f172a;">UPI Payment</strong><br>
          UPI ID: {{ $settings['company.bank.upiId'] ?? '' }}<br>
          <br>
          <!-- UPI Button -->
          <table cellspacing="0" cellpadding="0" border="0">
            <tr>
              <td align="center" bgcolor="#2563eb" style="border-radius: 4px;">
                <a href="{{ $upiLink }}" style="font-size: 12px; font-weight: bold; color: #ffffff; text-decoration: none; display: inline-block; padding: 8px 16px;">Pay via UPI App</a>
              </td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
  </div>

  <!-- Payment Confirmation Note -->
  <div class="notice-bg" style="background-color: #f1f5f9; border-left: 4px solid #2563eb; border-radius: 4px; padding: 12px 16px; margin-bottom: 24px; font-size: 13px; color: #334155;">
    <strong>Already completed your payment?</strong> Please reply to this email with your transaction reference number or a screenshot so we can promptly update your ledger account.
  </div>

  <p class="text-muted" style="font-size: 13px; color: #64748b; margin-bottom: 24px;">
    For invoice discrepancies or balance reconciliation inquiries, please email <a href="mailto:{{ $settings['company.accountsEmail'] ?? 'accounts@bxamra.dev' }}" style="color: #2563eb; text-decoration: none;">{{ $settings['company.accountsEmail'] ?? 'accounts@bxamra.dev' }}</a>.
  </p>

  <p class="text-main" style="margin-bottom: 0; font-size: 14px; color: #334155;">Best regards,<br><strong>{{ $settings['company.name'] ?? 'BXAMRA IT Solutions' }}</strong></p>
</x-email-layout>
