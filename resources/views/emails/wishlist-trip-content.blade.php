{{--
  Wishlist "new trip" notification - the <body> content only, included
  from emails.wishlist-trip-document. Same branded shell as the
  newsletter/trip-reminder emails. Currently sent by a separate service
  (wishedSites/emailQueueFlush - a Python Azure Function, not this repo)
  as a single plain-text sentence with no HTML at all (Pablo, 2026-09-22:
  asked for a sample of "a trip that shows up when in my wishlist" using
  the new blank-template design, for review before deciding how that
  service would actually produce this HTML).

  Expected variables: $preheader, $siteName, $dateFormatted,
  $operatorName (nullable), $tripUrl, $wishlistUrl.
--}}
<div style="display:none; max-height:0; overflow:hidden; mso-hide:all;">
  {{ $preheader }}
</div>
<div style="display:none; max-height:0; overflow:hidden; mso-hide:all;">
  &#8199;&#847; &#8199;&#847; &#8199;&#847; &#8199;&#847; &#8199;&#847; &#8199;&#847; &#8199;&#847; &#8199;&#847; &#8199;&#847; &#8199;&#847;
</div>

<center style="width:100%; background-color:#eef3f5;">
<!--[if mso]>
<table role="presentation" width="600" align="center" cellpadding="0" cellspacing="0" border="0"><tr><td>
<![endif]-->
<div class="dh-container" style="max-width:600px; margin:0 auto;">

  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%; max-width:600px; margin:0 auto; background-color:#ffffff;">

    <!-- Header / logo band -->
    <tr>
      <td align="center" style="background-color:#0b2a3a; padding:28px 24px;">
        <table role="presentation" cellpadding="0" cellspacing="0" border="0">
          <tr>
            <td style="padding-right:10px; vertical-align:middle;">
              <img src="https://divers-hub.com/assets/img/logos/logo_circle_small.png" width="36" height="36" alt="Divers Hub" style="display:block; border-radius:50%; width:36px; height:36px;">
            </td>
            <td style="vertical-align:middle;">
              <span style="font-family:Arial, Helvetica, sans-serif; font-size:19px; font-weight:bold; color:#ffffff; letter-spacing:.3px;">Divers Hub</span>
            </td>
          </tr>
        </table>
      </td>
    </tr>

    <!-- Eyebrow -->
    <tr>
      <td class="dh-px" align="center" style="padding:22px 32px 0;">
        <span style="font-family:Arial, Helvetica, sans-serif; font-size:11px; font-weight:bold; letter-spacing:1.5px; text-transform:uppercase; color:#0e7c9e;">
          From your wishlist
        </span>
      </td>
    </tr>

    <!-- Hero headline -->
    <tr>
      <td class="dh-px" align="center" style="padding:10px 32px 24px;">
        <h1 class="dh-h1" style="margin:0; font-family:Georgia, 'Times New Roman', serif; font-size:28px; line-height:34px; font-weight:normal; color:#0b2a3a;">
          New trip to {{ $siteName }}
        </h1>
      </td>
    </tr>

    <!-- Divider -->
    <tr>
      <td style="padding:0 32px;"><table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="border-top:1px solid #e2e8ec; font-size:0; line-height:0;">&nbsp;</td></tr></table></td>
    </tr>

    <!-- Body -->
    <tr>
      <td class="dh-px" style="padding:28px 32px 0; font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:24px; color:#1f2d36;">
        <p style="margin:0 0 16px;">A dive site on your wishlist just got a new trip on the calendar:</p>
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f4f7f8; border-radius:8px; margin-bottom:16px;">
          <tr>
            <td style="padding:16px 20px;">
              <span style="display:block; font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:22px; color:#0b2a3a;">
                <b>{{ $siteName }}</b><br>
                {{ $dateFormatted }}<br>
                @if($operatorName)
                  Operator: {{ $operatorName }}
                @endif
              </span>
            </td>
          </tr>
        </table>
      </td>
    </tr>

    <!-- CTA button -->
    <tr>
      <td align="center" style="padding:8px 32px 32px;">
        <table role="presentation" cellpadding="0" cellspacing="0" border="0">
          <tr>
            <td align="center" style="border-radius:6px; background-color:#0b2a3a;">
              <a class="dh-btn" href="{{ $tripUrl }}" style="display:inline-block; padding:13px 28px; font-family:Arial, Helvetica, sans-serif; font-size:14px; font-weight:bold; color:#ffffff; text-decoration:none; border-radius:6px;">
                View this trip
              </a>
            </td>
          </tr>
        </table>
      </td>
    </tr>

  </table>

  <!-- Footer -->
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%; max-width:600px; margin:0 auto;">
    <tr>
      <td class="dh-px" align="center" style="padding:28px 32px;">
        <p style="margin:0 0 12px; font-family:Arial, Helvetica, sans-serif; font-size:12px; line-height:18px; color:#8a99a3;">
          You're getting this because {{ $siteName }} is on your Divers Hub wishlist.
        </p>
        <p style="margin:0 0 12px; font-family:Arial, Helvetica, sans-serif; font-size:12px; line-height:18px; color:#8a99a3;">
          <a href="{{ $wishlistUrl }}" style="color:#5a6b78; text-decoration:underline;">Manage your wishlist</a>
        </p>
        <p style="margin:0; font-family:Arial, Helvetica, sans-serif; font-size:11px; line-height:16px; color:#a8b3ba;">
          Divers Hub &middot; 681 Ranch Rd &middot; Weston, FL 33326<br>
          &copy; {{ date('Y') }} Divers Hub. All rights reserved.
        </p>
      </td>
    </tr>
  </table>

</div>
<!--[if mso]>
</td></tr></table>
<![endif]-->
</center>
