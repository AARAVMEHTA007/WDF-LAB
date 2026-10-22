// js/add_student.js
// Bootstrap validation helper and optional AJAX (disabled by default)
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('addStudentForm');
    if (!form) return;

    form.addEventListener('submit', function (event) {
        // Trigger HTML5 validation UI
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        form.classList.add('was-validated');
        // If needed, you could submit via AJAX here.
    }, false);
});
