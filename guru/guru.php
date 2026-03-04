<?php
session_start();
include '../conn.php';
$active_page = 'guru';
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Handle hapus guru
if(isset($_GET['hapus'])){
    $id = $_GET['hapus'];
    // Hapus entri di tabel guru terlebih dahulu (jika ada), lalu hapus user
    mysqli_query($conn, "DELETE FROM guru WHERE user_id='$id'");
    mysqli_query($conn, "DELETE FROM users WHERE id='$id' AND role='guru'");
    header("Location: guru.php");
}

// Handle edit (tampilkan form edit jika diminta)
if(isset($_GET['edit'])){
    $edit_id = intval($_GET['edit']);
    $res = mysqli_query($conn, "SELECT u.*, g.posisi, g.mapel FROM users u LEFT JOIN guru g ON g.user_id = u.id WHERE u.id='$edit_id' AND u.role='guru'");
    $edit_data = mysqli_fetch_assoc($res);
}

// Handle update guru
if(isset($_POST['update_guru'])){
    $id = intval($_POST['id']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $posisi = mysqli_real_escape_string($conn, $_POST['posisi'] ?? '');
    $mapel = mysqli_real_escape_string($conn, $_POST['mapel'] ?? '');

    // Jika password diisi, update juga
    if(!empty($_POST['password'])){
        $password = mysqli_real_escape_string($conn, $_POST['password']);
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $update = mysqli_query($conn, "UPDATE users SET nama='$nama', email='$email', password='$hashed' WHERE id='$id' AND role='guru'");
    } else {
        $update = mysqli_query($conn, "UPDATE users SET nama='$nama', email='$email' WHERE id='$id' AND role='guru'");
    }

    // Update tabel guru (posisi, mapel)
    $update_guru = mysqli_query($conn, "SELECT id FROM guru WHERE user_id='$id'");
    if(mysqli_num_rows($update_guru) > 0){
        mysqli_query($conn, "UPDATE guru SET posisi='$posisi', mapel='$mapel' WHERE user_id='$id'");
    } else {
        // Jika belum ada catatan di tabel guru, buat satu
        mysqli_query($conn, "INSERT INTO guru (user_id, nama, posisi, mapel) VALUES ('$id', '$nama', '$posisi', '$mapel')");
    }

    if($update){
        header("Location: guru.php");
        exit;
    } else {
        $error = "Gagal memperbarui data guru!";
    }
}

// Handle tambah guru
if(isset($_POST['tambah_guru'])){
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $posisi = mysqli_real_escape_string($conn, $_POST['posisi'] ?? '');
    $mapel = mysqli_real_escape_string($conn, $_POST['mapel'] ?? '');
    $role = 'guru';
    
    // Cek email sudah ada atau belum
    $check = mysqli_query($conn, "SELECT id FROM users WHERE email='$email'");
    if(mysqli_num_rows($check) > 0){
        $error = "Email sudah terdaftar!";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        // Masukkan ke users lalu ke tabel guru
        mysqli_begin_transaction($conn);
        $insert = mysqli_query($conn, "INSERT INTO users (nama, email, password, role) VALUES ('$nama', '$email', '$hashed_password', '$role')");
        if($insert){
            $new_user_id = mysqli_insert_id($conn);
            $insert_guru = mysqli_query($conn, "INSERT INTO guru (user_id, nama, posisi, mapel) VALUES ('$new_user_id', '$nama', '$posisi', '$mapel')");
            if($insert_guru){
                mysqli_commit($conn);
                $success = "Guru berhasil ditambahkan!";
                echo "<script>
                    setTimeout(function(){
                        document.getElementById('modalTambahGuru').style.display = 'none';
                        location.reload();
                    }, 1000);
                </script>";
            } else {
                mysqli_rollback($conn);
                $error = "Gagal menambahkan data guru (tabel guru).";
            }
        } else {
            mysqli_rollback($conn);
            $error = "Gagal menambahkan pengguna guru!";
        }
    }
}

include '../partials/header.php';
include '../partials/sidebar.php';
?>

<div class="app-content">
    <h2>👨‍🏫 Kelola Data Guru</h2>

    <?php if(isset($success)): ?>
        <div style="padding:15px; background:#d4edda; color:#155724; border-radius:5px; margin-bottom:20px;">
            ✅ <?= $success ?>
        </div>
    <?php endif; ?>

    <?php if(isset($error)): ?>
        <div style="padding:15px; background:#f8d7da; color:#721c24; border-radius:5px; margin-bottom:20px;">
            ❌ <?= $error ?>
        </div>
    <?php endif; ?>

    <button class="btn btn-primary" onclick="openModal()">➕ Tambah Guru</button>

    <table class="table">
        <tr>
            <th>Nama</th>
            <th>Email</th>
            <th>Posisi</th>
            <th>Mapel</th>
            <th>Aksi</th>
        </tr>

        <?php
        $q = mysqli_query($conn,"SELECT u.*, g.posisi, g.mapel FROM users u LEFT JOIN guru g ON g.user_id = u.id WHERE u.role='guru' ORDER BY u.nama ASC");
        if(mysqli_num_rows($q) > 0) {
            while($d=mysqli_fetch_assoc($q)){
        ?>
        <tr>
            <td><?= $d['nama'] ?></td>
            <td><?= $d['email'] ?></td>
            <td><?= htmlspecialchars($d['posisi'] ?? '') ?></td>
            <td><?= htmlspecialchars($d['mapel'] ?? '') ?></td>
            <td>
                     <a href="#" class="btn btn-secondary btn-edit"
                         data-id="<?= $d['id'] ?>"
                         data-nama="<?= htmlspecialchars($d['nama']) ?>"
                         data-email="<?= htmlspecialchars($d['email']) ?>"
                         data-posisi="<?= htmlspecialchars($d['posisi'] ?? '') ?>"
                         data-mapel="<?= htmlspecialchars($d['mapel'] ?? '') ?>"
                     >Edit</a>
                <a href="?hapus=<?= $d['id'] ?>" class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus guru ini?')">Hapus</a>
            </td>
        </tr>
        <?php 
            }
        } else {
            echo "<tr><td colspan='4' style='text-align:center; color:#999;'>Belum ada guru</td></tr>";
        }
        ?>
    </table>
</div>

<!-- MODAL EDIT GURU (selalu ada, diisi lewat JS) -->
<div class="modal" id="modalEditGuru">
    <div class="modal-box">
        <h3>✏️ Edit Guru</h3>
        <form method="POST" style="max-width:600px;">
            <input type="hidden" id="edit_id" name="id" value="">
            <div>
                <label>Nama Guru</label>
                <input type="text" id="edit_nama" name="nama" value="" required>
            </div>
            <div>
                <label>Email</label>
                <input type="email" id="edit_email" name="email" value="" required>
            </div>
            <div>
                <label>Posisi</label>
                <select id="edit_posisi" name="posisi" required>
                    <option value="">--Pilih Posisi--</option>
                    <option value="guru mapel">Guru Mapel</option>
                    <option value="guru BK">Guru BK</option>
                    <option value="wali kelas">Wali Kelas</option>
                </select>
            </div>
            <div>
                <label>Mapel</label>
                <input type="text" id="edit_mapel" name="mapel" value="" placeholder="Contoh: Matematika">
            </div>
            <div>
                <label>Password (kosongkan jika tidak ingin mengganti)</label>
                <input type="password" id="edit_password" name="password" placeholder="Biarkan kosong jika tidak ingin mengganti">
            </div>
            <div style="margin-top:12px; display:flex; gap:10px;">
                <button type="submit" name="update_guru" class="btn btn-success">💾 Simpan Perubahan</button>
                <button type="button" id="btnCancelEdit" class="btn btn-secondary">Batal</button>
            </div>
        </form>
    </div>
</div>
<script>
document.getElementById('btnCancelEdit').addEventListener('click', function(){
    const m = document.getElementById('modalEditGuru'); if(m) m.style.display='none';
});

// Buka modal dan isi data ketika tombol .btn-edit diklik
document.querySelectorAll('.btn-edit').forEach(btn => {
    btn.addEventListener('click', function(e){
        e.preventDefault();
        const id = btn.dataset.id || '';
        const nama = btn.dataset.nama || '';
        const email = btn.dataset.email || '';
        const posisi = btn.dataset.posisi || '';
        const mapel = btn.dataset.mapel || '';

        document.getElementById('edit_id').value = id;
        document.getElementById('edit_nama').value = nama;
        document.getElementById('edit_email').value = email;
        document.getElementById('edit_posisi').value = posisi;
        document.getElementById('edit_mapel').value = mapel;

        const modal = document.getElementById('modalEditGuru');
        if(modal) modal.style.display = 'flex';
    });
});
</script>

<!-- MODAL TAMBAH GURU -->
<div class="modal" id="modalTambahGuru">
    <div class="modal-box">
        <h3>➕ Tambah Guru Baru</h3>

        <form method="POST">
            <div>
                <label>Nama Guru</label>
                <input type="text" name="nama" placeholder="Masukkan nama guru" required>
            </div>

            <div>
                <label>Email</label>
                <input type="email" name="email" placeholder="Masukkan email guru" required>
            </div>

            <div>
                <label>Password</label>
                <input type="password" name="password" placeholder="Masukkan password" required>
            </div>
            <div>
                <label>Posisi</label>
                <select name="posisi" required>
                    <option value="">--Pilih Posisi--</option>
                    <option value="guru mapel">Guru Mapel</option>
                    <option value="guru BK">Guru BK</option>
                    <option value="wali kelas">Wali Kelas</option>
                </select>
            </div>

            <div>
                <label>Mapel (opsional)</label>
                <input type="text" name="mapel" placeholder="Masukkan mata pelajaran (opsional)">
            </div>

            <div style="margin-top:20px; display:flex; gap:10px;">
                <button type="submit" name="tambah_guru" class="btn btn-success">💾 Simpan</button>
                <button type="button" onclick="closeModal()" class="btn btn-secondary">Batal</button>
            </div>
        </form>
    </div>
</div>

<style>
/* Container Utama Modal */
.modal {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 1000;
    background: rgba(0, 0, 0, 0.4);
    backdrop-filter: blur(8px); /* Efek blur di belakang modal */
    padding: 20px;
}

.modal.active {
    display: flex;
    align-items: center;
    justify-content: center;
    animation: fadeIn 0.3s ease-out;
}

/* Konten Modal (Kotak Putih) */
.modal-content {
    background: #ffffff;
    padding: 2rem;
    border-radius: 16px;
    width: 100%;
    max-width: 500px;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    transform: scale(0.95);
    transition: transform 0.3s ease;
}

.modal.active .modal-content {
    transform: scale(1);
    animation: slideUp 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

/* Animasi */
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideUp {
    from { opacity: 0; transform: translateY(20px) scale(0.95); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}
label {
    display: block;
    margin-top: 20px;
    margin-bottom: 8px;
    font-size: 0.9rem;
    font-weight: 500;
    color: #64748b; /* Warna abu-abu kebiruan yang modern */
    transition: color 0.3s ease;
}

/* Bonus: Styling Input agar senada */
input, select, textarea {
    width: 100%;
    padding: 10px 14px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    outline: none;
    transition: all 0.3s ease;
}

input:focus {
    border-color: #6366f1; /* Warna indigo sesuai tren modern */
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}
.table-container {
    overflow-x: auto;
    border-radius: 12px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    background: #fff;
}

.table th {
    background: #f1f5f9;
    padding: 16px;
    text-align: left;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #475569;
    border-bottom: 1px solid #e2e8f0;
}

.table td {
    padding: 16px;
    color: #1e293b;
    border-bottom: 1px solid #f1f5f9;
    transition: all 0.2s ease;
}

.table tr:last-child td {
    border-bottom: none;
}

.table tr:hover td {
    background: #f8fafc;
    color: #6366f1; /* Warna teks berubah saat hover */
    transform: scale(1.002);
}
</style>

<script>
function openModal(){
    document.getElementById('modalTambahGuru').style.display = 'flex';
}

function closeModal(){
    document.getElementById('modalTambahGuru').style.display = 'none';
}

// Tutup modal jika klik di luar
window.onclick = function(event) {
    const modalAdd = document.getElementById('modalTambahGuru');
    const modalEdit = document.getElementById('modalEditGuru');
    if (event.target == modalAdd) {
        modalAdd.style.display = 'none';
    }
    if (modalEdit && event.target == modalEdit) {
        modalEdit.style.display = 'none';
        if(window.history && window.history.replaceState){
            window.history.replaceState({}, document.title, 'guru.php');
        }
    }
}
</script>
