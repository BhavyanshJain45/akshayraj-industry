# Testing Checklist & Guide

**Last Updated:** February 21, 2026  
**Status:** Ready for Testing  
**Prerequisites:** Local PHP server, MySQL database, admin account created via installation wizard

---

## Task 8: Test All Admin Panel Pages Locally ✅

### Before Starting
1. Ensure `/install/install.php` has been run to:
   - Create `.env` file with database credentials
   - Import database schema
   - Create admin user account
2. Run PHP server locally
3. Access `http://localhost/admin/` to start testing

### Admin Login Page Tests

| Test Case | Steps | Expected Result | Status |
|-----------|-------|-----------------|--------|
| **Valid Login** | Enter correct admin email & password, submit form | Redirects to dashboard | ☐ |
| **Invalid Email** | Enter non-existent email, submit form | Shows error message | ☐ |
| **Wrong Password** | Enter correct email, wrong password, submit form | Shows error message | ☐ |
| **Empty Fields** | Try submitting with empty email/password | Form validation prevents submission | ☐ |
| **Email Format** | Enter invalid email format, submit form | Shows validation error | ☐ |
| **Session Timeout** | Login, wait >1 hour inactive, refresh page | Redirects to login | ☐ |
| **Rate Limiting** | Try 6 login attempts in <5 min | After 5 attempts: shows rate limit message | ☐ |
| **CSRF Token** | Try bypassing CSRF token in form | Request fails security check | ☐ |

### Admin Dashboard Tests

| Test Case | Steps | Expected Result | Status |
|-----------|-------|-----------------|--------|
| **Dashboard Load** | Access `/admin/dashboard.php` after login | Dashboard displays with all stats | ☐ |
| **Product Count** | Check total products stat | Shows correct number of active products | ☐ |
| **Unread Messages** | Check unread messages stat | Shows correct count of unread messages | ☐ |
| **Last Login Time** | Check last login display | Shows accurate timestamp | ☐ |
| **Quick Action Buttons** | Click each quick action button | Links navigate to correct pages | ☐ |
| **Sidebar Navigation** | Click each sidebar menu item | All pages load correctly | ☐ |
| **Logout Function** | Click logout button | Clears session, redirects to login | ☐ |
| **Session Active** | Perform actions, check session duration | Session stays active for up to 1 hour | ☐ |

### Admin Responsive Design Tests

| Test Case | Steps | Expected Result | Status |
|-----------|-------|-----------------|--------|
| **Desktop View** | View on 1920x1080 screen | Sidebar visible, proper layout | ☐ |
| **Tablet View** | Resize to 1024px width | Sidebar adjusts, content readable | ☐ |
| **Mobile View** | Resize to 375px width | Sidebar stacks, navigation accessible | ☐ |
| **Mobile Menu** | On mobile, check sidebar menu access | Menu items visible and clickable | ☐ |

---

## Task 9: Test Contact Form Submission & Email ✅

### Frontend Contact Form Tests

| Test Case | Steps | Expected Result | Status |
|-----------|-------|-----------------|--------|
| **Form Load** | Navigate to /contact page | Form displays properly | ☐ |
| **Required Fields** | Try submitting empty form | Shows validation error prompting to fill fields | ☐ |
| **Name Field** | Enter name, submit | Accepted and submitted | ☐ |
| **Email Field** | Enter valid email | Accepted | ☐ |
| **Invalid Email** | Enter invalid email (no @), submit | Shows error | ☐ |
| **Phone Field** | Enter phone number | Accepted | ☐ |
| **Message Field** | Enter message, submit | Accepted | ☐ |
| **Message Length** | Try submitting message > 500 chars | Shows "Message must be 500 characters or less" | ☐ |
| **Success Response** | Submit valid form | Shows success message, form clears | ☐ |
| **Error Handling** | Simulate server error | Shows error message gracefully | ☐ |

### Backend Contact API Tests

| Test Case | Steps | Expected Result | Status |
|-----------|-------|-----------------|--------|
| **API Endpoint** | POST to `/api/contact.php` with valid data | Returns JSON with success: true | ☐ |
| **Rate Limiting** | Submit 6 forms from same IP in 1 hour | 6th attempt shows rate limit error (429) | ☐ |
| **Input Sanitization** | Submit text with HTML tags: `<script>alert('test')</script>` | HTML tags are escaped/removed | ☐ |
| **Email Validation** | Submit with invalid email | Returns 422 validation error | ☐ |
| **Missing Fields** | POST without required fields | Returns 422 with field error | ☐ |
| **Database Insert** | Submit form, check database | Message appears in contact_messages table | ☐ |
| **Email to Admin** | Submit form, check admin email inbox | Receives notification email within 2 min | ☐ |
| **Confirmation Email** | Submit form, check user email | User receives confirmation email | ☐ |

### Email Content Tests

| Test Case | Steps | Expected Result | Status |
|-----------|-------|-----------------|--------|
| **Admin Email Subject** | Check email received by admin | Subject: "New Contact Form Submission - Akshayraj" | ☐ |
| **Admin Email Body** | Check admin email content | Includes: name, email, phone, message, timestamp | ☐ |
| **User Confirmation Email** | Check email received by user | Professional HTML formatted email | ☐ |
| **Email Formatting** | Check email rendering in Gmail, Outlook | Proper HTML rendering, no broken styling | ☐ |

---

## Task 10: Test Product CRUD Operations ✅

### Create Product Tests

| Test Case | Steps | Expected Result | Status |
|-----------|-------|-----------------|--------|
| **Access Add Form** | Click "Add Product" button on admin/products.php | Form displays | ☐ |
| **Add Required Info** | Fill all required fields (title, description, category) | Form accepts input | ☐ |
| **Add Optional Info** | Fill capacity, features, price | All fields accept input | ☐ |
| **Upload Image** | Upload JPG image (< 5MB) | Image upload succeeds | ☐ |
| **Invalid Image Type** | Try uploading .gif or .exe | Shows "Invalid file type" error | ☐ |
| **Large Image** | Try uploading image > 5MB | Shows "File size too large" error | ☐ |
| **Create Product** | Submit form with valid data | Product created, redirects to list, success message | ☐ |
| **Database Check** | Check products table | New product appears in list | ☐ |
| **Image Resized** | Check uploaded image in /uploads folder | Image resized to 1200x900px max | ☐ |

### Read/List Products Tests

| Test Case | Steps | Expected Result | Status |
|-----------|-------|-----------------|--------|
| **List All** | Access admin/products.php | Shows all active products in table | ☐ |
| **Product Info** | Check product row | Shows: title, category, capacity, price, date | ☐ |
| **Edit Button** | Click edit button on product row | Loads edit form with product data | ☐ |
| **Delete Button** | Check delete button | Button appears on each row | ☐ |
| **No Products State** | If no products in DB | Shows "No products found" message | ☐ |

### Update Product Tests

| Test Case | Steps | Expected Result | Status |
|-----------|-------|-----------------|--------|
| **Open Edit Form** | Click edit on existing product | Form pre-fills with product data | ☐ |
| **Edit Title** | Change title, save | Product title updates in database | ☐ |
| **Edit Description** | Change description, save | Product description updates in database | ☐ |
| **Update Price** | Change product price, save | Price updates correctly | ☐ |
| **Change Image** | Upload new product image, save | Old image replaced with new image | ☐ |
| **Keep Same Image** | Edit other fields but don't upload new image | Old image is preserved | ☐ |
| **Update Features** | Modify features list, save | Features update in database | ☐ |
| **Success Message** | After successful edit | Shows "Product updated successfully" | ☐ |

### Delete Product Tests

| Test Case | Steps | Expected Result | Status |
|-----------|-------|-----------------|--------|
| **Delete Button** | Click delete on product | Shows confirmation dialog | ☐ |
| **Confirm Delete** | Click "OK" on confirmation | Product marked as inactive (soft delete) | ☐ |
| **Verify Deleted** | Check products list after delete | Product no longer appears in list | ☐ |
| **Database Status** | Check products table | Product is_active = 0 | ☐ |

### Products API Tests

| Test Case | Steps | Expected Result | Status |
|-----------|-------|-----------------|--------|
| **GET All Products** | Fetch `/api/products.php` | Returns JSON with products array, pagination | ☐ |
| **GET Single Product** | Fetch `/api/products.php?id=1` | Returns single product by ID | ☐ |
| **Filter by Category** | Fetch `/api/products.php?category=water-tanks` | Returns only products in that category | ☐ |
| **Search Products** | Fetch `/api/products.php?search=tank` | Returns products matching search term | ☐ |
| **Pagination** | Fetch `/api/products.php?limit=5&offset=0` | Returns paginated results | ☐ |

---

## Task 11: Test Message Inbox & Search Filters ✅

### Message List Tests

| Test Case | Steps | Expected Result | Status |
|-----------|-------|-----------------|--------|
| **Load Messages** | Access admin/messages.php | Shows all contact messages in table | ☐ |
| **Message Columns** | Check table columns | Shows: name, email, phone, message preview, status, date | ☐ |
| **Message Count** | Check header | Shows total messages and unread count | ☐ |
| **Unread Styling** | Check new messages | Unread messages appear in bold/highlighted | ☐ |
| **message Preview** | Check message column | Shows first 60 chars of message | ☐ |

### Search & Filter Tests

| Test Case | Steps | Expected Result | Status |
|-----------|-------|-----------------|--------|
| **Search by Name** | Enter name in search, click search | Shows only messages from that person | ☐ |
| **Search by Email** | Enter email in search box | Shows only messages from that email | ☐ |
| **Search by Phone** | Enter phone number in search | Shows only messages from that phone | ☐ |
| **Filter: All** | Click "All" filter | Shows all messages | ☐ |
| **Filter: Unread** | Click "Unread" filter | Shows only unread messages | ☐ |
| **Filter: Read** | Click "Read" filter | Shows only read messages | ☐ |
| **No Results** | Search for non-existent data | Shows "No messages found" | ☐ |
| **Clear Search** | Clear search box, click search | Shows all messages again | ☐ |

### Message Details & Actions

| Test Case | Steps | Expected Result | Status |
|-----------|-------|-----------------|--------|
| **View Message** | Click "View" button on message | Modal opens showing full message details | ☐ |
| **Message Modal** | Check modal content | Shows: name, email, phone, full message, date | ☐ |
| **Mark as Read** | On unread message modal, click action | Message marked as read, list updates | ☐ |
| **Mark as Unread** | On read message modal, click action | Message marked as unread, list updates | ☐ |
| **Close Modal** | Click X or outside modal | Modal closes | ☐ |
| **Delete Message** | Click delete button, confirm | Message deleted from database | ☐ |
| **Delete Confirmation** | Try to delete | Shows "Are you sure?" confirmation | ☐ |

---

## Task 12: Test Settings Save & Retrieval ✅

### Settings Form Tests

| Test Case | Steps | Expected Result | Status |
|-----------|-------|-----------------|--------|
| **Load Settings Page** | Access admin/settings.php | Form displays with all settings fields | ☐ |
| **Prefilled Values** | Check form fields | All fields prefilled with current values from database | ☐ |
| **Site Name Field** | Try changing site name | Field accepts input | ☐ |
| **Site Email Field** | Try changing site email | Field validates email format | ☐ |
| **Invalid Email** | Enter invalid email format | Shows validation error | ☐ |
| **Phone Field** | Enter phone number | Field accepts input | ☐ |
| **Address Field** | Enter address | Field accepts input | ☐ |
| **Description Field** | Edit description | Textarea accepts longer text | ☐ |

### Settings Save & Retrieval

| Test Case | Steps | Expected Result | Status |
|-----------|-------|-----------------|--------|
| **Save Settings** | Modify all fields, click save | Shows success message | ☐ |
| **Database Update** | Check settings table | All values updated in database | ☐ |
| **Reload Page** | Refresh settings page | Values persist and display correctly | ☐ |
| **API Retrieval** | Fetch `/api/settings.php` | Returns all updated settings in JSON | ☐ |
| **Frontend Display** | Check if frontend displays updated values | Settings appear correctly on public site | ☐ |
| **Email Integration** | Update admin email, send contact form | Email notification goes to new address | ☐ |
| **Partial Update** | Change only one field, save | Only that field updates, others unchanged | ☐ |

---

## Summary & Next Steps

Once all tests pass:
- ✅ Mark corresponding task as COMPLETED
- ✅ Note any issues in the "Status" column
- ✅ Proceed to deployment preparation (Tasks 13-16)

### Common Issues & Troubleshooting

```
❌ 404 Error on /admin/ pages
✓ Solution: Check .htaccess rewrite rules, ensure PHP file handler is enabled

❌ Database connection errors
✓ Solution: Verify .env file credentials match your database

❌ Email not sending
✓ Solution: Check mail() function enabled on server, verify SMTP settings

❌ Images not displaying
✓ Solution: Verify image paths use /assets/, check upload permissions

❌ Rate limiting blocks legitimate requests
✓ Solution: Check if client IP detection is correct (_SERVER["REMOTE_ADDR"])

❌ Session timeout too quick/slow
✓ Solution: Adjust SESSION_TIMEOUT constant in includes/config.php (default: 3600 sec)
```

---

**Test Status:** Ready to execute  
**Estimated Time:** 2-3 hours  
**Next:** Task 13 - Document .htaccess Rewrite Rules
