<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Mode</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .maintenance-container {
            background: white;
            padding: 3rem;
            border-radius: 1rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            text-align: center;
            max-width: 500px;
            margin: 2rem;
        }
        .maintenance-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        .maintenance-title {
            font-size: 2rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 1rem;
        }
        .maintenance-message {
            color: #6b7280;
            font-size: 1.1rem;
            line-height: 1.6;
            margin-bottom: 2rem;
        }
        .maintenance-footer {
            color: #9ca3af;
            font-size: 0.9rem;
        }
        .spinner {
            border: 4px solid #f3f4f6;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="maintenance-container">
        <div class="spinner"></div>
        <div class="maintenance-icon">🔧</div>
        <h1 class="maintenance-title">Under Maintenance</h1>
        <p class="maintenance-message">
            {{ $message ?? 'We are currently performing scheduled maintenance. We should be back online shortly.' }}
        </p>
        <p class="maintenance-footer">
            Thank you for your patience. We'll be back soon!
        </p>
    </div>
</body>
</html>
