<x-email-layout>
  <x-slot:title>
    Payment Overdue: {{ $document->document_number }}
  </x-slot>

  <p class="text-main" style="margin-bottom: 20px; font-size: 14px; color: #334155;">
    Hi {{ $document->customer->contact_person ?: $document->customer->company_name }},
  </p>

  <p class="text-main" style="margin-bottom: 20px; font-size: 14px; color: #334155;">
    We hope you're having a great day!
  </p>

  <p class="text-main" style="margin-bottom: 24px; font-size: 14px; color: #334155;">
    We are reaching out because payment for invoice <strong>{{ $document->document_number }}</strong>, which was due on <strong>{{ $document->due_date?->format('F d, Y') ?? 'N/A' }}</strong> is now past due. We know how busy things can get, so we just wanted to float this to the top of your inbox.
  </p>

  <!-- PDF Attachment Notice -->
  <p class="notice-bg" style="margin-bottom: 24px; font-size: 14px; background-color: #f1f5f9; padding: 12px 16px; border-left: 4px solid #2563eb; border-radius: 4px; color: #1e293b;">
    <strong>NOTE:</strong> A PDF copy of the invoice is attached to this email for your convenience.
  </p>

  <!-- Friendly Overdue Notice -->
  <div class="notice-bg" style="background-color: #fffbeb; border-left: 4px solid #f59e0b; border-radius: 4px; padding: 14px 16px; margin-bottom: 24px; font-size: 14px; color: #92400e;">
    <strong style="display: block; margin-bottom: 4px;">Outstanding Balance: ₹{{ number_format($document->balance, 2) }}</strong>
    Please process this payment at your earliest convenience to bring your account up to date. We have included the payment options below!
  </div>

  <!-- Payment Details Block -->
  <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; margin-bottom: 24px;">
    <h3 style="margin-top: 0; margin-bottom: 16px; font-size: 14px; color: #0f172a; text-transform: uppercase; letter-spacing: 0.5px;">Remittance Options</h3>

    <table width="100%" cellspacing="0" cellpadding="0" border="0">
      <!-- Bank Transfer Row -->
      <tr>
        <td valign="top" style="padding-bottom: 20px;">
          <strong style="color: #0f172a; font-size: 13px; display: block; margin-bottom: 12px;">Bank Transfer</strong>
          <table width="100%" cellspacing="0" cellpadding="0" border="0" style="font-size: 13px; color: #475569;">
            <tr>
              <td style="padding-bottom: 6px; width: 85px; color: #64748b;">A/C Name:</td>
              <td style="padding-bottom: 6px; color: #0f172a; font-weight: 500;">{{ $settings['company.bank.accountName'] ?? '' }}</td>
            </tr>
            <tr>
              <td style="padding-bottom: 6px; color: #64748b;">Bank:</td>
              <td style="padding-bottom: 6px; color: #0f172a; font-weight: 500;">{{ $settings['company.bank.bankName'] ?? '' }} ({{ $settings['company.bank.branch'] ?? '' }})</td>
            </tr>
            <tr>
              <td style="padding-bottom: 6px; color: #64748b;">Account:</td>
              <td style="padding-bottom: 6px; color: #0f172a; font-weight: 500;">{{ $settings['company.bank.accountNumber'] ?? '' }}</td>
            </tr>
            <tr>
              <td style="padding-bottom: 6px; color: #64748b;">IFSC:</td>
              <td style="padding-bottom: 6px; color: #0f172a; font-weight: 500;">{{ $settings['company.bank.ifsc'] ?? '' }}</td>
            </tr>
            @if(!empty($settings['company.bank.swift']))
            <tr>
              <td style="padding-bottom: 0px; color: #64748b;">SWIFT:</td>
              <td style="padding-bottom: 0px; color: #0f172a; font-weight: 500;">{{ $settings['company.bank.swift'] }}</td>
            </tr>
            @endif
          </table>
        </td>
      </tr>

      <!-- Mobile Wallet Payments -->
      @if(!empty($paymentLinks))
      <tr>
        <td valign="top" style="padding-top: 20px; border-top: 1px dashed #cbd5e1;">
          <strong style="color: #0f172a; font-size: 13px; display: block; margin-bottom: 12px;">Pay Online</strong>
          <table cellspacing="0" cellpadding="0" border="0">
            <tr>
              @foreach($paymentLinks as $provider => $link)
              <td align="center" style="padding-right: 8px; padding-bottom: 8px;">
                <a href="{{ $link }}"
                   style="font-size: 12px; font-weight: bold; color: #ffffff; text-decoration: none; display: inline-block; padding: 10px 18px; background-color: #2563eb; border-radius: 4px;">
                  Pay Now via {{ strtoupper($provider) }}
                </a>
              </td>
              @endforeach
            </tr>
          </table>
        </td>
      </tr>
      @endif
    </table>
  </div>

  <p class="text-main" style="margin-bottom: 24px; font-size: 13px; color: #334155;">
    If you've already sent this over, please accept our apologies and just reply to let us know so we can update your account!
  </p>

  <p class="text-muted" style="font-size: 13px; color: #64748b; margin-bottom: 24px;">
    Got a question about this invoice? Just reply to this email or reach out to <a href="mailto:{{ $settings['company.accountsEmail'] ?? 'accounts@bxamra.dev' }}" style="color: #2563eb; text-decoration: none;">{{ $settings['company.accountsEmail'] ?? 'accounts@bxamra.dev' }}</a>.
  </p>

  <p class="text-main" style="margin-bottom: 0; font-size: 14px; color: #334155;">
    Best regards,<br>
    <strong>{{ $settings['company.name'] ?? 'BXAMRA IT Solutions' }}</strong>
  </p>
</x-email-layout>
