# Berkah Laundry - Web Application

Aplikasi web untuk layanan jasa laundry berbasis PHP dan MySQL.

## 📋 Persyaratan

- PHP 7.4 atau lebih tinggi
- MySQL 5.7 atau MariaDB 10.3+
- Apache dengan modul mod_rewrite
- cURL untuk WhatsApp integration

## 🚀 Quick Start (Local Development)

### 1. Setup Database
```bash
# Import database structure
mysql -u root -p laundry_db < database.sql
```

### 2. Configure Environment
```bash
# Copy .env.example ke .env
cp .env.example .env

# Edit .env dengan kredensial lokal
DB_HOST=localhost
DB_USER=root
DB_PASS=
DB_NAME=laundry_db
```

### 3. Run on Localhost
```bash
# Start XAMPP/WAMP/LAMP
# Access aplikasi di: http://localhost/laundry
```

## 🌐 Deployment (Infinity Free Hosting)

### 1. Prepare Files
```bash
# Pastikan .env tidak di-commit
git check-ignore .env        # Should show .env

# Verify all files
ls -la
```

### 2. Upload ke Infinity Free
- Buka cPanel → File Manager
- Arahkan ke folder `public_html`
- Delete folder `laundry` jika sudah ada
- Upload SEMUA file ke `public_html` (bukan subfolder)

Struktur di server:
```
public_html/
├── index.php                    ← Jangan di subfolder laundry!
├── db.php
├── .htaccess
├── .env                         ← HARUS ada dengan credentials
├── config/
├── includes/
├── views/
├── assets/
├── logs/
└── ...
```

### 3. Configure .env di Server
- Edit `.env` di File Manager
- Update dengan credential Infinity Free:

```env
DB_HOST=sql100.infinityfree.com
DB_USER=if0_40959960
DB_PASS=berkahlaundry
DB_NAME=if0_40959960_laundry_db
APP_ENV=production
APP_URL=https://berkahlaundry.42web.io
```

### 4. Set Permissions
```
Directory:  755
File:       644
logs/:      755 (writable)
.env:       600 (read-only)
```

### 5. Verify Deployment

✅ Check points:
- [ ] Akses `https://berkahlaundry.42web.io` → halaman home muncul
- [ ] Click "Order Now" → form order tampil
- [ ] Coba login dengan credentials yang ada
- [ ] Tidak ada error 404 atau 403
- [ ] Console tidak ada warning/error

❌ If 404 error:
- Cek File Manager → pastikan structure benar
- Cek jika file .htaccess ter-upload
- Cek permissions folder dan file
- Restart Infinity Free domain

## 🔐 Security Features

### Implemented
- ✅ Prepared Statements (SQL Injection prevention)
- ✅ CSRF Token Protection
- ✅ Password Hashing (bcrypt)
- ✅ Input Validation & Sanitization
- ✅ Session Security (HTTPOnly, Secure, SameSite)
- ✅ Rate Limiting (5 login attempts per 15 minutes)
- ✅ Security Headers (.htaccess)
- ✅ XSS Protection
- ✅ Directory Access Control
- ✅ Error Logging (tidak ditampilkan ke user)

### Read Security Documentation
See [SECURITY.md](SECURITY.md) for detailed security implementation.

## 📁 Project Structure

```
.
├── index.php                 # Homepage
├── db.php                    # Database connection (uses .env)
├── .env                      # Environment variables (not in git)
├── .env.example              # Template .env
├── .htaccess                 # Security & rewrite rules
├── .gitignore                # Exclude sensitive files
├── SECURITY.md               # Security documentation
├── config/
│   ├── security.php          # Security functions
│   └── Finance.php           # Finance logic
├── includes/
│   ├── header.php            # Navigation & initialization
│   ├── admin-header.php
│   └── footer.php
├── views/
│   ├── login.php             # Authentication
│   ├── register.php
│   ├── logout.php
│   ├── user/
│   │   ├── order.php         # Create order
│   │   ├── fetch-orders.php
│   │   ├── pricelist.php
│   │   ├── profile.php
│   │   ├── contact.php
│   │   └── riwayat.php       # Order history
│   └── admin/
│       ├── dashboard.php
│       ├── customers.php
│       ├── finances.php
│       ├── finances-add.php
│       ├── messages.php
│       └── invoice.php
├── assets/
│   ├── css/
│   │   ├── home.css
│   │   ├── auth.css
│   │   ├── order.css
│   │   ├── profile.css
│   │   ├── price.css
│   │   ├── kontak.css
│   │   ├── receipt.css
│   │   ├── order-history.css
│   │   └── admin.css
│   └── images/
└── logs/
    └── security.log          # Security event log
```

## 👥 Default User Credentials

### Admin
- Email: admin@berkahlaundry.com
- Password: admin123

### User (Test)
- Email: user@berkahlaundry.com
- Password: password123

⚠️ **CHANGE DEFAULT CREDENTIALS AFTER FIRST LOGIN**

## 📱 Features

### User Features
- ✅ Register & Login
- ✅ Buat Order Laundry
- ✅ Lihat Riwayat Order
- ✅ Edit Profile
- ✅ Hubungi Support (WhatsApp)
- ✅ Lihat Price List

### Admin Features
- ✅ Dashboard Analytics
- ✅ Manage Users/Customers
- ✅ View & Process Orders
- ✅ Manage Finances
- ✅ Generate Invoice
- ✅ Message Management

## 🔄 WhatsApp Integration

Untuk mengirim order ke WhatsApp:
- Nomor WhatsApp: +62-813-1926-5466
- Sistem auto-format pesan order
- Redirect ke WhatsApp Web

## 🛠️ Troubleshooting

### 404 Error on Production
```
Solusi:
1. Pastikan file upload ke public_html/ (bukan subfolder)
2. Cek .htaccess di-upload
3. Cek folder permissions (755)
4. Reload/refresh Infinity Free domain di cPanel
```

### Database Connection Error
```
Solusi:
1. Verifikasi .env credentials
2. Test koneksi di Infinity Free → phpMyAdmin
3. Pastikan database user memiliki akses yang cukup
4. Check file logs/security.log untuk error detail
```

### CSRF Token Error
```
Solusi:
1. Clear browser cookies
2. Clear session (logout)
3. Try login again
4. Jika persisten, cek php.ini session settings
```

### Session Timeout
```
Solusi:
1. Pastikan session.save_path writable
2. Check logs/security.log
3. Increase SESSION_TIMEOUT di .env jika perlu
```

## 📞 Support

Untuk bantuan:
1. Check [SECURITY.md](SECURITY.md) untuk security troubleshooting
2. Check logs/security.log untuk error details
3. Hubungi admin: admin@berkahlaundry.com

## 📝 License

© 2024 Berkah Laundry. All rights reserved.

## ⚡ Performance Tips

1. **Enable Caching**: Images & CSS di-cache 1 minggu (.htaccess)
2. **Use CDN**: Bootstrap dari CDN (cdn.jsdelivr.net)
3. **Compress**: GZIP compression enabled (.htaccess)
4. **Database**: Gunakan prepared statements (sudah implemented)
5. **Logs**: Regularly backup & clean logs/

## 🔗 Useful Links

- [Infinity Free Console](https://www.infinityfree.com/cpanel)
- [Infinity Free FileManager](https://www.infinityfree.com/cpanel)
- [OWASP Security Guidelines](https://owasp.org/)
- [PHP Security Best Practices](https://www.php.net/manual/en/security.php)

---

**Last Updated**: January 26, 2025
**Version**: 2.0 (Secured)
