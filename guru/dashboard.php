<?php
include '../conn.php';
$active_page = 'dashboard';
include '../partials/header.php';
include '../partials/sidebar.php';

/* Logika Query Tetap (Tidak Diubah) */
$total_surat = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) total FROM surat"))['total'];
$surat_kirim = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) total FROM surat WHERE status='terkirim'"))['total'];
$surat_masuk = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) total FROM surat_pribadi"))['total'];
// $evaluasi = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) total FROM evaluasi"))['total'] ?? 0;
?>

<style>
    :root {
        --primary: #1e293b;
        --accent: #2563eb;
        --bg-light: #f8fafc;
    }

    .app-content { background: var(--bg-light); padding: 25px; }
    
    /* Stats Card Styling */
    .stats-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    .stat-card {
        background: white;
        padding: 20px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        transition: transform 0.2s;
    }
    .stat-card:hover { transform: translateY(-5px); }
    .stat-card i {
        font-size: 2.5rem;
        margin-right: 20px;
        color: var(--accent);
        opacity: 0.8;
    }
    .stat-card h4 { margin: 0; color: #64748b; font-size: 14px; text-transform: uppercase; }
    .stat-card span { font-size: 24px; font-weight: 700; color: var(--primary); }

    /* Layout Grid */
    .dashboard-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 25px;
        margin-bottom: 25px;
    }
    .card-box {
        background: white;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    .card-box h3 { 
        margin-bottom: 20px; 
        font-size: 18px; 
        display: flex; 
        align-items: center; 
        justify-content: space-between;
        color: var(--primary);
    }

    /* Table Styling */
    .table-custom { width: 100%; border-collapse: collapse; }
    .table-custom th { text-align: left; padding: 12px; border-bottom: 2px solid #edf2f7; color: #4a5568; font-size: 13px; }
    .table-custom td { padding: 12px; border-bottom: 1px solid #edf2f7; font-size: 14px; color: #2d3748; }
    .surat-row { cursor: pointer; transition: background 0.2s; }
    .surat-row:hover { background: #f1f5f9; }

    /* Modal Styling */
    .modal { display:none; position:fixed; z-index:9999; inset:0; background:rgba(0,0,0,0.6); padding: 20px; overflow-y: auto; }
    .modal-box { background:white; max-width:800px; margin: 20px auto; border-radius:12px; position:relative; overflow: hidden; }
    
    .paper-preview {
        padding: 50px 60px;
        font-family: 'Tinos', 'Times New Roman', Times, serif;
        line-height: 1.6;
        color: black;
    }

    @media (max-width: 992px) { .dashboard-grid { grid-template-columns: 1fr; } }
</style>

<div class="app-content">
    <div style="margin-bottom: 25px;">
        <h2 style="margin:0; color: var(--primary);">📊 Dashboard Admin</h2>
        <p style="color: #64748b;">Ringkasan Sistem Layanan Madrasah Aliyah Kejuruan Negeri Ende.</p>
    </div>

    <div class="stats-container">
        <div class="stat-card">
            <i class="fas fa-file-alt"></i>
            <div>
                <h4>Total Surat</h4>
                <span><?= $total_surat ?></span>
            </div>
        </div>
        <div class="stat-card">
            <i class="fas fa-paper-plane"></i>
            <div>
                <h4>Surat Terkirim</h4>
                <span><?= $surat_kirim ?></span>
            </div>
        </div>
        <div class="stat-card">
            <i class="fas fa-inbox"></i>
            <div>
                <h4>Surat Masuk</h4>
                <span><?= $surat_masuk ?></span>
            </div>
        </div>
        <div class="stat-card">
            <i class="fas fa-comments"></i>
            <div>
                <h4>Evaluasi</h4>
                <span><?= $evaluasi ?></span>
            </div>
        </div>
    </div>

    <div class="dashboard-grid">
<div class="card-box">
    <h3>
        <span><i class="fas fa-envelope text-primary"></i> Surat Masuk Terbaru</span>
        <a href="surat_masuk.php" style="font-size:12px; color:var(--accent); text-decoration:none;">Lihat Semua →</a>
    </h3>
    <table class="table-custom">
        <thead>
            <tr>
                <th>Judul Surat</th>
                <th>Pengirim</th>
                <th>Tanggal</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $q_masuk = mysqli_query($conn,"SELECT sp.id, sp.judul, u.nama, sp.tanggal, sp.status, sp.isi 
                                         FROM surat_pribadi sp 
                                         INNER JOIN users u ON sp.id_orangtua = u.id 
                                         ORDER BY sp.id DESC LIMIT 5");
            if(mysqli_num_rows($q_masuk) > 0) {
                while($d=mysqli_fetch_assoc($q_masuk)){
                    // Membersihkan karakter aneh agar tidak merusak JS
                    $isi_clean = str_replace(array("\r", "\n"), ' ', htmlspecialchars($d['isi']));
                    $nama_clean = htmlspecialchars($d['nama']);
                    $judul_clean = htmlspecialchars($d['judul']);
                    
                    echo "<tr class='surat-row' onclick=\"previewSuratMasuk('{$judul_clean}', '{$nama_clean}', '{$d['tanggal']}', '{$isi_clean}')\">
                            <td><strong>{$d['judul']}</strong></td>
                            <td>{$nama_clean}</td>
                            <td style='color:#64748b;'>".date('d M Y', strtotime($d['tanggal']))."</td>
                          </tr>";
                }
            } else { echo "<tr><td colspan='3' align='center'>Belum ada surat masuk</td></tr>"; }
            ?>
        </tbody>
    </table>
</div>

<div class="card-box">
    <h3>
        <span><i class="fas fa-paper-plane text-success"></i> Surat Keluar Terbaru</span>
        <a href="surat.php" style="font-size:12px; color:var(--accent); text-decoration:none;">Lihat Semua →</a>
    </h3>
    <table class="table-custom">
        <thead>
            <tr>
                <th>Judul Surat</th>
                <th>Tujuan</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $q_keluar = mysqli_query($conn,"SELECT id, judul, tujuan, tanggal, status, isi FROM surat ORDER BY id DESC LIMIT 5");
            if(mysqli_num_rows($q_keluar) > 0) {
                while($d=mysqli_fetch_assoc($q_keluar)){
                    $isi_clean = str_replace(array("\r", "\n"), ' ', htmlspecialchars($d['isi']));
                    $tujuan_clean = htmlspecialchars($d['tujuan']);
                    $judul_clean = htmlspecialchars($d['judul']);
                    $stClass = ($d['status'] == 'terkirim') ? 'color: #16a34a; background: #dcfce7;' : 'color: #92400e; background: #fef3c7;';
                    
                    echo "<tr class='surat-row' onclick=\"previewSuratKeluar('{$judul_clean}', '{$tujuan_clean}', '{$d['tanggal']}', '{$isi_clean}')\">
                            <td><strong>{$d['judul']}</strong></td>
                            <td>{$tujuan_clean}</td>
                            <td><span style='padding:2px 8px; border-radius:10px; font-size:11px; {$stClass}'>{$d['status']}</span></td>
                          </tr>";
                }
            } else { echo "<tr><td colspan='3' align='center'>Belum ada surat keluar</td></tr>"; }
            ?>
        </tbody>
    </table>
</div>
    </div>

<div class="modal" id="modalSuratMasuk">
    <div class="modal-box">
        <div style="background:var(--primary); color:white; padding:15px; display:flex; justify-content:space-between;">
            <span><i class="fas fa-envelope"></i> Detail Surat Masuk</span>
            <span onclick="closeSuratMasuk()" style="cursor:pointer;">&times;</span>
        </div>
        <div class="paper-preview">
            <div style="text-align:center; border-bottom: 2px solid #000; padding-bottom:10px; margin-bottom:20px;">
                <h3 style="margin:0;">SURAT PEMBERITAHUAN WALI MURID</h3>
                <p style="margin:0; font-size:12px;">MAK NEGERI ENDE</p>
            </div>
            <p>Kepada Yth,<br><b>Bapak/Ibu Guru / Wali Kelas</b><br>di Tempat</p>
            <p>Assalamu'alaikum Wr. Wb.</p>
            <p>Yang bertanda tangan di bawah ini, Orang Tua/Wali dari siswa:</p>
            <table style="margin-left:20px;">
                <tr><td>Nama Pengirim</td><td>: <b id="modalPengirimNama"></b></td></tr>
                <tr><td>Tanggal</td><td>: <span id="modalTanggalMasuk"></span></td></tr>
                <tr><td>Perihal</td><td>: <u id="modalJudulMasuk"></u></td></tr>
            </table>
            <div id="modalIsiMasuk" style="margin:20px 0; min-height:100px; text-align:justify; white-space:pre-wrap; font-style:italic; border-left:3px solid #ddd; padding-left:15px;"></div>
            <div style="float:right; text-align:center; width:200px;">
                <p>Hormat Saya,</p>
                <div style="height:60px;"></div>
                <b id="modalPengirimTtd"></b>
            </div>
            <div style="clear:both;"></div>
        </div>
    </div>
</div>

<div class="modal" id="modalSuratKeluar">
    <div class="modal-box">
        <div style="background:#166534; color:white; padding:15px; display:flex; justify-content:space-between;">
            <span><i class="fas fa-file-signature"></i> Detail Surat Keluar Resmi</span>
            <span onclick="closeSuratKeluar()" style="cursor:pointer;">&times;</span>
        </div>
        <div class="paper-preview">
            <div style="display:flex; align-items:center; border-bottom:3px double #000; padding-bottom:10px; margin-bottom:20px;">
                <img src="../assets/logokemenag.png" style="width:70px; margin-right:15px;">
                <div style="text-align:center; flex-grow:1;">
                    <h4 style="margin:0; font-size:14px;">KEMENTERIAN AGAMA REPUBLIK INDONESIA</h4>
                    <h3 style="margin:0; font-size:16px;">MADRASAH ALIYAH KEJURUAN NEGERI ENDE</h3>
                    <p style="margin:0; font-size:10px; font-style:italic;">Jalan Raya Ende-Bajawa KM.21 Ende 86352</p>
                </div>
            </div>
            <table width="100%">
                <tr>
                    <td width="60%">Perihal: <b id="modalPerihalKeluar"></b></td>
                    <td align="right">Ende, <span id="modalTanggalKeluar"></span></td>
                </tr>
            </table>
            <p>Kepada Yth,<br><b id="modalTujuanKeluar"></b><br>di Tempat</p>
            <div id="modalIsiKeluar" style="margin:20px 0; min-height:150px; text-align:justify; white-space:pre-wrap;"></div>
            <div style="float:right; text-align:center; width:200px;">
                <p>Kepala Madrasah,</p>
                <div style="height:60px;"></div>
                <b>(____________________)</b>
            </div>
            <div style="clear:both;"></div>
        </div>
    </div>
</div>

<script>
// Fungsi Preview Surat Masuk (Diterima dari Wali Murid)
function previewSuratMasuk(judul, pengirim, tanggal, isi){
    document.getElementById('modalJudulMasuk').innerText = judul;
    document.getElementById('modalPengirimNama').innerText = pengirim;
    document.getElementById('modalPengirimTtd').innerText = pengirim;
    document.getElementById('modalTanggalMasuk').innerText = tanggal;
    document.getElementById('modalIsiMasuk').innerText = isi;
    document.getElementById('modalSuratMasuk').style.display = 'block';
}

// Fungsi Preview Surat Keluar (Surat Resmi Madrasah)
function previewSuratKeluar(judul, tujuan, tanggal, isi){
    document.getElementById('modalPerihalKeluar').innerText = judul;
    document.getElementById('modalTujuanKeluar').innerText = tujuan;
    document.getElementById('modalTanggalKeluar').innerText = tanggal;
    document.getElementById('modalIsiKeluar').innerText = isi;
    document.getElementById('modalSuratKeluar').style.display = 'block';
}

function closeSuratMasuk(){ document.getElementById('modalSuratMasuk').style.display = 'none'; }
function closeSuratKeluar(){ document.getElementById('modalSuratKeluar').style.display = 'none'; }

// Menutup modal jika user klik area di luar kotak modal
window.onclick = function(event) {
    if (event.target.className === 'modal') {
        closeSuratMasuk();
        closeSuratKeluar();
    }
}
</script>