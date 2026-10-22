<?php
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Practical 7: PHP Form Processing & CSV/JSON File Storage</title>
  <link rel="stylesheet" href="./css/styles.css">
</head>
<body>

  <div class="app-container">
    
    <!-- Application Header -->
    <header class="app-header">
      <div class="header-top">
        <div class="header-title-group">
          <h1>Practical 7: PHP Form Processing & File Storage</h1>
          <p>Server-side POST validation, input sanitization, CSRF token protection, and CSV/JSON file writing</p>
        </div>
        <div>
          <button id="btn-theme-toggle" class="btn btn-secondary">☀️ Light / Dark</button>
        </div>
      </div>

      <!-- Navigation Tabs -->
      <nav class="nav-tabs-container">
        <div class="tab-group" role="tablist">
          <button class="tab-btn active" data-tab="form-section">
            📝 Registration Form
          </button>
          <button class="tab-btn" data-tab="records-section">
            📊 Stored Submissions (CSV/JSON)
          </button>
          <button class="tab-btn" data-tab="lab-report-section">
            📋 Lab Sheet & Viva Q&A
          </button>
        </div>
      </nav>
    </header>

    <!-- Global Alert Banners -->
    <div id="alert-success" class="alert alert-success" style="display: none;"></div>
    <div id="alert-danger" class="alert alert-danger" style="display: none;"></div>

    <!-- TAB 1: Registration Form -->
    <main id="form-section">
      <section class="form-card">
        <h2 class="section-title">Student Registration Form</h2>
        <p class="section-desc">Submit your details below. Form data is validated and sanitized on the server side before saving to CSV and JSON files.</p>

        <form id="student-form" action="process.php" method="POST" novalidate>
          <!-- CSRF Security Token -->
          <input type="hidden" name="csrf_token" id="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">

          <div class="form-grid">
            
            <!-- Full Name -->
            <div class="form-group">
              <label for="fullName">Full Name <span class="required">*</span></label>
              <input type="text" id="fullName" name="fullName" class="form-control" placeholder="e.g. Aarav Mehta" required>
              <span class="error-text" id="err-fullName"></span>
            </div>

            <!-- Email Address -->
            <div class="form-group">
              <label for="email">Email Address <span class="required">*</span></label>
              <input type="email" id="email" name="email" class="form-control" placeholder="e.g. aarav.mehta@student.edu" required>
              <span class="error-text" id="err-email"></span>
            </div>

            <!-- Roll Number -->
            <div class="form-group">
              <label for="rollNo">Roll Number <span class="required">*</span></label>
              <input type="text" id="rollNo" name="rollNo" class="form-control" placeholder="e.g. 23WDF042" required>
              <span class="error-text" id="err-rollNo"></span>
            </div>

            <!-- Department -->
            <div class="form-group">
              <label for="department">Department <span class="required">*</span></label>
              <select id="department" name="department" class="form-select" required>
                <option value="">Select Department</option>
                <option value="Computer Science">Computer Science</option>
                <option value="Information Technology">Information Technology</option>
                <option value="Artificial Intelligence">Artificial Intelligence</option>
                <option value="Data Science">Data Science</option>
                <option value="Electronics & Telecom">Electronics & Telecom</option>
              </select>
              <span class="error-text" id="err-department"></span>
            </div>

            <!-- Course Year -->
            <div class="form-group">
              <label for="courseYear">Course Year <span class="required">*</span></label>
              <select id="courseYear" name="courseYear" class="form-select" required>
                <option value="">Select Year</option>
                <option value="1st Year">1st Year</option>
                <option value="2nd Year">2nd Year</option>
                <option value="3rd Year">3rd Year</option>
                <option value="4th Year">4th Year</option>
              </select>
              <span class="error-text" id="err-courseYear"></span>
            </div>

            <!-- Phone Number -->
            <div class="form-group">
              <label for="phone">Phone Number <span class="required">*</span></label>
              <input type="tel" id="phone" name="phone" class="form-control" placeholder="10-digit mobile number" required>
              <span class="error-text" id="err-phone"></span>
            </div>

            <!-- City -->
            <div class="form-group">
              <label for="city">City <span class="required">*</span></label>
              <input type="text" id="city" name="city" class="form-control" placeholder="e.g. Mumbai" required>
              <span class="error-text" id="err-city"></span>
            </div>

            <!-- Message / Reason -->
            <div class="form-group full-width">
              <label for="message">Registration Notes / Remarks <span class="required">*</span></label>
              <textarea id="message" name="message" class="form-control" rows="3" placeholder="Enter any additional details or reason for registration..." required></textarea>
              <span class="error-text" id="err-message"></span>
            </div>

          </div>

          <div style="margin-top: 1.5rem; display: flex; gap: 1rem;">
            <button type="submit" id="btn-submit" class="btn btn-primary">Submit Registration (POST)</button>
            <button type="reset" id="btn-reset" class="btn btn-secondary">Reset Form</button>
          </div>

        </form>
      </section>
    </main>

    <!-- TAB 2: Stored Submissions Table (Intermediate Extension) -->
    <section id="records-section" style="display: none;">
      <div class="table-card">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1rem;">
          <div>
            <h2 class="section-title">Stored Submissions</h2>
            <p class="section-desc">Records saved on server-side file storage (CSV / JSON format).</p>
          </div>

          <div style="display: flex; gap: 0.5rem;">
            <button id="btn-view-json" class="btn btn-primary">JSON View</button>
            <button id="btn-view-csv" class="btn btn-secondary">CSV View</button>
            <button id="btn-refresh-records" class="btn btn-secondary">🔄 Refresh</button>
          </div>
        </div>

        <div class="table-responsive">
          <table class="records-table">
            <thead>
              <tr id="records-table-header">
                <th>ID</th>
                <th>Full Name</th>
                <th>Roll No</th>
                <th>Department</th>
                <th>Year</th>
                <th>Email</th>
                <th>Phone</th>
                <th>City</th>
                <th>Submitted At</th>
              </tr>
            </thead>
            <tbody id="records-table-body">
              <tr>
                <td colspan="9" style="text-align: center; color: var(--text-muted);">Loading stored records...</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </section>

    <!-- TAB 3: Lab Sheet Specifications -->
    <section id="lab-report-section" style="display: none;">
      <article class="lab-report-card">
        <h2 class="section-title">Practical 7 Specification Sheet</h2>
        <p class="section-desc">PHP Form Processing with Server-Side Validation and CSV/JSON File Storage</p>

        <table class="lab-meta-table">
          <tr>
            <th>Problem Definition</th>
            <td>Process submitted registration/contact form data using PHP. Validate and sanitize inputs on the server side and store records in CSV or JSON file format. Display success/error messages.</td>
          </tr>
          <tr>
            <th>Key Questions / Analysis</th>
            <td>
              <ol style="margin-left: 1.2rem;">
                <li>Is the form submitted using POST?</li>
                <li>Are inputs validated and sanitized server side?</li>
                <li>Is file writing handled safely?</li>
                <li>Are success and error responses displayed clearly?</li>
              </ol>
            </td>
          </tr>
          <tr>
            <th>Key Skills Addressed</th>
            <td>PHP forms, POST handling, server-side validation, sanitization, file writing, CSV/JSON storage</td>
          </tr>
          <tr>
            <th>Applications</th>
            <td>Backend form processors and small data collection systems</td>
          </tr>
          <tr>
            <th>Learning Outcome</th>
            <td>Students will implement server-side form processing with safe file-based storage.</td>
          </tr>
          <tr>
            <th>CO Mapping</th>
            <td>CO1, CO5</td>
          </tr>
          <tr>
            <th>Dataset / Test Data</th>
            <td>Registration / contact form inputs</td>
          </tr>
          <tr>
            <th>Tools / Technology Used</th>
            <td>PHP, XAMPP / WAMP / LAMP, browser dev tools</td>
          </tr>
          <tr>
            <th>Total Hours</th>
            <td>Implementation: 4 hours | Total engagement: 6 hours</td>
          </tr>
          <tr>
            <th>Post Laboratory Work Description</th>
            <td>Submit PHP source code, test cases, and generated CSV/JSON file.</td>
          </tr>
          <tr>
            <th>Evaluation Strategy Including Viva</th>
            <td>POST handling, validation, sanitization, storage correctness, and viva.</td>
          </tr>
          <tr>
            <th>Advanced / Intermediate Extension</th>
            <td>
              <strong>Intermediate:</strong> Display stored CSV/JSON records on a webpage.<br>
              <strong>Advanced:</strong> Add CSRF token validation for form submission.
            </td>
          </tr>
        </table>

        <h3 style="margin-top: 2rem; margin-bottom: 1rem; color: var(--primary);">🧠 Viva Voce Questions & Answers</h3>

        <div class="qa-box">
          <h4>Q1. Is the form submitted using POST method? Why is POST preferred over GET for forms?</h4>
          <p>Yes, the form specifies <code>method="POST"</code> and the server verifies <code>$_SERVER['REQUEST_METHOD'] === 'POST'</code>. POST is preferred because form data is included in the HTTP request body rather than visible in URL parameters, preventing sensitive data exposure and URL length restrictions.</p>
        </div>

        <div class="qa-box">
          <h4>Q2. How are inputs validated and sanitized on the server side?</h4>
          <p>Inputs are sanitized using <code>trim()</code>, <code>strip_tags()</code>, <code>htmlspecialchars()</code>, and <code>filter_var(..., FILTER_SANITIZE_EMAIL)</code> to remove unwanted whitespace, strip HTML tags, and escape XSS vectors. Validation checks required fields, email syntax (<code>FILTER_VALIDATE_EMAIL</code>), phone numbers (10 digits regex), and character lengths.</p>
        </div>

        <div class="qa-box">
          <h4>Q3. How is file writing handled safely for CSV and JSON file formats?</h4>
          <p>File locking is performed using <code>flock($handle, LOCK_EX)</code> before writing to prevent file corruption during concurrent requests. For JSON, existing contents are parsed, appended with the new record array, and rewritten. For CSV, <code>fputcsv()</code> appends rows cleanly with proper escaping.</p>
        </div>

        <div class="qa-box">
          <h4>Q4. How are CSRF tokens validated for enhanced form security?</h4>
          <p>A random cryptographically secure token (<code>bin2hex(random_bytes(32))</code>) is stored in the PHP session and embedded as a hidden field in the form. On POST submission, <code>hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])</code> compares both tokens to prevent cross-site request forgery attacks.</p>
        </div>

      </article>
    </section>

  </div>

  <script src="./js/app.js"></script>

</body>
</html>
