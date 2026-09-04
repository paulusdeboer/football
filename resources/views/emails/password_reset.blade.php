@extends('emails.layout')

@section('content')
    <h1 style="margin:0 0 24px; color:#1f2937; font-size:26px; line-height:34px;">Wachtwoord resetten</h1>
    <p style="margin:0 0 16px; color:#5f7593; font-size:16px; line-height:25px;">Hallo {{ $userName }},</p>
    <p style="margin:0 0 24px; color:#5f7593; font-size:16px; line-height:25px;">We hebben een verzoek ontvangen om het wachtwoord van je account opnieuw in te stellen.</p>
    <p style="margin:0 0 26px; text-align:center;">
        <a href="{{ $resetUrl }}" style="display:inline-block; padding:12px 22px; border-radius:5px; background:#2563eb; color:#ffffff; font-size:15px; font-weight:700; text-decoration:none;">Wachtwoord resetten</a>
    </p>
    <p style="margin:0 0 16px; color:#5f7593; font-size:16px; line-height:25px;">Deze link blijft {{ $expiresIn }} minuten geldig.</p>
    <p style="margin:0 0 24px; color:#5f7593; font-size:16px; line-height:25px;">Heb je dit verzoek niet gedaan? Dan hoef je niets te doen; je wachtwoord blijft ongewijzigd.</p>
    <p style="margin:0; color:#5f7593; font-size:16px; line-height:25px;">Sportieve groet,<br><strong style="color:#1f2937;">Vrijdag voetbal</strong></p>
@endsection
