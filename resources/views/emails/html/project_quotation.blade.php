<x-email-layout>
  <x-slot:title>
    Project Quotation: {{ $projectName }}
  </x-slot>

  <h1 style="margin-top: 0; font-size: 20px; color: #0f172a; font-weight: 600;">Project Quotation & Technical Proposal</h1>
  
  <p style="margin-bottom: 20px; font-size: 14px;">Dear {{ $clientName ?? 'Valued Client' }},</p>
  
  <p style="margin-bottom: 24px; font-size: 14px;">Thank you for discussing your project requirements with us. We have put together a comprehensive quotation and interactive scope document for <strong>{{ $projectName }}</strong>.</p>
  
  <!-- Information Card -->
  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #f8fafc; border-left: 4px solid #2563eb; padding: 16px; margin-bottom: 24px;">
    <tr>
      <td style="font-size: 14px; color: #334155;">
        <p style="margin: 0 0 8px 0;"><strong>Estimated Timeline:</strong> {{ $estimatedTimeline }}</p>
        <p style="margin: 0;"><strong>Estimated Cost:</strong> ₹{{ number_format($quotedAmount, 2) }}</p>
      </td>
    </tr>
  </table>

  <p style="margin-bottom: 24px; font-size: 14px;">You can view the full technical architecture, module breakdowns, delivery milestones, and interactive scope on our online documentation portal below:</p>

  <!-- CTA Button -->
  <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-bottom: 28px;">
    <tr>
      <td style="background-color: #2563eb; border-radius: 6px; text-align: center;">
        <a href="{{ $scopeLink }}" target="_blank" style="display: inline-block; padding: 12px 24px; color: #ffffff; text-decoration: none; font-weight: 600; font-size: 14px;">View Complete Scope Online</a>
      </td>
    </tr>
  </table>

  <p style="margin-bottom: 24px; font-size: 14px;">Feel free to review the documentation and let us know if you would like to schedule a quick call to refine any specifics.</p>

  <p style="margin-bottom: 0; font-size: 14px;">Best regards,<br><strong>{{ $settings['company.name'] ?? 'BXAMRA IT Solutions' }}</strong></p>
</x-email-layout>