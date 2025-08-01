@if(!empty($body))
    {!! $body !!}
@else
    <p>Hello,</p>
    <p>You have been invited to complete your registration.</p>
    <p>Please click the link below to register:</p>
    @if(!empty($registrationUrl))
        <p><a href="{{ $registrationUrl }}" target="_blank">Complete Registration</a></p>
    @endif
    <p>If you did not request this, you can ignore this email.</p>
    <br />
    <p>Thank you,<br />Dolphin Team</p>
@endif
