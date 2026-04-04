<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cafe POS - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="auth-shell">
        <div class="auth-card">
            <div class="auth-side">
                <div>
                    <div class="brand-badge">Cafe POS</div>
                    <h1>Welcome Back</h1>
                    <p>Access your cafe management dashboard quickly and securely. Start orders, track POS sessions, and configure settings in one place.</p>
                </div>
                <div class="auth-side-footer">Trusted by cafe owners for a smooth, modern point-of-sale experience.</div>
            </div>
            <div class="auth-form">
                <div class="auth-header">
                    <h2>Login to Cafe POS</h2>
                    <p>Enter your username and password to continue.</p>
                </div>

                <?php
                if (isset($_GET['error'])) {
                    echo '<div class="alert alert-danger">Invalid username or password.</div>';
                }
                ?>

                <form action="login.php" method="POST">
                    <div class="form-group">
                        <input type="text" class="form-control" name="username" placeholder="Username" required>
                    </div>
                    <div class="form-group">
                        <input type="password" class="form-control" name="password" placeholder="Password" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Login</button>
                </form>

                <div style="margin: 22px 0; text-align: center; color: var(--muted);">Don’t have an account yet?</div>
                <a href="signup.php" class="btn btn-secondary">Create a new account</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>