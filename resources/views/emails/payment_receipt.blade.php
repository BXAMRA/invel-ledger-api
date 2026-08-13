<x-email-layout>
  <x-slot:title>
    Payment Confirmation: {{ $document->document_number }}
  </x-slot>

  <h1 class="text-dark" style="margin-top: 0; font-size: 20px; color: #0f172a; font-weight: 600;">Payment Confirmation & Receipt</h1>

  <p class="text-main" style="margin-bottom: 20px; font-size: 14px; color: #334155;">
    Dear {{ $document->customer->company_name ?: ($document->customer->name ?: 'Valued Client') }},
  </p>

  <p class="text-main" style="margin-bottom: 24px; font-size: 14px; color: #334155; line-height: 1.6;">
    This email is to confirm that we have successfully received and processed a payment of <strong>{{ $settings['invoice.currency'] ?? '₹' }}{{ number_format($payment->amount, 2) }}</strong> via <strong>{{ $payment->payment_method ?? 'Unknown' }}</strong>
    @if($payment->reference_number)
      (Ref <strong>#{{ strtoupper($payment->reference_number) }}</strong>)
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


  <!-- Parse Mobile Wallets JSON -->
  @php
    $rawWallets = json_decode($settings['company.mobileWallets'] ?? '[]', true);
    $activeWallets = is_array($rawWallets) ? array_filter($rawWallets, function($w) {
        return !isset($w['_deleted']) || $w['_deleted'] === false;
    }) : [];
  @endphp

  <!-- Dynamic Remittance Options -->
  @if($document->balance > 0 || (isset($otherPendingInvoices) && count($otherPendingInvoices) > 0))
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

      <!-- Mobile Wallet Payments & Links -->
      @if(!empty($activeWallets) || !empty($paymentLinks))
      <tr>
        <td valign="top" style="padding-top: 20px; border-top: 1px dashed #cbd5e1;">
          <strong style="color: #0f172a; font-size: 13px; display: block; margin-bottom: 12px;">Mobile Wallets & Online Payment</strong>

          <!-- Text Details for Wallets -->
          @if(!empty($activeWallets))
          <table width="100%" cellspacing="0" cellpadding="0" border="0" style="font-size: 13px; color: #475569; margin-bottom: 12px;">
            @foreach($activeWallets as $wallet)
            <tr>
              <td style="padding-bottom: 6px; width: 85px; color: #64748b;">{{ $wallet['provider'] ?? 'Wallet' }}:</td>
              <td style="padding-bottom: 6px; color: #0f172a; font-weight: 500;">{{ $wallet['value'] ?? '' }}</td>
            </tr>
            @endforeach
          </table>
          @endif

          <!-- Payment Buttons -->
          @if(!empty($paymentLinks))
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
          @endif
        </td>
      </tr>
      @endif
    </table>
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

            @if(!empty($otherInvoice['due']))
            <span style="color: #64748b; font-size: 13px;">Due: {{ \Carbon\Carbon::parse($otherInvoice['due'])->format('F d, Y') }}</span><br>
            @endif

            <span style="color: #64748b; font-size: 13px;">Total: {{ $settings['invoice.currency'] ?? '₹' }}{{ number_format($otherInvoice['total'], 2) }}</span><br>
            <span style="color: #dc2626; font-size: 13px; font-weight: 600;">Pending: {{ $settings['invoice.currency'] ?? '₹' }}{{ number_format($otherInvoice['pending'], 2) }}</span>
          </td>
          <td style="padding: 14px 16px; text-align: right; vertical-align: middle; width: 30%;">
            <a href="{{ $otherInvoice['link'] }}" style="display: inline-block; padding: 8px 16px; background-color: #2563eb; color: #ffffff; text-decoration: none; font-weight: 600; font-size: 12px; border-radius: 4px;">Pay Online</a>
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
    <strong>{{ $settings['company.name'] ?? 'BXAMRA IT Solutions' }}</strong>
  </p>
</x-email-layout>
