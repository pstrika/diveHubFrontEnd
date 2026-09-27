{{--
  Group dive reminder (3-day/1-day) - the complete, self-contained HTML
  document, same idea as emails.newsletter-document: sent to Mailgun as
  the single {{{body}}} variable on the "2026 new dh template" template
  (the blank shell - see that file's own comment for why), so the actual
  design lives here in git instead of Mailgun's dashboard (Pablo,
  2026-09-22: "we need to start using the blank template in mailgun - the
  same one we have with newsletters" - replacing the old dedicated
  "tripreminder" Mailgun template). Mailgun stays the sender for this and
  every other one-way send (newsletters, reminders, trip updates); the
  support@divers-hub.com/Graph integration is only for the admin
  console's two-way conversations.

  Expected variables: $preheader, $tripName, $daysAhead, $dateFormatted,
  $timeFormatted, $operatorName (nullable), $waiverLink (nullable),
  $goingNames, $groupUrl.
--}}
<!doctype html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="color-scheme" content="light">
<meta name="supported-color-schemes" content="light">
<title>Divers Hub Trip Reminder</title>
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
@include('emails.trip-reminder-content', [
    'preheader' => $preheader,
    'tripName' => $tripName,
    'daysAhead' => $daysAhead,
    'dateFormatted' => $dateFormatted,
    'timeFormatted' => $timeFormatted,
    'operatorName' => $operatorName,
    'waiverLink' => $waiverLink,
    'goingNames' => $goingNames,
    'groupUrl' => $groupUrl,
])
</body>
</html>
