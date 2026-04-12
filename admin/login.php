<?php
session_start();
require '../config/database.php';

if (isset($_SESSION['admin_logged_in'])) {
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        echo json_encode(['status' => 'success']); exit;
    }
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_id'] = $user['id'];
        
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            echo json_encode(['status' => 'success']);
            exit;
        }
        header("Location: index.php");
        exit;
    } else {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            echo json_encode(['status' => 'error', 'message' => 'Invalid username or password']);
            exit;
        }
        $error = 'Invalid username or password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Web Sales</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Base CSS -->
    <link rel="stylesheet" href="assets/base.css?v=<?= time() ?>">
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background: var(--bg); 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            height: 100vh; 
            color: var(--text);
        }
        .login-box { 
            background: var(--surface); 
            padding: 40px; 
            border-radius: var(--r); 
            box-shadow: 0 10px 30px rgba(0,0,0,0.5); 
            width: 100%; 
            max-width: 400px; 
            border: 1px solid var(--border);
        }
        .login-box h2 { 
            margin: 0 0 24px; 
            font-weight: 700; 
            text-align: center; 
            color: var(--text); 
        }
        .form-control {
            background-color: var(--hover);
            border: 1px solid var(--border);
            color: var(--text);
            padding: 12px 16px;
        }
        .form-control:focus {
            background-color: var(--surface);
            color: var(--text);
            border-color: var(--accent);
            box-shadow: 0 0 0 0.2rem var(--ag);
        }
        .btn-login {
            background: var(--accent);
            color: #fff;
            padding: 12px;
            font-weight: 600;
            border: none;
            width: 100%;
            border-radius: var(--rs);
            margin-top: 10px;
            transition: all 0.2s;
        }
        .btn-login:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Admin Portal</h2>
        <?php if(isset($error)): ?>
        <div class="alert alert-danger" style="background: var(--es); color: var(--err); border: none; font-size: 13.5px;">
            <?= $error ?>
        </div>
        <?php endif; ?>
        <form id="loginForm" method="POST">
            <div class="mb-3">
                <label class="form-label text-muted" style="font-size: 13px;">Username</label>
                <input type="text" name="username" class="form-control" required autofocus placeholder="Enter your username">
            </div>
            <div class="mb-4">
                <label class="form-label text-muted" style="font-size: 13px;">Password</label>
                <input type="password" name="password" class="form-control" required placeholder="Enter your password">
            </div>
            <button type="submit" class="btn-login" id="btnLogin">Login to Dashboard</button>
        </form>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.all.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#loginForm').submit(function(e) {
                e.preventDefault();
                let $btn = $('#btnLogin');
                $btn.prop('disabled', true).html('Process...');
                
                $.ajax({
                    url: 'login.php',
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(res) {
                        if(res.status === 'success') {
                            window.location.href = 'index.php';
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Login Failed',
                                text: res.message,
                                background: 'var(--surface)',
                                color: 'var(--text)'
                            });
                            $btn.prop('disabled', false).html('Login to Dashboard');
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An unexpected error occurred.',
                            background: 'var(--surface)',
                            color: 'var(--text)'
                        });
                        $btn.prop('disabled', false).html('Login to Dashboard');
                    }
                });
            });
        });
    </script>
</body>
</html>
