<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>Your DisasterResource+ account is ready</title>
<!--[if mso]>
<noscript><xml><o:OfficeDocumentSettings><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml></noscript>
<![endif]-->
<style>
  body, table, td { font-family: Arial, Helvetica, sans-serif; }
  body { margin:0; padding:0; background-color:#F3F1EA; -webkit-text-size-adjust:100%; }
  table { border-collapse:collapse; }
  img { border:0; line-height:100%; outline:none; text-decoration:none; }
  a { color:#B5761F; }
  .btn:hover { background-color:#9C6419 !important; }
  @media only screen and (max-width:600px){
    .container { width:100% !important; }
    .px { padding-left:20px !important; padding-right:20px !important; }
  }
</style>
</head>
<body style="margin:0;padding:0;background-color:#F3F1EA;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#F3F1EA;">
    <tr>
      <td align="center" style="padding:32px 16px;">

        <table role="presentation" class="container" width="600" cellpadding="0" cellspacing="0" style="width:600px;max-width:600px;background-color:#FFFFFF;border:1px solid #E1DCCE;">

          <!-- Header -->
          <tr>
            <td style="background-color:#0B0D0C;padding:22px 32px;">
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td valign="middle" style="width:34px;">
                    <table role="presentation" cellpadding="0" cellspacing="0" style="width:28px;height:28px;border:2px solid #F2A63C;">
                      <tr><td align="center" valign="middle" style="font-family:Arial,Helvetica,sans-serif;font-size:15px;font-weight:bold;color:#F2A63C;line-height:24px;">+</td></tr>
                    </table>
                  </td>
                  <td valign="middle" style="padding-left:10px;font-family:Arial,Helvetica,sans-serif;font-size:18px;letter-spacing:1px;color:#EDEDE6;font-weight:bold;text-transform:uppercase;">
                    DISASTER<span style="color:#F2A63C;">RESOURCE+</span>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- Status strip -->
          <tr>
            <td style="background-color:#DEF2EA;border-bottom:1px solid #E1DCCE;padding:10px 32px;font-family:'Courier New',Courier,monospace;font-size:11px;letter-spacing:1px;color:#1F7A5E;text-transform:uppercase;">
              ACCOUNT PROVISIONED &middot; ACCESS GRANTED
            </td>
          </tr>

          <!-- Body -->
          <tr>
            <td class="px" style="padding:32px;">
              <p style="margin:0 0 6px;font-family:Arial,Helvetica,sans-serif;font-size:20px;font-weight:bold;color:#1D1F1E;text-transform:uppercase;letter-spacing:0.5px;">
                Welcome to DisasterResource+
              </p>
              <p style="margin:0 0 24px;font-family:Arial,Helvetica,sans-serif;font-size:14px;line-height:22px;color:#54605B;">
                Hi {{ $userName }},<br>
                An account has been created for you on DisasterResource+, the resource optimization and visualization system for disaster response. Your access details are below.
              </p>

              <!-- Account details box -->
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #E1DCCE;margin-bottom:24px;">
                <tr>
                  <td style="padding:14px 16px;border-bottom:1px solid #E1DCCE;font-family:Arial,Helvetica,sans-serif;font-size:12px;color:#8B9089;text-transform:uppercase;letter-spacing:0.5px;">Name</td>
                  <td style="padding:14px 16px;border-bottom:1px solid #E1DCCE;font-family:Arial,Helvetica,sans-serif;font-size:13px;color:#1D1F1E;text-align:right;">{{ $userName }}</td>
                </tr>
                <tr>
                  <td style="padding:14px 16px;border-bottom:1px solid #E1DCCE;font-family:Arial,Helvetica,sans-serif;font-size:12px;color:#8B9089;text-transform:uppercase;letter-spacing:0.5px;">Email</td>
                  <td style="padding:14px 16px;border-bottom:1px solid #E1DCCE;font-family:Arial,Helvetica,sans-serif;font-size:13px;color:#1D1F1E;text-align:right;">{{ $userEmail }}</td>
                </tr>
                <tr>
                  <td style="padding:14px 16px;font-family:Arial,Helvetica,sans-serif;font-size:12px;color:#8B9089;text-transform:uppercase;letter-spacing:0.5px;">Role</td>
                  <td style="padding:14px 16px;text-align:right;">
                    <span style="display:inline-block;padding:3px 10px;background-color:#F6E4C4;color:#B5761F;font-family:'Courier New',Courier,monospace;font-size:11px;letter-spacing:0.5px;">{{ $userRole }}</span>
                  </td>
                </tr>
              </table>

              <!-- CTA button -->
              <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 auto 8px;">
                <tr>
                  <td align="center" class="btn" style="background-color:#B5761F;">
                    <a href="{{ $setPasswordUrl }}" target="_blank" style="display:inline-block;padding:13px 28px;font-family:Arial,Helvetica,sans-serif;font-size:13px;font-weight:bold;letter-spacing:0.5px;color:#FFFFFF;text-decoration:none;text-transform:uppercase;">
                      Set your password &amp; log in
                    </a>
                  </td>
                </tr>
              </table>

              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-left:3px solid #C8C1AE;background-color:#F3F1EA;margin-top:16px;">
                <tr>
                  <td style="padding:14px 16px;font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:19px;color:#54605B;">
                    This link expires in <strong>{{ $expiryHours }} hours</strong>. If you weren't expecting this account, contact your system administrator instead of clicking the link.
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td style="background-color:#0B0D0C;padding:20px 32px;font-family:Arial,Helvetica,sans-serif;font-size:11px;color:#8B9089;">
              DisasterResource+ &middot; Resource Optimization &amp; Visualization System for Disaster Response<br>
              This is an automated message — please do not reply directly to this email.
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>
</body>
</html>
