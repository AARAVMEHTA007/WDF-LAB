<?php
require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        $stmt = $conn->prepare("SELECT id, username, password, role, status FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if ($user['status'] === 'inactive') {
                $error = 'Your account is inactive. Contact the administrator.';
            } elseif (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                // Log successful login
                logAudit($conn, $user['id'], 'LOGIN', $user['username'] . ' logged in successfully');

                // Redirect based on role
                if ($user['role'] === 'admin') {
                    header("Location: dashboard.php");
                } else {
                    header("Location: student_home.php");
                }
                exit();
            } else {
                $error = 'Invalid username or password.';
            }
        } else {
            $error = 'Invalid username or password.';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            max-width: 420px;
            width: 100%;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            border: none;
        }
        .login-card .card-header {
            background: linear-gradient(135deg, #5b4a9e, #7c3aed);
            color: white;
            text-align: center;
            border-radius: 15px 15px 0 0 !important;
            padding: 30px;
        }
        .login-card .card-header i {
            font-size: 3rem;
            margin-bottom: 10px;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #5a6fd6, #6a3f9a);
        }
    </style>
</head>
<body>
    <div class="card login-card">
        <div class="card-header">
            <i class="fas fa-shield-alt"></i>
            <h3 class="mb-0">Admin Dashboard</h3>
            <small>Role-Based Access Control System</small>
        </div>
        <div class="card-body p-4">
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label for="username" class="form-label"><i class="fas fa-user me-1"></i> Username</label>
                    <input type="text" class="form-control form-control-lg" id="username" name="username" 
                           placeholder="Enter username" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                </div>
                <div class="mb-4">
                    <label for="password" class="form-label"><i class="fas fa-lock me-1"></i> Password</label>
                    <input type="password" class="form-control form-control-lg" id="password" name="password" 
                           placeholder="Enter password" required>
                </div>
                <button type="submit" class="btn btn-primary btn-lg w-100">
                    <i class="fas fa-sign-in-alt me-2"></i>Login
                </button>
            </form>

            <div class="mt-4 p-3 bg-light rounded">
                <small class="text-muted">
                    <strong>Demo Credentials:</strong><br>
                    <i class="fas fa-user-shield me-1"></i> Admin: <code>admin</code> / <code>password</code><br>
                    <i class="fas fa-user me-1"></i> Student: <code>john_student</code> / <code>password</code>
                </small>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
