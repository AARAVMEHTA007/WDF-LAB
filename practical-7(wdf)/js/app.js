/**
 * Student Hub Portal - Practical 7
 * Handles tab switching, form validation, POST submission, and table rendering.
 */

var currentFormat = 'json';

// Tab Switcher (called from HTML onclick)
function showTab(tabId, clickedBtn) {
  var tabs = ['form-section', 'records-section'];
  tabs.forEach(function(id) {
    var el = document.getElementById(id);
    if (el) el.style.display = 'none';
  });

  var target = document.getElementById(tabId);
  if (target) target.style.display = 'block';

  // Update active nav style
  document.querySelectorAll('.navbar ul li').forEach(function(li) {
    li.classList.remove('active');
  });
  if (clickedBtn && clickedBtn.parentElement) {
    clickedBtn.parentElement.classList.add('active');
  }

  // Load records when switching to records tab
  if (tabId === 'records-section') {
    loadRecords(currentFormat);
  }
}

// Load Records (called from HTML onclick)
function loadRecords(format) {
  currentFormat = format;

  var btnJson = document.getElementById('btn-json');
  var btnCsv = document.getElementById('btn-csv');
  if (btnJson && btnCsv) {
    if (format === 'json') {
      btnJson.className = 'btn btn-primary';
      btnCsv.className = 'btn btn-secondary';
    } else {
      btnCsv.className = 'btn btn-primary';
      btnJson.className = 'btn btn-secondary';
    }
  }

  var tbody = document.getElementById('records-tbody');
  tbody.innerHTML = '<tr><td colspan="9" style="text-align:center;">Loading ' + format.toUpperCase() + ' records...</td></tr>';

  fetch('get_records.php?format=' + format)
    .then(function(res) {
      if (!res.ok) throw new Error('PHP not available');
      return res.json();
    })
    .then(function(result) {
      if (result.data && result.data.length > 0) {
        renderRows(result.data);
        showRawOutput(format, result.data);
      } else {
        tbody.innerHTML = '<tr><td colspan="9" style="text-align:center; color:#888;">No records found in storage.</td></tr>';
        showRawOutput(format, []);
      }
    })
    .catch(function() {
      // Fallback: load from localStorage when PHP is not running
      var fallback = getFallbackRecords();
      if (fallback.length > 0) {
        renderRows(fallback);
        showRawOutput(format, fallback);
      } else {
        tbody.innerHTML = '<tr><td colspan="9" style="text-align:center; color:#888;">No submissions found. Submit the form to see records here.</td></tr>';
        showRawOutput(format, []);
      }
    });
}

function renderRows(records) {
  var tbody = document.getElementById('records-tbody');
  tbody.innerHTML = '';
  records.forEach(function(r) {
    var tr = document.createElement('tr');
    tr.innerHTML =
      '<td>' + esc(r.id || r.ID || '') + '</td>' +
      '<td>' + esc(r.fullName || r['Full Name'] || '') + '</td>' +
      '<td><code>' + esc(r.rollNo || r['Roll No'] || '') + '</code></td>' +
      '<td>' + esc(r.department || r.Department || '') + '</td>' +
      '<td>' + esc(r.courseYear || r['Course Year'] || '') + '</td>' +
      '<td>' + esc(r.email || r.Email || '') + '</td>' +
      '<td>' + esc(r.phone || r.Phone || '') + '</td>' +
      '<td>' + esc(r.city || r.City || '') + '</td>' +
      '<td>' + esc(r.submittedAt || r['Submitted At'] || '') + '</td>';
    tbody.appendChild(tr);
  });
}

// Show raw JSON or CSV text output
function showRawOutput(format, data) {
  var section = document.getElementById('raw-output-section');
  var box = document.getElementById('raw-output-box');
  var label = document.getElementById('raw-output-label');

  if (!section || !box) return;

  section.style.display = 'block';

  if (format === 'json') {
    label.textContent = '\u{1F4C4} Raw JSON File Output (data/submissions.json)';
    box.textContent = JSON.stringify(data, null, 2);
  } else {
    label.textContent = '\u{1F4C4} Raw CSV File Output (data/submissions.csv)';
    // Build CSV string from data
    if (!data || data.length === 0) {
      box.textContent = 'No CSV records found.';
      return;
    }
    var keys = Object.keys(data[0]);
    var csvLines = [keys.join(',')];
    data.forEach(function(row) {
      var line = keys.map(function(k) {
        var val = String(row[k] || '').replace(/"/g, '""');
        return '"' + val + '"';
      }).join(',');
      csvLines.push(line);
    });
    box.textContent = csvLines.join('\n');
  }
}

// Copy raw output to clipboard
function copyRawOutput() {
  var box = document.getElementById('raw-output-box');
  if (!box) return;
  navigator.clipboard.writeText(box.textContent).then(function() {
    alert('Copied to clipboard!');
  }).catch(function() {
    box.select && box.select();
  });
}

function esc(str) {
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;');
}

// Form Submit Handler
document.addEventListener('DOMContentLoaded', function() {
  var form = document.getElementById('student-form');
  if (form) {
    form.addEventListener('submit', function(e) {
      e.preventDefault();
      clearMessages();

      var errors = validateForm();
      if (Object.keys(errors).length > 0) {
        Object.keys(errors).forEach(function(key) {
          markError(key, errors[key]);
        });
        showDanger('Please fill in all required fields correctly.');
        return;
      }

      var formData = new FormData(form);

      fetch('process.php', { method: 'POST', body: formData })
        .then(function(res) { return res.json(); })
        .then(function(result) {
          if (result.success) {
            showSuccess(result.message || 'Registration submitted successfully!');
            form.reset();
            if (result.newCsrfToken) {
              var t = document.getElementById('csrf_token');
              if (t) t.value = result.newCsrfToken;
            }
          } else {
            if (result.errors) {
              Object.keys(result.errors).forEach(function(k) {
                markError(k, result.errors[k]);
              });
            }
            showDanger(result.message || 'Submission failed. Please check errors.');
          }
        })
        .catch(function() {
          // PHP not running — simulate save to localStorage
          var rec = {
            id: Date.now(),
            fullName: document.getElementById('fullName').value.trim(),
            rollNo: document.getElementById('rollNo').value.trim(),
            department: document.getElementById('department').value,
            courseYear: document.getElementById('courseYear').value,
            email: document.getElementById('email').value.trim(),
            phone: document.getElementById('phone').value.trim(),
            city: document.getElementById('city').value.trim(),
            message: document.getElementById('message').value.trim(),
            submittedAt: new Date().toLocaleString()
          };
          saveFallbackRecord(rec);
          showSuccess('Registration submitted! (Note: Open via XAMPP for PHP server storage. Data saved locally for preview.)');
          form.reset();
        });
    });
  }
});

function validateForm() {
  var errors = {};
  var fullName = document.getElementById('fullName').value.trim();
  var email = document.getElementById('email').value.trim();
  var rollNo = document.getElementById('rollNo').value.trim();
  var department = document.getElementById('department').value;
  var courseYear = document.getElementById('courseYear').value;
  var phone = document.getElementById('phone').value.trim();
  var city = document.getElementById('city').value.trim();
  var message = document.getElementById('message').value.trim();

  if (!fullName) errors.fullName = 'Full Name is required.';
  else if (!/^[a-zA-Z\s]{2,50}$/.test(fullName)) errors.fullName = 'Only letters allowed (2-50 chars).';

  if (!email) errors.email = 'Email is required.';
  else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) errors.email = 'Enter a valid email address.';

  if (!rollNo) errors.rollNo = 'Roll Number is required.';

  if (!department) errors.department = 'Please select a department.';

  if (!courseYear) errors.courseYear = 'Please select academic year.';

  if (!phone) errors.phone = 'Phone number is required.';
  else if (!/^[0-9]{10}$/.test(phone)) errors.phone = 'Phone must be exactly 10 digits.';

  if (!city) errors.city = 'City is required.';

  if (!message) errors.message = 'Remarks field is required.';

  return errors;
}

function markError(fieldId, msg) {
  var el = document.getElementById(fieldId);
  var err = document.getElementById('err-' + fieldId);
  if (el) el.classList.add('is-invalid');
  if (err) { err.innerText = msg; err.classList.add('active'); }
}

function clearMessages() {
  document.querySelectorAll('.form-control, .form-select').forEach(function(el) {
    el.classList.remove('is-invalid');
  });
  document.querySelectorAll('.error-text').forEach(function(el) {
    el.innerText = '';
    el.classList.remove('active');
  });
  document.getElementById('alert-success').style.display = 'none';
  document.getElementById('alert-danger').style.display = 'none';
}

function showSuccess(msg) {
  var el = document.getElementById('alert-success');
  el.innerText = msg;
  el.style.display = 'block';
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function showDanger(msg) {
  var el = document.getElementById('alert-danger');
  el.innerText = msg;
  el.style.display = 'block';
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function saveFallbackRecord(rec) {
  var existing = getFallbackRecords();
  existing.push(rec);
  localStorage.setItem('p7_fallback', JSON.stringify(existing));
}

function getFallbackRecords() {
  var defaultData = [
    { id: 1, fullName: 'Aarav Mehta', rollNo: '23WDF042', department: 'Computer Science', courseYear: '3rd Year', email: 'aarav.mehta@student.edu', phone: '9876543210', city: 'Mumbai', submittedAt: '29/09/2026, 2:30:15 PM' },
    { id: 2, fullName: 'Priya Sharma', rollNo: '23WDF018', department: 'Information Technology', courseYear: '2nd Year', email: 'priya.sharma@student.edu', phone: '9812345678', city: 'Pune', submittedAt: '29/09/2026, 4:45:22 PM' }
  ];
  try {
    var stored = localStorage.getItem('p7_fallback');
    return stored ? JSON.parse(stored) : defaultData;
  } catch (e) {
    return defaultData;
  }
}
