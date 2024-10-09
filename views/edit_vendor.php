<?php
include '../config/db.php';

// Mendapatkan ID vendor dari URL
$id = $_GET['id'] ?? '';

if (!$id) {
    echo "ID vendor tidak ditemukan!";
    exit;
}

// Mendapatkan data vendor berdasarkan ID
$stmt = $conn->prepare("SELECT * FROM vendor WHERE id = :id");
$stmt->execute(['id' => $id]);
$vendor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$vendor) {
    echo "Vendor tidak ditemukan!";
    exit;
}

// Proses update data vendor ketika form disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $_POST['nama'];
    $kontak = $_POST['kontak'];
    $nama_barang = $_POST['nama_barang'];
    $nomor_invoice = $_POST['nomor_invoice'];

    // Update vendor
    $stmt = $conn->prepare("UPDATE vendor SET nama = :nama, kontak = :kontak, nama_barang = :nama_barang, nomor_invoice = :nomor_invoice WHERE id = :id");
    $stmt->execute([
        'nama' => $nama,
        'kontak' => $kontak,
        'nama_barang' => $nama_barang,
        'nomor_invoice' => $nomor_invoice,
        'id' => $id,
    ]);

    // Update nama barang di tabel inventory
    $stmt = $conn->prepare("UPDATE inventory SET nama_barang = :nama_barang WHERE vendor_id = :vendor_id");
    $stmt->execute([
        'nama_barang' => $nama_barang,
        'vendor_id' => $id, // Menggunakan ID vendor sebagai vendor_id dalam inventory
    ]);

    $message = "Vendor dan nama barang berhasil diperbarui!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <title>Edit Vendor</title>
</head>
<body>
    <div class="container">
        <h1 class="mt-4">Edit Vendor</h1>

        <!-- Menampilkan pesan sukses jika update berhasil -->
        <?php if (isset($message)): ?>
            <div class="alert alert-success">
                <?php echo $message; ?>
            </div>
        <?php endif;?>

        <!-- Form untuk mengedit vendor -->
        <form action="" method="POST">
            <div class="form-group">
                <label for="nama">Nama Vendor</label>
                <input type="text" name="nama" id="nama" class="form-control" value="<?=htmlspecialchars($vendor['nama'])?>" required>
            </div>
            <div class="form-group">
                <label for="kontak">Kontak Vendor</label>
                <input type="text" name="kontak" id="kontak" class="form-control" value="<?=htmlspecialchars($vendor['kontak'])?>" required>
            </div>
            <div class="form-group">
                <label for="nama_barang">Nama Barang</label>
                <input type="text" name="nama_barang" id="nama_barang" class="form-control" value="<?=htmlspecialchars($vendor['nama_barang'])?>" required>
            </div>
            <div class="form-group">
                <label for="nomor_invoice">Nomor Invoice</label>
                <input type="text" name="nomor_invoice" id="nomor_invoice" class="form-control" value="<?=htmlspecialchars($vendor['nomor_invoice'])?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Update Vendor</button>
        </form>
    </div>
</body>
</html>
