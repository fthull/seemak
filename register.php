<?php
session_start();
include 'conn.php';

if (isset($_POST['daftar'])) {

    $nama_ortu = mysqli_real_escape_string($conn, $_POST['nama_ortu']);
    $nama_anak = mysqli_real_escape_string($conn, $_POST['nama_anak']);
    $email      = mysqli_real_escape_string($conn, $_POST['email']);
    $password   = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // cek email sudah terdaftar
    $cek_email = mysqli_query($conn, "SELECT id FROM users WHERE email='$email'");
    if (mysqli_num_rows($cek_email) > 0) {
        echo "<script>alert('Email sudah terdaftar');</script>";
    } else {
        // cek nama anak di table siswa
        $cek_anak = mysqli_query($conn, "SELECT id FROM siswa WHERE nama='$nama_anak'");
        if (mysqli_num_rows($cek_anak) == 0) {
            echo "<script>alert('Nama anak tidak ditemukan di database. Silakan hubungi sekolah untuk mendaftarkan anak terlebih dahulu.');</script>";
        } else {
            $data_anak = mysqli_fetch_assoc($cek_anak);
            $siswa_id = $data_anak['id'];

            // insert ke table users
            $insert_user = mysqli_query($conn, "
                INSERT INTO users 
                (nama, email, password, role)
                VALUES
                ('$nama_ortu','$email','$password','orang tua/wali')
            ");

            if($insert_user) {
                $user_id = mysqli_insert_id($conn);

                // insert ke table orang_tua
                mysqli_query($conn, "
                    INSERT INTO orang_tua 
                    (user_id, siswa_id, nama, email)
                    VALUES
                    ('$user_id','$siswa_id','$nama_ortu','$email')
                ");

                echo "<script>
                    alert('Registrasi berhasil, silakan login');
                    location='login.php';
                </script>";
            } else {
                echo "<script>alert('Terjadi kesalahan saat registrasi. Silakan coba lagi.');</script>";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Registrasi Orang Tua | MAKN</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        *{box-sizing:border-box;font-family:'Poppins',sans-serif}
        body{
            min-height:100vh;
            background:linear-gradient(135deg,#1e7f5c,#2fbf71);
            display:flex;
            align-items:center;
            justify-content:center;
        }
        .box{
            background:#fff;
            width:100%;
            max-width:400px;
            padding:30px;
            border-radius:12px;
            box-shadow:0 15px 30px rgba(0,0,0,.15);
        }
        .box h2{
            text-align:center;
            color:#1e7f5c;
            margin-bottom:5px;
        }
        .box p{
            text-align:center;
            font-size:13px;
            color:#777;
            margin-bottom:20px;
        }
        .form-group{
            margin-bottom:15px;
        }
        .form-group label{
            font-size:13px;
            color:#444;
        }
        .form-group input{
            width:100%;
            padding:10px;
            margin-top:5px;
            border-radius:8px;
            border:1px solid #ccc;
        }
        .form-group input:focus{
            border-color:#1e7f5c;
            outline:none;
        }
        .btn{
            width:100%;
            padding:10px;
            border:none;
            border-radius:8px;
            cursor:pointer;
        }
        .btn-primary{
            background:#1e7f5c;
            color:#fff;
        }
        .btn-primary:hover{
            background:#16694c;
        }
        .btn-link{
            margin-top:12px;
            display:block;
            text-align:center;
            font-size:13px;
            color:#1e7f5c;
            text-decoration:none;
        }
    </style>
</head>

<body>

<div class="box">
    <h2><i class="fas fa-user-plus"></i> Registrasi</h2>
    <p>Orang Tua / Wali Siswa MAKN Ende</p>

    <form method="POST">
        <div class="form-group">
            <label>Nama Orang Tua / Wali</label>
            <input type="text" name="nama_ortu" placeholder="Nama lengkap orang tua" required>
        </div>

        <div class="form-group">
            <label>Nama Anak (Siswa)</label>
            <input type="text" name="nama_anak" placeholder="Nama lengkap anak" required>
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" placeholder="email@email.com" required>
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" placeholder="••••••••" required>
        </div>

        <button name="daftar" class="btn btn-primary">
            <i class="fas fa-paper-plane"></i> Daftar
        </button>
    </form>

    <a href="login.php" class="btn-link">
        Sudah punya akun? Login
    </a>
</div>

</body>
</html>
