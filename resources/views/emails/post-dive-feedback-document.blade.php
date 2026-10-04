{{--
  Post-dive feedback request - full HTML document for the "2026 new dh
  template" blank Mailgun shell, same pattern as emails.trip-reminder-document
  (see that file's comment for why a blank shell + a body document, rather
  than a dedicated Mailgun template). Sent by
  App\Console\Commands\SendPostDiveFeedbackRequests when WhatsApp/SMS aren't
  available or didn't accept the send (Pablo, 2026-10-04).

  Expected variables: $preheader, $tripName, $dateFormatted, $siteName
  (nullable), $operatorName (nullable), $wizardUrl.
--}}
<!doctype html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="color-scheme" content="light">
<meta name="supported-color-schemes" content="light">
<title>How was your dive?</title>
<!--[if mso]>
<noscript>
<xml>
<o:OfficeDocumentSettings>
<o:PixelsPerInch>96</o:PixelsPerInch>
</o:OfficeDocumentSettings>
</xml>
</noscript>
<style>
  table, td, div, h1, p { font-family: Arial, sans-serif; }
</style>
<![endif]-->
<style>
  body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
  table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
  img { -ms-interpolation-mode: bicubic; border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; }
  body { margin: 0; padding: 0; width: 100% !important; height: 100% !important; background-color: #eef3f5; }

  a.dh-btn { transition: background-color .15s ease; }
  a.dh-btn:hover { background-color: #0a3f56 !important; }

  @media screen and (max-width: 600px) {
    .dh-container { width: 100% !important; }
    .dh-px { padding-left: 20px !important; padding-right: 20px !important; }
    .dh-hide-mobile { display: none !important; }
    .dh-h1 { font-size: 24px !important; line-height: 30px !important; }
  }
</style>
</head>
<body style="margin:0; padding:0; background-color:#eef3f5;">
@include('emails.post-dive-feedback-content', [
    'preheader' => $preheader,
    'tripName' => $tripName,
    'dateFormatted' => $dateFormatted,
    'siteName' => $siteName,
    'operatorName' => $operatorName,
    'wizardUrl' => $wizardUrl,
])
</body>
</html>
