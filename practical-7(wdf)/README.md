# Practical 7: PHP Form Processing with Server-Side Validation and CSV/JSON File Storage

## 📌 Problem Definition
Process submitted registration/contact form data using PHP. Validate and sanitize inputs on the server side and store records in CSV or JSON file format. Display success/error messages.

---

## 🛠️ Key Features Implemented

1. **PHP Server-Side Form Processing (`process.php`)**
   - Verification of `POST` HTTP request method.
   - Server-side input sanitization (`trim()`, `strip_tags()`, `htmlspecialchars()`, `filter_var()`).
   - Comprehensive validation rules for Full Name, Email, Roll Number, Department, Course Year, Phone (10 digits), City, and Remarks.

2. **Advanced Extension: CSRF Token Protection**
   - Session-based CSRF token generation (`bin2hex(random_bytes(32))`).
   - Secure server verification using `hash_equals()`.

3. **Safe File-Based Storage (`data/submissions.json` & `data/submissions.csv`)**
   - Safe file writing using `flock($handle, LOCK_EX)` for concurrent access safety.
   - Dual-format recording: appends to JSON array and appends row to CSV using `fputcsv()`.

4. **Intermediate Extension: Stored Submissions Viewer**
   - Interactive webpage view displaying all stored records from CSV and JSON file storage.

5. **Lab Specifications & Viva Q&A**
   - Built-in lab documentation tab covering problem statement, learning outcomes (CO1, CO5), and detailed answers to all viva voce questions.

---

## 💻 How to Run the Project

1. Copy the project folder into your XAMPP/WAMP/LAMP web server directory (e.g. `htdocs/practical-7`).
2. Start Apache Server in XAMPP / WAMP Control Panel.
3. Open `http://localhost/practical-7/index.php` in your web browser.
4. Fill and submit the student registration form to see server-side validation and file storage in action.
5. Click **Stored Submissions** tab to toggle between JSON and CSV stored records.
