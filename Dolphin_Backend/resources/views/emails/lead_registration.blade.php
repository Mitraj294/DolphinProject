<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Complete Your Registration</title>
    <style>
        body {
            background: #f4f4f4;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 36px auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            padding: 28px 24px;
            color: #333;
        }
        .logo {
            width: 90px;
            margin: 0 auto 18px auto;
            display: block;
        }
        .title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 12px;
            text-align: center;
        }
        .content {
            font-size: 15px;
            color: #555;
            line-height: 1.5;
            margin-bottom: 20px;
            text-align: left;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background: #007bff;
            color: #fff !important;
            border-radius: 4px;
            text-decoration: none;
            font-weight: bold;
        }
        .muted {
            font-size: 12px;
            color: #888;
            margin-top: 18px;
            text-align: center;
        }
        .fallback-link {
            word-break:break-all;
            color: #007bff;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <img src="{{ url('/images/Logo.svg') }}" alt="Dolphin Logo" class="logo">
        <style>
            /* reduce logo to half of its current size (90px -> 45px) */
            .logo { width: 45px !important; }
        </style>
        @if(!empty($body))
            <div class="content">
                {!! $body !!}
            </div>
        @else
            <div class="title">Complete Your Registration</div>
            <div class="content">
                @if(!empty($name))
                    <p style="margin-top:0;">Hello {{ e($name) }},</p>
                @else
                    <p style="margin-top:0;">Hello,</p>
                @endif
                <p>You have been invited to complete your registration.</p>
                <p>Please click the button below to register:</p>
            </div>

            @if(!empty($registrationUrl))
                <div style="text-align:center; margin-bottom:18px;">
                    <a href="{{ $registrationUrl }}" class="button">Complete Registration</a>
                </div>
            @endif

            <div class="content" style="font-size:13px; color:#666;">
                <p>If you did not request this, you can ignore this email.</p>
            </div>

            @if(!empty($registrationUrl))
                <div class="muted">
                    If you’re having trouble clicking the "Complete Registration" button, copy and paste the URL below into your web browser:<br>
                    <a class="fallback-link" href="{{ $registrationUrl }}">{{ $registrationUrl }}</a>
                </div>
            @endif

        @endif

        <div style="margin-top:20px; text-align:center; font-size:12px; color:#aaa;">
            &copy; {{ date('Y') }} {{ config('app.name', 'Dolphin') }}. All rights reserved.
        </div>
    </div>
</body>
</html>

