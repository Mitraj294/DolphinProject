
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width,initial-scale=1" />
        <title>Dolphin - Payment Receipt</title>
        <style>
            body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; color: #222; }
            .container { max-width: 680px; margin: 0 auto; padding: 20px; }
            .card { background: #fff; border-radius: 8px; padding: 18px; box-shadow: 0 1px 4px rgba(0,0,0,0.06); }
            .table { width: 100%; border-collapse: collapse; margin-top: 12px; }
            .table td, .table th { padding: 8px 6px; border-top: 1px solid #eee; }
            .table th { text-align: left; font-weight: 600; vertical-align: top; width: 60%; }
            .table td.value { text-align: right; }
            .button { display: inline-block; margin-top: 14px; padding: 10px 14px; background: #0074c2; color: #fff; border-radius: 6px; text-decoration: none; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="card">
                <h1>Thank you for your payment</h1>

                @if(!empty($subscription['customer_name']))
                    <p>Hello {{ $subscription['customer_name'] }},</p>
                @endif

                <p>We have received your payment for your subscription. Below are the details:</p>

                <table class="table" aria-labelledby="receipt-details">
                    <caption id="receipt-details" style="display:none">Subscription payment details</caption>
                    <tr>
                        <th scope="row">Plan</th>
                        <td class="value">{{ $subscription['plan'] ?? $subscription['plan_name'] ?? 'Subscription' }}</td>
                    </tr>
                    <tr>
                        <th scope="row">Amount</th>
                        <td class="value">{{ isset($subscription['amount']) ? '$' . number_format($subscription['amount'], 2) : '—' }}</td>
                    </tr>
                    <tr>
                        <th scope="row">Invoice #</th>
                        <td class="value">{{ $subscription['invoice_number'] ?? '—' }}</td>
                    </tr>
                    <tr>
                        <th scope="row">Payment date</th>
                        <td class="value">{{ $subscription['payment_date'] ?? '—' }}</td>
                    </tr>
                    <tr>
                        <th scope="row">Next billing</th>
                        <td class="value">{{ $subscription['next_billing'] ?? $subscription['subscription_end'] ?? '—' }}</td>
                    </tr>
                </table>

                @if(!empty($subscription['receipt_url']))
                    <p>
                        <a href="{{ $subscription['receipt_url'] }}" target="_blank" rel="noopener noreferrer" aria-label="View your receipt" style="display:inline-block; padding:10px 14px; color:#ffffff; background:#0074c2; text-decoration:none; border-radius:6px; font-weight:600;">
                            View your receipt
                        </a>
                    </p>
                    <p style="font-size:12px; word-break:break-word; margin-top:8px;">
                        If the button doesn't work, open: <a href="{{ $subscription['receipt_url'] }}">{{ $subscription['receipt_url'] }}</a>
                    </p>
                @endif

                <p>If you need help, reply to this email.</p>

                <p>Thanks,<br/>Dolphin Team</p>
            </div>
        </div>
    </body>
</html>