# .htaccess Rewrite Rules & Configuration Documentation

**File Location:** `/server/.htaccess`  
**Created:** February 21, 2026  
**Purpose:** SEO-friendly URLs, security headers, performance optimization

---

## Overview

The `.htaccess` file is an Apache configuration file that controls:
- URL rewriting (removing `.php` extensions)
- Security headers (HSTS, CSP, X-Frame-Options, etc.)
- Compression (gzip)
- Caching rules
- Directory protection
- Bot prevention

⚠️ **Important:** This file requires:
- Apache Web Server
- `mod_rewrite` enabled
- `mod_headers` enabled
- `mod_deflate` enabled (for compression)
- `mod_expires` enabled (for caching)

---

## Section-by-Section Explanation

### 1. Enable Rewrite Engine

```apache
<IfModule mod_rewrite.c>
  RewriteEngine On
  RewriteBase /
</IfModule>
```

**What it does:**
- Enables Apache's URL rewriting module
- Sets the base directory for rewrite rules to `/` (root)

**Why it matters:**
- Without this, SEO-friendly URL rewriting won't work
- All subsequent rewrite rules depend on this

**Example:**
- User requests: `https://site.com/api/products` (no .php)
- Apache internally serves: `https://site.com/api/products.php`
- User sees clean URL without `.php` extension

---

### 2. Remove .php Extension from URLs

```apache
<IfModule mod_rewrite.c>
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteRule ^([^\.]+)$ $1.php [NC,L]
</IfModule>
```

**Breakdown:**

| Component | Meaning |
|-----------|---------|
| `RewriteCond %{REQUEST_FILENAME} !-f` | Skip if file exists (-f = file) |
| `RewriteCond %{REQUEST_FILENAME} !-d` | Skip if directory exists (-d = directory) |
| `RewriteRule ^([^\.]+)$ $1.php` | Add .php extension to filename |
| `[NC,L]` | NC=No-Case, L=Last rule |

**Example:**
```
Request: /api/contact
Check: Does /api/contact file exist? NO
Check: Does /api/contact directory exist? NO
Action: Serve /api/contact.php
User sees: /api/contact (clean URL)
```

**Why it breaks without `!-f` and `!-d`:**
```
Without conditions:
Request for /api/contact.php 
→ Adds another .php 
→ Becomes /api/contact.php.php 
→ 404 ERROR
```

---

### 3. Block Sensitive Directories

```apache
RewriteRule ^(includes|config|install|logs)(/|$) - [F]
```

**What it does:**
- Prevents direct access to sensitive folders
- Returns 403 Forbidden error
- Protects PHP configuration files

**Protected Directories:**
| Directory | Protection | Why |
|-----------|-----------|-----|
| `/includes/` | Database credentials, Security class | Would expose passwords |
| `/config/` | Environment variables, API keys | Sensitive configuration |
| `/install/` | Setup wizard, database schema | Dangerous after setup |
| `/logs/` | Security logs, system logs | Contains sensitive info |

**Example Blocks:**
```
✗ GET /includes/Database.php → 403 Forbidden
✗ GET /config/database.ini → 403 Forbidden
✗ GET /install/schema.sql → 403 Forbidden
✓ GET /api/products.php → OK (allowed)
✓ GET /admin/dashboard.php → OK (allowed)
```

---

### 4. Security Headers

```apache
Header always set X-Frame-Options "SAMEORIGIN"
Header always set X-XSS-Protection "1; mode=block"
Header always set X-Content-Type-Options "nosniff"
Header always unset Server
Header always unset X-Powered-By
```

#### A. X-Frame-Options (Clickjacking Protection)

```apache
Header always set X-Frame-Options "SAMEORIGIN"
```

**What it prevents:** Clickjacking attacks  
**How:** Website only allows itself to be embedded in iframes, not malicious sites

**Attack Example:**
```
Attacker creates invisible iframe pointing to your site
User clicks thinking they're clicking attacker's site
↓
Actually clicks on your site (CSRF attack)

X-Frame-Options: SAMEORIGIN PREVENTS THIS
```

**Values:**
- `DENY` - Never allow iframes (strictest)
- `SAMEORIGIN` - Allow only same domain (recommended)
- `ALLOW-FROM uri` - Allow specific domains

---

#### B. X-XSS-Protection (XSS Prevention)

```apache
Header always set X-XSS-Protection "1; mode=block"
```

**What it prevents:** Cross-Site Scripting (XSS) attacks  
**How:** Tells browser to block page if XSS detected

**Attack Example (XSS):**
```javascript
// Attacker injects:
// <img src="x" onerror="alert('hacked')">

// Browser with X-XSS-Protection: 1; mode=block
// → Blocks page if XSS detected
```

---

#### C. X-Content-Type-Options (MIME Sniffing Prevention)

```apache
Header always set X-Content-Type-Options "nosniff"
```

**What it prevents:** MIME type sniffing attacks  
**How:** Forces browser to respect Content-Type header

**Attack Example:**
```
Server sends: .txt file with HTML content
Header: Content-Type: text/plain
↓
Without nosniff: Browser might execute as HTML
With nosniff: Browser treats as plain text (safe)
```

---

#### D. Remove Server Information

```apache
Header always unset Server
Header always unset X-Powered-By
```

**What it does:** Hides server software information  
**Why:** Prevents attackers from identifying specific versions to exploit

**Before (leaks info):**
```
Server: Apache/2.4.41 (Ubuntu)
X-Powered-By: PHP/7.4.3
```
↓ Attacker knows exact versions to exploit

**After (with headers removed):**
```
Server: (removed)
X-Powered-By: (removed)
```
↓ Attacker can't identify version

---

### 5. Strict Transport Security (HSTS)

```apache
Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
```

**What it does:** Forces HTTPS for all connections  
**Duration:** 31536000 seconds = 1 year

**How it works:**
```
First visit: http://site.com
Server responds: "Always use HTTPS for 1 year"
↓
Subsequent visits: Browser FORCES https:// automatically
Even if user types: http://site.com
Browser redirects to: https://site.com
```

**Prevents:** Man-in-the-middle attacks on HTTP connections

---

### 6. Content Security Policy (CSP)

```apache
Header always set Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' cdn.jsdelivr.net; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' fonts.googleapis.com fonts.gstatic.com cdn.jsdelivr.net; connect-src 'self'; frame-ancestors 'none';"
```

**What it does:** Restricts where resources (scripts, styles, images) can come from

| Directive | Allows | Blocks |
|-----------|--------|--------|
| `default-src 'self'` | Resources from same domain | Resources from other domains |
| `script-src 'self' 'unsafe-inline' cdn.jsdelivr.net` | Scripts from: self, inline, jsdelivr | Scripts from random CDNs |
| `img-src 'self' data: https:` | Images from: self domain, data URIs, HTTPS URLs | Image hotlinks from unsecured sources |
| `frame-ancestors 'none'` | Prevents all framing | Stops clickjacking |

**Protects Against:** XSS, malicious script injection, resource hijacking

---

### 7. Compression (gzip)

```apache
<IfModule mod_deflate.c>
  AddOutputFilterByType DEFLATE text/css
  AddOutputFilterByType DEFLATE application/javascript
  AddOutputFilterByType DEFLATE text/html
  AddOutputFilterByType DEFLATE application/json
  AddOutputFilterByType DEFLATE application/xml
  AddOutputFilterByType DEFLATE image/svg+xml
</IfModule>
```

**What it does:** Reduces file sizes before sending to browser

**Example Compression:**
```
Original CSS file: 50 KB
After gzip-compress: 12 KB (75% smaller)
↓
Faster download, less bandwidth used
```

**File Types Compressed:**
- CSS (`.css`) - stylesheets
- JavaScript (`.js`) - scripts
- HTML (`.html`) - pages
- JSON - API responses
- XML - data format
- SVG - vector images

**Not Compressed:**
- Images (`.jpg`, `.png`) - already compressed
- Videos (`.mp4`) - already compressed
- PDFs - already compressed

---

### 8. Browser Caching

```apache
<IfModule mod_expires.c>
  ExpiresActive On
  ExpiresDefault "access plus 2 days"
  
  ExpiresByType text/html "access plus 2 days"
  ExpiresByType application/json "access plus 12 hours"
  ExpiresByType image/x-icon "access plus 1 year"
  ExpiresByType image/jpeg "access plus 1 year"
  ExpiresByType image/png "access plus 1 year"
  ExpiresByType text/css "access plus 1 year"
  ExpiresByType application/javascript "access plus 1 year"
  ExpiresByType font/ttf "access plus 1 year"
</IfModule>
```

**What it does:** Tells browsers how long to cache files locally

| File Type | Cache Duration | Why |
|-----------|---|---|
| HTML pages | 2 days | Pages change frequently |
| JSON API | 12 hours | Dynamic data |
| Images | 1 year | Images rarely change |
| CSS/JS | 1 year | Scripts/styles rarely change |
| Fonts | 1 year | Fonts never change |

**Benefits:**
```
First visit: Download 50 images (3MB)
Second visit: Browser uses cached images (0MB downloaded)
= 3MB faster, less server strain
```

---

### 9. Directory Protection

```apache
<IfModule mod_autoindex.c>
  Options -Indexes
</IfModule>
```

**What it does:** Prevents directory listing  
**Effect:** If user visits `/api/`, they get 403 instead of file list

**Before (`-Indexes` not set):**
```
GET /api/
Response:
[   ] contact.php
[   ] products.php
[   ] settings.php
↓ Attacker sees all files
```

**After (`-Indexes` set):**
```
GET /api/
Response: 403 Forbidden
↓ Attacker can't see directory contents
```

---

### 10. Script Execution Prevention in Upload Directory

```apache
<FilesMatch "\.(php|php3|php4|php5|php7|phtml|exe|sh)$">
  deny from all
</FilesMatch>
```

**What it does:** Prevents users from executing scripts uploaded to `/uploads/`

**Why it matters:**
```
Attacker uploads: malicious.php to /uploads/
Without this rule:
  GET /uploads/malicious.php
  → PHP executes, attacker gains server access

With this rule:
  GET /uploads/malicious.php
  → 403 Forbidden, script doesn't execute
```

**Protected File Types:**
- `.php` (PHP)
- `.php3`, `.php4`, `.php5`, `.php7` (PHP variants)
- `.exe` (executables)
- `.sh` (shell scripts)

---

### 11. Disable Old PHP Versions

```apache
# Prevent execution of PHP files in certain directories
<FilesMatch "\.php$">
  SetHandler "proxy:unix:/var/run/php-fpm.sock|fcgi://localhost"
</FilesMatch>
```

**Note:** This is server-dependent and may not be needed on all hosts

---

## URL Rewriting Examples

### Example 1: Contact Form Endpoint

```
User's Browser View:
  URL: https://site.com/api/contact

Apache Internal Route:
  File: /server/api/contact.php

How Rewrite Works:
  1. User requests /api/contact
  2. Apache checks: Does file "/api/contact" exist? NO
  3. Apache checks: Does directory "/api/contact" exist? NO
  4. Apache applies rule: ^([^\.]+)$ → $1.php
  5. Apache serves: /server/api/contact.php
  6. User still sees: /api/contact in address bar
```

### Example 2: Admin Dashboard

```
User's Request:  /admin/dashboard
Apache Serves:   /server/admin/dashboard.php
User Sees:       /admin/dashboard (clean)
```

### Example 3: Blocked Directory

```
User tries: /api/includes/Database.php
Apache checks: Does path match (includes|config|install|logs)?
Result: YES, matches "includes"
Apache returns: 403 Forbidden
User sees: Error page
```

---

## Common Errors & Fixes

### Problem: 404 Not Found on Clean URLs

**Symptoms:**
```
Request: https://site.com/api/products
Response: 404 Not Found
```

**Causes & Fixes:**

| Cause | Check | Fix |
|-------|-------|-----|
| mod_rewrite not enabled | `php -m \| grep rewrite` | Enable in Apache: `a2enmod rewrite` |
| .htaccess not being read | Check `<Directory>` in VirtualHost | Add `AllowOverride All` in Apache config |
| Wrong RewriteBase | Check RewriteBase path | Ensure RewriteBase matches site root |
| File/directory conditions reversed | Check `!-f` and `!-d` | These must have `!` prefix |

### Problem: HSTS Preventing HTTP Access

**Symptoms:**
```
Chrome: Cannot access http://site.com
Shows: NET::ERR_STRICT_TRANSPORT_SECURITY_PRELOAD_FAILED
```

**Fix:**
```apache
# Remove HSTS header if switching to HTTPS later
Header unset Strict-Transport-Security
```

Or reset via HTTPS:
```apache
Header always set Strict-Transport-Security "max-age=0"
```

Then wait 24 hours for browser cache to clear.

### Problem: Blocked Resources (Images, Fonts)

**Symptoms:**
```
Images not loading
Fonts not rendering
Console error: Refused to load due to CSP
```

**Fix:** Modify CSP header to allow domain:

```apache
# Add domain to appropriate sources
Header always set Content-Security-Policy "... img-src 'self' data: https: images.example.com; ..."
```

---

## Testing .htaccess Rules

### Test 1: URL Rewriting Works

```bash
# Should serve products.php but show clean URL
curl -I https://site.com/api/products

# Should show:
# HTTP/1.1 200 OK
# (not 404)
```

### Test 2: Sensitive Directories Blocked

```bash
# Should return 403 Forbidden
curl -I https://site.com/includes/Database.php
curl -I https://site.com/config.php

# Should show:
# HTTP/1.1 403 Forbidden
```

### Test 3: Security Headers Present

```bash
# Check response headers
curl -I https://site.com/

# Should show:
# X-Frame-Options: SAMEORIGIN
# X-XSS-Protection: 1; mode=block
# X-Content-Type-Options: nosniff
# Strict-Transport-Security: max-age=31536000
# Content-Security-Policy: ...
```

### Test 4: Compression Enabled

```bash
# Check for compression
curl -H "Accept-Encoding: gzip" -I https://site.com/

# Should show:
# Content-Encoding: gzip
# Content-Length: (smaller number than uncompressed)
```

### Test 5: Directory Listing Disabled

```bash
# Should return 403, not directory listing
curl https://site.com/api/

# Should show:
# HTTP/1.1 403 Forbidden
# (not index of files)
```

---

## Best Practices

✅ **DO:**
- Keep `.htaccess` backed up
- Test changes on staging before production
- Monitor Apache error logs for rewrite issues
- Keep security headers up to date
- Review cache times periodically

❌ **DON'T:**
- Modify `.htaccess` without understanding each rule
- Make all files/directories cacheable
- Use overly permissive CSP rules
- Disable security headers to "fix problems"
- Use HTTP instead of HTTPS in production

---

## Deployment Checklist for .htaccess

- [ ] `.htaccess` is in `/server/` root directory
- [ ] Apache has `AllowOverride All` enabled for `/server/` directory
- [ ] `mod_rewrite` is enabled: `a2enmod rewrite`
- [ ] `mod_headers` is enabled: `a2enmod headers`
- [ ] `mod_deflate` is enabled: `a2enmod deflate`
- [ ] `mod_expires` is enabled: `a2enmod expires`
- [ ] HTTPS is enabled on server
- [ ] Test clean URLs work: `/api/products` → 200 OK
- [ ] Test blocked paths return 403: `/includes/` → 403 Forbidden
- [ ] Test security headers present: Check via curl
- [ ] Monitor Apache error logs: `tail -f /var/log/apache2/error.log`

---

**Last Updated:** February 21, 2026  
**Next:** Task 14 - Create Deployment Checklist for Hostinger
