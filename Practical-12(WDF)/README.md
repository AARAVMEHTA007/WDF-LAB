# Practical 12: Event Management CRUD with Poster Upload and File Validation

## 🎯 Problem Definition
Develop event management functionality for admin users. Admin can add, view, update, delete, and upload event posters with file type and file size validation.

---

## 📋 Key Analysis & Implementation Details

| Key Question | How It Is Addressed in Code |
| :--- | :--- |
| **1. Are add, update, and delete functionalities implemented correctly?** | `add_event.php`, `edit_event.php`, `delete_event.php`, and `view_event.php` provide full CRUD with parameterized SQL sanitization and input handling. |
| **2. Is the UI linked with the database correctly?** | `db.php` connects to MySQL database `event_management`. `index.php` queries and dynamically renders event cards and statistics. |
| **3. Are poster uploads validated for type and size?** | Both client-side (real-time feedback) and server-side validation (`mime_content_type`, extension check `jpg/png/gif/webp`, max size `2MB`, and `getimagesize()` integrity check). |
| **4. Are success and failure messages displayed?** | Bootstrap alert banners render success or failure messages after all operations (add, edit, delete, invalid uploads). |

---

## 🌟 Intermediate & Advanced Extensions Included

1. **Intermediate Extension:**
   - **Event Category Filter:** Filter by Conference, Workshop, Seminar, Webinar, Meetup, Concert, Sports, Other.
   - **Open/Closed Status Filter:** Quickly view active vs. completed events.
   - **Live Statistics Cards:** Displays Total Events, Open Events, Closed Events, and Events with Posters.

2. **Advanced Extension:**
   - **Secure Image Renaming:** Uploaded posters are renamed using timestamp + random crypto tokens (`event_TIMESTAMP_HASH.ext`) to avoid collision and execution risks.
   - **Upload Directory Protection:** `.htaccess` file inside `uploads/` disables script execution (e.g. `.php`, `.cgi`) and directory browsing.
   - **Audit Trail Logging:** `audit_log` table records every `CREATE`, `UPDATE`, and `DELETE` action with old and new values in JSON format with timestamps. Viewable via `audit_log.php`.

---

## 🚀 How to Run

1. Open **XAMPP Control Panel** and start **Apache** and **MySQL**.
2. Run database migration by running in command prompt / browser:
   - Command: `C:\xampp\php\php.exe setup_db.php`
   - Or open in browser: `http://localhost/Practical-12(WDF)/setup_db.php`
3. Access the web app:
   - **Main Dashboard:** `http://localhost/Practical-12(WDF)/index.php`
   - **Add Event:** `http://localhost/Practical-12(WDF)/add_event.php`
   - **Audit Trail:** `http://localhost/Practical-12(WDF)/audit_log.php`

---

## 📁 File Structure
```
Practical-12(WDF)/
├── db.php                 # MySQL database connection
├── index.php              # Event listings, cards, statistics, and filters
├── add_event.php          # Add event with poster upload & validation
├── edit_event.php         # Edit event details and replace poster
├── view_event.php         # Single event detailed view
├── delete_event.php       # Delete event and remove its poster image
├── audit_log.php          # Audit log history (Advanced feature)
├── setup_db.php           # Setup script to initialize DB and posters
├── sql/
│   └── events.sql         # Database schema and initial sample records
└── uploads/
    └── .htaccess          # Security rule preventing script execution
```
