<?php
session_start();
include '../conn.php';
$active_page = 'surat_masuk';

if ($_SESSION['role'] !== 'wali') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['id'];
$orang_tua = mysqli_query($conn, "SELECT id FROM orang_tua WHERE user_id='$user_id'");
$data_ortu = mysqli_fetch_assoc($orang_tua);
$id_orangtua = $data_ortu['id'] ?? 0;

include '../partials/header.php';
include '../partials/sidebar.php';
?>

<style>
    /* CSS KONSISTEN DENGAN MODUL GURU/ADMIN */
    .app-content { background: #f8fafc; padding: 30px; border-radius: 20px; }
    
    .card-box {
        background: white; border-radius: 15px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        padding: 20px; margin-top: 20px;
    }

    .table-modern { width: 100%; border-collapse: separate; border-spacing: 0 10px; }
    .table-modern th { 
        padding: 15px; color: #64748b; font-size: 0.8rem; 
        text-transform: uppercase; letter-spacing: 0.05em; text-align: left;
    }
    .table-modern td { 
        padding: 15px; background: #fff; border-top: 1px solid #f1f5f9; border-bottom: 1px solid #f1f5f9; 
    }
    .table-modern td:first-child { border-left: 1px solid #f1f5f9; border-top-left-radius: 12px; border-bottom-left-radius: 12px; }
    .table-modern td:last-child { border-right: 1px solid #f1f5f9; border-top-right-radius: 12px; border-bottom-right-radius: 12px; text-align:center; }
    .table-modern tr:hover td { background: #f8faff; }

    /* BADGE STATUS */
    .badge-status { padding: 5px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
    .status-new { background: #eff6ff; color: #3b82f6; }
    .status-read { background: #f1f5f9; color: #64748b; }

    /* PREVIEW SURAT (MODAL) - PERSIS FORMAT ADMIN */
    @import url('https://fonts.googleapis.com/css2?family=Tinos:ital,wght@0,400;0,700;1,400&display=swap');
    /* Menstabilkan Container Utama Modal */
    .modal-full {
        display: none; 
        position: fixed; 
        inset: 0; 
        background: rgba(0,0,0,0.85); 
        z-index: 9999; 
        overflow-y: auto; 
        padding: 20px;
    }

    .modal-content-wrapper {
        max-width: 850px;
        margin: 0 auto;
        background: white;
        border-radius: 8px;
        overflow: hidden;
    }

    /* Kertas Surat A4 */
    .paper-preview {
        background: white;
        padding: 60px 80px;
        min-height: 1000px;
        font-family: "Times New Roman", Times, serif;
        color: black;
        line-height: 1.6;
        display: flex;
        flex-direction: column;
    }

    /* Kop Surat */
    .kop-wrapper {
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        border-bottom: 4px double black;
        padding-bottom: 10px;
        margin-bottom: 25px;
        margin-left: -30px;
    }

    /* Isi Surat */
    .isi-surat {
        text-align: justify;
        margin-top: 20px;
        flex-grow: 1; /* Mengisi ruang agar TTD tetap di bawah */
    }

    /* Tanda Tangan (TTD) - Menggunakan Flexbox agar stabil */
    .ttd-section {
        display: flex;
        justify-content: flex-end; /* Mendorong TTD ke kanan */
        margin-top: 50px;
    }

    .ttd-box {
        text-align: center;
        width: 300px;
    }

    .ttd-space {
        height: 80px;
    }

    /* Tombol Cetak */
    .no-print-area {
        background: #f1f5f9;
        padding: 15px;
        text-align: center;
        border-top: 1px solid #e2e8f0;
    }
</style>

<div class="app-content">
    <h2 style="font-weight: 800; color: #1e293b;"><i class="fas fa-envelope-open-text text-primary"></i> Surat Masuk</h2>
    <p style="color: #64748b;">Daftar pemberitahuan resmi dan undangan dari MAK Negeri Ende.</p>

    <div class="card-box">
        <table class="table-modern">
            <thead>
                <tr>
                    <th>Informasi Surat</th>
                    <th>Tanggal Terbit</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $q = mysqli_query($conn,"
                    SELECT s.id, s.judul, s.nomor, s.tanggal, st.status
                    FROM surat s
                    INNER JOIN surat_resmi st ON s.id = st.id_surat
                    WHERE st.id_orangtua='$id_orangtua'
                    ORDER BY s.id DESC
                ");
                
                if(mysqli_num_rows($q) > 0) {
                    while($d=mysqli_fetch_assoc($q)){
                $statusClass = ($d['status'] == 'diterima') ? 'status-new' : 'status-read';
                ?>
                <tr>
                    <td>
                        <div style="font-weight: 700; color: #1e293b;"><?= $d['judul'] ?></div>
                        <small style="color: #94a3b8;"><?= $d['nomor'] ?></small>
                    </td>
                    <td>
                        <div style="color: #475569;"><i class="far fa-calendar-alt mr-1"></i> <?= $d['tanggal'] ?></div>
                    </td>
                    <td><span class="badge-status <?= $statusClass ?>">Diterima</span></td>
                    <td>
                        <button type="button" class="btn btn-primary btn-sm" onclick="lihatSurat(<?= $d['id'] ?>)" style="border-radius:8px;">
                            <i class="fas fa-eye mr-1"></i> Buka Surat
                        </button>
                    </td>
                </tr>
                <?php 
                    }
                } else {
                    echo "<tr><td colspan='4' style='text-align:center; padding:50px; color:#94a3b8;'>
                            <i class='fas fa-envelope-open' style='display:block; font-size:2rem; margin-bottom:10px;'></i>
                            Belum ada surat masuk untuk Anda
                          </td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>
<div id="modalSurat" class="modal-full">
    <div class="modal-content-wrapper">
        <div style="background: #1e293b; padding: 15px; display: flex; justify-content: space-between; align-items: center;" class="no-print">
            <span style="color: white; font-weight: 600;">📄 Pratinjau Surat Resmi</span>
            <button onclick="closeSurat()" style="background: none; border: none; color: white; font-size: 24px; cursor: pointer;">&times;</button>
        </div>
        
        <div class="paper-preview">
            <div class="kop-wrapper">
                <img src="../assets/logokemenag.png" style="width: 100px; margin-left: -50px; margin-right: 40px;">
                <div class="kop-text">
                    <h3 style="margin:0; font-size: 14pt;">KEMENTERIAN AGAMA REPUBLIK INDONESIA</h3>
                    <h4 style="margin:0; font-size: 12pt;">KANTOR KEMENTERIAN AGAMA KABUPATEN ENDE</h4>
                    <h3 style="margin:0; font-size: 14pt;">MADRASAH ALIYAH KEJURUAN NEGERI ENDE</h3>
                    <p style="margin:0; font-size: 10pt;">Jalan Raya Ende-Bajawa KM.21 Ende 86352</p>
                    <p style="margin:0; font-size: 10pt;">Website: mak-ende.sch.id | Email: makn.ende.anaraja@gmail.com</p>
                </div>
            </div>

            <table width="100%" style="font-size: 11pt;">
                <tr>
                    <td width="15%">Nomor</td><td width="2%">:</td>
                    <td id="previewNomor" width="45%"></td>
                    <td align="right">Ende, <span id="previewTanggal"></span></td>
                </tr>
                <tr>
                    <td>Perihal</td><td>:</td>
                    <td id="previewPerihal" style="font-weight: bold;"></td>
                    <td></td>
                </tr>
            </table>

            <div style="margin-top: 30px; font-size: 11pt;">
                Kepada Yth,<br>
                <b id="previewTujuan"></b><br>
                Di Tempat
            </div>

            <div id="previewIsi" class="isi-surat"></div>

            <div class="ttd-section">
                <div class="ttd-box">
                    <p style="margin:0;">Kepala MAK Negeri Ende,</p>
                    <div class="ttd-space"></div>
                    <b id="previewTtd" style="text-decoration: underline;"></b>
                </div>
            </div>
        </div>

        <div class="no-print-area no-print">
            <button onclick="window.print()" class="btn btn-success">
                <i class="fas fa-print"></i> Cetak Surat ke PDF
            </button>
        </div>
    </div>
</div>
<script>
function lihatSurat(id){
    fetch(`get_surat_resmi.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('previewNomor').textContent = data.nomor;
            document.getElementById('previewPerihal').textContent = data.perihal;
            document.getElementById('previewTujuan').textContent = data.tujuan;
            document.getElementById('previewIsi').innerHTML = data.isi;
            document.getElementById('previewTanggal').textContent = data.tanggal;
            document.getElementById('previewTtd').textContent = data.ttd;
            document.getElementById('modalSurat').style.display = 'block';

            // 🔥 TAMBAHAN: update status jadi dibaca
            fetch(`update_status.php?id=${id}`);

            // 🔥 TAMBAHAN: kurangi notif langsung
            kurangiNotif();
        });
}

function kurangiNotif(){
    let badge = document.querySelector('.notif-badge');

    if(badge){
        let jumlah = parseInt(badge.textContent);

        if(jumlah > 1){
            badge.textContent = jumlah - 1;
        } else {
            // kalau tinggal 1 → hilangkan badge
            badge.remove();
        }
    }
}
function closeSurat(){
    document.getElementById('modalSurat').style.display = 'none';
}
</script>