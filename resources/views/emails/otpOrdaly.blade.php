<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ordaly - Password Reset</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            padding: 20px 0;
            border-bottom: 2px solid #eee;
        }
        .content {
            padding: 30px 20px;
        }
        .otp-box {
            background-color: #f8f9fa;
            padding: 15px;
            text-align: center;
            margin: 20px 0;
            border-radius: 4px;
            border: 1px dashed #ddd;
        }
        .otp-code {
            font-size: 32px;
            font-weight: bold;
            color: #2c3e50;
            letter-spacing: 5px;
        }
        .footer {
            text-align: center;
            padding: 20px;
            background-color: #f8f9fa;
            border-top: 1px solid #eee;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="color: #2c3e50;">Ordaly AI</h1>
        </div>

        <div class="content">
            <h2>Password Reset</h2>
            <p>Hello, {{$username}}</p>
            <p>We received a request to reset your password. Please use the following OTP code to complete your password reset:</p>

            <div class="otp-box">
                <div class="otp-code">{{ $otp }}</div>
            </div>

            <p>This code will expire in 10 minutes. If you didn't request this password, please contact us immediately.</p>
            <p>If you have any questions or need assistance, please contact us at <a href="mailto:support@foodstuff.store">support@foodstuff.store</a></p>

            <p>Best regards,<br>
            The Ordaly AI Team</p>
        </div>

        <div class="footer">
            <p>© 2025 Ordaly AI. All rights reserved.</p>
            <p>This is an automated message, please do not reply to this email.</p>
            <p>Contact us at: support@foodstuffstore.com</p>
        </div>
    </div>
</body>
</html>
