<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Assessment Scheduled</title>
    <style>
        body {
            background: #f4f4f4;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 500px;
            margin: 40px auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            padding: 32px 24px;
            text-align: center;
        }
        .logo {
            width: 80px;
            margin-bottom: 24px;
        }
        .title {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 12px;
            color: #333;
        }
        .content {
            font-size: 16px;
            color: #555;
            margin-bottom: 24px;
        }
        .button {
            display: inline-block;
            padding: 12px 28px;
            background: #007bff;
            color: #fff !important;
            border-radius: 4px;
            text-decoration: none;
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 20px;
        }
        .footer {
            font-size: 12px;
            color: #aaa;
            margin-top: 32px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <img src="{{ asset('Logo.png') }}" alt="Dolphin Logo" class="logo">
        <div class="title">Assessment Scheduled</div>
        <div class="content">
            You have an assessment scheduled.<br><br>
            To answer the assessment, click the button below:
        </div>
        <a href="{{ $assessmentUrl }}" class="button">Answer Assessment</a>
        <div class="content" style="font-size:13px; color:#888; margin-top:18px;">
            If you’re having trouble clicking the "Answer Assessment" button, copy and paste the URL below into your web browser:<br>
            <a href="{{ $assessmentUrl }}" style="color:#007bff;">{{ $assessmentUrl }}</a>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} Dolphin. All rights reserved.
        </div>
    </div>
</body>
</html>