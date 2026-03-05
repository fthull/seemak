<?php
include '../conn.php';
$active_page = 'surat_masuk';
include '../partials/header.php';
include '../partials/sidebar.php';
?>

<div class="app-content">
    <h2>📨 Surat Masuk dari Orang Tua</h2>
    <p>Kelola surat-surat dari orang tua/wali siswa</p>

    <div class="card-box">
    <table class="table-modern">
        <thead>
            <tr>
                <th>Judul & Perihal</th>
                <th>Tujuan Surat</th>
                <th>Tanggal Keluar</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $q = mysqli_query($conn, "SELECT * FROM surat_pribadi ORDER BY id DESC");
            while ($d = mysqli_fetch_assoc($q)) {
                // Tentukan class badge berdasarkan status
                $statusClass = ($d['status'] == 'sent') ? 'status-sent' : 'status-draft';
                $statusText = ($d['status'] == 'sent') ? 'Terkirim' : 'Draft';
            ?>
                <tr>
                    <td>
                        <div style="font-weight: 600; color: #1e293b;"><?= $d['judul'] ?></div>
                        <small style="color: #64748b;"><?= $d['nomor'] ?></small>
                    </td>
                    <td>
                        <span style="color: #475569;"><i class="fas fa-user-circle mr-1"></i> <?= $d['nama'] ?></span>
                    </td>
                    <td>
                        <span style="color: #64748b;"><i class="far fa-calendar-alt mr-1"></i> <?= $d['tanggal'] ?></span>
                    </td>
                    <td>
                        <span class="badge-status <?= $statusClass ?>"><?= $statusText ?></span>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>
</div>

<!-- MODAL LIHAT SURAT -->
<div class="modal" id="modalDetailSurat">
    <div class="modal-box surat" style="max-width:800px; max-height:90vh; overflow-y:auto;">
        <h3 style="text-align:right; margin-top:-10px;">
            <button type="button" onclick="closeDetailSurat()" style="background:none; border:none; font-size:24px; cursor:pointer; color:#999;">&times;</button>
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
