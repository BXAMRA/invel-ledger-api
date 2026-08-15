@props(['settings' => []])
@php
  foreach ($settings as $key => $val) {
      if (is_string($val) && (str_starts_with(trim($val), '{') || str_starts_with(trim($val), '['))) {
          $decoded = json_decode($val, true);
          if (json_last_error() === JSON_ERROR_NONE) $settings[$key] = $decoded;
      }
  }
@endphp
{{ strtoupper($settings['company.emailHeaderName'] ?? $settings['company.name'] ?? 'Company Name') }} {{  strtoupper($settings['company.emailTagline']) }}

======================================================================

{{ $slot }}

======================================================================
{{ $settings['company.name'] ?? 'Company Name' }}
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
  $addressLine = implode(', ', $addressParts) . (!empty($pin['value']) && filter_var($pin['email'] ?? true, FILTER_VALIDATE_BOOLEAN) ? (count($addressParts) > 0 ? ' - ' : '') . $pin['value'] : '');
@endphp
@if(!empty(trim($addressLine)))
{{ $addressLine }}
@endif
@if(!empty($settings['company.phone']) || !empty($settings['company.email']))
{{ $settings['company.phone'] ?? '' }}{{ (!empty($settings['company.phone']) && !empty($settings['company.email'])) ? ' | ' : '' }}{{ $settings['company.email'] ?? '' }}
@endif
@if(!empty($settings['company.taxes']) && is_array($settings['company.taxes']))
@foreach($settings['company.taxes'] as $tax)
@if(!empty($tax['email']))
{{ $tax['label'] }}: {{ $tax['value'] }}
@endif
@endforeach
@endif
@if(!empty($settings['company.registrations']) && is_array($settings['company.registrations']))
@foreach($settings['company.registrations'] as $reg)
@if(!empty($reg['email']))
{{ $reg['label'] }}: {{ $reg['value'] }}
@endif
@endforeach
@endif
@if(!empty($settings['company.socials']) && is_array($settings['company.socials']))
@php
$activeSocials = array_filter($settings['company.socials'], fn($s) => !empty($s['footer']));
@endphp
@foreach($activeSocials as $social)
{{ $social['label'] }}: {{ str_starts_with($social['value'], 'http') ? $social['value'] : 'https://'.$social['value'] }}
@endforeach
@elseif(!empty($settings['company.website']))
Website: {{ str_starts_with($settings['company.website'], 'http') ? $settings['company.website'] : 'https://'.$settings['company.website'] }}
@endif
