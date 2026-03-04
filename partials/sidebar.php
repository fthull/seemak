<style>
    /* Variabel Warna Berdasarkan Laman Register */
    :root {
        --seemak-primary: #1e7f5c;    /* Hijau Tua Register */
        --seemak-gradient-start: #1e7f5c;
        --seemak-gradient-end: #2fbf71;  /* Hijau Muda Register */
        --seemak-accent: #ffd700;       /* Emas untuk Active State */
        --sidebar-bg: #ffffff;
        --text-dark: #333333;
        --text-muted: #777777;
        --hover-bg: #f0fff4;            /* Hijau Sangat Muda untuk Hover */
        --transition-speed: 0.3s;
    }

    /* Reset Dasar untuk Sidebar */
    .sidebar {
        width: 270px;
        height: 100vh;
        background-color: var(--sidebar-bg);
        position: fixed;
        left: 0;
        top: 0;
        /* Shadow lembut senada dengan box di register */
        box-shadow: 4px 0 25px rgba(0,0,0,0.08);
        display: flex;
        flex-direction: column;
        font-family: 'Poppins', sans-serif; /* Menggunakan Poppins agar sama */
        z-index: 1000;
        overflow: hidden;
        border-top-right-radius: 20px;
        border-bottom-right-radius: 20px;
    }

    /* Header Sidebar: Menggunakan Gradien & Shadow Register */
    .sidebar-header {
        padding: 30px 25px;
        background: linear-gradient(135deg, var(--seemak-gradient-start), var(--seemak-gradient-end));
        color: white;
        text-align: center;
        border-bottom-right-radius: 20px;
        box-shadow: 0 4px 12px rgba(30, 127, 92, 0.2);
    }

    .sidebar-header i {
        font-size: 2.5rem;
        margin-bottom: 10px;
        display: block;
    }

    .sidebar-header h3 {
        margin: 0;
        font-size: 1.4rem;
        font-weight: 700;
        letter-spacing: 1px;
    }

    .sidebar-header p {
        margin: 5px 0 0;
        font-size: 0.75rem;
        opacity: 0.8;
        font-weight: 300;
    }

    /* Container Navigasi */
    .nav-container {
        flex: 1;
        overflow-y: auto;
        padding: 20px 15px;
    }

    /* Grouping Menu Label */
    .menu-label {
        padding: 15px 15px 5px;
        font-size: 0.7rem;
        text-transform: uppercase;
        color: var(--text-muted);
        font-weight: 600;
        letter-spacing: 1.5px;
    }

    /* Styling Link Navigasi */
    .nav-link {
        display: flex;
        align-items: center;
        padding: 14px 18px;
        color: var(--text-dark);
        text-decoration: none;
        border-radius: 10px; /* Rounded corners seperti input register */
        margin-bottom: 6px;
        font-weight: 500;
        font-size: 0.95rem;
        
        /* Animasi Transisi Halus */
        transition: all var(--transition-speed) ease;
        position: relative;
        left: 0;
    }

    /* Styling Ikon */
    .nav-link i {
        width: 30px;
        font-size: 1.1rem;
        margin-right: 12px;
        color: var(--seemak-primary);
        transition: transform var(--transition-speed) ease;
    }

    /* --- ANIMASI & EFEK HOVER --- */
    .nav-link:hover {
        background-color: var(--hover-bg);
        color: var(--seemak-primary);
        /* Efek sedikit bergeser ke kanan */
        left: 5px;
        box-shadow: 2px 4px 8px rgba(30, 127, 92, 0.05);
    }

    /* Ikon sedikit membesar saat hover */
    .nav-link:hover i {
        transform: scale(1.1);
    }

    /* --- STYLING ACTIVE STATE (Halaman Saat Ini) --- */
    .nav-link.active {
        background: linear-gradient(135deg, var(--seemak-gradient-start), var(--seemak-gradient-end));
        color: white;
        font-weight: 600;
        box-shadow: 0 4px 10px rgba(30, 127, 92, 0.3);
    }

    /* Warna ikon saat active */
    .nav-link.active i {
        color: var(--seemak-accent); /* Emas agar kontras */
    }

    /* Menghilangkan efek geser saat active (opsional) */
    .nav-link.active:hover {
        left: 0;
    }

    /* --- BAGIAN OUT --- */
    .logout-section {
        padding: 15px;
        border-top: 1px solid #eee;
    }

    .logout-link {
        color: #d32f2f !important; /* Warna Merah */
        background-color: #fff1f1;
    }
    
    .logout-link i {
        color: #d32f2f !important;
    }

    .logout-link:hover {
        background-color: #ffebee !important;
        box-shadow: 2px 4px 8px rgba(211, 47, 47, 0.1) !important;
    }

    /* Custom Scrollbar untuk Navigasi */
    .nav-container::-webkit-scrollbar {
        width: 5px;
    }
    .nav-container::-webkit-scrollbar-thumb {
        background: #eee;
        border-radius: 10px;
    }
</style>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<div class="sidebar">
    <div class="sidebar-header">
        <i class="fas fa-university"></i>
        <h3>SeeMAK</h3>
        <p>Madrasah Vokasi Kompeten</p>
    </div>

    <div class="nav-container">
        <div class="menu-label">Menu Utama</div>
        
        <a href="dashboard.php" class="nav-link <?= $active_page=='dashboard'?'active':'' ?>">
            <i class="fas fa-th-large"></i>
            <span>Dashboard</span>
        </a>

        <a href="surat.php" class="nav-link <?= $active_page=='surat'?'active':'' ?>">
            <i class="fas fa-paper-plane"></i>
            <span>Buat Surat</span>
        </a>

        <a href="surat_masuk.php" class="nav-link <?= $active_page=='surat_masuk'?'active':'' ?>">
            <i class="fas fa-inbox"></i>
            <span>Surat Masuk</span>
        </a>

        <a href="evaluasi.php" class="nav-link <?= $active_page=='evaluasi'?'active':'' ?>">
            <i class="fas fa-clipboard-check"></i>
            <span>Evaluasi</span>
        </a>

        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <div class="menu-label">Administrasi</div>
            
            <a href="../guru/guru.php" class="nav-link <?= $active_page=='guru'?'active':'' ?>">
                <i class="fas fa-user-tie"></i>
                <span>Kelola Guru</span>
            </a>

            <a href="../guru/siswa.php" class="nav-link <?= $active_page=='siswa'?'active':'' ?>">
                <i class="fas fa-user-graduate"></i>
                <span>Kelola Siswa</span>
            </a>
        <?php endif; ?>
    </div>

    <div class="logout-section">
        <a href="../logout.php" class="nav-link logout-link">
            <i class="fas fa-sign-out-alt"></i>
            <span>Keluar Sistem</span>
        </a>
    </div>
</div>