<?php
session_start();
include '../conn.php';
$active_page = 'surat';
if ($_SESSION['role'] !== 'wali') {
    header("Location: ../login.php");
    exit;
}

$wali_id = $_SESSION['id'];
$user_id = $_SESSION['id'];

$qOrtu = mysqli_query($conn,"
SELECT id FROM orang_tua 
WHERE user_id='$user_id'
LIMIT 1
");

$dataOrtu = mysqli_fetch_assoc($qOrtu);
$wali_id = $dataOrtu['id'];
$q_guru = mysqli_query($conn,"SELECT id,nama FROM guru ORDER BY nama ASC");
// Ambil nama siswa berdasarkan orang tua yang login
$query_siswa = mysqli_query($conn, "
    SELECT s.nama FROM siswa s
    INNER JOIN orang_tua ot ON s.id = ot.siswa_id
    WHERE ot.user_id='$wali_id'
    LIMIT 1
");
$data_siswa = mysqli_fetch_assoc($query_siswa);
$nama_siswa = $data_siswa['nama'] ?? 'Siswa';

if(isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM surat_wali WHERE id = '$id'");
    echo "<script>alert('Surat berhasil dihapus'); location='surat.php';</script>";
}

if (isset($_POST['buat_surat'])) {
    $judul   = mysqli_real_escape_string($conn, $_POST['judul']);
$tanggal = $_POST['tanggal'];
$isi     = mysqli_real_escape_string($conn, $_POST['isi']);
    mysqli_query($conn, "
        INSERT INTO surat_wali ( id_orangtua, judul, isi, tanggal, status)
        VALUES ('$wali_id', '$judul', '$isi', '$tanggal', 'draft')
    ");

    echo "<script>alert('Surat berhasil dibuat'); location='surat.php';</script>";
}

if(isset($_POST['kirim_surat'])){

$id_surat = $_POST['id_surat'];
$id_guru_arr = $_POST['id_guru'] ?? [];

if(empty($id_guru_arr)) {
    echo "<script>alert('Pilih minimal satu guru tujuan'); location='surat.php';</script>";
    exit;
}

$q = mysqli_query($conn,"SELECT * FROM surat_wali WHERE id='$id_surat'");
$d = mysqli_fetch_assoc($q);

$judul = $d['judul'];
$isi   = $d['isi'];
$id_orangtua = $d['id_orangtua'];

$berhasil = 0;
foreach($id_guru_arr as $id_guru) {
    mysqli_query($conn,"INSERT INTO surat_pribadi
    (id_orangtua,id_guru,judul,isi,tanggal,status)
    VALUES
    ('$id_orangtua','$id_guru','$judul','$isi',NOW(),'terkirim')");
    $berhasil++;
}

if($berhasil > 0) {
    mysqli_query($conn,"UPDATE surat_wali SET status='sent' WHERE id='$id_surat'");
    echo "<script>
    alert('Surat berhasil dikirim ke $berhasil guru');
    location='surat.php';
    </script>";
} else {
    echo "<script>alert('Gagal mengirim surat'); location='surat.php';</script>";
}

}
// SIMPAN ATAU UPDATE SURAT
if (isset($_POST['update_surat'])) {

$id      = mysqli_real_escape_string($conn, $_POST['id']);
$judul   = mysqli_real_escape_string($conn, $_POST['judul']);
$isi     = mysqli_real_escape_string($conn, $_POST['isi']);
$tanggal = $_POST['tanggal'];

mysqli_query($conn, "
UPDATE surat_wali 
SET judul='$judul', isi='$isi', tanggal='$tanggal'
WHERE id='$id'
");

echo "<script>
alert('Surat berhasil diupdate');
location='surat.php';
</script>";

}

include '../partials/header.php';
include '../partials/sidebar.php';
?>
<style>
    /* Styling Modal agar ke tengah */
    .modal-surat {
        position: fixed; inset: 0; background: rgba(0,0,0,0.7); 
        z-index: 9999; overflow-y: auto; padding: 20px;
    }
    .modal-surat-box {
        max-width: 800px; margin: auto; background: white; 
        border-radius: 8px; overflow: hidden; font-family: 'Tinos', 'Times New Roman', serif;
    }

    /* Format Kertas Surat */
    .surat-body {
        padding: 40px 60px; color: black; line-height: 1.5;
    }

    /* Kop Surat Persis Foto */
    .kop-surat {
        display: flex; align-items: center; text-align: center;
        border-bottom: 3px double black; padding-bottom: 10px; margin-bottom: 20px;
    }
    .kop-text h2, .kop-text h3 { margin: 0; text-transform: uppercase; font-size: 14px; }
    .kop-text h1 { margin: 0; font-size: 18px; color: #000; }
    .kop-text p { margin: 0; font-size: 11px; font-style: italic; }

    /* Input Styling agar menyatu dengan teks surat */
    .input-surat {
        border: none; border-bottom: 1px dashed #ccc; padding: 2px 5px;
        font-family: inherit; font-size: inherit; background: #f9f9f9;
    }
    .input-surat:focus { outline: none; background: #fff; border-bottom-color: #000; }
    
    .isi-surat {
        width: 100%; min-height: 150px; border: 1px solid #eee; padding: 10px;
        font-family: inherit; font-size: 12pt; resize: vertical; margin: 10px 0;
    }

    .tabel-identitas td { padding: 3px 0; font-size: 12pt; }
    .kanan { text-align: right; }

    .table-modern{
width:100%;
border-collapse:collapse;
background:white;
border-radius:10px;
overflow:hidden;
}

.table-modern thead{
background:#f1f5f9;
}

.table-modern th{
padding:14px;
text-align:left;
font-size:14px;
color:#334155;
}

.table-modern td{
padding:14px;
border-top:1px solid #f1f5f9;
}

.table-modern tr:hover{
background:#f8fafc;
cursor:pointer;
}

.badge-status{
padding:5px 10px;
border-radius:6px;
font-size:12px;
font-weight:600;
}

.status-draft{
background:#fef3c7;
color:#92400e;
}

.status-sent{
background:#dcfce7;
color:#166534;
}

.action-btn{
border:none;
padding:7px 10px;
border-radius:6px;
cursor:pointer;
margin-right:5px;
}

.btn-send{
background:#2563eb;
color:white;
}

.btn-edit{
background:#f59e0b;
color:white;
}

.btn-delete{
background:#ef4444;
color:white;
}
</style>

<div class="app-content">
    <h2>📨 Surat Pribadi ke Sekolah</h2>

<button class="btn btn-primary" onclick="openSurat()">
    ✉️ Buat Surat Pribadi
</button>

   <div class="card-box">

<table class="table-modern">
<thead>
<tr>
    <th>Judul Surat</th>
    <th>Tujuan Surat</th>
    <th>Tanggal</th>
    <th>Status</th>
    <th>Guru</th>
    <th>Aksi</th>
</tr>
</thead>

<tbody>

<?php
$q = mysqli_query($conn, "
SELECT sw.*, g.nama as nama_guru
FROM surat_wali sw
LEFT JOIN guru g ON sw.id_guru = g.id
WHERE sw.id_orangtua='$wali_id'
ORDER BY sw.id DESC
");

if(mysqli_num_rows($q) > 0){

while ($d = mysqli_fetch_assoc($q)) {

    $statusClass = ($d['status'] == 'terkirim') ? 'status-sent' : 'status-draft';
    $statusText  = ($d['status'] == 'terkirim') ? 'Terkirim' : 'Draft';
?>

<tr onclick="lihatSurat(<?= $d['id'] ?>)">

<td>
<div style="font-weight:600;color:#1e293b;">
<?= $d['judul'] ?>
</div>
<small style="color:#64748b;">
Surat dari Wali Murid
</small>
</td>

<td>
<span style="color:#475569;">
<i class="fas fa-user-circle mr-1"></i>
Kepala Madrasah / Wali Kelas
</span>
</td>

<td>
<span style="color:#64748b;">
<i class="far fa-calendar-alt mr-1"></i>
<?= $d['tanggal'] ?>
</span>
</td>

<td>
<span class="badge-status <?= $statusClass ?>">
<?= $statusText ?>
</span>
</td>

<td>
<span class="badge-status <?= $statusClass ?>">
<?= $d['nama_guru'] ?></span>
</td>

<td>

<button class="action-btn btn-send"
onclick="event.stopPropagation(); openModalKirim(<?= $d['id'] ?>)">
<i class="fas fa-paper-plane"></i>
</button>

<button class="action-btn btn-edit"
title="Edit Surat"
onclick="event.stopPropagation(); editExistingSurat(<?= json_encode($d) ?>)">
<i class="fas fa-edit"></i>
</button>

<a href="surat.php?hapus=<?= $d['id'] ?>"
class="action-btn btn-delete"
title="Hapus"
onclick="event.stopPropagation(); return confirm('Apakah Anda yakin ingin menghapus surat ini?')">
<i class="fas fa-trash"></i>
</a>

</td>
</tr>

<?php
}}

else{
echo "
<tr>
<td colspan='5' style='text-align:center;padding:25px;color:#94a3b8;'>
Belum ada surat yang dibuat
</td>
</tr>
";
}
?>

</tbody>
</table>

</div>
</div>
<div class="modal-surat" id="modalSurat" style="display:none;">
    <div class="modal-surat-box">
        <div class="modal-header" style="background:#1e293b; color:white; padding:15px; display:flex; justify-content:space-between; align-items:center;">
            <h3 style="margin:0; font-size:16px;">✉️ Buat Surat Pribadi ke Sekolah</h3>
            <span onclick="closeSurat()" style="cursor:pointer; font-size:24px;">&times;</span>
        </div>

        <form method="POST">
            <div class="surat-body">
                
                <div style="margin-bottom:20px; text-align:center;">
                    <input type="text" name="judul" class="input-surat" placeholder="JUDUL SURAT (Contoh: SURAT IZIN TIDAK MASUK)" required style="width:70%; text-align:center; font-weight:bold; text-transform:uppercase;">
                </div>

                <p>
                    Kepada Yth.<br>
                    <b>Kepala Madrasah / Wali Kelas</b><br>
                    <b>MAK Negeri Ende</b><br>
                    di - Tempat
                </p>

                <p style="margin-top:20px;">Assalamu’alaikum Warahmatullahi Wabarakatuh</p>

                <p>
                    Dengan hormat,<br>
                    Saya yang bertanda tangan di bawah ini, orang tua/wali dari siswa:
                </p>

                <table class="tabel-identitas" style="margin-left: 30px;">
                    <tr>
                        <td width="120">Nama Siswa</td>
                        <td>: <b><?= $nama_siswa ?></b></td>
                    </tr>
                    <tr>
                        <td>Nama Wali</td>
                        <td>: <b><?= $_SESSION['nama'] ?></b></td>
                    </tr>
                </table>

                <p style="margin-top:15px;">Melalui surat ini saya ingin menyampaikan hal sebagai berikut:</p>

                <textarea name="isi" class="isi-surat" required 
                    placeholder="Tuliskan alasan izin atau pesan Anda di sini..."></textarea>

                <p>
                    Demikian surat ini saya sampaikan. Atas perhatian dan kerjasamanya Bapak/Ibu, saya ucapkan terima kasih.
                </p>

                <p>Wassalamu’alaikum Warahmatullahi Wabarakatuh</p>

                <br>
<p class="kanan">
                    <input type="text" name="tempat" class="input-surat" value="Ende" required style="width:100px;">, 
                    <input type="date" name="tanggal" class="input-surat" value="<?= date('Y-m-d') ?>" required>
                </p>
                <div class="kanan" style="margin-top:20px; margin-right: 50px;">
                    <p>Hormat saya,</p>
                    <div style="height: 80px;"></div> <b><u><?= $_SESSION['nama'] ?></u></b><br>
                    (Wali Siswa)
                </div>
            </div>

            <div class="modal-footer" style="padding:15px; background:#f8fafc; text-align:right; border-top:1px solid #eee;">
                <button type="button" onclick="closeSurat()" class="btn btn-secondary" style="padding:8px 20px;">Batal</button>
                <button name="buat_surat" class="btn btn-success" style="padding:8px 25px; font-weight:bold;">
                    <i class="fas fa-paper-plane"></i> Buat Surat
                </button>
            </div>
        </form>
    </div>
</div>

<div class="modal-surat" id="modalEditSurat" style="display:none;">
    <div class="modal-surat-box">
        <div class="modal-header" style="background:#1e293b; color:white; padding:15px; display:flex; justify-content:space-between; align-items:center;">
            <h3 style="margin:0; font-size:16px;">✏️ Edit Surat Pribadi</h3>
            <span onclick="closeEditSurat()" style="cursor:pointer; font-size:24px;">&times;</span>
        </div>

        <form method="POST">
            <input type="hidden" name="id" id="edit_id">
            <div class="surat-body">
                <div style="margin-bottom:20px; text-align:center;">
                    <input type="text" name="judul" id="edit_judul" class="input-surat" placeholder="JUDUL SURAT (Contoh: SURAT IZIN TIDAK MASUK)" required style="width:70%; text-align:center; font-weight:bold; text-transform:uppercase;">
                </div>

                <p>
                    Kepada Yth.<br>
                    <b>Kepala Madrasah / Wali Kelas</b><br>
                    <b>MAK Negeri Ende</b><br>
                    di - Tempat
                </p>

                <p style="margin-top:20px;">Assalamu'alaikum Warahmatullahi Wabarakatuh</p>

                <p>
                    Dengan hormat,<br>
                    Saya yang bertanda tangan di bawah ini, orang tua/wali dari siswa:
                </p>

                <table class="tabel-identitas" style="margin-left: 30px;">
                    <tr>
                        <td width="120">Nama Siswa</td>
                        <td>: <b><?= $nama_siswa ?></b></td>
                    </tr>
                    <tr>
                        <td>Nama Wali</td>
                        <td>: <b><?= $_SESSION['nama'] ?></b></td>
                    </tr>
                </table>

                <p style="margin-top:15px;">Melalui surat ini saya ingin menyampaikan hal sebagai berikut:</p>

                <textarea name="isi" id="edit_isi" class="isi-surat" required 
                    placeholder="Tuliskan alasan izin atau pesan Anda di sini..."></textarea>

                <p>
                    Demikian surat ini saya sampaikan. Atas perhatian dan kerjasamanya Bapak/Ibu, saya ucapkan terima kasih.
                </p>

                <p>Wassalamu'alaikum Warahmatullahi Wabarakatuh</p>

                <br>
                <p class="kanan">
                    <input type="text" name="tempat" id="edit_tempat" class="input-surat" value="Ende" required style="width:100px;">, 
                    <input type="date" name="tanggal" id="edit_tanggal" class="input-surat" required>
                </p>
                <div class="kanan" style="margin-top:20px; margin-right: 50px;">
                    <p>Hormat saya,</p>
                    <div style="height: 80px;"></div> <b><u><?= $_SESSION['nama'] ?></u></b><br>
                    (Wali Siswa)
                </div>

            </div>

            <div class="modal-footer" style="padding:15px; background:#f8fafc; text-align:right; border-top:1px solid #eee;">
                <button type="button" onclick="closeEditSurat()" class="btn btn-secondary" style="padding:8px 20px;">Batal</button>
                <button name="update_surat" class="btn btn-success" style="padding:8px 25px; font-weight:bold;">
                    <i class="fas fa-save"></i> Update Surat
                </button>
            </div>
        </form>
    </div>
</div>

<div class="modal-surat" id="modalViewSurat" style="display:none;">
    <div class="modal-surat-box">
        <div class="modal-header" style="background:#1e293b;color:white;padding:15px;display:flex;justify-content:space-between;">
            <h3 style="margin:0;">📄 Detail Surat</h3>
            <span onclick="closeViewSurat()" style="cursor:pointer;font-size:24px;">&times;</span>
        </div>
        <div class="surat-body">
            <div style="text-align:center; margin-bottom:20px;">
                <h3 id="viewJudul" style="text-decoration:underline; text-transform:uppercase;"></h3>
            </div>
            <p>
                    Kepada Yth.<br>
                    <b>Kepala Madrasah / Wali Kelas</b><br>
                    <b>MAK Negeri Ende</b><br>
                    di - Tempat
                </p>

                <p style="margin-top:20px;">Assalamu'alaikum Warahmatullahi Wabarakatuh</p>

                <p>
                    Dengan hormat,<br>
                    Saya yang bertanda tangan di bawah ini, orang tua/wali dari siswa:
                </p>

                <table class="tabel-identitas" style="margin-left: 30px;">
                    <tr>
                        <td width="120">Nama Siswa</td>
                        <td>: <b><?= $nama_siswa ?></b></td>
                    </tr>
                    <tr>
                        <td>Nama Wali</td>
                        <td>: <b><?= $_SESSION['nama'] ?></b></td>
                    </tr>
                </table>

                <p style="margin-top:15px;">Melalui surat ini saya ingin menyampaikan hal sebagai berikut:</p>

            <p id="viewIsi" class="isi-surat-popup" style="white-space:pre-line;"></p>
            <p>
                    Demikian surat ini saya sampaikan. Atas perhatian dan kerjasamanya Bapak/Ibu, saya ucapkan terima kasih.
                </p>

                <p>Wassalamu'alaikum Warahmatullahi Wabarakatuh</p>

                <br>

            <p class="kanan">Ende, <span id="viewTanggal"></span></p>

            <div class="kanan" style="margin-top:20px;">
                <p>Hormat saya,</p>
                <div style="height:50px;"></div>
                <b><u><?= $_SESSION['nama'] ?></u></b>
            </div>
        </div>
    </div>
</div>
<div id="modalKirim" style="display:none;">
<form method="POST">

<input type="hidden" name="id_surat" id="idSuratKirim">

<button type="submit" name="kirim" class="btn btn-success">
Kirim Surat
</button>

</form>
</div>

<div class="modal-surat" id="modalKirimSurat" style="display:none;">
<div class="modal-surat-box">
<div class="modal-header" style="background:#1e293b; color:white; padding:15px; display:flex; justify-content:space-between; align-items:center;">
<h3 style="margin:0; font-size:16px;">📨 Kirim Surat</h3>
<span onclick="closeModalKirim()" style="cursor:pointer; font-size:24px;">&times;</span>
</div>

<form method="POST" onsubmit="return validateKirimForm()">
<div class="surat-body">
<input type="hidden" name="id_surat" id="id_surat_kirim">

<label>Pilih Guru Tujuan (bisa pilih lebih dari satu)</label>

<div style="max-height: 300px; overflow-y: auto; border: 1px solid #e5e7eb; border-radius: 8px; margin: 10px 0;">
<table style="width: 100%; border-collapse: collapse;">
<thead style="background: #f8fafc; position: sticky; top: 0;">
<tr>
<th style="padding: 12px; text-align: left; border-bottom: 1px solid #e5e7eb; font-weight: 600; color: #374151;">Pilih</th>
<th style="padding: 12px; text-align: left; border-bottom: 1px solid #e5e7eb; font-weight: 600; color: #374151;">Nama Guru</th>
<th style="padding: 12px; text-align: left; border-bottom: 1px solid #e5e7eb; font-weight: 600; color: #374151;">Posisi</th>
<th style="padding: 12px; text-align: left; border-bottom: 1px solid #e5e7eb; font-weight: 600; color: #374151;">Mata Pelajaran</th>
</tr>
</thead>
<tbody>
<?php
$qGuru = mysqli_query($conn,"SELECT id, nama, posisi, mapel FROM guru ORDER BY nama ASC");
while($g = mysqli_fetch_assoc($qGuru)){
?>
<tr style="border-bottom: 1px solid #f1f5f9;">
<td style="padding: 12px;">
<input type="checkbox" name="id_guru[]" value="<?= $g['id'] ?>" style="width: 16px; height: 16px;">
</td>
<td style="padding: 12px; font-weight: 500; color: #1f2937;">
<?= $g['nama'] ?>
</td>
<td style="padding: 12px; color: #6b7280;">
<span style="background: #dbeafe; color: #1e40af; padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: 500;">
<?= strtoupper($g['posisi']) ?>
</span>
</td>
<td style="padding: 12px; color: #6b7280;">
<?= $g['mapel'] ?: '-' ?>
</td>
</tr>
<?php } ?>
</tbody>
</table>
</div>

<div style="margin-top: 15px; padding: 10px; background: #fef3c7; border-radius: 6px; border-left: 4px solid #f59e0b;">
<i class="fas fa-info-circle" style="color: #d97706; margin-right: 8px;"></i>
<strong>Catatan:</strong> Surat akan dikirim ke semua guru yang dipilih.
</div>

</div>

<div class="modal-footer" style="padding:15px; background:#f8fafc; text-align:right; border-top:1px solid #eee;">
<button type="button" onclick="closeModalKirim()" class="btn btn-secondary" style="padding:8px 20px;">Batal</button>
<button type="submit" name="kirim_surat" class="btn btn-primary" style="padding:8px 25px;">
<i class="fas fa-paper-plane"></i> Kirim Surat
</button>
</div>
</form>
</div>
</div>

<script>
function openSurat(){
    document.getElementById('modalSurat').style.display = 'flex';
}
function closeSurat(){
    document.getElementById('modalSurat').style.display = 'none';
}

function lihatSurat(id) {
    fetch('get_surat_wali.php?id=' + id)
        .then(res => {
            if (!res.ok) throw new Error('Network response was not ok');
            return res.json();
        })
        .then(data => {
            if (data.error) {
                alert(data.error);
                return;
            }

            // Mengisi data ke elemen modal
            document.getElementById("viewJudul").innerText = data.judul;
            document.getElementById("viewTanggal").innerText = data.tanggal;
            
            // PENTING: Gunakan innerHTML agar tag <p>, <br>, dll dieksekusi browser
            // Jika data.isi mengandung script HTML mentah, ini akan merapikannya
            document.getElementById("viewIsi").innerHTML = data.isi;

            // Tampilkan modal
            document.getElementById("modalViewSurat").style.display = "flex";
        })
        .catch(err => {
            alert("Gagal memuat surat. Pastikan koneksi stabil.");
            console.error("Fetch error:", err);
        });
}

function closeViewSurat(){
document.getElementById("modalViewSurat").style.display="none";
}

function editExistingSurat(data){

// isi form edit
document.getElementById('edit_id').value = data.id;
document.getElementById('edit_judul').value = data.judul;
document.getElementById('edit_tanggal').value = data.tanggal;
document.getElementById('edit_isi').value = data.isi;

// buka modal edit
document.getElementById('modalEditSurat').style.display = 'flex';

}
function closeEditSurat(){
document.getElementById('modalEditSurat').style.display = 'none';
}

function openModalKirim(id){
document.getElementById('id_surat_kirim').value = id;
document.getElementById('modalKirimSurat').style.display = 'flex';
}

function closeModalKirim(){
document.getElementById('modalKirimSurat').style.display = 'none';
}

function validateKirimForm() {
    const checkboxes = document.querySelectorAll('input[name="id_guru[]"]:checked');
    if (checkboxes.length === 0) {
        alert('Pilih minimal satu guru tujuan');
        return false;
    }
    return true;
}
function openModal(){
    document.getElementById('idSurat').value = '';
    document.getElementById('formSurat').reset();
    document.getElementById('btnSimpan').name = 'simpan';
    document.getElementById('btnSimpan').textContent = 'Simpan Surat';
    document.getElementById('modalSurat').style.display='block';
}


// Tutup modal jika klik di luar
window.onclick = function(event) {
    const modal = document.getElementById('modalViewSurat');
    const modalKirim = document.getElementById('modalKirimSurat');
    const modalSurat = document.getElementById('modalSurat');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
    if (event.target == modalKirim) {
        modalKirim.style.display = 'none';
    }
    if (event.target == modalSurat) {
        modalSurat.style.display = 'none';
    }
}
</script>