<x-email-layout>
  <x-slot:title>
    Pending Invoice Reminder
  </x-slot>

  <h1 class="text-dark" style="margin-top: 0; font-size: 20px; color: #0f172a; font-weight: 600;">Statement of Account</h1>

  <p class="text-main" style="margin-bottom: 20px; font-size: 14px; color: #334155;">
    Hi {{ $customer->contact_person ?: $customer->company_name ?: 'Valued Client' }},
  </p>

  <p class="text-main" style="margin-bottom: 20px; font-size: 14px; color: #334155;">
    We hope you're having a great day!
  </p>

  <p class="text-main" style="margin-bottom: 24px; font-size: 14px; color: #334155;">
    We are reaching out to provide you with a quick bi-monthly account balance reminder regarding pending invoices against your account as of the close of business yesterday.
  </p>

  <!-- PDF Attachment Notice -->
  <p class="notice-bg" style="margin-bottom: 24px; font-size: 14px; background-color: #f1f5f9; padding: 12px 16px; border-left: 4px solid #2563eb; border-radius: 4px; color: #1e293b;">
    <strong>NOTE:</strong> PDF copies for the pending invoices listed below have been attached to this email for your convenience.
  </p>

  <h3 style="margin-top: 0; margin-bottom: 16px; font-size: 14px; color: #0f172a; text-transform: uppercase; letter-spacing: 0.5px;">Pending Invoices</h3>

  <!-- Stacked Mobile-Friendly Cards -->
  @foreach($documents as $doc)
  @php
    $dueDate = \Carbon\Carbon::parse($doc->due_date);
    $daysDiff = now()->startOfDay()->diffInDays($dueDate->startOfDay(), false);

    $statusText = '';
    $statusColor = '#64748b'; // neutral gray for upcoming

    if ($daysDiff < 0) {
        $statusText = "Overdue by " . abs($daysDiff) . " days";
        $statusColor = '#dc2626'; // red
    } elseif ($daysDiff == 0) {
        $statusText = "Due today";
        $statusColor = '#d97706'; // amber
    } else {
        $statusText = "Due in $daysDiff days";
    }
  @endphp
  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom: 12px; background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; overflow: hidden;">
    <tr>
          <td style="padding: 14px 16px; width: 65%;">
            <strong style="color: #0f172a; font-size: 14px; display: block; margin-bottom: 4px;">{{ $doc->document_number }}</strong>
            <span style="color: {{ $statusColor }}; font-size: 13px; font-weight: 500;">Status: {{ $statusText }} ({{ $dueDate->format('M d, Y') }})</span><br>
            <span style="color: #64748b; font-size: 13px;">Total: {{ $settings['invoice.currency'] ?? '₹' }}{{ number_format($doc->grand_total, 2) }}</span><br>
            <span style="color: #dc2626; font-size: 13px; font-weight: 600;">Pending: {{ $settings['invoice.currency'] ?? '₹' }}{{ number_format($doc->balance, 2) }}</span>
          </td>
          <td style="padding: 14px 16px; text-align: right; vertical-align: middle; width: 35%;">
            @if(!empty($paymentLinks[$doc->document_number]))
              @foreach($paymentLinks[$doc->document_number] as $provider => $link)
                <a href="{{ $link }}" style="display: inline-block; padding: 8px 14px; margin-bottom: 4px; background-color: #2563eb; color: #ffffff; text-decoration: none; font-weight: 600; font-size: 12px; border-radius: 4px;">Pay Now via {{ strtoupper($provider) }}</a><br>
              @endforeach
            @endif
          </td>
        </tr>
  </table>
  @endforeach

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
  <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; margin-top: 24px; margin-bottom: 24px;">
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

      <!-- Text Details for Wallets -->
      @if(!empty($activeWallets))
      <tr>
        <td valign="top" style="padding-top: 20px; border-top: 1px dashed #cbd5e1;">
          <strong style="color: #0f172a; font-size: 13px; display: block; margin-bottom: 12px;">Mobile Wallets</strong>
          <table width="100%" cellspacing="0" cellpadding="0" border="0" style="font-size: 13px; color: #475569;">
            @foreach($activeWallets as $wallet)
            <tr>
              <td style="padding-bottom: 6px; width: 85px; color: #64748b;">{{ $wallet['provider'] ?? 'Wallet' }}:</td>
              <td style="padding-bottom: 6px; color: #0f172a; font-weight: 500;">{{ $wallet['value'] ?? '' }}</td>
            </tr>
            @endforeach
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
    Got a question about this statement? Just reply to this email or reach out to <a href="mailto:{{ $settings['company.accountsEmail'] ?? 'accounts@bxamra.dev' }}" style="color: #2563eb; text-decoration: none;">{{ $settings['company.accountsEmail'] ?? 'accounts@bxamra.dev' }}</a>.
  </p>

  <p class="text-main" style="margin-bottom: 0; font-size: 14px; color: #334155;">
    Best regards,<br>
    <strong>{{ $settings['company.name'] ?? 'BXAMRA IT Solutions' }}</strong>
  </p>
</x-email-layout>
