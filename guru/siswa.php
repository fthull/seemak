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
    <h2>👨‍🏫 Kelola Data Siswa</h2>

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

    <button class="btn btn-primary" onclick="openModal()">➕ Tambah Siswa</button>

    <table class="table">
        <tr>
            <th>Nama</th>
            <th>Kelas</th>
            <th>Aksi</th>
        </tr>

        <?php
        $q = mysqli_query($conn,"SELECT * FROM siswa ORDER BY nama ASC");
        if(mysqli_num_rows($q) > 0) {
            while($d=mysqli_fetch_assoc($q)){
        ?>
        <tr>
            <td><?= $d['nama'] ?></td>
            <td><?= $d['kelas'] ?></td>
                <td>
                     <a href="#" class="btn btn-secondary btn-edit-siswa"
                         data-id="<?= $d['id'] ?>"
                         data-nama="<?= htmlspecialchars($d['nama']) ?>"
                         data-kelas="<?= htmlspecialchars($d['kelas']) ?>"
                     >Edit</a>
                <a href="?hapus=<?= $d['id'] ?>" class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus siswa ini?')">Hapus</a>
            </td>
        </tr>
        <?php 
            }
        } else {
            echo "<tr><td colspan='3' style='text-align:center; color:#999;'>Belum ada siswa</td></tr>";
        }
        ?>
    </table>
</div>

<!-- MODAL TAMBAH GURU -->
<div class="modal" id="modalTambahSiswa">
    <div class="modal-box">
        <h3>➕ Tambah Siswa</h3>

        <form method="POST">
            <div>
                <label>Nama Siswa</label>
                <input type="text" name="nama" placeholder="Masukkan nama siswa" required>
            </div>

            <div>
                <label>Kelas</label>
                <input type="text" name="kelas" placeholder="Masukkan kelas siswa" required>
            </div>

            

            <div style="margin-top:20px; display:flex; gap:10px;">
                <button type="submit" name="tambah_siswa" class="btn btn-success">💾 Simpan</button>
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
}</style>

<script>
function openModal(){
    document.getElementById('modalTambahSiswa').style.display = 'flex';
}

function closeModal(){
    document.getElementById('modalTambahSiswa').style.display = 'none';
}

// Tutup modal jika klik di luar
window.onclick = function(event) {
    const modal = document.getElementById('modalTambahSiswa');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}

// Jika sedang mengedit, scroll ke atas agar form edit terlihat
<?php // client-side modal handles edit display ?>
</script>

<!-- MODAL EDIT SISWA -->
<div class="modal" id="modalEditSiswa">
    <div class="modal-box">
        <h3>✏️ Edit Siswa</h3>
        <form method="POST" style="max-width:600px;">
            <input type="hidden" id="edit_siswa_id" name="id" value="">
            <div>
                <label>Nama Siswa</label>
                <input type="text" id="edit_siswa_nama" name="nama" value="" required>
            </div>
            <div>
                <label>Kelas</label>
                <input type="text" id="edit_siswa_kelas" name="kelas" value="" required>
            </div>
            <div style="margin-top:12px; display:flex; gap:10px;">
                <button type="submit" name="update_siswa" class="btn btn-success">💾 Simpan Perubahan</button>
                <button type="button" id="btnCancelEditSiswa" class="btn btn-secondary">Batal</button>
            </div>
        </form>
    </div>
</div>

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
