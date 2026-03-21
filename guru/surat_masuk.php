<?php
include '../conn.php';
$active_page = 'surat_masuk';
include '../partials/header.php';
include '../partials/sidebar.php';

$role = $_SESSION['role'];
$user_id = $_SESSION['id'];

$id_guru = null;

if ($role == 'guru') {
    $qGuru = mysqli_query($conn, "
        SELECT id FROM guru 
        WHERE user_id='$user_id'
        LIMIT 1
    ");
    $dataGuru = mysqli_fetch_assoc($qGuru);
    $id_guru = $dataGuru['id'] ?? 0;
}


?>

<div class="app-content">
    <h2>📨 Surat Masuk dari Orang Tua</h2>
    <p>Kelola surat-surat dari orang tua/wali siswa</p>

    <div class="card-box">
    <table class="table-modern">
        <thead>
            <tr>
                <th>Judul & Perihal</th>
                <th>Pengirim </th>
                <th>Tanggal Keluar</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
           if ($role == 'admin') {

    $q = mysqli_query($conn, "
        SELECT sp.*, ot.nama as nama_orangtua, s.nama as nama_siswa, s.kelas
        FROM surat_pribadi sp
        LEFT JOIN orang_tua ot ON sp.id_orangtua = ot.id
        LEFT JOIN siswa s ON ot.siswa_id = s.id
        ORDER BY sp.id DESC
    ");

} else if ($role == 'guru') {

    $q = mysqli_query($conn, "
        SELECT sp.*, ot.nama as nama_orangtua, s.nama as nama_siswa, s.kelas
        FROM surat_pribadi sp
        LEFT JOIN orang_tua ot ON sp.id_orangtua = ot.id
        LEFT JOIN siswa s ON ot.siswa_id = s.id
        WHERE sp.id_guru = '$id_guru'
        ORDER BY sp.id DESC
    ");

} else {
    echo "<tr><td colspan='4'>Akses ditolak</td></tr>";
}
            while ($d = mysqli_fetch_assoc($q)) {
                // Tentukan class badge berdasarkan status
                $statusClass = ($d['status'] == 'terkirim') ? 'status-sent' : 'status-draft';
                $statusText = ($d['status'] == 'terkirim') ? 'Terkirim' : 'Draft';
            ?>
                <tr class="surat-row" data-surat='<?= json_encode($d) ?>'>
                    <td>
                        <div style="font-weight: 600; color: #1e293b;"><?= $d['judul'] ?></div>
                    </td>
                    <td>
                        <span style="color: #475569;"><i class="fas fa-user-circle mr-1"></i> <?= $d['nama_orangtua'] ?> (<?= $d['nama_siswa'] ?>)</span>
                    </td>
                    <td>
                        <span style="color: #64748b;"><i class="far fa-calendar-alt mr-1"></i> <?= date('d/m/Y', strtotime($d['tanggal'])) ?></span>
                    </td>
                    <td>
                        <span class="badge-status <?= $statusClass ?>"><?= $statusText ?></span>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<!-- POPUP HOVER SURAT -->
<div id="suratPopup" class="surat-popup" style="display: none;">
    <div class="surat-popup-content">
        <div class="surat-popup-header">
            <h4>📄 Surat Pribadi dari Orang Tua</h4>
            <button type="button" onclick="closeSuratPopup()" class="popup-close-btn">&times;</button>
        </div>
        
        <div class="surat-popup-body">
            <!-- Kop Surat -->

            <!-- Header Surat -->
            <div style="margin-bottom:20px; text-align:center;">
                <h3 id="popupJudul" style="text-transform:uppercase; margin:0;"></h3>
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
                    <td>: <b id="popupNamaSiswa"></b></td>
                </tr>
                <tr>
                    <td>Nama Wali</td>
                    <td>: <b id="popupNamaWali"></b></td>
                </tr>
            </table>

            <p style="margin-top:15px;">Melalui surat ini saya ingin menyampaikan hal sebagai berikut:</p>

            <div id="popupIsi" class="isi-surat-popup"></div>

            <p>
                Demikian surat ini saya sampaikan. Atas perhatian dan kerjasamanya Bapak/Ibu, saya ucapkan terima kasih.
            </p>

            <p>Wassalamu'alaikum Warahmatullahi Wabarakatuh</p>

            <br>

            <p class="kanan">
                Ende, <span id="popupTanggal"></span>
            </p>

            <div class="kanan" style="margin-top:20px; margin-right: 50px;">
                <p>Hormat saya,</p>
                <div style="height: 80px;"></div> 
                <b><u id="popupTtd"></u></b><br>
                (Wali Siswa)
            </div>
        </div>
    </div>
</div>
</div>

<!-- MODAL LIHAT SURAT -->
<div class="modal" id="modalDetailSurat">
    <div class="modal-box surat" style="max-width:800px; max-height:90vh; overflow-y:auto;">
        <h3 style="text-align:right; margin-top:-10px;">
            <button type="button" onclick="closeDetailSurat()" style="background:none; border:none; font-size:24px; cursor:pointer; color:#999;">&times;</button>
        </h3>

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
                    <td>: <b id="detailPengirim"></b></td>
                </tr>
                <tr>
                    <td>Tanggal</td>
                    <td>: <b id="detailTanggal"></b></td>
                </tr>
            </table>

            <p>
                <b>Perihal: <span id="detailJudul"></span></b>
            </p>

            <p>Melalui surat ini saya ingin menyampaikan pesan sebagai berikut:</p>

            <div id="detailIsi" style="border:1px solid #000; padding:10px; font-family:'Times New Roman', serif; min-height:100px; line-height:1.6; white-space:pre-wrap;"></div>

            <p style="margin-top:20px;">
                Demikian surat ini saya sampaikan. Atas perhatian Bapak/Ibu,
                saya ucapkan terima kasih.
            </p>

            <p>Wassalamu'alaikum Warahmatullahi Wabarakatuh</p>

            <br>

            <p style="text-align:right; margin-top:30px;">
                Hormat saya,<br><br><br>
                <b id="detailPengirimTtd"></b><br>
                (Wali Siswa)
            </p>
        </div>

        <div style="padding:0 20px 20px 20px; text-align:right;">
            <button type="button" onclick="closeDetailSurat()" class="btn btn-secondary">Tutup</button>
        </div>
    </div>
</div>

<style>
    /* Desain Tabel Modern */
.table-modern {
    border-collapse: separate;
    border-spacing: 0 10px; /* Jarak antar baris */
    width: 100%;
}

.table-modern th {
    color: #64748b;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.05em;
    padding: 15px 20px;
    border: none;
    text-align: left;
}

.table-modern tr {
    transition: all 0.2s ease;
}

.table-modern td {
    background: #fff;
    padding: 15px 20px;
    border-top: 1px solid #f1f5f9;
    border-bottom: 1px solid #f1f5f9;
    color: #1e293b;
    font-size: 0.95rem;
}

/* Membuat sudut baris melengkung */
.table-modern td:first-child {
    border-left: 1px solid #f1f5f9;
    border-top-left-radius: 12px;
    border-bottom-left-radius: 12px;
}

.table-modern td:last-child {
    border-right: 1px solid #f1f5f9;
    border-top-right-radius: 12px;
    border-bottom-right-radius: 12px;
    text-align: center;
}

/* Efek Hover Baris */
.table-modern tbody tr:hover td {
    background: #f8faff;
    border-color: #e2e8f0;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
}

/* Badge Status Sesuai Foto */
.badge-status {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
}

.status-sent { background: #ecfdf5; color: #059669; } /* Hijau */
.status-draft { background: #fef3c7; color: #d97706; } /* Kuning */

/* Desain Tombol Aksi */
.action-btn {
    width: 35px;
    height: 35px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    border: none;
    margin: 0 3px;
    transition: 0.2s;
    cursor: pointer;
}

.btn-send { background: #eff6ff; color: #3b82f6; }
.btn-edit { background: #f0fdf4; color: #22c55e; }
.btn-delete { background: #fef2f2; color: #ef4444; }

.action-btn:hover {
    transform: scale(1.1);
    filter: brightness(0.95);
}

.surat {
    font-family: 'Times New Roman', serif;
    line-height: 1.4;
}

/* POPUP HOVER SURAT */
.surat-popup {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.surat-popup-content {
    background: white;
    border-radius: 12px;
    max-width: 900px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
    position: relative;
}

.surat-popup-header {
    padding: 20px 30px;
    background: #1e293b;
    color: white;
    border-radius: 12px 12px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.surat-popup-header h4 {
    margin: 0;
    font-size: 18px;
}

.popup-close-btn {
    background: none;
    border: none;
    color: white;
    font-size: 28px;
    cursor: pointer;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.surat-popup-body {
    padding: 30px;
    font-family: 'Times New Roman', serif;
    line-height: 1.6;
    color: #1f2937;
}

/* Kop Surat dalam Popup */
.kop-surat {
    display: flex;
    align-items: center;
    text-align: center;
    border-bottom: 3px double black;
    padding-bottom: 10px;
    margin-bottom: 20px;
}

.kop-text h3, .kop-text h1 {
    margin: 0;
    text-transform: uppercase;
    font-size: 12px;
}

.kop-text h1 {
    font-size: 16px;
    color: #000;
    margin: 5px 0;
}

.kop-text p {
    margin: 2px 0;
    font-size: 10px;
    font-style: italic;
}

.kanan {
    text-align: right;
}

.tabel-identitas td {
    padding: 3px 0;
    font-size: 14px;
}

.isi-surat-popup {
    border: 1px solid #e5e7eb;
    padding: 15px;
    min-height: 120px;
    background: #f9fafb;
    border-radius: 6px;
    margin: 15px 0;
    white-space: pre-wrap;
    line-height: 1.6;
}
</style>

<script>
function lihatSuratMasuk(judul, pengirim, tanggal, isi){
    document.getElementById('detailJudul').textContent = judul;
    document.getElementById('detailPengirim').textContent = pengirim;
    document.getElementById('detailPengirimTtd').textContent = pengirim;
    document.getElementById('detailTanggal').textContent = tanggal;
    document.getElementById('detailIsi').textContent = isi;
    document.getElementById('modalDetailSurat').style.display = 'block';
}

function closeDetailSurat(){
    document.getElementById('modalDetailSurat').style.display = 'none';
}

function closeSuratPopup(){
    document.getElementById('suratPopup').style.display = 'none';
}

function showSuratPopup(data) {
    document.getElementById('popupJudul').textContent = data.judul;
    document.getElementById('popupTanggal').textContent = new Date(data.tanggal).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });
    document.getElementById('popupNamaSiswa').textContent = data.nama_siswa;
    document.getElementById('popupNamaWali').textContent = data.nama_orangtua;
    document.getElementById('popupTtd').textContent = data.nama_orangtua;
    document.getElementById('popupIsi').textContent = data.isi;
    
    document.getElementById('suratPopup').style.display = 'flex';
}

// Hover functionality for surat popup
document.addEventListener('DOMContentLoaded', function() {
    const rows = document.querySelectorAll('.surat-row');
    const popup = document.getElementById('suratPopup');
    
    rows.forEach(row => {
        let hoverTimeout;
        
        row.addEventListener('mouseenter', function() {
            hoverTimeout = setTimeout(() => {
                const data = JSON.parse(this.getAttribute('data-surat'));
                showSuratPopup(data);
            }, 800); // Delay 800ms before showing popup
        });
        
        row.addEventListener('mouseleave', function() {
            clearTimeout(hoverTimeout);
            // Check if mouse is over popup, if not hide it
            setTimeout(() => {
                if (!popup.matches(':hover')) {
                    closeSuratPopup();
                }
            }, 100);
        });
    });
    
    // Hide popup when mouse leaves the popup area
    if (popup) {
        popup.addEventListener('mouseleave', function() {
            closeSuratPopup();
        });
    }
});

// Tutup modal jika klik di luar
window.onclick = function(event) {
    const modal = document.getElementById('modalDetailSurat');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}
</script>

</div>
</body>
</html>
