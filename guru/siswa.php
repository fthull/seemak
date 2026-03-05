<?php
session_start();
include '../conn.php';
$active_page = 'siswa';
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Handle hapus guru
if(isset($_GET['hapus'])){
    $id = $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM siswa WHERE id='$id'");
    header("Location: siswa.php");
}

// Handle edit (tampilkan form edit jika diminta)
if(isset($_GET['edit'])){
    // deprecated: client-side modal will handle edit; keep for backward compatibility
    $edit_id = intval($_GET['edit']);
    $res = mysqli_query($conn, "SELECT * FROM siswa WHERE id='$edit_id'");
    $edit_data = mysqli_fetch_assoc($res);
}

// Handle update siswa
if(isset($_POST['update_siswa'])){
    $id = intval($_POST['id']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $kelas = mysqli_real_escape_string($conn, $_POST['kelas']);

    $update = mysqli_query($conn, "UPDATE siswa SET nama='$nama', kelas='$kelas' WHERE id='$id'");
    if($update){
        header("Location: siswa.php");
        exit;
    } else {
        $error = "Gagal memperbarui siswa!";
    }
}

// Handle tambah guru
if(isset($_POST['tambah_siswa'])){
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $kelas = mysqli_real_escape_string($conn, $_POST['kelas']);
    
    // Cek email sudah ada atau belum
    $check = mysqli_query($conn, "SELECT id FROM siswa WHERE nama='$nama' AND kelas='$kelas'");
    if(mysqli_num_rows($check) > 0){
        $error = "Siswa sudah terdaftar!";
    } else {
        $insert = mysqli_query($conn, "
            INSERT INTO siswa (nama, kelas)
            VALUES ('$nama', '$kelas')
        ");
        
        if($insert){
            $success = "Siswa berhasil ditambahkan!";
            echo "<script>
                setTimeout(function(){
                    document.getElementById('modalTambahSiswa').style.display = 'none';
                    location.reload();
                }, 1000);
            </script>";
        } else {
            $error = "Gagal menambahkan siswa!";
        }
    }
}

include '../partials/header.php';
include '../partials/sidebar.php';
?>

<div class="app-content">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h2 style="font-weight: 800; color: #1e293b; margin: 0;">👨‍🎓 Kelola Data Siswa</h2>
        <button class="btn btn-primary" onclick="openModal()" style="border-radius: 12px; padding: 12px 24px; font-weight: 600; box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.3);">
            <i class="fas fa-plus-circle mr-2"></i> Tambah Siswa Baru
        </button>
    </div>

    <?php if(isset($success)): ?>
        <div style="padding:15px 20px; background:#ecfdf5; color:#059669; border-radius:12px; margin-bottom:25px; border-left: 5px solid #10b981; display:flex; align-items:center; gap:10px;">
            <i class="fas fa-check-circle"></i> <?= $success ?>
        </div>
    <?php endif; ?>

    <div class="table-container">
        <table class="table-siswa">
            <thead>
                <tr>
                    <th>Nama Lengkap</th>
                    <th>Tingkat / Kelas</th>
                    <th style="text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $q = mysqli_query($conn,"SELECT * FROM siswa ORDER BY nama ASC");
                if(mysqli_num_rows($q) > 0) {
                    while($d=mysqli_fetch_assoc($q)){
                ?>
                <tr>
                    <td>
                        <div style="font-weight: 700; color: #1e293b;"><?= $d['nama'] ?></div>
                        <small style="color: #94a3b8;">NIS: #<?= $d['id'] + 1000 ?></small>
                    </td>
                    <td>
                        <span class="badge-kelas">
                            <i class="fas fa-graduation-cap mr-1"></i> Kelas <?= $d['kelas'] ?>
                        </span>
                    </td>
                    <td style="text-align: center;">
                        <button class="btn-action btn-edit-siswa-alt btn-edit-siswa"
                            data-id="<?= $d['id'] ?>"
                            data-nama="<?= htmlspecialchars($d['nama']) ?>"
                            data-kelas="<?= htmlspecialchars($d['kelas']) ?>">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <a href="?hapus=<?= $d['id'] ?>" class="btn-action btn-delete-siswa-alt" onclick="return confirm('Yakin ingin menghapus siswa ini?')">
                            <i class="fas fa-trash-alt"></i> Hapus
                        </a>
                    </td>
                </tr>
                <?php 
                    }
                } else {
                    echo "<tr><td colspan='3' style='text-align:center; padding:50px; color:#94a3b8;'>
                            <i class='fas fa-user-slash' style='display:block; font-size:2rem; margin-bottom:10px;'></i>
                            Belum ada data siswa
                          </td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>
<div class="modal" id="modalTambahSiswa">
    <div class="modal-box" style="border-radius: 20px; padding: 30px;">
        <h3 style="font-weight: 700; color: #1e293b; margin-bottom: 25px;">➕ Tambah Siswa Baru</h3>
        <form method="POST">
            <div style="margin-bottom: 15px;">
                <label style="display:block; font-weight:600; color:#475569; margin-bottom:8px;">Nama Siswa</label>
                <input type="text" name="nama" class="form-control" style="width:100%; padding:12px; border-radius:10px; border:1.5px solid #e2e8f0;" placeholder="Masukkan nama lengkap" required>
            </div>
            <div style="margin-bottom: 15px;">
                <label style="display:block; font-weight:600; color:#475569; margin-bottom:8px;">Kelas</label>
                <input type="text" name="kelas" class="form-control" style="width:100%; padding:12px; border-radius:10px; border:1.5px solid #e2e8f0;" placeholder="Contoh: XII TKJ 1" required>
            </div>
            <div style="margin-top:25px; display:flex; gap:10px;">
                <button type="submit" name="tambah_siswa" class="btn btn-success" style="border-radius:10px; padding:10px 20px;">💾 Simpan Siswa</button>
                <button type="button" onclick="closeModal()" class="btn btn-secondary" style="border-radius:10px; padding:10px 20px;">Batal</button>
            </div>
        </form>
    </div>
</div>
<div class="modal" id="modalEditSiswa">
    <div class="modal-box" style="border-radius: 20px; padding: 30px; max-width:800px;">
        <h3 style="font-weight: 700; color: #1e293b; margin-bottom: 20px;">✏️ Edit Siswa</h3>
        <form method="POST" style="max-width:700px; width:100%;">
            <input type="hidden" id="edit_siswa_id" name="id" value="">
            <div style="margin-bottom:15px;">
                <label style="display:block; font-weight:600; color:#475569; margin-bottom:8px;">Nama Siswa</label>
                <input type="text" id="edit_siswa_nama" name="nama" value="" required class="form-control" style="width:100%; padding:12px; border-radius:10px; border:1.5px solid #e2e8f0;">
            </div>
            <div style="margin-bottom:15px;">
                <label style="display:block; font-weight:600; color:#475569; margin-bottom:8px;">Kelas</label>
                <input type="text" id="edit_siswa_kelas" name="kelas" value="" required class="form-control" style="width:100%; padding:12px; border-radius:10px; border:1.5px solid #e2e8f0;">
            </div>
            <div style="margin-top:18px; display:flex; gap:10px;">
                <button type="submit" name="update_siswa" class="btn btn-success" style="border-radius:10px; padding:10px 20px;">💾 Simpan Perubahan</button>
                <button type="button" id="btnCancelEditSiswa" class="btn btn-secondary" style="border-radius:10px; padding:10px 20px;">Batal</button>
            </div>
        </form>
    </div>
</div>

<style>
/* Container Utama */
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
}

/* Tabel Siswa Modern */
.table-siswa {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0 8px;
}

.table-siswa th {
    padding: 15px;
    color: #64748b;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    text-align: left;
}

.table-siswa td {
    padding: 15px;
    background: #fff;
    border-top: 1px solid #f1f5f9;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
}

.table-siswa td:first-child {
    border-left: 1px solid #f1f5f9;
    border-top-left-radius: 12px;
    border-bottom-left-radius: 12px;
}

.table-siswa td:last-child {
    border-right: 1px solid #f1f5f9;
    border-top-right-radius: 12px;
    border-bottom-right-radius: 12px;
}

.table-siswa tbody tr:hover td {
    background: #f8faff;
    border-color: #e2e8f0;
}

/* Badge Kelas */
.badge-kelas {
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    background: #f0fdf4;
    color: #16a34a; /* Hijau untuk membedakan dengan Guru (Biru) */
}

/* Tombol Aksi */
.btn-action {
    padding: 8px 12px;
    border-radius: 8px;
    font-size: 0.85rem;
    border: none;
    transition: 0.2s;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.btn-edit-siswa-alt { background: #eff6ff; color: #3b82f6; }
.btn-delete-siswa-alt { background: #fef2f2; color: #dc2626; }

.btn-action:hover { transform: scale(1.05); filter: brightness(0.95); }
</style>

<script>
function openModal(){
    document.getElementById('modalTambahSiswa').style.display = 'flex';
}

function closeModal(){
    document.getElementById('modalTambahSiswa').style.display = 'none';
}

// Tutup modal jika klik di luar
window.onclick = function(event) {
    const modalTambah = document.getElementById('modalTambahSiswa');
    const modalEdit = document.getElementById('modalEditSiswa');
    if (event.target == modalTambah) { modalTambah.style.display = 'none'; }
    if (event.target == modalEdit) { modalEdit.style.display = 'none'; }
}

// Jika sedang mengedit, scroll ke atas agar form edit terlihat
<?php // client-side modal handles edit display ?>
</script>


<script>
document.getElementById('btnCancelEditSiswa').addEventListener('click', function(){
    const m = document.getElementById('modalEditSiswa'); if(m) m.style.display='none';
});
document.querySelectorAll('.btn-edit-siswa').forEach(btn=>{
    btn.addEventListener('click', function(e){
        e.preventDefault();
        const id = btn.dataset.id || '';
        const nama = btn.dataset.nama || '';
        const kelas = btn.dataset.kelas || '';
        document.getElementById('edit_siswa_id').value = id;
        document.getElementById('edit_siswa_nama').value = nama;
        document.getElementById('edit_siswa_kelas').value = kelas;
        const modal = document.getElementById('modalEditSiswa'); if(modal) modal.style.display='flex';
    });
});
</script>
