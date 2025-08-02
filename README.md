# SMTP Mailer - Penetration Testing Tool

⚠️ **WARNING: This tool is intended for educational and authorized penetration testing purposes only. Ensure you have proper authorization before using this script against any systems. Unauthorized use may be illegal and could result in serious legal consequences.**

## Overview

This PHP-based SMTP mailer tool is designed for ethical penetration testing and security assessments. It provides functionality to test SMTP configurations, send test emails, and demonstrate potential security vulnerabilities in email systems.

## Features

- **SMTP Testing**: Test SMTP server connections and authentication
- **Email Sending**: Send test emails with customizable content
- **Web Interface**: Capture credentials via a realistic webmail interface
- **CLI Interface**: Command-line tools for direct testing
- **Security Features**: Rate limiting, input validation, and logging
- **Multiple Protocols**: Support for TLS, SSL, and unencrypted connections
- **Preset Configurations**: Pre-configured settings for common email providers
- **Comprehensive Logging**: Detailed logs for analysis and reporting

## Installation

### Prerequisites

- PHP 7.4 or higher
- Composer (for dependency management)
- Required PHP extensions: `curl`, `mbstring`, `xml`, `zip`

### Setup

1. **Clone or download** the project files
2. **Install dependencies** using Composer:
   ```bash
   composer install
   ```
3. **Configure SMTP settings** in `config.php`
4. **Set proper permissions** for log files:
   ```bash
   chmod 666 *.log
   ```

## Configuration

### Basic Configuration

Edit `config.php` to configure your SMTP settings:

```php
'smtp' => [
    'host' => 'smtp.example.com',    // SMTP server hostname
    'port' => 587,                   // SMTP port (25, 465, 587, 2525)
    'encryption' => 'tls',           // 'tls', 'ssl', or '' for none
    'auth' => true,                  // Enable SMTP authentication
    'username' => 'your-email@example.com',
    'password' => 'your-password',
    'timeout' => 30,                 // Connection timeout in seconds
    'debug' => 2,                    // Debug level (0-3)
],
```

### Security Settings

```php
'security' => [
    'rate_limit' => 10,              // Max emails per minute
    'max_recipients' => 50,          // Max recipients per email
    'allowed_domains' => [],         // Empty = allow all
    'blocked_domains' => ['localhost'],
],
```

### Preset Configurations

Use built-in presets for common email providers:

- `gmail` - Gmail SMTP settings
- `outlook` - Outlook/Hotmail SMTP settings
- `yahoo` - Yahoo Mail SMTP settings
- `local` - Local SMTP server settings

## Usage

### Command Line Interface

#### Basic Commands

```bash
# Test SMTP connection
php smtp_test.php test-connection

# Send a test email
php smtp_test.php send-email --to recipient@example.com

# Show current configuration
php smtp_test.php config

# List available presets
php smtp_test.php presets

# Use a preset configuration
php smtp_test.php send-email --preset gmail --to test@example.com

# Interactive mode
php smtp_test.php interactive
```

#### Advanced Usage

```bash
# Send email with custom subject and body
php smtp_test.php send-email \
  --to recipient@example.com \
  --subject "Custom Subject" \
  --body "Custom message body"

# Use custom config file
php smtp_test.php test-connection --config custom-config.php

# Test local SMTP server
php smtp_test.php send-email --preset local --to admin@localhost
```

### Web Interface

The web interface mimics a webmail login page to capture credentials:

1. **Place files** on a web server with PHP support
2. **Configure** `postmailer.php` with your target settings
3. **Access** `index.html` to see the phishing interface
4. **Captured credentials** are logged and can be emailed

### Interactive Mode

Launch interactive mode for step-by-step testing:

```bash
php smtp_test.php interactive
```

Available interactive commands:
- `test-conn` - Test SMTP connection
- `send EMAIL` - Send test email
- `config` - Show configuration
- `presets` - List presets
- `load-preset NAME` - Load a preset
- `stats` - Show statistics
- `quit` - Exit

## File Structure

```
smtp-mailer/
├── composer.json          # PHP dependencies
├── config.php            # SMTP configuration
├── smtp_mailer.php       # Core SMTP mailer class
├── postmailer.php        # Web form handler
├── smtp_test.php         # CLI testing interface
├── index.html            # Phishing interface (provided)
├── README.md             # This documentation
└── logs/
    ├── smtp_log.txt      # SMTP operation logs
    ├── login_attempts.log # Captured login attempts
    └── error.log         # Error logs
```

## Security Features

### Rate Limiting
- Configurable email sending limits
- Time-based windows for rate calculation
- IP-based tracking for web interface

### Input Validation
- Email address validation
- Domain whitelist/blacklist support
- SQL injection prevention
- XSS protection

### Logging
- Comprehensive activity logging
- Error tracking and reporting
- Credential capture logging
- Connection attempt monitoring

## Common Use Cases

### 1. SMTP Server Testing
Test if an SMTP server accepts connections and authentication:

```bash
# Test connection to a specific server
php smtp_test.php test-connection

# Test with different configurations
php smtp_test.php test-connection --preset local
```

### 2. Email Relay Testing
Test if an SMTP server can be used to relay emails:

```bash
# Send test email through target server
php smtp_test.php send-email --to external@example.com
```

### 3. Credential Harvesting Simulation
Demonstrate phishing attacks that capture email credentials:

1. Set up the web interface on a server
2. Configure email notifications in `postmailer.php`
3. Direct targets to the fake login page
4. Review captured credentials in logs

### 4. Authentication Testing
Test different authentication mechanisms:

```bash
# Test with authentication
php smtp_test.php send-email --to test@example.com

# Test without authentication (modify config.php)
php smtp_test.php test-connection
```

## Troubleshooting

### Common Issues

**Connection Timeout**
- Check firewall rules
- Verify SMTP server hostname and port
- Test with telnet: `telnet smtp.example.com 587`

**Authentication Failed**
- Verify username and password
- Check if 2FA is enabled (use app passwords)
- Ensure authentication is enabled in config

**SSL/TLS Errors**
- Verify encryption type (TLS vs SSL)
- Check certificate validity
- Try different ports (587 for TLS, 465 for SSL)

**Permission Denied**
- Check file permissions for log files
- Ensure PHP has write access to directory
- Verify SELinux/AppArmor settings

### Debug Modes

Enable debugging for detailed output:

```php
'smtp' => [
    'debug' => 3,  // Maximum debug output
],
```

Debug levels:
- `0` - No debug output
- `1` - Client messages
- `2` - Client and server messages
- `3` - Connection status and all messages

## Legal and Ethical Considerations

### ⚠️ Important Disclaimers

1. **Authorization Required**: Only use this tool against systems you own or have explicit written permission to test
2. **Educational Purpose**: This tool is designed for learning and authorized security assessments
3. **Legal Compliance**: Ensure compliance with local laws and regulations
4. **Responsible Disclosure**: Report vulnerabilities through proper channels
5. **No Warranty**: This tool is provided as-is without any warranties

### Best Practices

- Always obtain proper authorization before testing
- Use in isolated testing environments when possible
- Document all testing activities
- Follow responsible disclosure practices
- Respect rate limits and server resources
- Remove tools and logs after testing

## Contributing

When contributing to this project:

1. Ensure all additions maintain the educational focus
2. Include proper warnings about authorized use only
3. Test thoroughly before submitting
4. Document new features clearly
5. Follow PHP coding standards

## License

This project is intended for educational and authorized penetration testing use only. Use at your own risk and ensure compliance with all applicable laws and regulations.

## Support

For issues and questions:

1. Check the troubleshooting section above
2. Review configuration settings
3. Enable debug mode for detailed output
4. Check log files for error messages

Remember: This tool should only be used for authorized testing and educational purposes. Always ensure you have proper permission before testing any systems.