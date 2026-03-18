<?php
session_start();
include 'conn.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $query = mysqli_query($conn, "
        SELECT * FROM users 
        WHERE email = '$email'
        LIMIT 1
    ");

    if (mysqli_num_rows($query) === 1) {
        $user = mysqli_fetch_assoc($query);

        if (password_verify($password, $user['password'])) {

            $_SESSION['id']    = $user['id'];
            $_SESSION['nama']  = $user['nama'];
            $_SESSION['email'] = $user['email'];

            // Normalisasi role: beberapa data menggunakan teks 'orang tua/wali' atau 'wali kelas'
            $rawRole = $user['role'];
            if (strpos($rawRole, 'wali') !== false) {
                $role = 'wali';
            } else {
                $role = $rawRole;
            }

            $_SESSION['role']  = $role;

            if ($role === 'admin') {
                header("Location: guru/dashboard.php");
            } else if ($role === 'guru') {
                header("Location: guru/dashboard.php");
            } else {
                header("Location: orangtua/dashboard.php");
            }
            exit;

        } else {
            $error = 'Password salah';
        }
    } else {
        $error = 'Email tidak terdaftar';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login | Sistem Madrasah</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        *{box-sizing:border-box;font-family:'Poppins',sans-serif}
        body{
            min-height:100vh;
            display:flex;
            justify-content:center;
            align-items:center;
            background:linear-gradient(135deg,#1e7f5c,#2fbf71);
        }
        .login-box{
            background:#fff;
            width:100%;
            max-width:380px;
            padding:30px;
            border-radius:12px;
            box-shadow:0 15px 30px rgba(0,0,0,.15)
        }
        .login-header{text-align:center;margin-bottom:20px}
        .login-header i{font-size:40px;color:#1e7f5c}
        .login-header h2{font-size:20px}
        .login-header p{font-size:13px;color:#777}
        .form-group{margin-bottom:15px}
        label{font-size:13px}
        input{
            width:100%;
            padding:10px;
            border-radius:8px;
            border:1px solid #ccc
        }
        .btn-login{
            width:100%;
            padding:10px;
            background:#1e7f5c;
            color:#fff;
            border:none;
            border-radius:8px;
            cursor:pointer
        }
        .error{
            background:#ffe5e5;
            color:#c0392b;
            padding:8px;
            border-radius:6px;
            margin-bottom:10px;
            font-size:13px;
            text-align:center
        }

        .divider{
    text-align:center;
    margin:12px 0;
    font-size:12px;
    color:#999;
}

.btn-register{
    display:block;
    text-align:center;
    padding:10px;
    border-radius:8px;
    background:#fff;
    color:#1e7f5c;
    border:1px solid #1e7f5c;
    text-decoration:none;
    font-size:14px;
    transition:.2s;
}

.btn-register:hover{
    background:#1e7f5c;
    color:#fff;
}

    </style>
</head>

<body>

<div class="login-box">
    <div class="login-header">
        <i class="fas fa-mosque"></i>
        <h2>Sistem Layanan Madrasah</h2>
        <p>MAKN • Aman • Terintegrasi</p>
    </div>

    <?php if ($error): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" required>
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>

        <button class="btn-login">
    <i class="fas fa-sign-in-alt"></i> Masuk
</button>

<div class="divider">atau</div>

<a href="register.php" class="btn-register">
    <i class="fas fa-user-plus"></i> Registrasi
</a>

    </form>
</div>

</body>
</html>
