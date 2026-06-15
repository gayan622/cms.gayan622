<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>New Contact Message</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Segoe UI', Arial, sans-serif; background: #f5f7fa; color: #1a2535; }
    .wrapper { max-width: 580px; margin: 32px auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 24px rgba(15,27,45,0.08); }
    .header { background: #0f1b2d; padding: 32px 36px; }
    .header h1 { font-size: 22px; font-weight: 700; color: #c9a84c; letter-spacing: -0.3px; }
    .header p { font-size: 13px; color: #8da3be; margin-top: 4px; }
    .body { padding: 32px 36px; }
    .field { margin-bottom: 20px; }
    .field label { display: block; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; color: #8da3be; margin-bottom: 5px; }
    .field .value { font-size: 15px; color: #1a2535; font-weight: 500; }
    .message-box { background: #f5f7fa; border-left: 3px solid #c9a84c; border-radius: 0 10px 10px 0; padding: 16px 18px; margin-top: 6px; font-size: 14px; line-height: 1.7; color: #4a5e75; white-space: pre-wrap; }
    .divider { height: 1px; background: #edf0f4; margin: 24px 0; }
    .footer { background: #f5f7fa; padding: 20px 36px; text-align: center; }
    .footer p { font-size: 12px; color: #8da3be; line-height: 1.6; }
    .footer a { color: #c9a84c; text-decoration: none; }
    .badge { display: inline-block; background: #c9a84c; color: #0f1b2d; font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 20px; margin-bottom: 12px; }
  </style>
</head>
<body>
  <div class="wrapper">
    <div class="header">
      <div class="badge">New Message</div>
      <h1>Gayan622 Online</h1>
      <p>You have received a new contact form submission</p>
    </div>

    <div class="body">
      <div class="field">
        <label>From</label>
        <div class="value">{{ $contactMessage->name }}</div>
      </div>
      <div class="field">
        <label>Email</label>
        <div class="value">
          <a href="mailto:{{ $contactMessage->email }}" style="color:#c9a84c; text-decoration:none;">
            {{ $contactMessage->email }}
          </a>
        </div>
      </div>
      @if($contactMessage->subject)
      <div class="field">
        <label>Subject</label>
        <div class="value">{{ $contactMessage->subject }}</div>
      </div>
      @endif
      <div class="field">
        <label>Message</label>
        <div class="message-box">{{ $contactMessage->message }}</div>
      </div>

      <div class="divider"></div>

      <div class="field">
        <label>Received</label>
        <div class="value" style="font-size:13px; color:#8da3be;">
          {{ $contactMessage->created_at->format('D, d M Y \a\t H:i') }} (Sri Lanka Time)
        </div>
      </div>
    </div>

    <div class="footer">
      <p>
        This message was sent via the contact form at
        <a href="{{ config('app.url') }}">{{ config('app.name') }}</a>.<br>
        Reply directly to this email to respond to {{ $contactMessage->name }}.
      </p>
    </div>
  </div>
</body>
</html>
