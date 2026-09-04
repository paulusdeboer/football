<!doctype html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vrijdag voetbal</title>
</head>
<body style="margin:0; padding:0; background:#eef4fb; color:#263238; font-family:Arial, Helvetica, sans-serif;">
<!--suppress HtmlDeprecatedAttribute -->
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%; background:#eef4fb;">
    <tr>
        <!--suppress HtmlDeprecatedAttribute -->
        <td align="center" style="padding:32px 16px;">
            <!--suppress HtmlDeprecatedAttribute -->
            <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="width:100%; max-width:600px;">
                <tr>
                    <!--suppress HtmlDeprecatedAttribute -->
                    <td align="center" style="padding:0 0 24px;">
                        <img src="{{ $logoUrl }}" width="58" height="58" alt="Vrijdag voetbal" style="display:block; width:58px; height:58px; margin:0 auto 10px;">
                        <div style="color:#1f2937; font-size:20px; font-weight:700; letter-spacing:.3px;">Vrijdag voetbal</div>
                    </td>
                </tr>
                <tr>
                    <td style="background:#ffffff; border:1px solid #dbe4ee; border-radius:10px; padding:36px 40px; box-shadow:0 4px 14px rgba(31,41,55,.07);">
                        @yield('content')
                    </td>
                </tr>
                <tr>
                    <!--suppress HtmlDeprecatedAttribute -->
                    <td align="center" style="padding:22px 10px 0; color:#7b8794; font-size:12px; line-height:18px;">
                        © {{ date('Y') }} Vrijdag voetbal<br>
                        Deze e-mail is automatisch verzonden.
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
