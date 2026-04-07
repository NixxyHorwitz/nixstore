<?php
session_start();
require '../config/database.php';

if (isset($_SESSION['admin_logged_in'])) {
    header("Location: index.php");
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_id'] = $user['id'];
        header("Location: index.php");
        exit;
    } else {
        $error = 'Invalid username or password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login - Web Sales</title>
    <style>
        body { margin:0; padding:0; font-family:'Inter', sans-serif; background:#f4f4f9; display:flex; align-items:center; justify-content:center; height:100vh; }
        .login-box { background:#fff; padding:40px; border-radius:12px; box-shadow:0 4px 20px rgba(0,0,0,0.05); width:100%; max-width:350px; }
        .login-box h2 { margin:0 0 20px; font-weight:600; text-align:center; color:#333; }
        .form-group { margin-bottom:15px; }
        .form-group label { display:block; margin-bottom:5px; color:#555; font-size:14px; }
        .form-group input { width:100%; padding:10px; border:1px solid #ddd; border-radius:6px; font-size:14px; box-sizing:border-box; }
        .btn { width:100%; padding:10px; border:none; background:#000; color:#fff; border-radius:6px; font-size:16px; cursor:pointer; font-weight:500; }
        .btn:hover { background:#333; }
        .error { color:red; font-size:14px; margin-bottom:15px; text-align:center; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Admin Portal</h2>
        <?php if($error): ?><div class="error"><?= $error ?></div><?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required autofocus>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn">Login</button>
        </form>
    </div>
</body>
</html>
