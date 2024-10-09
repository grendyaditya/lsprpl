<?php
include '../auth/auth.php';
checkAuth();
include '../config/db.php';

// Mengaktifkan error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

$vendor_name = $_POST['vendor_name'] ?? '';
$nama_barang_options = [];
$storage_units = [];

// Mengambil data storage unit
$stmt = $conn->query("SELECT id, nama_gudang FROM storage_unit");
$storage_units = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Cek jika form disubmit untuk menambah inventory
    if (isset($_POST['submit_inventory'])) {
        $stmt = $conn->prepare("SELECT id FROM vendor WHERE nama = :vendor_name");
        $stmt->execute(['vendor_name' => $vendor_name]);
        $vendor = $stmt->fetch();

        if ($vendor) {
            // Dapatkan nama_gudang berdasarkan storage_unit_id yang dipilih
            $stmt = $conn->prepare("SELECT nama_gudang FROM storage_unit WHERE id = :storage_unit_id");
            $stmt->execute(['storage_unit_id' => $_POST['storage_unit_id']]);
            $storage_unit = $stmt->fetch();

            if ($storage_unit) {
                // Masukkan data inventory dengan lokasi_gudang diambil dari storage_unit
                $stmt = $conn->prepare("INSERT INTO inventory (vendor_id, nama_barang, jenis_barang, kuantitas_stok, storage_unit_id, lokasi_gudang, harga, barcode)
                                        VALUES (:vendor_id, :nama_barang, :jenis_barang, :kuantitas_stok, :storage_unit_id, :lokasi_gudang, :harga, :barcode)");
                $stmt->execute([
                    'vendor_id' => $vendor['id'],
                    'nama_barang' => $_POST['nama_barang'],
                    'jenis_barang' => $_POST['jenis_barang'],
                    'kuantitas_stok' => $_POST['kuantitas_stok'],
                    'storage_unit_id' => $_POST['storage_unit_id'],
                    'lokasi_gudang' => $storage_unit['nama_gudang'], // Simpan nama_gudang sebagai lokasi_gudang
                    'harga' => $_POST['harga'],
                    'barcode' => $_POST['barcode'],
                ]);
                echo "Inventory berhasil ditambahkan!";
            } else {
                echo "Storage unit tidak ditemukan!";
            }
        } else {
            echo "Vendor tidak ditemukan!";
        }
    }

    // Mengambil nama barang dari vendor yang dipilih
    if (!empty($vendor_name)) {
        $stmt = $conn->prepare("SELECT DISTINCT nama_barang FROM vendor WHERE nama = :vendor_name");
        $stmt->execute(['vendor_name' => $vendor_name]);
        $nama_barang_options = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <title>Add Inventory</title>
</head>
<body>

<?php include '../partials/header.php';?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-3">
            <?php include '../partials/sidebar.php';?>
        </div>
        <div class="col-md-9">
            <h1 class="mt-4">Add Inventory</h1>

            <!-- Form untuk menambahkan inventory -->
            <form method="POST">
                <div class="form-group">
                    <label for="vendor_name">Vendor</label>
                    <select name="vendor_name" id="vendor_name" class="form-control" onchange="this.form.submit()">
                        <option value="">Pilih Vendor</option>
                        <?php
// Mengambil daftar vendor
foreach ($conn->query("SELECT DISTINCT nama FROM vendor") as $vendor) {
    $selected = ($vendor['nama'] == $vendor_name) ? 'selected' : '';
    echo "<option value='" . htmlspecialchars($vendor['nama']) . "' $selected>" . htmlspecialchars($vendor['nama']) . "</option>";
}
?>
                    </select>
                </div>

                <!-- Dropdown nama barang hanya muncul jika vendor dipilih -->
                <?php if ($nama_barang_options): ?>
                    <div class="form-group">
                        <label for="nama_barang">Nama Barang</label>
                        <select name="nama_barang" id="nama_barang" class="form-control">
                            <?php
foreach ($nama_barang_options as $option) {
    echo "<option value='" . htmlspecialchars($option['nama_barang']) . "'>" . htmlspecialchars($option['nama_barang']) . "</option>";
}
?>
                        </select>
                    </div>
                <?php endif;?>

                <div class="form-group">
                    <label for="jenis_barang">Jenis Barang</label>
                    <input type="text" name="jenis_barang" class="form-control" required value="<?=htmlspecialchars($_POST['jenis_barang'] ?? '')?>">
                </div>
                <div class="form-group">
                    <label for="kuantitas_stok">Kuantitas Stok</label>
                    <input type="number" name="kuantitas_stok" class="form-control" required value="<?=htmlspecialchars($_POST['kuantitas_stok'] ?? '')?>">
                </div>

                <!-- Dropdown untuk memilih storage unit -->
                <div class="form-group">
                    <label for="storage_unit_id">Lokasi Gudang</label>
                    <select name="storage_unit_id" class="form-control" required>
                        <?php foreach ($storage_units as $storage_unit): ?>
                            <option value="<?php echo $storage_unit['id']; ?>" <?php echo (isset($_POST['storage_unit_id']) && $_POST['storage_unit_id'] == $storage_unit['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($storage_unit['nama_gudang']); ?>
                            </option>
                        <?php endforeach;?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="harga">Harga</label>
                    <input type="number" name="harga" class="form-control" required value="<?=htmlspecialchars($_POST['harga'] ?? '')?>">
                </div>
                <div class="form-group">
                    <label for="barcode">Barcode</label>
                    <input type="text" name="barcode" class="form-control" required value="<?=htmlspecialchars($_POST['barcode'] ?? '')?>">
                </div>
                <button type="submit" name="submit_inventory" class="btn btn-primary">Add Inventory</button>
            </form>
        </div>
    </div>
</div>

<?php include '../partials/footer.php';?>
</body>
</html>
