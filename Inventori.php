<?php
// 1. PENGATURAN ERROR (Agar tidak muncul layar putih polos jika ada salah ketik)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ==========================================
// 2. KONEKSI & PEMBUATAN DATABASE OTOMATIS (Query.sql & Koneksidb.php)
// ==========================================
$host = "localhost";
$user = "root"; // Sesuaikan dengan username XAMPP Anda
$pass = "";     // Sesuaikan dengan password XAMPP Anda
$db   = "db_inventori_lab";

// Koneksi awal ke MySQL
$koneksi = mysqli_connect($host, $user, $pass);
if (!$koneksi) {
    die("Koneksi ke MySQL Gagal: " . mysqli_connect_error());
}

// Buat Database jika belum ada
$sql_db = "CREATE DATABASE IF NOT EXISTS $db";
mysqli_query($koneksi, $sql_db);

// Pilih Database
mysqli_select_db($koneksi, $db);

// Buat Tabel Barang jika belum ada
$sql_tabel = "CREATE TABLE IF NOT EXISTS barang (
    id_barang INT(11) AUTO_INCREMENT PRIMARY KEY,
    kode_barang VARCHAR(20) NOT NULL,
    nama_barang VARCHAR(100) NOT NULL,
    spesifikasi TEXT,
    jumlah INT(11) NOT NULL,
    kondisi ENUM('Baik', 'Rusak', 'Perbaikan') NOT NULL
)";
mysqli_query($koneksi, $sql_tabel);


// ==========================================
// 3. LOGIKA INPUT DATA (Tambahmhs.php / Tambahbarang.php)
// ==========================================
$pesan = "";
if (isset($_POST['submit'])) {
    $kode    = $_POST['kode_barang'];
    $nama    = $_POST['nama_barang'];
    $spek    = $_POST['spesifikasi'];
    $jumlah  = $_POST['jumlah'];
    $kondisi = $_POST['kondisi'];

    // Query Insert
    $query_insert = "INSERT INTO barang (kode_barang, nama_barang, spesifikasi, jumlah, kondisi) 
                     VALUES ('$kode', '$nama', '$spek', '$jumlah', '$kondisi')";

    if (mysqli_query($koneksi, $query_insert)) {
        // Redirect ke halaman ini sendiri agar tidak double input saat di-refresh
        header("Location: " . $_SERVER['PHP_SELF'] . "?status=sukses");
        exit();
    } else {
        $pesan = "<div class='alert-error'>Gagal menyimpan data: " . mysqli_error($koneksi) . "</div>";
    }
}

// Cek status setelah redirect
if (isset($_GET['status']) && $_GET['status'] == 'sukses') {
    $pesan = "<div class='alert-sukses'>✅ Data inventori berhasil disimpan!</div>";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Sistem Inventori Laboratorium Komputer</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; background-color: #f9f9f9; color: #333; }
        h2 { color: #2C3E50; border-bottom: 2px solid #2C3E50; padding-bottom: 8px; }
        .container { display: flex; gap: 30px; }
        .form-box { flex: 1; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .tabel-box { flex: 2; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        
        /* Form Styling */
        label { display: block; margin-top: 10px; font-weight: bold; }
        input[type=text], input[type=number], select, textarea { width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        input[type=submit] { width: 100%; background-color: #27AE60; color: white; padding: 12px; margin-top: 15px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; font-weight: bold; }
        input[type=submit]:hover { background-color: #219653; }
        
        /* Tabel Styling */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #34495E; color: white; }
        tr:nth-child(even) { background-color: #f2f2f2; }
        
        /* Alert */
        .alert-sukses { background-color: #D4EDDA; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
        .alert-error { background-color: #F8D7DA; color: #721C24; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
    </style>
</head>
<body>

    <center><h2>SISTEM INVENTORI LABORATORIUM KOMPUTER</h2></center>
    
    <?php echo $pesan; ?>

    <div class="container">
        
        <div class="form-box">
            <h3>Tambah Data Barang</h3>
            <form method="POST" action="">
                <label>Kode Barang:</label>
                <input type="text" name="kode_barang" placeholder="Contoh: LAB-PC-01" required>

                <label>Nama Barang:</label>
                <input type="text" name="nama_barang" placeholder="Contoh: Monitor LG 24 Inch" required>

                <label>Spesifikasi / Detail:</label>
                <textarea name="spesifikasi" rows="3" placeholder="Contoh: Core i5, RAM 8GB, SSD 256GB" required></textarea>

                <label>Jumlah:</label>
                <input type="number" name="jumlah" min="1" required>

                <label>Kondisi Barang:</label>
                <select name="kondisi" required>
                    <option value="Baik">Baik</option>
                    <option value="Rusak">Rusak</option>
                    <option value="Perbaikan">Perbaikan</option>
                </select>

                <input type="submit" name="submit" value="Simpan ke Database">
            </form>
        </div>

        <div class="tabel-box">
            <h3>Daftar Inventori yang Tersimpan</h3>
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode</th>
                        <th>Nama Barang</th>
                        <th>Spesifikasi</th>
                        <th>Jumlah</th>
                        <th>Kondisi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Query Select Data
                    $query_select = "SELECT * FROM barang ORDER BY id_barang DESC";
                    $result = mysqli_query($koneksi, $query_select);
                    $no = 1;

                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo "<tr>";
                            echo "<td>" . $no++ . "</td>";
                            echo "<td>" . htmlspecialchars($row['kode_barang']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['nama_barang']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['spesifikasi']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['jumlah']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['kondisi']) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6' style='text-align:center; color: #777;'>Belum ada data inventori.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

    </div>

</body>
</html>
<?php
// Tutup Koneksi di akhir file
mysqli_close($koneksi);
?>