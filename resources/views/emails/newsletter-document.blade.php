{{--
  The Weekly Dive newsletter - the complete, self-contained HTML document.
  Rendered server-side and sent to Mailgun as the single {{{body}}}
  variable on the "2026 new dh template" template, which holds nothing but
  that one placeholder (Pablo, 2026-09-21: "we don't need mailgun
  templates other than just a basic one with a HTML placeholder... we get
  full control of the email look if we do this").

  Every real design decision - doctype, head, style reset, layout, colors,
  copy - lives here in git, testable by rendering this view to a file and
  opening it in a browser like any other Blade view, without ever
  touching Mailgun's dashboard again. See
  app/Console/Commands/SendNewsletter.php for the render+send side.

  Expected variables: $preheader, $date, $headline, $body (raw HTML),
  $conditions (raw HTML), $unsubscribeUrl.
--}}
<!doctype html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="color-scheme" content="light">
<meta name="supported-color-schemes" content="light">
<title>Divers Hub Newsletter</title>
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
  /* Client resets - most of these get stripped by Outlook/Gmail anyway,
     which is why every element in newsletter-content.blade.php also
     carries the same rule inline. Kept here only for the clients that do
     honor a <style> block (Apple Mail, most mobile clients). */
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
@include('emails.newsletter-content', [
    'preheader' => $preheader,
    'date' => $date,
    'headline' => $headline,
    'body' => $body,
    'conditions' => $conditions,
    'unsubscribeUrl' => $unsubscribeUrl,
])
</body>
</html>
