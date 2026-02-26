# Akshayraj Industry - PHP CMS Backend

## Folder Structure

```
/
├── admin/                      # Admin panel
│   ├── index.php              # Admin login
│   ├── dashboard.php          # Admin dashboard
│   ├── products.php           # Product management
│   ├── messages.php           # Contact messages
│   ├── settings.php           # Site settings
│   ├── mailbox.php            # Email viewer (IMAP)
│   └── logout.php             # Logout handler
├── api/                        # API endpoints
│   ├── products.php           # Product CRUD
│   ├── contact.php            # Contact form handler
│   ├── settings.php           # Settings CRUD
│   └── auth.php               # Authentication
├── uploads/                    # File uploads
│   └── products/              # Product images
├── includes/                   # Shared functions
│   ├── config.php             # Database config
│   ├── Database.php           # PDO database class
│   ├── Security.php           # Security functions
│   ├── Auth.php               # Authentication class
│   ├── Mailer.php             # Email handler
│   ├── ImageHandler.php       # Image processing
│   └── helpers.php            # Utility functions
├── public/                     # Static files
│   ├── css/                   # Admin CSS
│   └── js/                    # Admin JS
├── install/                    # Installation files
│   ├── install.php            # Installation wizard
│   ├── schema.sql             # Database schema
│   └── .htaccess              # Apache rewrite rules
└── .htaccess                  # Root rewrite rules
```

## Installation Steps

1. Upload all files to your Hostinger server
2. Create MySQL database via cPanel
3. Run `/install/install.php` in browser
4. Delete `/install/` folder after setup
5. Login to `/admin/` with default credentials
6. Configure settings from admin panel

## Security Features

- CSRF token protection on all forms
- PDO prepared statements for SQL injection prevention
- Input sanitization for all user data
- Password hashing with PHP password_hash()
- Session-based authentication
- .htaccess security headers
- File upload validation (whitelist types)
- Local File Inclusion (LFI) prevention
- Directory browsing disabled

## API Endpoints

- `POST /api/contact.php` - Submit contact form
- `GET /api/products.php` - Get all products
- `POST /api/auth.php` - Admin login

## Frontend Integration

Your React contact form already points to `/sendmail.php` - this will now save to database and send email.
