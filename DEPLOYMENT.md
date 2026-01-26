# 📤 DEPLOYMENT GUIDE - Infinity Free Hosting

## ⚠️ PENTING: Jangan Upload Folder Laundry Lagi!

Jika website Anda menampilkan **404 error**, itu karena folder struktur salah.

### ❌ SALAH - Struktur yang menyebabkan 404
```
public_html/
└── laundry/              ← Jangan ada folder laundry!
    ├── index.php
    ├── db.php
    └── ...
```

**URL menjadi**: `berkahlaundry.42web.io/laundry/index.php` ❌ TIDAK BEKERJA

---

### ✅ BENAR - Struktur yang harus dipakai
```
public_html/
├── index.php             ← Langsung di public_html!
├── db.php
├── .htaccess
├── .env
├── config/
├── includes/
├── views/
├── assets/
└── logs/
```

**URL menjadi**: `berkahlaundry.42web.io/index.php` ✅ BEKERJA

---

## 🚀 Step-by-Step Upload Instructions

### 1. Backup File Lokal
```bash
# Pastikan sudah commit di git
cd c:\xampp\htdocs\laundry
git status
# Pastikan tidak ada uncommitted changes
```

### 2. Delete Folder Laundry di Server

**Di cPanel Infinity Free:**
1. Buka **File Manager**
2. Navigate ke **public_html**
3. **Jika ada folder "laundry"**, delete-nya:
   - Right-click folder "laundry" → Delete
   - Confirm deletion
4. **Jika ada index.html**, delete juga

### 3. Upload Semua File ke public_html

**Method A: Drag & Drop (Recommended)**
1. Buka File Manager → public_html
2. Di local computer, buka folder `c:\xampp\htdocs\laundry`
3. Select SEMUA files & folders:
   - Ctrl+A (select all)
4. Drag ke File Manager window
5. Drop di public_html
6. Tunggu upload selesai

**Method B: Upload ZIP**
1. Zip folder laundry di local: `laundry.zip`
2. Upload `laundry.zip` ke public_html via File Manager
3. Extract ZIP di public_html:
   - Right-click laundry.zip → Extract
   - Pilih ekstrak ke current folder
4. Sekarang file akan berada di public_html/laundry/
5. **PENTING**: Cut & move semua files dari laundry/ langsung ke public_html/
   - Select all files di laundry/
   - Cut & Paste ke public_html
   - Delete folder laundry kosong

### 4. Verify File Structure

Di File Manager, pastikan struktur terlihat:
```
public_html/
├── index.php          ← File harus langsung terlihat di root!
├── db.php
├── .htaccess
├── .env
├── config/
├── includes/
├── views/
├── assets/
├── logs/
├── .gitignore
├── README.md
├── SECURITY.md
└── ... (files lain)
```

### 5. Set File Permissions

Untuk keamanan, set permissions yang tepat:

**Method A: Via Terminal (Recommended)**
```bash
# SSH ke Infinity Free (jika available)
# Set directory permissions
find . -type d -exec chmod 755 {} \;
# Set file permissions
find . -type f -exec chmod 644 {} \;
# Make logs writable
chmod 770 logs/
# Secure .env file
chmod 600 .env
```

**Method B: Via File Manager**
1. Select folder/file
2. Right-click → Permissions
3. Set permissions:
   - Directories: 755
   - Files: 644
   - logs/: 755 (writable)
   - .env: 600

### 6. Configure .env di Server

1. File Manager → public_html → Find `.env`
2. Right-click → Edit
3. Verify credentials:

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

4. Save file

### 7. Force Refresh Domain

Kemungkinan server masih cache file lama:

1. **Restart Infinity Free Domain**:
   - cPanel → Addon Domains → Find domain
   - Click addon domain
   - Cari option "Park" / "Unpark" domain
   - Temporarily unpark, then reparked

2. **Clear Browser Cache**:
   - Ctrl+Shift+Delete
   - Clear all cache
   - Close browser completely
   - Open again

3. **Test URL**:
   - Open `https://berkahlaundry.42web.io`
   - Should show homepage ✅

---

## ✅ Verification Checklist

Setelah upload, pastikan:

- [ ] Buka `https://berkahlaundry.42web.io` → Homepage tampil (bukan 404)
- [ ] Click "Order Now" → Form order muncul
- [ ] Coba login dengan email & password
- [ ] Check browser console (F12) → No red errors
- [ ] Check Security Headers (F12 → Network → Headers):
  - `X-Frame-Options: SAMEORIGIN` ✅
  - `X-Content-Type-Options: nosniff` ✅
  - `Content-Security-Policy: ...` ✅

## 🐛 Troubleshooting

### Problem: 404 Error
```
Solution:
1. Pastikan file langsung di public_html/, bukan subfolder
2. Cek file structure via File Manager
3. Cek .htaccess ter-upload (hidden file, show hidden files)
4. Refresh domain di cPanel atau clear browser cache
```

### Problem: 403 Forbidden
```
Solution:
1. Check file permissions (644 untuk files, 755 untuk dirs)
2. Cek .htaccess rules
3. Pastikan logs/ directory writable (755)
```

### Problem: Database Connection Error
```
Solution:
1. Verify .env credentials benar
2. Test di phpMyAdmin (cPanel):
   - Login dengan credentials di .env
   - Check database exists
3. Check logs/security.log untuk error detail
4. Verify database user privileges
```

### Problem: Blank White Page
```
Solution:
1. Turn on error display temporarily (NOT recommended for production):
   - Edit db.php line 50:
   - Change: ini_set('display_errors', 0);
   - To: ini_set('display_errors', 1);
2. Reload page, check error message
3. Fix error, change back to 0
4. Or check error log via File Manager → logs/security.log
```

### Problem: "Too many login attempts"
```
Solution:
1. Rate limiting is working (5 attempts per 15 mins)
2. Wait 15 minutes or clear session:
   - Delete browser cookies
   - Close & reopen browser
3. Try again
```

---

## 🔐 Security Reminders

### ✅ Do
- [x] Keep .env file private (NOT in git)
- [x] Set proper file permissions (644 files, 755 dirs)
- [x] Use HTTPS (should be automatic on Infinity Free)
- [x] Change default admin password immediately
- [x] Regular backup of database
- [x] Monitor security.log for suspicious activity

### ❌ Don't
- [ ] Don't share .env file
- [ ] Don't commit .env to git
- [ ] Don't set permissions to 777 (too permissive)
- [ ] Don't display database errors to users
- [ ] Don't disable CSRF protection
- [ ] Don't use GET method for sensitive operations

---

## 📞 If Still Having Issues

1. **Check Infinity Free Support**:
   - Login to Infinity Free → Help → Support Tickets
   - Mention: mod_rewrite issue, 404 error

2. **Check Error Logs**:
   - File Manager → logs/security.log
   - Copy error message
   - Search error message online for solution

3. **Manual Testing**:
   - Test PHP works: Create `test.php`:
     ```php
     <?php phpinfo(); ?>
     ```
   - Access `berkahlaundry.42web.io/test.php`
   - Should show PHP info
   - Delete test.php after testing

4. **Database Test**:
   - Create `dbtest.php`:
     ```php
     <?php require 'db.php'; echo "Connected!"; ?>
     ```
   - Access in browser
   - Delete after testing

---

## 🎉 Success!

Jika semua berjalan lancar:
1. Homepage muncul ✅
2. Login bekerja ✅
3. Order form berfungsi ✅
4. Tidak ada error 404/403 ✅
5. Security headers ada ✅

**Website Anda LIVE dan AMAN!** 🚀

---

**Last Updated**: January 26, 2025
**Version**: 2.0 (Secured)
