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

// KIRIM SURAT
if(isset($_POST['kirim_surat'])) {
    $id_surat = $_POST['id_surat'];
    $id_orangtua_list = $_POST['id_orangtua'] ?? [];

    if(count($id_orangtua_list) == 0){
        echo "<script>alert('Pilih minimal 1 orang tua');</script>";
    } else {
        // Insert ke tabel surat_tujuan untuk setiap orang tua yang dipilih
        $success = true;
        foreach($id_orangtua_list as $id_ortu){
            $query = "INSERT INTO surat_tujuan (id_surat, id_orangtua, status, tanggal) 
                      VALUES ('$id_surat', '$id_ortu', 'terkirim', NOW())";
            if(!mysqli_query($conn, $query)) {
                $success = false;
                break;
            }
        }

        if($success) {
            // Update status surat menjadi terkirim
            mysqli_query($conn, "UPDATE surat SET status='terkirim' WHERE id='$id_surat'");
            echo "<script>alert('Surat berhasil dikirim ke ".count($id_orangtua_list)." orang tua');location='surat.php';</script>";
        } else {
            echo "<script>alert('Terjadi kesalahan saat mengirim surat. Pastikan orang tua yang dipilih valid.');</script>";
        }
    }
}


// SIMPAN SURAT BARU
if (isset($_POST['simpan'])) {
    $nomor    = $_POST['nomor'];
    $perihal  = $_POST['perihal'];
    $tujuan   = $_POST['tujuan'];
    $isi      = $_POST['isi'];
    $tanggal  = $_POST['tanggal'];
    $ttd      = $_POST['ttd'];
    $judul    = $_POST['perihal'];

    mysqli_query($conn, "
        INSERT INTO surat 
        (nomor, perihal, tujuan, isi, tanggal, ttd, judul)
        VALUES 
        ('$nomor','$perihal','$tujuan','$isi','$tanggal','$ttd','$judul')
    ");

    echo "<script>alert('Surat berhasil dibuat'); location='surat.php';</script>";
}

// UPDATE SURAT
if(isset($_POST['update'])) {
    $id       = $_POST['id'];
    $nomor    = $_POST['nomor'];
    $perihal  = $_POST['perihal'];
    $tujuan   = $_POST['tujuan'];
    $isi      = $_POST['isi'];
    $tanggal  = $_POST['tanggal'];
    $ttd      = $_POST['ttd'];
    $judul    = $_POST['perihal'];

    mysqli_query($conn, "
        UPDATE surat 
        SET nomor='$nomor', perihal='$perihal', tujuan='$tujuan', 
            isi='$isi', tanggal='$tanggal', ttd='$ttd', judul='$judul'
        WHERE id='$id'
    ");

    echo "<script>alert('Surat berhasil diupdate'); location='surat.php';</script>";
}
?>


<div class="app-content">
    <h2>📄 Surat Sekolah</h2>
    <p>Kelola dan buat surat resmi madrasah</p>

    <button class="btn btn-primary" onclick="openModal()">
    <i class="fas fa-file-alt"></i> Buat Surat
</button>


    <div class="card-box">
        <table width="100%" cellpadding="10">
            <tr>
                <th>Judul</th>
                <th>Tujuan</th>
                <th>Tanggal</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>

            <?php
            $q = mysqli_query($conn,"
                SELECT * FROM surat
                ORDER BY id DESC
            ");           
            if(mysqli_num_rows($q) > 0) {
            while($d=mysqli_fetch_assoc($q)){
                $nomor = htmlspecialchars($d['nomor']);
                $perihal = htmlspecialchars($d['perihal']);
                $tujuan = htmlspecialchars($d['tujuan']);
                $isi = htmlspecialchars($d['isi']);
                $tanggal = htmlspecialchars($d['tanggal']);
                $ttd = htmlspecialchars($d['ttd']);
                
                echo "
                <tr class='rowSurat' data-id='{$d['id']}' data-nomor='$nomor' data-perihal='$perihal' data-tujuan='$tujuan' data-isi='$isi' data-tanggal='$tanggal' data-ttd='$ttd' style='cursor:pointer;'>
                    <td>{$d['judul']}</td>
                    <td>{$d['tujuan']}</td>
                    <td>{$d['tanggal']}</td>
                    <td>{$d['status']}</td>
                    <td onclick='event.stopPropagation()'>
                        <button type='button' class='btn btn-primary' onclick='openModalKirim({$d['id']})'>
                            <i class='fas fa-paper-plane'></i>
                        </button>
                        <button type='button' class='btn btn-success btnEdit' data-id='{$d['id']}' data-nomor='$nomor' data-perihal='$perihal' data-tujuan='$tujuan' data-isi='$isi' data-tanggal='$tanggal' data-ttd='$ttd'>
                            <i class='fas fa-edit'></i>
                        </button>
                        <button type='button' class='btn btn-danger' onclick='hapusSurat({$d['id']})'>
                            <i class='fas fa-trash'></i>
                        </button>
                    </td>
                </tr>";
            }} else {
                echo "<tr><td colspan='5' style='text-align:center; color:#999; padding:20px;'>Belum ada surat</td></tr>";
            }
            ?>
        </table>
    </div>
</div>

<!-- MODAL BUAT SURAT -->
<div class="modal" id="modalSurat">
    <div class="modal-box surat">

        <!-- KOP SURAT -->
        <div class="kop">
            <h3>KEMENTERIAN AGAMA REPUBLIK INDONESIA</h3>
            <h4>MADRASAH ALIYAH KEJURUAN NEGERI ENDE</h4>
            <p>Alamat Madrasah · Telp · Email</p>
            <hr>
        </div>

        <form method="POST" id="formSurat">

        <input type="hidden" id="idSurat" name="id">

        <!-- NOMOR SURAT -->
        <table width="100%">
            <tr>
                <td width="60%">
                    Nomor : <input type="text" id="nomor" name="nomor" required><br>
                    Lampiran : <input type="text" id="lampiran" name="lampiran"><br>
                    Perihal : <input type="text" id="perihal" name="perihal" required>
                </td>
                <td align="right">
                    Ende, <input type="date" id="tanggal" name="tanggal" required>
                </td>
            </tr>
        </table>

        <br>

        <!-- TUJUAN -->
        <p>
            Kepada Yth,<br>
            <b><input type="text" id="tujuan" name="tujuan" placeholder="Orang Tua/Wali Siswa" required></b><br>
            Di Tempat
        </p>

        <br>

        <!-- ISI -->
        <textarea id="isi" name="isi" rows="8" required>
Dengan hormat,

Sehubungan dengan ...
        </textarea>

        <br><br>

        <!-- PENUTUP -->
        <p>Demikian surat ini kami sampaikan. Atas perhatian Bapak/Ibu kami ucapkan terima kasih.</p>

        <br><br>

        <!-- TTD -->
        <div style="text-align:right">
            <p>Hormat Kami,</p>
            <br><br>
            <b>Kepala Madrasah</b><br>
            <u><input type="text" id="ttd" name="ttd" placeholder="Nama Kepala Madrasah" required></u>
        </div>

        <br>

        <button type="submit" name="simpan" class="btn btn-success" id="btnSimpan">Simpan Surat</button>
        <button type="button" onclick="closeModal()" class="btn btn-secondary">Batal</button>

        </form>
    </div>
</div>

<!-- MODAL KIRIM SURAT -->
<div class="modal" id="modalKirim">
    <div class="modal-box" style="max-width:700px; max-height:80vh; overflow-y:auto;">
        <h3>📧 Kirim Surat ke Wali</h3>

        <form method="POST">
            <input type="hidden" name="id_surat" id="idSuratKirim">

            <table width="100%" cellpadding="10" border="1" style="border-collapse:collapse">
                <tr style="background:#f0f0f0">
                    <th>Pilih</th>
                    <th>Nama Wali</th>
                    <th>Email</th>
                </tr>

                <?php
                $orang_tua_query = mysqli_query($conn, "SELECT id, nama, email FROM orang_tua");
                while($ot = mysqli_fetch_assoc($orang_tua_query)){
                    echo "
                    <tr>
                        <td align='center'>
                            <input type='checkbox' name='id_orangtua[]' value='{$ot['id']}'>
                        </td>
                        <td>{$ot['nama']}</td>
                        <td>{$ot['email']}</td>
                    </tr>";
                }
                ?>
            </table>

            <br>

            <button type="submit" name="kirim_surat" class="btn btn-primary" onclick="return validatePenerima()">
                <i class="fas fa-paper-plane"></i> Kirim Surat
            </button>
            <button type="button" onclick="closeModalKirim()" class="btn btn-secondary">
                Batal
            </button>
        </form>
    </div>
</div>

<!-- MODAL PREVIEW SURAT -->
<div class="modal" id="modalPreview">
    <div class="modal-box surat" style="max-width:800px; max-height:90vh; overflow-y:auto;">
        <h3 style="text-align:right; margin-top:-10px;">
            <button type="button" onclick="closeModalPreview()" style="background:none; border:none; font-size:24px; cursor:pointer; color:#999;">&times;</button>
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
                    <strong>Lampiran :</strong> <span id="previewLampiran"></span><br>
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

        <button type="button" onclick="closeModalPreview()" class="btn btn-secondary">Tutup</button>
    </div>
</div>

<script>
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
