<!doctype html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <style>
        /* Basic email styles */
        body { background-color: #edf2f7; font-family: Arial, sans-serif; margin:0; padding:20px; }
        .container { max-width:600px; margin:0 auto; background:#ffffff; padding:28px; border-radius:4px; }
        .logo { text-align:center; color:#9aa4ae; margin-bottom:12px; }
        .greeting { font-weight:700; margin-bottom:8px; }
        .body { color:#374151; line-height:1.5; margin-bottom:18px; }
        .button { display:inline-block; background:#ffffff; color:1f2937; padding:10px 16px; text-decoration:none; border-radius:4px; }
        .muted { color:#9aa4ae; font-size:12px; margin-top:18px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">{{-- optional logo area --}}</div>

        <div class="greeting">{{ isset($displayName) && $displayName ? 'Hello ' . e($displayName) . '!' : 'Hello!' }}</div>

        <div class="body">
            {!! nl2br(e($announcement->body)) !!}
        </div>

        <p>
            <a class="button" href="http://127.0.0.1:8080/get-notification">View Announcement</a>
        </p>
!
        <div class="muted">Thank you !<br />Regards,<br />{{ config('app.name', 'Dolphin') }}</div>

    <div style="margin-top:18px; font-size:11px; color:#b0b7bd;">If you're having trouble clicking the "View Announcement" button, copy and paste the URL below into your web browser: http://127.0.0.1:8080/get-notification</div>
    </div>
</body>
</html>