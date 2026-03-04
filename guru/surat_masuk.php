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
        <table width="100%" cellpadding="10">
            <tr>
                <th>Judul</th>
                <th>Dari</th>
                <th>Tanggal</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>

            <?php
            $q = mysqli_query($conn,"
                SELECT sp.id, sp.judul, u.nama, sp.tanggal, sp.status, sp.isi
                FROM surat_pribadi sp
                INNER JOIN users u ON sp.id_orangtua = u.id
                ORDER BY sp.id DESC
            ");
            
            if(mysqli_num_rows($q) > 0) {
                while($d=mysqli_fetch_assoc($q)){
                    echo "
                    <tr>
                        <td>{$d['judul']}</td>
                        <td>{$d['nama']}</td>
                        <td>{$d['tanggal']}</td>
                        <td>{$d['status']}</td>
                        <td>
                            <button type='button' class='btn btn-primary' onclick='lihatSuratMasuk(\"{$d['judul']}\", \"{$d['nama']}\", \"{$d['tanggal']}\", \"".htmlspecialchars($d['isi'])."\")'>
                                <i class='fas fa-eye'></i> Lihat
                            </button>
                        </td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='5' style='text-align:center; color:#999; padding:20px;'>Belum ada surat masuk</td></tr>";
            }
            ?>
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
