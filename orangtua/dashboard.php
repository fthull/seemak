<?php
session_start();
include '../conn.php';
$active_page = 'dashboard';
if ($_SESSION['role'] !== 'wali') {
    header("Location: ../login.php");
    exit;
}

// Ambil ID orang tua berdasarkan user_id yang login
$user_id = $_SESSION['id'];

$orang_tua = mysqli_query($conn, "
    SELECT id FROM orang_tua 
    WHERE user_id='$user_id'
");

$data_ortu = mysqli_fetch_assoc($orang_tua);
$id_orangtua = $data_ortu['id'] ?? 0;


// HITUNG SURAT KELUAR
$q_keluar = mysqli_query($conn, "
    SELECT COUNT(*) as total 
    FROM surat_pribadi 
    WHERE id_orangtua='$id_orangtua'
");
$data_keluar = mysqli_fetch_assoc($q_keluar);
$surat_keluar = $data_keluar['total'];


// HITUNG SURAT MASUK
$q_masuk = mysqli_query($conn, "
    SELECT COUNT(*) as total 
    FROM surat_resmi 
    WHERE id_orangtua='$id_orangtua'
");
$data_masuk = mysqli_fetch_assoc($q_masuk);
$surat_masuk = $data_masuk['total'];


// TOTAL
$total_surat = $surat_masuk + $surat_keluar;

include '../partials/header.php';
include '../partials/sidebar.php';
?>

<div class="app-content">
    <h2>📊 Dashboard</h2>
    <p>Ringkasan Sistem Layanan Madrasah</p>

    <!-- STAT -->
    <div class="stats">
        <div class="stat-card">
            <i class="fas fa-envelope-open-text"></i>
            <div>
                <h4>Total Surat</h4>
                <span><?= $total_surat ?></span>
            </div>
        </div>
        
        <div class="stat-card">
            <i class="fas fa-file-alt"></i>
            <div>
                <h4>Surat Masuk</h4>
                <span><?= $surat_masuk ?></span>
            </div>
        </div>
        
        <div class="stat-card">
            <i class="fas fa-paper-plane"></i>
            <div>
                <h4>Surat Terkirim</h4>
                <span><?= $surat_keluar ?></span>
            </div>
        </div>

        <div class="stat-card">
            <i class="fas fa-comments"></i>
            <div>
                <h4>Evaluasi Masuk</h4>
                <span><?= $evaluasi ?></span>
            </div>
        </div>
    </div>

    <!-- SURAT TERBARU -->
     <div class="card-box">
        <h3>📄 Surat Masuk</h3>
        <table width="100%" cellpadding="10">
            <tr>
                <th>Judul</th>
                <th>Tanggal</th>
                <th>Status</th>
            </tr>
            <?php
            $q = mysqli_query($conn,"
                SELECT s.id, s.judul, s.tanggal, st.status
                FROM surat s
                INNER JOIN surat_resmi st ON s.id = st.id_surat
                WHERE st.id_orangtua='$id_orangtua'
                ORDER BY s.id DESC
                LIMIT 5
            ");
            if(mysqli_num_rows($q) > 0) {
                while($d=mysqli_fetch_assoc($q)){
                    echo "
                    <tr class='surat-row' onclick='lihatSuratMasuk({$d['id']})'>
                        <td>{$d['judul']}</td>
                        <td>{$d['tanggal']}</td>
                        <td>{$d['status']}</td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='3' style='text-align:center; color:#999;'>Belum ada surat</td></tr>";
            }
            ?>
        </table>
    </div>
    <div class="card-box">
        <h3>📄 Surat Keluar</h3>
        <table width="100%" cellpadding="10">
            <tr>
                <th>Judul</th>
                <th>Tanggal</th>
                <th>Status</th>
            </tr>
            <?php
            $q = mysqli_query($conn,"
                SELECT id, judul, tanggal, status
                FROM surat_pribadi
                WHERE id_orangtua='$id_orangtua'
                ORDER BY id DESC
                LIMIT 5
            ");
            if(mysqli_num_rows($q) > 0) {
                while($d=mysqli_fetch_assoc($q)){
                    echo "
                    <tr class='surat-row' onclick='lihatSuratKeluar({$d['id']})'>
                        <td>{$d['judul']}</td>
                        <td>{$d['tanggal']}</td>
                        <td>{$d['status']}</td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='3' style='text-align:center; color:#999;'>Belum ada surat</td></tr>";
            }
            ?>
        </table>
    </div>
</div>

<!-- MODAL LIHAT SURAT MASUK -->
<div class="modal" id="modalSuratMasuk">
    <div class="modal-box surat" style="max-width:800px; max-height:90vh; overflow-y:auto;">
        <h3 style="text-align:right; margin-top:-10px;">
            <button type="button" onclick="closeSuratMasuk()" style="background:none; border:none; font-size:24px; cursor:pointer; color:#999;">&times;</button>
        </h3>

        <!-- KOP SURAT -->
        <div class="kop">
            <h3>KEMENTERIAN AGAMA REPUBLIK INDONESIA</h3>
            <h4>MADRASAH ALIYAH KEJURUAN NEGERI ENDE</h4>
            <p>Alamat Madrasah · Telp · Email</p>
            <hr>
        </div>

        <!-- NOMOR SURAT -->
        <table width="100%">
            <tr>
                <td width="60%">
                    <strong>Nomor :</strong> <span id="previewNomor"></span><br>
                    <strong>Perihal :</strong> <span id="previewPerihal"></span>
                </td>
                <td align="right">
                    Ende, <span id="previewTanggal"></span>
                </td>
            </tr>
        </table>

        <br>

        <!-- TUJUAN -->
        <p>
            Kepada Yth,<br>
            <b id="previewTujuan"></b><br>
            Di Tempat
        </p>

        <br>

        <!-- ISI -->
        <div id="previewIsi" style="border:1px solid #000; padding:10px; font-family:'Times New Roman', serif; min-height:100px;"></div>

        <br><br>

        <!-- PENUTUP -->
        <p>Demikian surat ini kami sampaikan. Atas perhatian Bapak/Ibu kami ucapkan terima kasih.</p>

        <br><br>

        <!-- TTD -->
        <div style="text-align:right">
            <p>Hormat Kami,</p>
            <br><br>
            <b>Kepala Madrasah</b><br>
            <u id="previewTtd"></u>
        </div>

        <br>

        <button type="button" onclick="closeSuratMasuk()" class="btn btn-secondary">Tutup</button>
    </div>
</div>

<!-- MODAL LIHAT SURAT KELUAR -->
<div class="modal" id="modalSuratKeluar">
    <div class="modal-box surat" style="max-width:800px; max-height:90vh; overflow-y:auto;">
        <h3 style="text-align:right; margin-top:-10px;">
            <button type="button" onclick="closeSuratKeluar()" style="background:none; border:none; font-size:24px; cursor:pointer; color:#999;">&times;</button>
        </h3>

        <!-- PREVIEW SURAT WALI -->
        <div style="border:1px solid #ddd; padding:20px; background:#f9f9f9; font-family:'Times New Roman', serif;">
            <h3 style="text-align:center; margin-bottom:20px;" id="previewJudul"></h3>
            
            <p style="text-align:right; margin-bottom:20px;">
                Tanggal: <span id="previewTglWali"></span>
            </p>

            <div style="border:1px solid #000; padding:15px; min-height:200px; margin:20px 0;">
                <p id="previewIsiWali"></p>
            </div>

            <p style="text-align:right; margin-top:20px;">
                Hormat,<br><br><b id="previewNamaWali"></b>
            </p>
        </div>

        <br>

        <button type="button" onclick="closeSuratKeluar()" class="btn btn-secondary">Tutup</button>
    </div>
</div>

<style>
.surat-row {
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.surat-row:hover {
    background-color: #f0f0f0;
}

.kop{
    text-align: center;
    margin-bottom: 20px;
}
.kop h3{
    margin: 5px 0;
    font-size: 14px;
    font-weight: bold;
}
.kop h4{
    margin: 5px 0;
    font-size: 13px;
    font-weight: bold;
}
.kop p{
    margin: 5px 0;
    font-size: 11px;
}
</style>

<script>
function lihatSuratMasuk(id){
    fetch(`../guru/get_surat.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('previewNomor').textContent = data.nomor;
            document.getElementById('previewPerihal').textContent = data.perihal;
            document.getElementById('previewTujuan').textContent = data.tujuan;
            document.getElementById('previewIsi').textContent = data.isi;
            document.getElementById('previewTanggal').textContent = data.tanggal;
            document.getElementById('previewTtd').textContent = data.ttd;
            document.getElementById('modalSuratMasuk').style.display = 'block';
        });
}

function closeSuratMasuk(){
    document.getElementById('modalSuratMasuk').style.display = 'none';
}

function lihatSuratKeluar(id){
    fetch(`get_surat_wali.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('previewJudul').textContent = data.judul;
            document.getElementById('previewTglWali').textContent = data.tanggal;
            document.getElementById('previewIsiWali').textContent = data.isi;
            document.getElementById('previewNamaWali').textContent = data.nama_wali;
            document.getElementById('modalSuratKeluar').style.display = 'block';
        })
        .catch(error => {
            alert('Gagal memuat surat');
            console.error(error);
        });
}

function closeSuratKeluar(){
    document.getElementById('modalSuratKeluar').style.display = 'none';
}

// Tutup modal jika klik di luar modal
window.onclick = function(event) {
    const modalMasuk = document.getElementById('modalSuratMasuk');
    const modalKeluar = document.getElementById('modalSuratKeluar');
    if (event.target == modalMasuk) {
        modalMasuk.style.display = 'none';
    }
    if (event.target == modalKeluar) {
        modalKeluar.style.display = 'none';
    }
}
</script>