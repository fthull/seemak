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
    $res = mysqli_query($conn, "SELECT u.id, u.nama, u.email FROM users u WHERE u.id='$edit_id' AND u.role='guru'");
    $edit_data = mysqli_fetch_assoc($res);
    
    // Ambil semua posisi untuk guru ini
    if($edit_data) {
        $pos_res = mysqli_query($conn, "SELECT id, posisi, mapel FROM guru_posisi WHERE user_id='$edit_id' ORDER BY id ASC");
        $edit_data['posisi_list'] = [];
        while($p = mysqli_fetch_assoc($pos_res)) {
            $edit_data['posisi_list'][] = $p;
        }
    }
}

// Handle update guru
if(isset($_POST['update_guru'])){
    $id = intval($_POST['id']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $posisi_arr = $_POST['posisi'] ?? [];
    $mapel_arr = $_POST['mapel'] ?? [];

    // Jika password diisi, update juga
    if(!empty($_POST['password'])){
        $password = mysqli_real_escape_string($conn, $_POST['password']);
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $update = mysqli_query($conn, "UPDATE users SET nama='$nama', email='$email', password='$hashed' WHERE id='$id' AND role='guru'");
    } else {
        $update = mysqli_query($conn, "UPDATE users SET nama='$nama', email='$email' WHERE id='$id' AND role='guru'");
    }

    // Hapus posisi lama, masukkan yang baru
    if($update) {
        mysqli_query($conn, "DELETE FROM guru_posisi WHERE user_id='$id'");
        
        foreach($posisi_arr as $idx => $posisi) {
            $posisi = mysqli_real_escape_string($conn, $posisi);
            $mapel = isset($mapel_arr[$idx]) ? mysqli_real_escape_string($conn, $mapel_arr[$idx]) : '';
            if(!empty($posisi)) {
                mysqli_query($conn, "INSERT INTO guru_posisi (user_id, posisi, mapel) VALUES ('$id', '$posisi', '$mapel')");
            }
        }
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
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h2 style="font-weight: 800; color: #1e293b; margin: 0;">👨‍🏫 Kelola Data Guru</h2>
        <button class="btn btn-primary" onclick="openModal()" style="border-radius: 12px; padding: 12px 24px; font-weight: 600; box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.3);">
            <i class="fas fa-plus-circle mr-2"></i> Tambah Guru Baru
        </button>
    </div>

    <?php if(isset($success)): ?>
        <div style="padding:15px 20px; background:#ecfdf5; color:#059669; border-radius:12px; margin-bottom:25px; border-left: 5px solid #10b981; display:flex; align-items:center; gap:10px;">
            <i class="fas fa-check-circle"></i> <?= $success ?>
        </div>
    <?php endif; ?>

    <div class="table-container">
        <table class="table-guru">
            <thead>
                <tr>
                    <th>Profil Guru</th>
                    <th>Kontak</th>
                    <th>Posisi & Tugas</th>
                    <th style="text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $q = mysqli_query($conn,"SELECT u.*, g.posisi, g.mapel FROM users u LEFT JOIN guru g ON g.user_id = u.id WHERE u.role='guru' ORDER BY u.nama ASC");
                if(mysqli_num_rows($q) > 0) {
                    while($d=mysqli_fetch_assoc($q)){
                ?>
                <tr>
                    <td>
                        <div style="font-weight: 700; color: #1e293b;"><?= $d['nama'] ?></div>
                        <small style="color: #64748b;">ID Guru: #<?= $d['id'] ?></small>
                    </td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 8px; color: #475569;">
                            <i class="far fa-envelope" style="font-size: 0.8rem; color: #94a3b8;"></i>
                            <?= $d['email'] ?>
                        </div>
                    </td>
                    <td>
                        <span class="badge-posisi"><?= strtoupper($d['posisi'] ?? 'Belum Diatur') ?></span>
                        <div style="margin-top: 5px; font-size: 0.85rem; color: #64748b;">
                            <i class="fas fa-book-reader mr-1"></i> <?= $d['mapel'] ?: '-' ?>
                        </div>
                    </td>
                    <td style="text-align: center;">
                        <button class="btn-action btn-edit-guru btn-edit" 
                            data-id="<?= $d['id'] ?>"
                            data-nama="<?= htmlspecialchars($d['nama']) ?>"
                            data-email="<?= htmlspecialchars($d['email']) ?>"
                            data-posisi="<?= htmlspecialchars($d['posisi'] ?? '') ?>"
                            data-mapel="<?= htmlspecialchars($d['mapel'] ?? '') ?>">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <a href="?hapus=<?= $d['id'] ?>" class="btn-action btn-delete-guru" onclick="return confirm('Yakin ingin menghapus guru ini?')">
                            <i class="fas fa-trash-alt"></i> Hapus
                        </a>
                    </td>
                </tr>
                <?php 
                    }
                } else {
                    echo "<tr><td colspan='4' style='text-align:center; padding:50px; color:#94a3b8;'>
                            <i class='fas fa-user-slash' style='display:block; font-size:2rem; margin-bottom:10px;'></i>
                            Belum ada data guru terdaftar
                          </td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
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
/* Card Container untuk Tabel */
.app-content {
    background: #f8fafc;
    padding: 30px;
    border-radius: 20px;
}

.table-container {
    background: white;
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    padding: 20px;
    margin-top: 20px;
    overflow-x: auto;
}

/* Tabel Modern */
.table-guru {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0 8px;
}

.table-guru th {
    padding: 15px;
    color: #64748b;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    border: none;
    text-align: left;
}

.table-guru tr {
    transition: transform 0.2s ease;
}

.table-guru td {
    padding: 15px;
    background: #fff;
    border-top: 1px solid #f1f5f9;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
}

.table-guru td:first-child {
    border-left: 1px solid #f1f5f9;
    border-top-left-radius: 12px;
    border-bottom-left-radius: 12px;
}

.table-guru td:last-child {
    border-right: 1px solid #f1f5f9;
    border-top-right-radius: 12px;
    border-bottom-right-radius: 12px;
}

.table-guru tbody tr:hover td {
    background: #f8faff;
    border-color: #e2e8f0;
}

/* Modal & Form Styling */
.modal-box {
    border-radius: 20px;
    padding: 30px;
    border: none;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}

.modal-box h3 {
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 25px;
}

form label {
    display: block;
    font-weight: 600;
    color: #475569;
    margin-bottom: 8px;
    font-size: 0.9rem;
}

form input, form select {
    width: 100%;
    padding: 12px 15px;
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    margin-bottom: 20px;
    transition: all 0.3s ease;
}

form input:focus, form select:focus {
    border-color: #3b82f6;
    outline: none;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
}

/* Badge untuk Posisi */
.badge-posisi {
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    background: #eff6ff;
    color: #3b82f6;
}

/* Action Buttons */
.btn-action {
    padding: 8px 12px;
    border-radius: 8px;
    font-size: 0.85rem;
    margin-right: 5px;
    border: none;
    transition: 0.2s;
}

.btn-edit-guru { background: #f0fdf4; color: #16a34a; }
.btn-delete-guru { background: #fef2f2; color: #dc2626; }

.btn-action:hover { transform: scale(1.05); filter: brightness(0.95); }
</style>

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
