<?php
include '../conn.php';
$active_page = 'surat';
include '../partials/header.php';
include '../partials/sidebar.php';

// HAPUS SURAT
if(isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM surat WHERE id = '$id'");
    echo "<script>alert('Surat berhasil dihapus'); location='surat.php';</script>";
}

// KIRIM SURAT (Logika tetap sama seperti kode Anda)
if(isset($_POST['kirim_surat'])) {
    $id_surat = $_POST['id_surat'];
    $id_orangtua_list = $_POST['id_orangtua'] ?? [];
    if(count($id_orangtua_list) == 0){
        echo "<script>alert('Pilih minimal 1 orang tua');</script>";
    } else {
        $success = true;
        foreach($id_orangtua_list as $id_ortu){
            $query = "INSERT INTO surat_tujuan (id_surat, id_orangtua, status, tanggal) 
                      VALUES ('$id_surat', '$id_ortu', 'terkirim', NOW())";
            if(!mysqli_query($conn, $query)) { $success = false; break; }
        }
        if($success) {
            mysqli_query($conn, "UPDATE surat SET status='terkirim' WHERE id='$id_surat'");
            echo "<script>alert('Surat berhasil dikirim');location='surat.php';</script>";
        }
    }
}

// SIMPAN ATAU UPDATE SURAT
if (isset($_POST['proses_surat'])) {
    $id      = mysqli_real_escape_string($conn, $_POST['id']);
    $nomor   = mysqli_real_escape_string($conn, $_POST['nomor']);
    $perihal = mysqli_real_escape_string($conn, $_POST['perihal']);
    $tujuan  = mysqli_real_escape_string($conn, $_POST['tujuan']);
    $isi     = mysqli_real_escape_string($conn, $_POST['isi']); 
    // tanggal dikendalikan oleh database (TIMESTAMP) – gunakan nilai sekarang
    // nilai dari POST diabaikan karena MySQL akan menyimpan CURRENT_TIMESTAMP
    $tanggal = date('Y-m-d');
    $ttd     = mysqli_real_escape_string($conn, $_POST['ttd']);
    $judul   = $perihal;

    if(empty($id)) {
        // otomatis gunakan NOW() di SQL hanya untuk kejelasan
        mysqli_query($conn, "INSERT INTO surat (nomor, perihal, tujuan, isi, tanggal, ttd, judul, status) 
                            VALUES ('$nomor','$perihal','$tujuan','$isi','$tanggal','$ttd','$judul', 'draft')");
        $msg = "Surat berhasil dibuat";
    } else {
        // timestamp akan diperbarui ke saat ini (atau bisa dihilangkan jika kolom AUTO_UPDATE)
        mysqli_query($conn, "UPDATE surat SET nomor='$nomor', perihal='$perihal', tujuan='$tujuan', 
                            isi='$isi', tanggal='$tanggal', ttd='$ttd', judul='$judul' WHERE id='$id'");
        $msg = "Surat berhasil diupdate";
    }
    echo "<script>alert('$msg'); location='surat.php';</script>";
}
?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Tinos:ital,wght@0,400;0,700;1,400&display=swap');
    /* Desain Modal Modern */
    .modal-custom {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.7); /* Overlay gelap transparan */
        backdrop-filter: blur(8px); /* Efek blur di belakang popup */
        z-index: 10000;
        align-items: center;
        justify-content: center;
        padding-left: 500px;
        padding-top: 150px;
        transition: all 0.3s ease;
    }

    .modal-card {
        background: white;
        width: 100%;
        align-items: center;
        justify-content: center;
        max-width: 450px;
        border-radius: 20px;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        padding: 30px;
        transform: translateY(20px);
        animation: slideUp 0.4s forwards ease-out;
    }

    @keyframes slideUp {
        to { transform: translateY(0); opacity: 1; }
    }

    .modal-header {
        text-align: center;
        margin-bottom: 25px;
    }

    .modal-header h4 {
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 8px;
    }

    .modal-header p {
        color: #64748b;
        font-size: 0.9rem;
    }

    /* Desain Tombol Menu */
    .template-option {
        display: flex;
        align-items: center;
        width: 100%;
        padding: 16px;
        margin-bottom: 15px;
        border: 2px solid #f1f5f9;
        border-radius: 15px;
        background: white;
        transition: all 0.2s ease;
        text-align: left;
        cursor: pointer;
    }

    .template-option:hover {
        border-color: #3b82f6;
        background: #f8faff;
        transform: scale(1.02);
    }

    .icon-box {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        font-size: 1.2rem;
    }

    .icon-undangan { background: #eff6ff; color: #3b82f6; }
    .icon-pemberitahuan { background: #f0fdf4; color: #22c55e; }

    .option-text b { display: block; color: #1e293b; font-size: 1rem; }
    .option-text span { font-size: 0.8rem; color: #64748b; }

    .btn-cancel {
        width: 100%;
        padding: 12px;
        border: none;
        background: transparent;
        color: #ef4444;
        font-weight: 600;
        margin-top: 10px;
        border-radius: 10px;
        transition: 0.2s;
    }

    /* Container Tabel */
.card-box {
    background: #ffffff;
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    padding: 20px;
    margin-top: 25px;
    border: 1px solid #edf2f7;
}

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

    .btn-cancel:hover { background: #fef2f2; }
    .modal-full { position: fixed; inset: 0; background: rgba(0,0,0,0.8); z-index: 9999; overflow-y: auto; display: none; padding: 20px; }
    
    .paper-a4 { 
        width: 210mm; min-height: 297mm; padding: 15mm 20mm; margin: auto; background: white; 
        font-family: 'Tinos', serif; color: black; line-height: 1.4; font-size: 11pt;
    }

    .kop-wrapper { display: flex; align-items: center; border-bottom: 3px solid black; padding-bottom: 5px; }
    .kop-wrapper img { width: 120px; margin-right: 0; }
    .kop-text { flex: 1; text-align: center; margin-left: -70px; }
    .kop-text h2 { font-size: 14pt; margin: 0; font-weight: bold; }
    .kop-text h1 { font-size: 16pt; margin: 0; font-weight: bold; text-transform: uppercase; }
    .kop-text p { font-size: 9pt; margin: 0; }
    .kop-line-thin { border-bottom: 1px solid black; margin-top: 2px; margin-bottom: 20px; }

    [contenteditable="true"]:hover { background: #f1f5f9; }
    .toolbar-editor { position: sticky; top: 0; background: #1e293b; padding: 15px; text-align: center; z-index: 100; margin-bottom: 20px; border-radius: 10px; }

    .judul-surat { text-align: center; margin-bottom: 25px; }
    .judul-surat h3 { text-decoration: underline; margin: 0; font-size: 12pt; text-transform: uppercase; }
    .judul-surat p { margin: 0; font-weight: bold; }

    .content-area { text-align: justify; }
    .content-area ol { padding-left: 20px; }
    .content-area li { margin-bottom: 10px; }

    [contenteditable="true"]:focus { outline: 2px solid #3b82f6; background: #fafafa; }
    
    @media print { .no-print { display: none; } .paper-a4 { box-shadow: none; margin: 0; } }
    /* Container Modal */
.modal-kirim-backdrop {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.7);
    backdrop-filter: blur(4px);
    z-index: 10001;
    align-items: center;
    justify-content: center;
    padding-left: 450px;
    padding-top: 150px;
}

.modal-kirim-box {
    background: white;
    width: 100%;
    max-width: 650px;
    border-radius: 20px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    max-height: 90vh;
    animation: zoomIn 0.3s ease-out;
}

@keyframes zoomIn {
    from { opacity: 0; transform: scale(0.95); }
    to { opacity: 1; transform: scale(1); }
}

.modal-kirim-header {
    padding: 25px;
    border-bottom: 1px solid #f1f5f9;
    background: #fff;
}

.modal-kirim-header h3 {
    margin: 0;
    font-weight: 700;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 10px;
}

/* Search Bar di dalam Modal */
.search-container {
    padding: 15px 25px;
    background: #f8fafc;
}

.modal-search-input {
    width: 100%;
    padding: 10px 15px;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
    outline: none;
    font-size: 0.9rem;
    transition: 0.2s;
}

.modal-search-input:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }

/* List Area */
.modal-kirim-body {
    flex: 1;
    overflow-y: auto;
    padding: 10px 25px;
}

.list-table {
    width: 100%;
    border-collapse: collapse;
}

.list-table th {
    text-align: left;
    padding: 12px;
    font-size: 0.75rem;
    text-transform: uppercase;
    color: #64748b;
    position: sticky;
    top: 0;
    background: white;
    z-index: 1;
}

.list-table tr { border-bottom: 1px solid #f1f5f9; transition: 0.2s; }
.list-table tr:hover { background: #f8faff; }

.list-table td { padding: 12px; font-size: 0.9rem; color: #334155; }

/* Checkbox Custom */
.custom-checkbox {
    width: 18px;
    height: 18px;
    cursor: pointer;
    accent-color: #3b82f6;
}

/* Footer */
.modal-kirim-footer {
    padding: 20px 25px;
    border-top: 1px solid #f1f5f9;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    background: #fff;
}
/* Toolbar Modern Floating */
.toolbar-modern {
    position: sticky;
    top: 20px;
    z-index: 1000;
    display: flex;
    justify-content: center;
    gap: 15px;
    padding: 15px 25px;
    background: rgba(255, 255, 255, 0.8); /* Efek kaca transparan */
    backdrop-filter: blur(10px); /* Blur latar belakang */
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 50px; /* Bentuk kapsul */
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    margin: 0 auto 30px auto;
    width: fit-content;
    transition: all 0.3s ease;
}

.toolbar-modern:hover {
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
    transform: translateY(-2px);
}

/* Styling Tombol di Toolbar */
.tool-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    border-radius: 30px;
    font-size: 0.9rem;
    font-weight: 600;
    border: none;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-save-db {
    background: #10b981; /* Emerald Green */
    color: white;
}

.btn-print-pdf {
    background: #3b82f6; /* Blue */
    color: white;
}

.btn-close-editor {
    background: #f1f5f9; /* Light Slate */
    color: #64748b;
}

/* Efek Hover Tombol */
.tool-btn:hover {
    filter: brightness(1.1);
    transform: scale(1.05);
}

.tool-btn:active {
    transform: scale(0.95);
}

/* Sembunyikan Toolbar Saat Cetak */
@media print {
    .no-print {
        display: none !important;
    }
}
</style>

<div class="app-content">
    <button class="btn btn-primary" onclick="openTemplateSelector()">Buat Surat Baru</button>

    <div class="card-box">
    <table class="table-modern">
        <thead>
            <tr>
                <th>Judul & Perihal</th>
                <th>Tujuan Surat</th>
                <th>Tanggal Keluar</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $q = mysqli_query($conn, "SELECT * FROM surat ORDER BY id DESC");
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
                        <span style="color: #475569;"><i class="fas fa-user-circle mr-1"></i> <?= $d['tujuan'] ?></span>
                    </td>
                    <td>
                        <span style="color: #64748b;"><i class="far fa-calendar-alt mr-1"></i> <?= $d['tanggal'] ?></span>
                    </td>
                    <td>
                        <span class="badge-status <?= $statusClass ?>"><?= $statusText ?></span>
                    </td>
                    <td>
                        <button class="action-btn btn-send" title="Kirim Surat" onclick="openModalKirim(<?= $d['id'] ?>)">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                        <button class="action-btn btn-edit" title="Edit Surat" onclick='editExistingSurat(<?= json_encode($d) ?>)'>
                            <i class="fas fa-edit"></i>
                        </button>
                        <a href="surat.php?hapus=<?= $d['id'] ?>" class="action-btn btn-delete" title="Hapus" onclick="return confirm('Apakah Anda yakin ingin menghapus surat ini?')">
                            <i class="fas fa-trash"></i>
                        </a>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>
</div>
<div id="templateSelector" class="modal-custom">
    <div class="modal-card">
        <div class="modal-header">
            <h4>Buat Dokumen Baru</h4>
            <p>Pilih format surat resmi MAK Negeri Ende</p>
        </div>

        <button class="template-option" onclick="launchEditor('undangan')">
            <div class="icon-box icon-undangan">
                <i class="fas fa-envelope-open-text"></i>
            </div>
            <div class="option-text">
                <b>Surat Undangan</b>
                <span>Format undangan LHB (Nomor di kiri)</span>
            </div>
        </button>

        <button class="template-option" onclick="launchEditor('pemberitahuan')">
            <div class="icon-box icon-pemberitahuan">
                <i class="fas fa-bullhorn"></i>
            </div>
            <div class="option-text">
                <b>Surat Pemberitahuan</b>
                <span>Format libur/poin (Judul di tengah)</span>
            </div>
        </button>

        <button class="btn-cancel" onclick="closeTemplateSelector()">
            <i class="fas fa-times mr-1"></i> Batalkan
        </button>
    </div>
</div>

<div id="editorModal" class="modal-full">
    <div class="toolbar-modern no-print">
    <button type="button" class="tool-btn btn-save-db" onclick="saveToDatabase()">
        <i class="fas fa-cloud-upload-alt"></i> Simpan ke Database
    </button>

    <button type="button" class="tool-btn btn-print-pdf" onclick="window.print()">
        <i class="fas fa-file-pdf"></i> Cetak / Simpan PDF
    </button>

    <div style="width: 1px; background: #e2e8f0; margin: 0 5px;"></div>

    <button type="button" class="tool-btn btn-close-editor" onclick="closeEditor()">
        <i class="fas fa-times-circle"></i> Tutup
    </button>
</div>

    <div class="paper-a4">
            <div class="kop-wrapper">
                <img src="../assets/logokemenag.png" > <div class="kop-text">
                    <h2>KEMENTERIAN AGAMA REPUBLIK INDONESIA</h2>
                    <h3>KANTOR KEMENTERIAN AGAMA KABUPATEN ENDE</h3>
                    <h1>MADRASAH ALIYAH KEJURUAN NEGERI ENDE</h1>
                    <p>Jalan Raya Ende-Bajawa KM.21 Ende 86352</p>
                    <p>Website: mak-ende.sch.id Email: makn.ende.anaraja@gmail.com</p>
                </div>
            </div>
            <div class="kop-line-thin"></div>

            <table width="100%">
                <tr>
                    <td width="12%">Nomor</td><td width="2%">:</td>
                    <td width="50%" contenteditable="true" id="edit_nomor">B-1070/Mak.20.01/PP.00.6/12/2025</td>
                    <td width="36%" align="right">Ende, <span contenteditable="true" id="edit_tanggal"><?= date('d F Y') ?></span></td>
                </tr>
                <tr><td>Lampiran</td><td>:</td><td contenteditable="true"><strong id="edit_lampiran">-</strong></td><td></td></tr>
                <tr><td>Perihal</td><td>:</td><td contenteditable="true"><strong id="edit_perihal">Undangan</strong></td><td></td></tr>
            </table>

            <div style="margin-top: 20px; margin-bottom: 10px;">
                <p>Kepada, <br>Yth.</p>
                <p contenteditable="true" id="edit_tujuan" style="font-weight: bold; ">
                    Bapak/Ibu Orang Tua Wali<br>
                    Siswa MAK Negeri Ende
                </p>
                <p>Di Tempat</p>
            </div>

            <div id="edit_isi" contenteditable="true" style="margin-top: 30px; min-height: 400px;">
                </div>

            <div style="margin-top: 50px; margin-left: 60%; text-align: center;">
                <p>Kepala MAK Negeri Ende</p>
                <div style="height: 80px;"></div>
                <strong style="text-decoration: underline;" contenteditable="true" id="edit_ttd">Abdul Wahab, S.Pd</strong>
            </div>
        </div>
    </div>
    

<form id="submitForm" method="POST" style="display:none;">
    <input type="hidden" name="id" id="val_id">
    <input type="hidden" name="nomor" id="val_nomor">
    <input type="hidden" name="perihal" id="val_perihal">
    <input type="hidden" name="lampiran" id="val_lampiran">
    <input type="hidden" name="tujuan" id="val_tujuan">
    <input type="hidden" name="isi" id="val_isi">
    <input type="hidden" name="tanggal" id="val_tanggal">
    <input type="hidden" name="ttd" id="val_ttd">
    <input type="submit" name="proses_surat" id="btnProses">
</form>

<!-- MODAL KIRIM SURAT -->
<div class="modal-kirim-backdrop" id="modalKirim">
    <div class="modal-kirim-box">
        <div class="modal-kirim-header">
            <h3><i class="fas fa-paper-plane text-primary"></i> Kirim Surat ke Wali</h3>
        </div>

        <div class="search-container">
            <input type="text" id="searchWali" class="modal-search-input" placeholder="Cari nama wali atau email..." onkeyup="filterWali()">
        </div>

        <form method="POST">
            <input type="hidden" name="id_surat" id="idSuratKirim">

            <div class="modal-kirim-body">
                <table class="list-table" id="tableWali">
                    <thead>
                        <tr>
                            <th width="40" style="text-align: center;">
                                <input type="checkbox" id="checkAll" class="custom-checkbox" onclick="toggleCheckAll(this)">
                            </th>
                            <th>Nama Wali</th>
                            <th>Email</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $orang_tua_query = mysqli_query($conn, "SELECT id, nama, email FROM orang_tua ORDER BY nama ASC");
                        while($ot = mysqli_fetch_assoc($orang_tua_query)){
                        ?>
                        <tr>
                            <td align="center">
                                <input type="checkbox" name="id_orangtua[]" value="<?= $ot['id'] ?>" class="custom-checkbox wali-checkbox">
                            </td>
                            <td class="wali-nama"><b><?= $ot['nama'] ?></b></td>
                            <td class="wali-email" style="color: #64748b;"><?= $ot['email'] ?></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <div class="modal-kirim-footer">
                <button type="button" onclick="closeModalKirim()" class="btn btn-light" style="border: 1px solid #e2e8f0;">Batal</button>
                <button type="submit" name="kirim_surat" class="btn btn-primary px-4" onclick="return validatePenerima()">
                    <i class="fas fa-paper-plane mr-2"></i> Kirim Sekarang
                </button>
            </div>
        </form>
    </div>
</div>
<!-- MODAL PREVIEW SURAT -->

<script>
   const tplUndangan = `
        <p>Assalamu’alaikum Warahmatullahi Wabarakaatuh</p>
        <p>Puji syukur kami panjatkan ke hadirat Allah SWT, karena masih diberi kesehatan dan kekuatan untuk melaksanakan tugas dan fungsi sebagai pendidik.</p>
        <p>Sehubungan dengan pembagian Laporan Hasil Belajar (LHB), kami mengundang Bapak/Ibu hadir pada :</p>
        <table style="margin-left: 20px;">
            <tr><td>Hari/tgl</td><td>: Senin, 22 Desember 2025</td></tr>
            <tr><td>Tempat</td><td>: Aula MAK Negeri Ende</td></tr>
        </table>
        <p>Demikian pemberitahuan ini kami sampaikan, atas perhatian dan kerjasamanya kami ucapkan terimakasih.</p>
        <p>Wassalamualaikum wr.wb.</p>
    `;
    const tplPemberitahuan = `
        <p>Assalamualaikum wr.wb.</p>
        <p>Bersama ini kami sampaikan hal-hal terkait libur Ramadhan siswa/i MAKN Ende tahun 2026 :</p>
        <ol>
            <li>Libur bulan suci Ramadhan bagi peserta didik MAKN Ende akan dimulai pada tanggal 01 Maret – 27 Maret 2026.</li>
            <li>Pada hari Ahad 01 Maret 2026 peserta didik MAKN Ende akan dipulangkan ke rumah masing-masing.</li>
            <li>Peserta didik MAKN Ende secara serentak diwajibkan kembali ke Madrasah pada hari Sabtu, 28 Maret 2026 sebelum sholat ashar.</li>
            <li>Siswa wajib membayar seluruh keuangan di Madrasah berupa uang komite, uang makan pada semester berjalan.</li>
        </ol>
        <p>Demikian pemberitahuan ini kami sampaikan, atas perhatian dan kerjasamanya kami ucapkan terimakasih.</p>
        <p>Wassalamualaikum wr.wb.</p>
    `;
    function openTemplateSelector() {
        document.getElementById('templateSelector').style.display = 'block';
    }

    function closeTemplateSelector() {
        document.getElementById('templateSelector').style.display = 'none';
    }

    function launchEditor(type) {
        closeTemplateSelector();
        let template = tplUndangan;
        if(type === 'pemberitahuan') {
            template = tplPemberitahuan;
        }
        document.getElementById('edit_isi').innerHTML = template;
        document.getElementById('editorModal').style.display = 'block';
    }

    // Fill the editor fields when a surat record is clicked for editing
    function editExistingSurat(data) {
        document.getElementById('edit_nomor').innerText     = data.nomor || '';
        document.getElementById('edit_perihal').innerText   = data.perihal || '';
        document.getElementById('edit_lampiran').innerText  = data.lampiran !== null && data.lampiran !== '' ? data.lampiran : '-';
        document.getElementById('edit_tujuan').innerText    = data.tujuan || '';
        document.getElementById('edit_isi').innerHTML        = data.isi || '';
        document.getElementById('edit_tanggal').innerText   = data.tanggal || '<?php echo date('d F Y'); ?>';
        document.getElementById('edit_ttd').innerText       = data.ttd || '';
        document.getElementById('editorModal').style.display = 'block';
    }

    function saveToDatabase() {
        document.getElementById('val_nomor').value = document.getElementById('edit_nomor').innerText;
        document.getElementById('val_perihal').value = document.getElementById('edit_perihal').innerText;
        document.getElementById('val_lampiran').value = document.getElementById('edit_lampiran').innerText;
        document.getElementById('val_tujuan').value = document.getElementById('edit_tujuan').innerText;
        document.getElementById('val_isi').value = document.getElementById('edit_isi').innerHTML;
        // tanggal akan diisi oleh server sebagai timestamp; tidak perlu mengambil dari editor
        document.getElementById('val_ttd').value = document.getElementById('edit_ttd').innerText;
        document.getElementById('btnProses').click();
    }

    function closeEditor() {
        document.getElementById('editorModal').style.display = 'none';
    }

    function submitSurat() {
        // Pindahkan dari contenteditable ke hidden input form
        document.getElementById('f_nomor').value = document.getElementById('e_nomor').innerText;
        document.getElementById('f_perihal').value = document.getElementById('e_perihal').innerText;
        document.getElementById('f_tujuan').value = document.getElementById('e_tujuan').innerText;
        document.getElementById('f_isi').value = document.getElementById('e_isi').innerText;
        document.getElementById('f_tanggal').value = document.getElementById('e_tanggal').innerText;
        document.getElementById('f_ttd').value = document.getElementById('e_ttd').innerText;
        
        document.getElementById('finalSubmit').click();
    }
    
    // Fungsi pembantu untuk modal kirim
    function openModalKirim(id){
        document.getElementById('idSuratKirim').value = id;
        document.getElementById('modalKirim').style.display = 'block';
    }
function openModal(){
    document.getElementById('idSurat').value = '';
    document.getElementById('formSurat').reset();
    document.getElementById('btnSimpan').name = 'simpan';
    document.getElementById('btnSimpan').textContent = 'Simpan Surat';
    document.getElementById('modalSurat').style.display='block';
}

function editSurat(id, nomor, perihal, tujuan, isi, tanggal, ttd){
    document.getElementById('idSurat').value = id;
    document.getElementById('nomor').value = nomor;
    document.getElementById('perihal').value = perihal;
    document.getElementById('tujuan').value = tujuan;
    document.getElementById('isi').value = isi;
    document.getElementById('tanggal').value = tanggal;
    document.getElementById('ttd').value = ttd;
    document.getElementById('btnSimpan').name = 'update';
    document.getElementById('btnSimpan').textContent = 'Update Surat';
    document.getElementById('modalSurat').style.display='block';
}

function hapusSurat(id){
    if(confirm('Yakin ingin menghapus surat ini?')){
        location='surat.php?hapus=' + id;
    }
}

function closeModal(){
    document.getElementById('modalSurat').style.display='none';
}
function openModalKirim(id){
    document.getElementById('idSuratKirim').value = id;
    document.getElementById('modalKirim').style.display = 'block';
}

function closeModalKirim(){
    document.getElementById('modalKirim').style.display = 'none';
}

function previewSurat(id, nomor, perihal, tujuan, isi, tanggal, ttd){
    document.getElementById('previewNomor').textContent = nomor;
    document.getElementById('previewLampiran').textContent = '';
    document.getElementById('previewPerihal').textContent = perihal;
    document.getElementById('previewTujuan').textContent = tujuan;
    document.getElementById('previewIsi').textContent = isi;
    document.getElementById('previewTanggal').textContent = tanggal;
    document.getElementById('previewTtd').textContent = ttd;
    document.getElementById('modalPreview').style.display = 'block';
}

function closeModalPreview(){
    document.getElementById('modalPreview').style.display = 'none';
}

function validatePenerima(){
    const checkboxes = document.querySelectorAll('input[name="id_orangtua[]"]:checked');
    if(checkboxes.length === 0) {
        alert('Silakan pilih minimal 1 orang tua');
        return false;
    }
    return true;
}

// Event listener untuk button edit
document.addEventListener('DOMContentLoaded', function(){
    // Event listener untuk button edit
    document.querySelectorAll('.btnEdit').forEach(btn => {
        btn.addEventListener('click', function(){
            const id = this.dataset.id;
            const nomor = this.dataset.nomor;
            const perihal = this.dataset.perihal;
            const tujuan = this.dataset.tujuan;
            const isi = this.dataset.isi;
            const tanggal = this.dataset.tanggal;
            const ttd = this.dataset.ttd;
            
            editSurat(id, nomor, perihal, tujuan, isi, tanggal, ttd);
        });
    });

    // Event listener untuk row surat (preview)
    document.querySelectorAll('.rowSurat').forEach(row => {
        row.addEventListener('click', function(){
            const id = this.dataset.id;
            const nomor = this.dataset.nomor;
            const perihal = this.dataset.perihal;
            const tujuan = this.dataset.tujuan;
            const isi = this.dataset.isi;
            const tanggal = this.dataset.tanggal;
            const ttd = this.dataset.ttd;
            
            previewSurat(id, nomor, perihal, tujuan, isi, tanggal, ttd);
        });
    });
});

</script>

</div>
</body>
</html>
