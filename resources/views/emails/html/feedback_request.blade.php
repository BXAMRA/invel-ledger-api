<x-email-layout>
  <x-slot:title>
    How was your experience with {{ $settings['company.name'] ?? 'our business' }}?
  </x-slot>

  <div style="text-align: center;">
    <h1 style="margin-top: 0; font-size: 20px; color: #0f172a; font-weight: 600;">How was your experience with us?</h1>
  </div>

  <p style="margin-bottom: 20px; font-size: 14px; text-align: left;">Dear {{ $clientName ?? 'Valued Client' }},</p>

  <p style="margin-bottom: 24px; font-size: 14px; text-align: left;">
    It was a pleasure building and delivering <strong>{{ $projectName }}</strong> for you. We hope the system is serving your team well!
  </p>

  <p style="margin-bottom: 24px; font-size: 14px; text-align: left;">
    As a growing business, reviews mean the world to us. If you enjoyed working with us, could you take 60 seconds to leave us an honest review on Google?
  </p>

  <!-- Review Box -->
  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 0px; padding: 24px; margin-bottom: 24px;">
    <tr>
      <td align="center">
        <p style="font-size: 20px; margin: 0 0 12px 0; color: #eab308;">★★★★★</p>
        <a href="{{ $reviewLink ?? 'https://g.page/r/CeWAnbjVbNLOEBM/review' }}" target="_blank" style="display: inline-block; padding: 12px 28px; background-color: #2563eb; color: #ffffff; text-decoration: none; font-weight: 600; font-size: 14px; border-radius: 6px;">Leave a Google Review</a>
      </td>
    </tr>
  </table>

  <p style="margin-bottom: 0; font-size: 14px; text-align: left;">
    Thank you for your trust and support!<br><br>
    Best regards,<br>
    <strong>{{ $settings['company.name'] ?? 'Company' }}</strong>
  </p>
</x-email-layout>
