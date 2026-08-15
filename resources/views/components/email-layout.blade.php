<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="color-scheme" content="light only">
  <meta name="supported-color-schemes" content="light">
  <title>{{ $title ?? $settings['company.name'] ?? 'Company Notification' }}</title>
  <style>
    :root {
      color-scheme: light only;
      supported-color-schemes: light;
    }

    @media (prefers-color-scheme: dark) {
      body, .body-bg, .email-bg {
        background-color: #f8fafc !important;
        color: #334155 !important;
      }
      .content-card {
        background-color: #ffffff !important;
      }
      .header-bg {
        background-color: #0f172a !important;
      }
      .text-dark {
        color: #0f172a !important;
      }
      .text-main {
        color: #334155 !important;
      }
      .text-muted {
        color: #64748b !important;
      }
      .footer-bg, .table-header, .notice-bg {
        background-color: #f1f5f9 !important;
      }
      .success-bg {
        background-color: #ecfdf5 !important;
      }
      .warning-bg {
        background-color: #fffbeb !important;
      }
    }

    [data-ogsc] .content-card,
    [data-ogsb] .content-card {
      background-color: #ffffff !important;
    }

    blockquote, blockquote[type="cite"] { margin: 0 !important; padding: 0 !important; border: 0 !important; }
    .AppleOriginalContents { margin: 0 !important; padding: 0 !important; }
    body { margin: 0 !important; padding: 0 !important; width: 100% !important; }
  </style>
</head>
<body class="body-bg" style="margin: 0; padding: 0; background-color: #f8fafc; color: #334155; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased;">
  @php
    foreach ($settings as $key => $val) {
        if (is_string($val) && (str_starts_with(trim($val), '{') || str_starts_with(trim($val), '['))) {
            $decoded = json_decode($val, true);
            if (json_last_error() === JSON_ERROR_NONE) $settings[$key] = $decoded;
        }
    }
  @endphp
  @if(app()->environment('local') && request()->is('preview-*'))
    <style>
      body { padding-left: 250px !important; }
    </style>
    <div style="position: fixed; top: 0; left: 0; bottom: 0; width: 250px; background-color: #0f172a; color: #f8fafc; padding: 24px; box-sizing: border-box; overflow-y: auto; z-index: 9999;">
      <h3 style="margin-top: 0; font-size: 13px; text-transform: uppercase; letter-spacing: 1px; color: #94a3b8; border-bottom: 1px solid #334155; padding-bottom: 12px; margin-bottom: 20px;">Email Previews</h3>
      <ul style="list-style: none; padding: 0; margin: 0; font-size: 14px;">
        <li style="margin-bottom: 16px;"><a target="_parent" href="/preview-invoice-issued" style="color: {{ request()->is('preview-invoice-issued') ? '#ffffff' : '#94a3b8' }}; text-decoration: none; font-weight: {{ request()->is('preview-invoice-issued') ? '600' : '400' }}; display: block;">Invoice Issued</a></li>
        <li style="margin-bottom: 16px;"><a target="_parent" href="/preview-payment-due" style="color: {{ request()->is('preview-payment-due') ? '#ffffff' : '#94a3b8' }}; text-decoration: none; font-weight: {{ request()->is('preview-payment-due') ? '600' : '400' }}; display: block;">Payment Due</a></li>
        <li style="margin-bottom: 16px;"><a target="_parent" href="/preview-payment-overdue" style="color: {{ request()->is('preview-payment-overdue') ? '#ffffff' : '#94a3b8' }}; text-decoration: none; font-weight: {{ request()->is('preview-payment-overdue') ? '600' : '400' }}; display: block;">Payment Overdue</a></li>
        <li style="margin-bottom: 16px;"><a target="_parent" href="/preview-payment-receipt" style="color: {{ request()->is('preview-payment-receipt') ? '#ffffff' : '#94a3b8' }}; text-decoration: none; font-weight: {{ request()->is('preview-payment-receipt') ? '600' : '400' }}; display: block;">Payment Receipt</a></li>
        <li style="margin-bottom: 16px;"><a target="_parent" href="/preview-bi-monthly" style="color: {{ request()->is('preview-bi-monthly') ? '#ffffff' : '#94a3b8' }}; text-decoration: none; font-weight: {{ request()->is('preview-bi-monthly') ? '600' : '400' }}; display: block;">Bi-Monthly Statement</a></li>
        <li style="margin-bottom: 16px;"><a target="_parent" href="/preview-project-quotation" style="color: {{ request()->is('preview-project-quotation') ? '#ffffff' : '#94a3b8' }}; text-decoration: none; font-weight: {{ request()->is('preview-project-quotation') ? '600' : '400' }}; display: block;">Project Quotation</a></li>
        <li style="margin-bottom: 16px;"><a target="_parent" href="/preview-feedback-request" style="color: {{ request()->is('preview-feedback-request') ? '#ffffff' : '#94a3b8' }}; text-decoration: none; font-weight: {{ request()->is('preview-feedback-request') ? '600' : '400' }}; display: block;">Feedback Request</a></li>
      </ul>
      <div style="margin-top: 32px; font-size: 11px; color: #475569; line-height: 1.5;">
        <em>This sidebar is only visible in local dev when viewing preview routes.</em>
      </div>
    </div>
  @endif

  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" class="email-bg" style="background-color: #f8fafc; padding: 0px;">
    <tr>
      <td align="center">
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" class="content-card" style="background-color: #ffffff; border-radius: 0px; border: 0; overflow: hidden; max-width: 600px; width: 100%; margin: 0 auto;">

          <!-- Global Header -->
          <tr>
            <td class="header-bg" style="background-color: #0f172a; padding: 24px 32px; text-align: left;">
              @if(($settings['company.emailHeaderType'] ?? 'logo') === 'logo' && !empty($settings['company.emailLogo']))
                <img src="{{ str_starts_with($settings['company.emailLogo'], 'http') ? $settings['company.emailLogo'] : asset($settings['company.emailLogo']) }}"
                     alt="{{ $settings['company.emailHeaderName'] ?? $settings['company.name'] ?? 'Company Logo' }}"
                     width="150"
                     style="display: block; width: 150px; max-width: 100%; height: auto; border: 0;" />
              @elseif(($settings['company.emailHeaderType'] ?? 'logo') === 'logo')
                <!-- Fallback to default if no logo configured but type is logo -->
                <img src="{{ asset('storage/logos/business-logo.png') }}"
                     alt="{{ $settings['company.emailHeaderName'] ?? $settings['company.name'] ?? 'Company Logo' }}"
                     width="150"
                     style="display: block; width: 150px; max-width: 100%; height: auto; border: 0;" />
              @else
                <h1 style="margin: 0; color: #ffffff; font-size: 24px;">{{ $settings['company.emailHeaderName'] ?? $settings['company.name'] ?? 'Company Name' }}</h1>
              @endif

              @if(!empty($settings['company.emailTagline']))
                <span style="color: #94a3b8; font-size: 15px; display: block; margin-top: 6px; padding-left: 5px; letter-spacing: 1.5px; font-weight: 600;">{{ strtoupper($settings['company.emailTagline']) }}</span>
              @endif
            </td>
          </tr>

          <!-- Dynamic Content Slot -->
          <tr>
            <td style="padding: 32px; color: #334155; line-height: 1.6;">
              {{ $slot }}
            </td>
          </tr>

          <!-- Global Footer -->
          <tr>
            <td class="footer-bg" style="background-color: #f1f5f9; padding: 24px 32px; border-top: 1px solid #e2e8f0; text-align: center; font-size: 12px; color: #64748b;">
              <p class="text-dark" style="margin: 0 0 8px 0; font-weight: 600; color: #334155;">{{ $settings['company.name'] ?? 'Company Name' }}</p>

              <p class="text-muted" style="margin: 0 0 6px 0; color: #64748b;">
                @php
                  $getAddress = function($key) use ($settings) {
                      $val = $settings[$key] ?? null;
                      if (is_array($val)) return $val;
                      return ['value' => $val, 'email' => true];
                  };
                  $addressParts = [];
                  foreach(['addressLine1', 'addressLine2', 'city', 'state', 'country'] as $k) {
                      $f = $getAddress("company.$k");
                      if (!empty($f['value']) && filter_var($f['email'] ?? true, FILTER_VALIDATE_BOOLEAN)) {
                          $addressParts[] = $f['value'];
                      }
                  }
                  $pin = $getAddress('company.pincode');
                @endphp
                {{ implode(', ', $addressParts) }}{{ !empty($pin['value']) && filter_var($pin['email'] ?? true, FILTER_VALIDATE_BOOLEAN) ? (count($addressParts) > 0 ? ' - ' : '') . $pin['value'] : '' }}
              </p>

              <p class="text-muted" style="margin: 0 0 6px 0; color: #64748b;">
                {{ $settings['company.phone'] ?? '' }} | <a href="mailto:{{ $settings['company.email'] ?? '' }}" style="color: #2563eb; text-decoration: none;">{{ $settings['company.email'] ?? '' }}</a>
              </p>

              @if(!empty($settings['company.taxes']) && is_array($settings['company.taxes']))
                @foreach($settings['company.taxes'] as $tax)
                  @if(!empty($tax['email']))
                    <p class="text-muted" style="margin: 0 0 6px 0; color: #64748b;">{{ $tax['label'] }}: {{ $tax['value'] }}</p>
                  @endif
                @endforeach
              @endif

              @if(!empty($settings['company.registrations']) && is_array($settings['company.registrations']))
                @foreach($settings['company.registrations'] as $reg)
                  @if(!empty($reg['email']))
                    <p class="text-muted" style="margin: 0 0 12px 0; color: #64748b;">{{ $reg['label'] }}: {{ $reg['value'] }}</p>
                  @endif
                @endforeach
              @endif

              <p style="margin: 0;">
                @if(!empty($settings['company.socials']) && is_array($settings['company.socials']))
                  @php
                    $activeSocials = array_filter($settings['company.socials'], fn($s) => !empty($s['footer']));
                  @endphp
                  @foreach($activeSocials as $social)
                    <a href="{{ str_starts_with($social['value'], 'http') ? $social['value'] : 'https://'.$social['value'] }}" style="color: #2563eb; text-decoration: none; margin: 0 6px;">{{ $social['label'] }}</a>
                    @if(!$loop->last)
                      <span style="color: #94a3b8;">&bull;</span>
                    @endif
                  @endforeach
                @else
                  @if(!empty($settings['company.website']))
                  <a href="{{ str_starts_with($settings['company.website'], 'http') ? $settings['company.website'] : 'https://'.$settings['company.website'] }}" style="color: #2563eb; text-decoration: none; margin: 0 6px;">Website</a>
                  @endif
                @endif
              </p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>
</body>
</html>
