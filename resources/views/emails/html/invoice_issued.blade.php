<x-email-layout>
  <x-slot:title>
    Invoice Issued: {{ $document->document_number }}
  </x-slot>

  <h1 style="margin-top: 0; font-size: 20px; color: #0f172a; font-weight: 600;">Invoice Issued {{ $document->document_number }}</h1>
  
  <p style="margin-bottom: 20px; font-size: 14px;">Dear {{ $document->customer->company_name ?: $document->customer->name }},</p>
  
  <p style="margin-bottom: 24px; font-size: 14px;">Please find attached the invoice for recent services rendered. The details are summarized below:</p>
  
  <!-- Summary Card -->
  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; margin-bottom: 24px;">
    <tr>
      <td style="padding: 20px;">
        <p style="margin: 0 0 8px 0; font-size: 13px; color: #64748b;"><strong>Invoice Number:</strong> {{ $document->document_number }}</p>
        <p style="margin: 0 0 8px 0; font-size: 13px; color: #64748b;"><strong>Due Date:</strong> {{ $document->due_date?->format('M d, Y') ?? 'N/A' }}</p>
        <p style="margin: 0; font-size: 16px; color: #0f172a;"><strong>Total Amount Due: ₹{{ number_format($document->balance ?? $document->grand_total, 2) }}</strong></p>
      </td>
    </tr>
  </table>

  <!-- Payment Details -->
  <h2 style="font-size: 15px; color: #0f172a; margin: 0 0 12px 0;">Payment Details</h2>
  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; margin-bottom: 24px; font-size: 13px;">
    <tr>
      <td style="padding: 16px; color: #334155;">
        <p style="margin: 0 0 6px 0;"><strong>Bank Name:</strong> {{ $settings['company.bank.bankName'] ?? '' }}</p>
        <p style="margin: 0 0 6px 0;"><strong>Account Name:</strong> {{ $settings['company.bank.accountName'] ?? '' }}</p>
        <p style="margin: 0 0 6px 0;"><strong>Account Number:</strong> {{ $settings['company.bank.accountNumber'] ?? '' }}</p>
        <p style="margin: 0 0 6px 0;"><strong>IFSC Code:</strong> {{ $settings['company.bank.ifsc'] ?? '' }}</p>
        <p style="margin: 0;"><strong>UPI ID:</strong> {{ $settings['company.bank.upiId'] ?? '' }}</p>
      </td>
    </tr>
  </table>

  <!-- Button -->
  <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-bottom: 24px;">
    <tr>
      <td style="background-color: #0f172a; border-radius: 6px; text-align: center;">
        <a href="{{ $invoiceLink }}" target="_blank" style="display: inline-block; padding: 12px 24px; color: #ffffff; text-decoration: none; font-weight: 600; font-size: 14px;">View Full Invoice Online</a>
      </td>
    </tr>
  </table>

  <p style="font-size: 13px; color: #64748b; margin-bottom: 24px;">
    For invoice discrepancies, please contact <a href="mailto:{{ $settings['company.accountsEmail'] ?? 'accounts@bxamra.dev' }}" style="color: #2563eb; text-decoration: none;">{{ $settings['company.accountsEmail'] ?? 'accounts@bxamra.dev' }}</a>.
  </p>

  <p style="margin-bottom: 0; font-size: 14px;">Best regards,<br><strong>{{ $settings['company.name'] ?? 'BXAMRA IT Solutions' }}</strong></p>
</x-email-layout>