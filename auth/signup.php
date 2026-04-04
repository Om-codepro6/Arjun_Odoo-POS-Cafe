<?php
include 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Check if passwords match
    if ($password !== $confirm_password) {
        $error = 'password_mismatch';
    } else {
        // Check if username exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $error = 'username_exists';
        } else {
            // Check if email exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $error = 'email_exists';
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Insert user
                $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $username, $email, $hashed_password);
                if ($stmt->execute()) {
                    $success = 'Account created successfully! Please login.';
                } else {
                    $error = 'general';
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cafe POS - Sign Up</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="auth-shell">
        <div class="auth-card">
            <div class="auth-side">
                <div>
                    <div class="brand-badge">odoo Cafe </div>
                    <h1>Create your cafe account</h1>
                    <p></p>
                </div>
                <div class="auth-side-footer">Join the circle. Stay for the beans</div>
            </div>
            <div class="auth-form">
                <div class="auth-header">
                    <h2>Create account</h2>
                    <p>Fill in your details to register.</p>
                </div>

                <?php
                if ($error) {
                    if ($error == 'username_exists') {
                        echo '<div class="alert alert-danger">Username already exists.</div>';
                    } elseif ($error == 'email_exists') {
                        echo '<div class="alert alert-danger">Email already exists.</div>';
                    } elseif ($error == 'password_mismatch') {
                        echo '<div class="alert alert-danger">Passwords do not match.</div>';
                    } else {
                        echo '<div class="alert alert-danger">An error occurred. Please try again.</div>';
                    }
                }
                if ($success) {
                    echo '<div class="alert alert-success">' . $success . '</div>';
                }
                ?>

                <form action="signup.php" method="POST">
                    <div class="form-group">
                        <input type="text" class="form-control" name="username" placeholder="Username" required>
                    </div>
                    <div class="form-group">
                        <input type="email" class="form-control" name="email" placeholder="Email" required>
                    </div>
                    <div class="form-group">
                        <input type="password" class="form-control" name="password" placeholder="Password" required>
                    </div>
                    <div class="form-group">
                        <input type="password" class="form-control" name="confirm_password" placeholder="Confirm Password" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Sign Up</button>
                </form>

                <div style="margin: 22px 0; text-align: center; color: var(--muted);">Already have an account?</div>
                <a href="login.php" class="btn btn-secondary">Login to your account</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>