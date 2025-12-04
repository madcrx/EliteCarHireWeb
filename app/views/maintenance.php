<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Mode - Elite Car Hire</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
        }

        .maintenance-container {
            text-align: center;
            padding: 40px 20px;
            max-width: 600px;
        }

        .icon {
            font-size: 80px;
            margin-bottom: 20px;
            opacity: 0.9;
        }

        h1 {
            font-size: 42px;
            margin-bottom: 20px;
            font-weight: 600;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        p {
            font-size: 18px;
            line-height: 1.6;
            color: #b8b8d1;
            margin-bottom: 15px;
        }

        .logo {
            margin-bottom: 30px;
        }

        .logo h2 {
            font-size: 28px;
            color: #fff;
            font-weight: 300;
            letter-spacing: 2px;
        }

        .contact {
            margin-top: 40px;
            padding: 20px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .contact p {
            font-size: 14px;
            margin-bottom: 10px;
        }

        .contact a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }

        .contact a:hover {
            text-decoration: underline;
        }

        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.5;
            }
        }

        .icon {
            animation: pulse 2s ease-in-out infinite;
        }
    </style>
</head>
<body>
    <div class="maintenance-container">
        <div class="logo">
            <h2>ELITE CAR HIRE</h2>
        </div>

        <div class="icon">🔧</div>

        <h1>We'll Be Back Soon!</h1>

        <p>We're currently performing scheduled maintenance to improve your experience.</p>
        <p>We apologize for any inconvenience and appreciate your patience.</p>

        <div class="contact">
            <p><strong>Need immediate assistance?</strong></p>
            <p>Phone: <a href="tel:0406907849">0406 907 849</a></p>
            <p>Email: <a href="mailto:support@elitecarhire.au">support@elitecarhire.au</a></p>
        </div>
    </div>
</body>
</html>
