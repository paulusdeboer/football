@extends('emails.layout')

@section('content')
    <h1 style="margin:0 0 24px; color:#1f2937; font-size:26px; line-height:34px;">Beoordeling gevraagd</h1>
    <p style="margin:0 0 16px; color:#5f7593; font-size:16px; line-height:25px;">Hallo,</p>
    <p style="margin:0 0 24px; color:#5f7593; font-size:16px; line-height:25px;">Wil je de spelers beoordelen voor de wedstrijd van <strong style="color:#1f2937;">{{ $gameDate }}</strong>? Met jouw beoordeling houden we de ratings eerlijk en actueel.</p>
    <p style="margin:0 0 26px; text-align:center;">
        <a href="{{ $url }}" style="display:inline-block; padding:12px 22px; border-radius:5px; background:#2563eb; color:#ffffff; font-size:15px; font-weight:700; text-decoration:none;">Beoordeel spelers</a>
    </p>
    <p style="margin:0 0 24px; color:#5f7593; font-size:16px; line-height:25px;">De beoordelingslink blijft 72 uur geldig.</p>
    <p style="margin:0; color:#5f7593; font-size:16px; line-height:25px;">Sportieve groet,<br><strong style="color:#1f2937;">Vrijdag voetbal</strong></p>
@endsection
