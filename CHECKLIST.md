# 🎯 PRE-DEPLOYMENT CHECKLIST

Sebelum upload ke Infinity Free, pastikan semua ini sudah done:

## ✅ Local Testing

- [ ] Delete folder laundry dari public_html di server (jika ada)
- [ ] PHP syntax valid: `php -l *.php` (sudah di-check)
- [ ] Database connection tested locally
- [ ] Login form bekerja
- [ ] CSRF token bekerja (coba submit form 2x)
- [ ] Rate limiting bekerja (coba login fail 6x, harusnya diblok)
- [ ] Security headers present (F12 → Network → Response Headers)

## 🔐 Security Checks

- [ ] .env file TIDAK di-commit ke git
  - Verify: `git log --oneline | grep .env` → should be empty
- [ ] .gitignore memiliki entry: `.env`
- [ ] Database credentials HANYA di .env, bukan hardcoded
- [ ] No debug output di production code
- [ ] Error messages tidak menampilkan SQL/database info
- [ ] All passwords hashed dengan password_hash()
- [ ] CSRF tokens di semua form POST
- [ ] SQL injection impossible (prepared statements)
- [ ] XSS prevention (htmlspecialchars/htmlEscape)

## 📁 File Structure Check

Pastikan file ada di local:

```
✅ Root Files
- [ ] index.php
- [ ] db.php
- [ ] .env (hanya lokal, bukan di git)
- [ ] .env.example (di git)
- [ ] .htaccess
- [ ] .gitignore
- [ ] README.md
- [ ] SECURITY.md
- [ ] DEPLOYMENT.md

✅ config/ Folder
- [ ] security.php (NEW - PENTING!)
- [ ] Finance.php
- [ ] .htaccess (NEW - blocking access)

✅ includes/ Folder
- [ ] header.php (UPDATED - security)
- [ ] admin-header.php
- [ ] footer.php
- [ ] .htaccess (NEW - blocking access)

✅ views/ Folder
- [ ] login.php (UPDATED - CSRF, rate limiting)
- [ ] register.php (UPDATED - CSRF, validation)
- [ ] logout.php
- [ ] user/
- [ ] admin/

✅ assets/ Folder
- [ ] css/ (all CSS files)
- [ ] images/ (all images)

✅ logs/ Folder (NEW)
- [ ] .htaccess (blocking access)
- [ ] (empty, akan berisi security.log)
```

## 🔧 Pre-Upload Configuration

### .env Setup
```env
DB_HOST=sql100.infinityfree.com
DB_USER=if0_40959960
DB_PASS=berkahlaundry
DB_NAME=if0_40959960_laundry_db
APP_ENV=production
APP_DEBUG=false
APP_URL=https://berkahlaundry.42web.io
WA_BUSINESS_PHONE=6281319265466
SESSION_TIMEOUT=1800
```
- [ ] .env file sudah ada (bukan .env.example)
- [ ] Credentials benar untuk Infinity Free
- [ ] APP_ENV=production (bukan development)
- [ ] APP_DEBUG=false (safety!)

## 📤 Upload Process

- [ ] Backup local copy (sudah ada di git)
- [ ] Delete folder "laundry" di public_html (jika ada)
- [ ] Upload ALL FILES to public_html root (bukan subfolder!)
- [ ] Verify file structure di server (File Manager)
- [ ] Set permissions: 755 dirs, 644 files
- [ ] Set permissions: logs/ = 755, .env = 600
- [ ] Refresh/restart domain di cPanel

## ✨ Post-Deployment Verification

### URL Testing
- [ ] `https://berkahlaundry.42web.io/` → Homepage loads
- [ ] No 404 error
- [ ] No 403 error
- [ ] All images load correctly
- [ ] CSS styling applied

### Functionality Testing
- [ ] "Order Now" button → order form loads
- [ ] "Login" link → login page loads
- [ ] "Register" link → register page loads
- [ ] Login form CSRF token present (view source)
- [ ] Register form CSRF token present (view source)

### Security Testing
- [ ] Open DevTools (F12) → Network tab
- [ ] Reload page
- [ ] Check Response Headers:
  - [x] `X-Frame-Options: SAMEORIGIN`
  - [x] `X-Content-Type-Options: nosniff`
  - [x] `X-XSS-Protection: 1; mode=block`
  - [x] `Content-Security-Policy: ...`

### Login Testing
- [ ] Register new user → successful
- [ ] Login with new user → successful
- [ ] Redirect to homepage → working
- [ ] Try login 6 times with wrong password → blocked on 6th attempt
- [ ] Wait 15 minutes or clear cookies → can login again

### Chrome Warning Check
- [ ] No "Not Secure" warning (should show padlock 🔒)
- [ ] No malware warning
- [ ] No redirect warning
- [ ] Console (F12) → no red errors

## 🚨 If Something Wrong

1. **404 Error**:
   - Check file structure at public_html root
   - Not in /laundry subfolder!
   - Delete old laundry folder
   - Upload again

2. **Database Error**:
   - Verify .env credentials
   - Test in phpMyAdmin (cPanel)
   - Check logs/security.log

3. **CSRF Token Error**:
   - Clear browser cookies
   - Close browser
   - Reopen and try again

4. **Still Blank**:
   - Check logs/security.log
   - Or temporarily enable display_errors in db.php
   - Look for PHP errors

## 🎉 All Done!

If all checks pass:
- Website LIVE ✅
- Security GOOD ✅
- No Chrome warnings ✅
- Ready for users! 🚀

---

**Updated**: January 26, 2025
