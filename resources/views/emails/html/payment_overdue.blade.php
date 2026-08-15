<x-email-layout>
  <x-slot:title>
    Payment Overdue: {{ $document->document_number }}
  </x-slot>

  <h1 style="margin-top: 0; margin-bottom: 20px; font-size: 20px; color: #dc2626; font-weight: 600;">Payment Overdue: {{ $document->document_number }}</h1>

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

  <!-- Parse Mobile Wallets -->
  @php
    $rawWallets = $settings['company.mobileWallets'] ?? [];
    if (is_string($rawWallets)) {
        $rawWallets = json_decode($rawWallets, true) ?? [];
    }
    $activeWallets = is_array($rawWallets) ? array_filter($rawWallets, function($w) {
        return empty($w['_deleted']);
    }) : [];
  @endphp

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

      <!-- Mobile Wallet Payments & Links -->
      @if(!empty($activeWallets) || !empty($paymentLinks))
      <tr>
        <td valign="top" style="padding-top: 20px; border-top: 1px dashed #cbd5e1;">
          <strong style="color: #0f172a; font-size: 13px; display: block; margin-bottom: 12px;">Mobile Wallets & Online Payment</strong>
          <table width="100%" cellspacing="0" cellpadding="0" border="0" style="font-size: 13px; color: #475569;">
            @php $handledProviders = []; @endphp
            @if(!empty($activeWallets))
              @foreach($activeWallets as $wallet)
                @php 
                  $provider = $wallet['provider'] ?? 'Wallet'; 
                  $provLower = strtolower($provider);
                  $handledProviders[] = $provLower;
                  $link = $paymentLinks[$provider] ?? $paymentLinks[$provLower] ?? $paymentLinks[strtoupper($provider)] ?? null;
                @endphp
                <tr>
                  <td style="padding-bottom: 10px; width: 85px; color: #64748b; vertical-align: middle;">{{ $provider }}:</td>
                  <td style="padding-bottom: 10px; color: #0f172a; font-weight: 500; vertical-align: middle;">{{ $wallet['value'] ?? '' }}</td>
                  <td style="padding-bottom: 10px; text-align: right; vertical-align: middle;">
                    @if($link)
                      <a href="{{ $link }}" style="font-size: 12px; font-weight: bold; color: #ffffff; text-decoration: none; display: inline-block; padding: 8px 14px; background-color: #2563eb; border-radius: 4px;">Pay Now via {{ strtoupper($provider) }}</a>
                    @endif
                  </td>
                </tr>
              @endforeach
            @endif

            @if(!empty($paymentLinks))
              @foreach($paymentLinks as $provider => $link)
                @if(!in_array(strtolower($provider), $handledProviders))
                <tr>
                  <td style="padding-bottom: 10px; width: 85px; color: #64748b; vertical-align: middle;">{{ strtoupper($provider) }}:</td>
                  <td style="padding-bottom: 10px; color: #0f172a; font-weight: 500; vertical-align: middle;">Online Link</td>
                  <td style="padding-bottom: 10px; text-align: right; vertical-align: middle;">
                    <a href="{{ $link }}" style="font-size: 12px; font-weight: bold; color: #ffffff; text-decoration: none; display: inline-block; padding: 8px 14px; background-color: #2563eb; border-radius: 4px;">Pay Now via {{ strtoupper($provider) }}</a>
                  </td>
                </tr>
                @endif
              @endforeach
            @endif
          </table>
        </td>
      </tr>
      @endif
    </table>
  </div>

  <!-- Payment Confirmation Note -->
  <div class="notice-bg" style="background-color: #f1f5f9; border-left: 4px solid #2563eb; border-radius: 4px; padding: 12px 16px; margin-bottom: 24px; font-size: 13px; color: #334155;">
    <strong>Already completed your payment?</strong> Please reply to this email with your transaction reference number or a screenshot so we can promptly update your ledger account.
  </div>

  <p class="text-muted" style="font-size: 13px; color: #64748b; margin-bottom: 24px;">
    Got a question about this invoice? Just reply to this email or reach out to <a href="mailto:{{ $settings['company.accountsEmail'] ?? 'accounts@bxamra.dev' }}" style="color: #2563eb; text-decoration: none;">{{ $settings['company.accountsEmail'] ?? 'accounts@bxamra.dev' }}</a>.
  </p>

  <p class="text-main" style="margin-bottom: 0; font-size: 14px; color: #334155;">
    Best regards,<br>
    <strong>{{ $settings['company.name'] ?? 'BXAMRA IT Solutions' }}</strong>
  </p>
</x-email-layout>
