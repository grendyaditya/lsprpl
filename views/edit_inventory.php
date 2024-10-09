<?php
include '../auth/auth.php';
checkAuth();
include '../config/db.php';

// Mengaktifkan error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

$id = $_GET['id'] ?? '';
$inventory = [];
$vendor_name = '';
$nama_barang_options = [];
$storage_units = [];

// Mengambil data storage unit
$stmt = $conn->query("SELECT id, nama_gudang FROM storage_unit");
$storage_units = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Mengambil data inventory yang akan diedit berdasarkan ID
if ($id) {
    $stmt = $conn->prepare("SELECT inventory.*, vendor.nama AS vendor_nama FROM inventory
                            JOIN vendor ON inventory.vendor_id = vendor.id WHERE inventory.id = :id");
    $stmt->execute(['id' => $id]);
    $inventory = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($inventory) {
        $vendor_name = $inventory['vendor_nama'];

        // Mengambil nama barang dari vendor yang dipilih
        $stmt = $conn->prepare("SELECT DISTINCT nama_barang FROM vendor WHERE nama = :vendor_name");
        $stmt->execute(['vendor_name' => $vendor_name]);
        $nama_barang_options = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        echo "Inventory tidak ditemukan!";
        exit;
    }

}

// Handle form submission untuk mengupdate data inventory
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $conn->prepare("SELECT id FROM vendor WHERE nama = :vendor_name");
    $stmt->execute(['vendor_name' => $_POST['vendor_name']]); // Mengambil vendor_name dari input hidden
    $vendor = $stmt->fetch();

    if ($vendor) {
        // Dapatkan nama_gudang berdasarkan storage_unit_id yang dipilih
        $stmt = $conn->prepare("SELECT nama_gudang FROM storage_unit WHERE id = :storage_unit_id");
        $stmt->execute(['storage_unit_id' => $_POST['storage_unit_id']]);
        $storage_unit = $stmt->fetch();

        if ($storage_unit) {
            // Update data inventory dengan lokasi_gudang diambil dari storage_unit
            $stmt = $conn->prepare("UPDATE inventory SET vendor_id = :vendor_id, nama_barang = :nama_barang,
                                    jenis_barang = :jenis_barang, kuantitas_stok = :kuantitas_stok,
                                    storage_unit_id = :storage_unit_id, lokasi_gudang = :lokasi_gudang, harga = :harga,
                                    barcode = :barcode WHERE id = :id");
            $stmt->execute([
                'vendor_id' => $vendor['id'],
                'nama_barang' => $_POST['nama_barang'], // Mengambil nama_barang dari input hidden
                'jenis_barang' => $_POST['jenis_barang'],
                'kuantitas_stok' => $_POST['kuantitas_stok'],
                'storage_unit_id' => $_POST['storage_unit_id'],
                'lokasi_gudang' => $storage_unit['nama_gudang'], // Ambil nama gudang dari storage_unit
                'harga' => $_POST['harga'],
                'barcode' => $_POST['barcode'],
                'id' => $id,
            ]);
            echo "Inventory berhasil diupdate!";
        } else {
            echo "Storage unit tidak ditemukan!";
        }
    } else {
        echo "Vendor tidak ditemukan!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <title>Edit Inventory</title>
</head>
<body>

<?php include '../partials/header.php';?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-3">
            <?php include '../partials/sidebar.php';?>
        </div>
        <div class="col-md-9">
            <h1 class="mt-4">Edit Inventory</h1>

            <!-- Form untuk mengedit inventory -->
            <form method="POST">
                <div class="form-group">
                    <label for="vendor_name">Vendor</label>
                    <!-- Tampilkan vendor name sebagai teks, bukan dropdown -->
                    <input type="text" class="form-control" value="<?=htmlspecialchars($vendor_name)?>" disabled>
                    <!-- Input hidden untuk mengirim vendor_name -->
                    <input type="hidden" name="vendor_name" value="<?=htmlspecialchars($vendor_name)?>">
                </div>

                <div class="form-group">
                    <label for="nama_barang">Nama Barang</label>
                    <!-- Tampilkan nama barang sebagai teks, bukan dropdown -->
                    <input type="text" class="form-control" value="<?=htmlspecialchars($inventory['nama_barang'])?>" disabled>
                    <!-- Input hidden untuk mengirim nama_barang -->
                    <input type="hidden" name="nama_barang" value="<?=htmlspecialchars($inventory['nama_barang'])?>">
                </div>

                <div class="form-group">
                    <label for="jenis_barang">Jenis Barang</label>
                    <input type="text" name="jenis_barang" class="form-control" required value="<?=htmlspecialchars($inventory['jenis_barang'] ?? '')?>">
                </div>
                <div class="form-group">
                    <label for="kuantitas_stok">Kuantitas Stok</label>
                    <input type="number" name="kuantitas_stok" class="form-control" required value="<?=htmlspecialchars($inventory['kuantitas_stok'] ?? '')?>">
                </div>

                <!-- Dropdown untuk memilih storage unit -->
                <div class="form-group">
                    <label for="storage_unit_id">Lokasi Gudang</label>
                    <select name="storage_unit_id" class="form-control" required>
                        <?php foreach ($storage_units as $storage_unit): ?>
                            <option value="<?php echo $storage_unit['id']; ?>" <?php echo ($inventory['storage_unit_id'] == $storage_unit['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($storage_unit['nama_gudang']); ?>
                            </option>
                        <?php endforeach;?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="harga">Harga</label>
                    <input type="number" name="harga" class="form-control" required value="<?=htmlspecialchars($inventory['harga'] ?? '')?>">
                </div>
                <div class="form-group">
    <label for="barcode">Barcode</label>
    <!-- Tampilkan barcode sebagai teks, bukan input field -->
    <input type="text" class="form-control" value="<?=htmlspecialchars($inventory['barcode'] ?? '')?>" disabled>
    <!-- Input hidden untuk mengirim barcode -->
    <input type="hidden" name="barcode" value="<?=htmlspecialchars($inventory['barcode'] ?? '')?>">
</div>

                <button type="submit" class="btn btn-primary">Update Inventory</button>
            </form>
        </div>
    </div>
</div>

<?php include '../partials/footer.php';?>
</body>
</html>
