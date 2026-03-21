<?php
session_start();
include '../conn.php';
$active_page = 'dashboard';
if ($_SESSION['role'] !== 'wali') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['id'];
$nama_user = $_SESSION['nama'];

// Ambil ID orang tua
$orang_tua = mysqli_query($conn, "SELECT id FROM orang_tua WHERE user_id='$user_id'");
$data_ortu = mysqli_fetch_assoc($orang_tua);
$id_orangtua = $data_ortu['id'] ?? 0;

// Statistik
$surat_keluar = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM surat_pribadi WHERE id_orangtua='$id_orangtua'"))['total'];
$surat_masuk = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM surat_resmi WHERE id_orangtua='$id_orangtua'"))['total'];
$total_surat = $surat_masuk + $surat_keluar;
// Asumsi variabel evaluasi jika ada tabelnya, jika tidak set 0
$evaluasi = 0; 

include '../partials/header.php';
include '../partials/sidebar.php';
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

    /* Table Styling */
    .dashboard-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 25px;
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
        gap: 10px;
        color: var(--primary);
    }
    .table-custom { width: 100%; border-collapse: collapse; }
    .table-custom th { text-align: left; padding: 12px; border-bottom: 2px solid #edf2f7; color: #4a5568; font-size: 13px; }
    .table-custom td { padding: 12px; border-bottom: 1px solid #edf2f7; font-size: 14px; color: #2d3748; }
    .surat-row { cursor: pointer; transition: background 0.2s; }
    .surat-row:hover { background: #f1f5f9; }

    /* Modal Layout */
    .modal { 
        display:none; position:fixed; z-index:9999; inset:0; 
        background:rgba(0,0,0,0.6); padding: 20px; overflow-y: auto;
    }
    .modal-box { 
        background:white; max-width:800px; margin: 20px auto; 
        border-radius:8px; position:relative; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);
    }
    
    /* Paper Style (Same as Official Letter) */
    .paper-preview {
        padding: 50px 60px;
        font-family: 'Tinos', 'Times New Roman', Times, serif;
        line-height: 1.6;
        color: black;
    }
    .kop-surat {
        text-align: center;
        border-bottom: 3px double black;
        padding-bottom: 10px;
        margin-bottom: 25px;
    }
    .badge-status {
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
    }
    .status-terkirim { background: #dcfce7; color: #166534; }
    .status-draft { background: #fef3c7; color: #92400e; }

    @media (max-width: 992px) {
        .dashboard-grid { grid-template-columns: 1fr; }
    }
</style>

<div class="app-content">
    <div style="margin-bottom: 25px;">
        <h2 style="margin:0; color: var(--primary);">Selamat Datang, <?= $nama_user ?></h2>
        <p style="color: #64748b;">Panel ringkasan aktivitas akademik anak Anda di MAK Negeri Ende.</p>
    </div>

    <div class="stats-container">
        <div class="stat-card">
            <i class="fas fa-layer-group"></i>
            <div>
                <h4>Total Surat</h4>
                <span><?= $total_surat ?></span>
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
            <i class="fas fa-paper-plane"></i>
            <div>
                <h4>Surat Keluar</h4>
                <span><?= $surat_keluar ?></span>
            </div>
        </div>
        <div class="stat-card">
            <i class="fas fa-star"></i>
            <div>
                <h4>Evaluasi</h4>
                <span><?= $evaluasi ?></span>
            </div>
        </div>
    </div>

    <div class="dashboard-grid">
        <div class="card-box">
            <h3><i class="fas fa-envelope-open text-primary"></i> Surat Masuk Terbaru</h3>
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>Perihal</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $q_m = mysqli_query($conn,"SELECT s.id, s.judul, s.tanggal FROM surat s 
                                               INNER JOIN surat_resmi st ON s.id = st.id_surat 
                                               WHERE st.id_orangtua='$id_orangtua' ORDER BY s.id DESC LIMIT 5");
                    if(mysqli_num_rows($q_m) > 0) {
                        while($d=mysqli_fetch_assoc($q_m)){
                            echo "<tr class='surat-row' onclick='lihatSuratMasuk({$d['id']})'>
                                    <td><strong>{$d['judul']}</strong></td>
                                    <td style='color:#64748b;'>".date('d M Y', strtotime($d['tanggal']))."</td>
                                  </tr>";
                        }
                    } else { echo "<tr><td colspan='2' align='center'>Tidak ada data</td></tr>"; }
                    ?>
                </tbody>
            </table>
        </div>

        <div class="card-box">
            <h3><i class="fas fa-paper-plane text-success"></i> Surat Keluar Terbaru</h3>
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>Judul</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $q_k = mysqli_query($conn,"SELECT id, judul, status FROM surat_pribadi 
                                               WHERE id_orangtua='$id_orangtua' ORDER BY id DESC LIMIT 5");
                    if(mysqli_num_rows($q_k) > 0) {
                        while($d=mysqli_fetch_assoc($q_k)){
                            $stClass = ($d['status'] == 'terkirim') ? 'status-terkirim' : 'status-draft';
                            echo "<tr class='surat-row' onclick='lihatSuratKeluar({$d['id']})'>
                                    <td><strong>{$d['judul']}</strong></td>
                                    <td><span class='badge-status $stClass'>{$d['status']}</span></td>
                                  </tr>";
                        }
                    } else { echo "<tr><td colspan='2' align='center'>Tidak ada data</td></tr>"; }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal" id="modalSuratMasuk">
    <div class="modal-box">
        <div class="paper-preview">
            <div class="kop-wrapper" style="
    display: flex; 
    align-items: center; 
    justify-content: center; 
    position: relative;
    border-bottom: 3px double #000;
    padding-bottom: 15px;
    margin-bottom: 20px;
">
    <img src="../assets/logokemenag.png" style="
        width: 80px; 
        position: absolute; 
        left: 0;
    ">

    <div class="kop-text" style="text-align: center; width: 100%;">
        <h4 style="margin:0; font-size:14px; font-weight: bold; letter-spacing: 0.5px;">KEMENTERIAN AGAMA REPUBLIK INDONESIA</h4>
        <h4 style="margin:2px 0; font-size:13px; font-weight: bold;">KANTOR KEMENTERIAN AGAMA KABUPATEN ENDE</h4>
        <h3 style="margin:2px 0; font-size:18px; font-weight: bold;">MADRASAH ALIYAH KEJURUAN NEGERI ENDE</h3>
        <p style="margin:2px 0; font-size:11px; font-style:italic;">Jalan Raya Ende-Bajawa KM.21 Ende 86352</p>
        <p style="margin:2px 0; font-size:10px; font-style:italic;">Website: mak-ende.sch.id | Email: makn.ende.anaraja@gmail.com</p>
    </div>
</div>
            <table width="100%" style="margin-bottom:20px;">
                <tr><td width="80">Nomor</td><td width="10">:</td><td id="mNomor"></td><td align="right" id="mTgl"></td></tr>
                <tr><td>Perihal</td><td>:</td><td id="mPerihal" style="font-weight:bold;"></td><td></td></tr>
            </table>
            <p>Kepada Yth,<br><b id="mTujuan"></b><br>di Tempat</p>
            <div id="mIsi" style="text-align:justify; min-height:150px; margin:20px 0; white-space:pre-line;"></div>
            <div style="float:right; text-align:center; width:200px; margin-top:30px;">
                <p>Kepala MAK Negeri Ende,</p>
                <div style="height:70px;"></div>
                <b id="mTtd" style="text-decoration:underline;"></b>
            </div>
            <div style="clear:both;"></div>
        </div>
        <div style="padding:15px; text-align:right; background:#f8fafc; border-radius:0 0 8px 8px;">
            <button onclick="closeSuratMasuk()" class="btn btn-secondary">Tutup</button>
        </div>
    </div>
</div>

<div class="modal" id="modalSuratKeluar">
    <div class="modal-box">
        <div style="background:#1e293b; color:white; padding:12px 20px; display:flex; justify-content:space-between; align-items:center; border-radius:8px 8px 0 0;">
            <span style="font-size: 14px; font-weight: 600;"><i class="fas fa-file-alt"></i> Detail Surat Keluar</span>
            <span onclick="closeSuratKeluar()" style="cursor:pointer; font-size:20px;">&times;</span>
        </div>

        <div class="paper-preview">
            <div class="kop-wrapper" style="display: flex; align-items: center; justify-content: center; position: relative; border-bottom: 3px double #000; padding-bottom: 15px; margin-bottom: 25px;">
                <img src="../assets/logokemenag.png" style="width: 75px; position: absolute; left: 0;">
                <div class="kop-text" style="text-align: center; width: 100%;">
                    <h4 style="margin:0; font-size:13px; font-weight: bold; text-transform: uppercase;">KEMENTERIAN AGAMA REPUBLIK INDONESIA</h4>
                    <h4 style="margin:2px 0; font-size:12px; font-weight: bold; text-transform: uppercase;">KANTOR KEMENTERIAN AGAMA KABUPATEN ENDE</h4>
                    <h3 style="margin:2px 0; font-size:16px; font-weight: bold; text-transform: uppercase;">MADRASAH ALIYAH KEJURUAN NEGERI ENDE</h3>
                    <p style="margin:0; font-size:10px; font-style:italic;">Jalan Raya Ende-Bajawa KM.21 Ende 86352</p>
                    <p style="margin:0; font-size:9px; font-style:italic;">Website: mak-ende.sch.id | Email: makn.ende.anaraja@gmail.com</p>
                </div>
            </div>

            <div style="text-align:center; margin-bottom:25px;">
                <u id="viewJudul" style="text-transform:uppercase; font-weight:bold; font-size:14pt;"></u>
            </div>

            <div style="margin-bottom: 20px;">
                Kepada Yth.<br>
                <b>Bapak/Ibu Guru & Wali Kelas</b><br>
                MAK Negeri Ende<br>
                di - Tempat
            </div>

            <p>Assalamu’alaikum Warahmatullahi Wabarakatuh,</p>
            <p>Dengan hormat, saya yang bertanda tangan di bawah ini:</p>

            <table style="margin: 15px 0 15px 30px; border-collapse: collapse; width: 90%;">
                <tr>
                    <td width="150">Nama Wali Murid</td>
                    <td width="10">:</td>
                    <td><b><?= $nama_user ?></b></td>
                </tr>
                <tr>
                    <td>Orang Tua Dari</td>
                    <td>:</td>
                    <td>
                        <?php 
                        // Mengambil nama siswa untuk tampilan statis di modal
                        $q_siswa = mysqli_query($conn, "SELECT s.nama FROM siswa s INNER JOIN orang_tua ot ON s.id = ot.siswa_id WHERE ot.user_id='$user_id' LIMIT 1");
                        $d_siswa = mysqli_fetch_assoc($q_siswa);
                        echo "<b>".($d_siswa['nama'] ?? '-')."</b>";
                        ?>
                    </td>
                </tr>
            </table>

            <p>Melalui surat ini ingin menyampaikan hal sebagai berikut:</p>

            <div id="viewIsi" style="text-align:justify; text-indent: 40px; margin: 20px 0; min-height: 100px; line-height: 1.6;"></div>

            <p>Demikian surat ini saya sampaikan. Atas perhatian dan kerjasamanya Bapak/Ibu, saya ucapkan terima kasih.</p>
            <p>Wassalamu’alaikum Warahmatullahi Wabarakatuh.</p>

            <div style="float:right; text-align:center; width:220px; margin-top:30px;">
                <p>Ende, <span id="viewTanggal"></span></p>
                <p>Hormat saya,</p>
                <div style="height:70px;"></div>
                <b><u><?= $nama_user ?></u></b><br>
                Wali Murid
            </div>
            <div style="clear:both;"></div>
        </div>

        <div style="padding:15px; text-align:right; background:#f8fafc; border-top:1px solid #eee; border-radius:0 0 8px 8px;">
            <button onclick="closeSuratKeluar()" class="btn btn-secondary" style="padding: 8px 20px;">Tutup</button>
            <button onclick="window.print()" class="btn btn-primary" style="padding: 8px 20px;"><i class="fas fa-print"></i> Cetak</button>
        </div>
    </div>
</div>

<script>
function lihatSuratMasuk(id){
    fetch(`get_surat_resmi.php?id=${id}`)
    .then(r => r.json()).then(data => {
        document.getElementById('mNomor').innerText = data.nomor;
        document.getElementById('mPerihal').innerText = data.perihal;
        document.getElementById('mTujuan').innerText = data.tujuan;
        document.getElementById('mIsi').innerHTML = data.isi;
        document.getElementById('mTgl').innerText = 'Ende, ' + data.tanggal;
        document.getElementById('mTtd').innerText = data.ttd;
        document.getElementById('modalSuratMasuk').style.display = 'block';
    });
}

function lihatSuratKeluar(id){
    fetch(`get_surat_wali.php?id=${id}`)
    .then(r => r.json()).then(data => {
        document.getElementById('viewJudul').innerText = data.judul;
        document.getElementById('viewTanggal').innerText = data.tanggal;
        document.getElementById('viewIsi').innerText = data.isi;
        document.getElementById('modalSuratKeluar').style.display = 'block';
    });
}

function closeSuratMasuk(){ document.getElementById('modalSuratMasuk').style.display = 'none'; }
function closeSuratKeluar(){ document.getElementById('modalSuratKeluar').style.display = 'none'; }

window.onclick = function(e) {
    if(e.target.className == 'modal') {
        closeSuratMasuk();
        closeSuratKeluar();
    }
}
</script>