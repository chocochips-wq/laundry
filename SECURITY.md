# Security Implementation & Best Practices

## Overview
This document outlines the security measures implemented in the Berkah Laundry application.

## ✅ Security Features Implemented

### 1. Database Security
- **.env File Configuration**: All credentials stored in `.env` file (not committed to git)
- **Prepared Statements**: All SQL queries use prepared statements to prevent SQL injection
- **Error Handling**: Database errors are logged but not displayed to users
- **Connection Pooling**: PDO connections with proper attributes set

```php
// Always use prepared statements
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
```

### 2. Input Validation & Sanitization
- **Email Validation**: Uses `filter_var()` with `FILTER_VALIDATE_EMAIL`
- **Phone Validation**: Validates Indonesian phone number formats
- **Password Validation**: Minimum 6 characters, maximum 255 characters
- **String Sanitization**: All user inputs sanitized with `htmlspecialchars()`

```php
$email = sanitizeInput($_POST['email'] ?? '');
$name = sanitizeInput($_POST['name'] ?? '');
```

### 3. CSRF Protection
- **CSRF Tokens**: Generated per session and validated on all POST requests
- **Token Verification**: Uses `hash_equals()` to prevent timing attacks
- **Automatic Token Generation**: `getCSRFToken()` generates on first use

```php
<!-- In form -->
<input type="hidden" name="csrf_token" value="<?php echo htmlEscape(getCSRFToken()); ?>">

<!-- In processing -->
if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $error_message = "Invalid request";
}
```

### 4. Password Security
- **Password Hashing**: Uses `password_hash()` with `PASSWORD_DEFAULT` algorithm (bcrypt)
- **Password Verification**: Uses `password_verify()` for comparison
- **No Plain Text Storage**: Passwords never stored in plaintext

```php
$hashed = password_hash($password, PASSWORD_DEFAULT);
if (password_verify($user_input, $hashed)) { ... }
```

### 5. Session Security
- **HTTPOnly Cookies**: Prevents JavaScript access to session cookies
- **Secure Flag**: HTTPS only transmission (when available)
- **SameSite Policy**: Strict mode prevents CSRF attacks
- **Session Timeout**: 30 minutes inactivity timeout
- **Session Regeneration**: ID regenerated on login to prevent fixation attacks

```php
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Strict');
session_regenerate_id(true);
```

### 6. XSS (Cross-Site Scripting) Protection
- **Output Escaping**: All user data escaped before output
- **HTML Escaping**: `htmlEscape()` for HTML context
- **JS Escaping**: `jsEscape()` for JavaScript context
- **CSP Headers**: Content Security Policy prevents inline scripts

```php
<?php echo htmlEscape($user_input); ?>
<script>var data = <?php echo jsEscape($json_data); ?>;</script>
```

### 7. Security Headers (.htaccess)
- **X-Frame-Options**: SAMEORIGIN - Prevents clickjacking
- **X-Content-Type-Options**: nosniff - Prevents MIME type sniffing
- **X-XSS-Protection**: 1; mode=block - XSS protection in older browsers
- **Content-Security-Policy**: Restrictive policy against injections
- **Referrer-Policy**: strict-origin-when-cross-origin
- **Permissions-Policy**: Disables unnecessary APIs (geolocation, microphone, camera)

### 8. Access Control
- **.htaccess Blocking**: Hidden files (.*), config/, includes/, logs/ blocked from direct access
- **Directory Listing Disabled**: Options -Indexes prevents directory browsing
- **Sensitive File Protection**: Environment files, git directories protected
- **Role-Based Access**: Admin vs user role checking

```htaccess
RewriteRule ^(\.env|\.git|config/|includes/)$ - [F,L]
Options -Indexes
```

### 9. Rate Limiting
- **Login Attempt Limiting**: Maximum 5 attempts per 15 minutes per IP
- **Prevents Brute Force**: Incremental rate limit checking
- **IP-Based Tracking**: Uses $_SERVER['REMOTE_ADDR']

```php
if (!checkRateLimit($rate_limit_key, 5, 900)) {
    $error_message = "Too many login attempts";
}
```

### 10. Security Logging
- **Event Logging**: Failed logins, CSRF mismatches, SQL errors logged
- **Security Audit Trail**: timestamp, IP address, user agent recorded
- **Log File Protection**: Stored outside public directory, .htaccess protected

```php
logSecurityEvent('LOGIN_FAILED', ['email' => $email, 'ip' => $_SERVER['REMOTE_ADDR']]);
```

### 11. Open Redirect Prevention
- **URL Validation**: Prevents redirects to external sites
- **Whitelist Validation**: Only allowed hosts can be redirect targets
- **Safe Redirect Function**: `safeRedirect()` validates all redirects

### 12. Performance & Security
- **Prepared Statements**: Prevent SQL injection + improve performance
- **Connection Pooling**: PDO with proper error modes
- **Error Logging**: Errors logged, not displayed to prevent information disclosure
- **HTTP Compression**: gzip enabled for faster transfers

## 📋 File Structure & Permissions

```
laundry/
├── .env                          # Environment variables (NOT committed)
├── .env.example                  # Template for .env file
├── .gitignore                    # Exclude sensitive files from git
├── .htaccess                     # Security headers & rewrite rules
├── index.php
├── db.php                        # Database configuration (uses .env)
├── config/
│   ├── .htaccess                 # Deny direct access
│   ├── security.php              # Security functions & headers
│   └── Finance.php
├── includes/
│   ├── .htaccess                 # Deny direct access
│   ├── header.php                # Security initialization
│   ├── admin-header.php
│   └── footer.php
├── logs/
│   ├── .htaccess                 # Deny direct access
│   └── security.log              # Security event log
├── views/
│   ├── login.php                 # With CSRF & rate limiting
│   ├── register.php              # With CSRF & validation
│   ├── user/
│   │   └── order.php
│   └── admin/
│       └── *.php
└── assets/
    ├── css/
    └── images/
```

## 🔐 Configuration Steps for Production

### 1. Environment Setup
```bash
# Copy .env.example to .env
cp .env.example .env

# Update .env with production credentials
DB_HOST=sql100.infinityfree.com
DB_USER=if0_40959960
DB_PASS=berkahlaundry
DB_NAME=if0_40959960_laundry_db
APP_ENV=production
APP_DEBUG=false
```

### 2. File Permissions
```bash
# Directory permissions
chmod 755 . logs config includes

# File permissions
chmod 644 *.php .htaccess *.example
chmod 600 .env

# Make logs writable
chmod 770 logs/
```

### 3. Enable HTTPS
Uncomment HTTPS redirect in `.htaccess`:
```htaccess
<IfModule mod_rewrite.c>
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
</IfModule>
```

### 4. Database Verification
- Test connection with production credentials in .env
- Verify all tables created with proper structure
- Ensure database user has limited privileges

## 🚨 Security Checklist for Deployment

- [ ] .env file exists with production credentials
- [ ] .env NOT committed to git (check .gitignore)
- [ ] .htaccess uploaded to server (prevents direct access)
- [ ] logs/ directory writable but not accessible via web
- [ ] HTTPS enabled and working
- [ ] Security headers visible in browser (DevTools → Network)
- [ ] No database errors displayed to users
- [ ] CSRF tokens working on all forms
- [ ] Password requirements enforced (min 6 chars)
- [ ] Rate limiting working (test with multiple login attempts)
- [ ] Session timeout working (30 minutes)
- [ ] Admin panel properly restricted to admin role only

## 🔍 Regular Security Maintenance

1. **Review Logs**: Check `logs/security.log` regularly for suspicious activity
2. **Update Dependencies**: Keep PHP and MySQL updated
3. **Backup Database**: Regular automated backups
4. **Monitor Rate Limits**: Watch for brute force attempts
5. **Update CSP Policy**: As new resources are added
6. **Test CSRF Protection**: Ensure tokens working after updates

## 🐛 Testing Security

### Test CSRF Protection
```php
// Remove CSRF token from form and submit - should fail
```

### Test SQL Injection
```
Email: admin' OR '1'='1
Password: (any)
// Should fail - prepared statement prevents injection
```

### Test XSS
```php
// Try injecting <script>alert('xss')</script> in any field
// Should be escaped and displayed as text, not executed
```

### Test Rate Limiting
```
// Try 6 failed login attempts in a row
// 6th attempt should be blocked for 15 minutes
```

## 📚 References
- [OWASP Top 10 - 2021](https://owasp.org/Top10/)
- [PHP Security Best Practices](https://www.php.net/manual/en/security.php)
- [Content Security Policy](https://developer.mozilla.org/en-US/docs/Web/HTTP/CSP)
- [CSRF Prevention Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Cross-Site_Request_Forgery_Prevention_Cheat_Sheet.html)
