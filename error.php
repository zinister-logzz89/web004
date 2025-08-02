<!DOCTYPE html>
<html>
<head>
    <title>Login Error - Webmail</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        
        .error-container {
            max-width: 500px;
            margin: 100px auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .error-icon {
            color: #dc3545;
            font-size: 48px;
            margin-bottom: 20px;
        }
        
        .error-message {
            color: #dc3545;
            margin-bottom: 20px;
            font-size: 18px;
        }
        
        .retry-btn {
            display: inline-block;
            padding: 12px 24px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 20px;
        }
        
        .retry-btn:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">⚠️</div>
        <h2>Login Failed</h2>
        <div class="error-message">
            The email address or password you entered is incorrect.
            Please check your credentials and try again.
        </div>
        <p>If you continue to experience problems, please contact your administrator.</p>
        <a href="index.php" class="retry-btn">Try Again</a>
    </div>
    
    <script>
        // Redirect back after a delay (optional)
        setTimeout(function() {
            // Uncomment the line below to auto-redirect after 5 seconds
            // window.location.href = 'index.php';
        }, 5000);
    </script>
</body>
</html>