<?php
include '../conn.php';
$active_page = 'dashboard';
include '../partials/header.php';
include '../partials/sidebar.php';
/* Query ringkas */
$total_surat = mysqli_fetch_assoc(
    mysqli_query($conn,"SELECT COUNT(*) total FROM surat")
)['total'];

$surat_kirim = mysqli_fetch_assoc(
    mysqli_query($conn,"SELECT COUNT(*) total FROM surat WHERE status='terkirim'")
)['total'];

$surat_masuk = mysqli_fetch_assoc(
    mysqli_query($conn,"SELECT COUNT(*) total FROM surat_pribadi")
)['total'];

?>

<div class="app-content">
    <h2>📊 Dashboard</h2>
    <p>Ringkasan Sistem Layanan Madrasah</p>

    <!-- STAT -->
    <div class="stats">
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
            <i class="fas fa-users"></i>
            <div>
                <h4>Surat Masuk</h4>
                <span><?= $surat_masuk?></span>
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
        <h3>📄 Surat Masuk 
            <a href="surat_masuk.php" style="float:right; font-size:13px; color:#007bff; text-decoration:none;">Lihat Semua →</a>
        </h3>
        <table width="100%" cellpadding="10">
            <tr>
                <th>Judul</th>
                <th>Pengirim</th>
                <th>Tanggal</th>
                <th>Status</th>
            </tr>
            <?php
            $q = mysqli_query($conn,"
                SELECT sp.id, sp.judul, u.nama, sp.tanggal, sp.status, sp.isi
                FROM surat_pribadi sp
                INNER JOIN users u ON sp.id_orangtua = u.id
                ORDER BY sp.id DESC
                LIMIT 5
            ");
            if(mysqli_num_rows($q) > 0) {
            while($d=mysqli_fetch_assoc($q)){
                $isi = htmlspecialchars($d['isi']);
                $nama = htmlspecialchars($d['nama']);
                echo "
                <tr class='rowSuratMasuk' onclick='previewSuratMasuk({$d['id']}, \"{$d['judul']}\", \"{$nama}\", \"{$d['tanggal']}\", \"{$isi}\")' style='cursor:pointer;'>
                    <td>{$d['judul']}</td>
                    <td>{$nama}</td>
                    <td>{$d['tanggal']}</td>
                    <td>{$d['status']}</td>
                </tr>";
            }} else {
                echo "<tr><td colspan='4' style='text-align:center; color:#999; padding:20px;'>Belum ada surat masuk</td></tr>";
            }
            ?>
        </table>
    </div>
    <div class="card-box">
        <h3>📄 Surat Keluar</h3>
        <table width="100%" cellpadding="10">
            <tr>
                <th>Judul</th>
                <th>Tujuan</th>
                <th>Tanggal</th>
                <th>Status</th>
            </tr>
            <?php
            $q = mysqli_query($conn,"
                SELECT id, judul, tujuan, tanggal, status, isi
                FROM surat
                ORDER BY id DESC
                LIMIT 5
            ");
            if(mysqli_num_rows($q) > 0) {
            while($d=mysqli_fetch_assoc($q)){
                $isi = htmlspecialchars($d['isi']);
                echo "
                <tr class='rowSuratKeluar' onclick='previewSuratKeluar({$d['id']}, \"{$d['judul']}\", \"{$d['tujuan']}\", \"{$d['tanggal']}\", \"{$isi}\")' style='cursor:pointer;'>
                    <td>{$d['judul']}</td>
                    <td>{$d['tujuan']}</td>
                    <td>{$d['tanggal']}</td>
                    <td>{$d['status']}</td>
                </tr>";
            }} else {
                echo "<tr><td colspan='4' style='text-align:center; color:#999; padding:20px;'>Belum ada surat keluar</td></tr>";
            }
            ?>
        </table>
    </div>

    <!-- EVALUASI -->
    <div class="card-box">
        <h3>💬 Pesan Evaluasi Terbaru</h3>
        <?php
        $e = mysqli_query($conn,"
            SELECT pesan, tanggal
            FROM evaluasi
            ORDER BY id DESC
            LIMIT 3
        ");
        while($d=mysqli_fetch_assoc($e)){
            echo "
            <div style='margin-bottom:12px'>
                <small style='color:#888'>{$d['created_at']}</small>
                <p>“{$d['pesan']}”</p>
            </div>";
        }
        ?>
    </div>

</div>

<!-- MODAL PREVIEW SURAT MASUK -->
<div class="modal" id="modalSuratMasuk">
    <div class="modal-box surat" style="max-width:800px; max-height:90vh; overflow-y:auto;">
        <h3 style="text-align:right; margin-top:-10px;">
            <button type="button" onclick="closeSuratMasuk()" style="background:none; border:none; font-size:24px; cursor:pointer; color:#999;">&times;</button>
        </h3>

        <!-- KOP SURAT -->
        <div style="text-align: center; margin-bottom: 20px;">
            <h3 style="margin: 5px 0; font-size: 14px; font-weight: bold;">KEMENTERIAN AGAMA REPUBLIK INDONESIA</h3>
            <h4 style="margin: 5px 0; font-size: 13px; font-weight: bold;">MADRASAH ALIYAH KEJURUAN NEGERI ENDE</h4>
            <p style="margin: 5px 0; font-size: 11px;">Alamat Madrasah · Telp · Email</p>
            <hr style="margin: 10px 0;">
        </div>

        <div style="padding:0 20px 20px 20px;">
            <p>
                Kepada Yth.<br>
                <b>Kepala Madrasah / Wali Kelas</b><br>
                <b>MAK Negeri Ende</b><br>
                di Tempat
            </p>

            <p>Assalamu'alaikum Warahmatullahi Wabarakatuh</p>

            <p>
                Dengan hormat,<br>
                Saya yang bertanda tangan di bawah ini, orang tua/wali dari siswa:
            </p>

            <table style="width:100%; margin-bottom:15px;">
                <tr>
                    <td>Nama Pengirim</td>
                    <td>: <b id="modalPengirimNama"></b></td>
                </tr>
                <tr>
                    <td>Tanggal</td>
                    <td>: <b id="modalTanggalMasuk"></b></td>
                </tr>
            </table>

            <p>
                <b>Perihal: <span id="modalJudulMasuk"></span></b>
            </p>

            <p>Melalui surat ini saya ingin menyampaikan pesan sebagai berikut:</p>

            <div id="modalIsiMasuk" style="border:1px solid #000; padding:10px; font-family:'Times New Roman', serif; min-height:100px; line-height:1.6; white-space:pre-wrap;"></div>

            <p style="margin-top:20px;">
                Demikian surat ini saya sampaikan. Atas perhatian Bapak/Ibu,
                saya ucapkan terima kasih.
            </p>

            <p>Wassalamu'alaikum Warahmatullahi Wabarakatuh</p>

            <br>

            <p style="text-align:right; margin-top:30px;">
                Hormat saya,<br><br><br>
                <b id="modalPengirimTtd"></b><br>
                (Wali Siswa)
            </p>
        </div>
    </div>
</div>

<!-- MODAL PREVIEW SURAT KELUAR -->
<div class="modal" id="modalSuratKeluar">
    <div class="modal-box surat" style="max-width:800px; max-height:90vh; overflow-y:auto;">
        <h3 style="text-align:right; margin-top:-10px;">
            <button type="button" onclick="closeSuratKeluar()" style="background:none; border:none; font-size:24px; cursor:pointer; color:#999;">&times;</button>
        </h3>

        <!-- KOP SURAT -->
        <div style="text-align: center; margin-bottom: 20px;">
            <h3 style="margin: 5px 0; font-size: 14px; font-weight: bold;">KEMENTERIAN AGAMA REPUBLIK INDONESIA</h3>
            <h4 style="margin: 5px 0; font-size: 13px; font-weight: bold;">MADRASAH ALIYAH KEJURUAN NEGERI ENDE</h4>
            <p style="margin: 5px 0; font-size: 11px;">Alamat Madrasah · Telp · Email</p>
            <hr style="margin: 10px 0;">
        </div>

        <div style="padding:0 20px 20px 20px;">
            <table style="width:100%; margin-bottom:15px;">
                <tr>
                    <td width="60%">
                        <strong>Nomor :</strong> <span id="modalNomorKeluar">-</span><br>
                        <strong>Perihal :</strong> <span id="modalPerihalKeluar"></span>
                    </td>
                    <td align="right" style="vertical-align:top;">
                        Ende, <span id="modalTanggalKeluar"></span>
                    </td>
                </tr>
            </table>

            <p>
                Kepada Yth,<br>
                <b id="modalTujuanKeluar"></b><br>
                Di Tempat
            </p>

            <p style="text-align:justify;">
                <div id="modalIsiKeluar" style="border:1px solid #000; padding:10px; font-family:'Times New Roman', serif; min-height:100px; line-height:1.6; white-space:pre-wrap;"></div>
            </p>

            <p style="margin-top:20px;">Demikian surat ini kami sampaikan. Atas perhatian Bapak/Ibu kami ucapkan terima kasih.</p>

            <br><br>

            <div style="text-align:right; margin-top:30px;">
                <p>Hormat Kami,</p>
                <br><br>
                <b>Kepala Madrasah</b><br>
                <u style="display:block; margin-top:30px;">(Nama Kepala Madrasah)</u>
            </div>
        </div>
    </div>
</div>

<style>
.rowSuratMasuk:hover,
.rowSuratKeluar:hover {
    background-color: #f5f5f5;
}

.surat {
    font-family: 'Times New Roman', serif;
    line-height: 1.4;
}
</style>

<script>
function previewSuratMasuk(id, judul, pengirim, tanggal, isi){
    document.getElementById('modalJudulMasuk').textContent = judul;
    document.getElementById('modalPengirimNama').textContent = pengirim;
    document.getElementById('modalPengirimTtd').textContent = pengirim;
    document.getElementById('modalTanggalMasuk').textContent = tanggal;
    document.getElementById('modalIsiMasuk').textContent = isi;
    document.getElementById('modalSuratMasuk').style.display = 'block';
}

function closeSuratMasuk(){
    document.getElementById('modalSuratMasuk').style.display = 'none';
}

function previewSuratKeluar(id, judul, tujuan, tanggal, isi){
    document.getElementById('modalPerihalKeluar').textContent = judul;
    document.getElementById('modalTujuanKeluar').textContent = tujuan;
    document.getElementById('modalTanggalKeluar').textContent = tanggal;
    document.getElementById('modalIsiKeluar').textContent = isi;
    document.getElementById('modalSuratKeluar').style.display = 'block';
}

function closeSuratKeluar(){
    document.getElementById('modalSuratKeluar').style.display = 'none';
}

// Tutup modal jika klik di luar
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

</div>
</body>
</html>
