<?php
include '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $_POST['nama'];
    $kontak = $_POST['kontak'];
    $nama_barang = $_POST['nama_barang'];
    $nomor_invoice = $_POST['nomor_invoice'];

    $stmt = $conn->prepare("INSERT INTO vendor (nama, kontak, nama_barang, nomor_invoice)
                            VALUES (:nama, :kontak, :nama_barang, :nomor_invoice)");
    $stmt->execute([
        'nama' => $nama,
        'kontak' => $kontak,
        'nama_barang' => $nama_barang,
        'nomor_invoice' => $nomor_invoice,
    ]);

    $message = "Vendor added successfully!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <title>Add Vendor</title>
</head>
<body>
    <div class="container">
        <h1 class="mt-4">Add Vendor</h1>

        <?php if (isset($message)): ?>
            <div class="alert alert-success">
                <?php echo $message; ?>
            </div>
        <?php endif;?>

        <form action="" method="POST">
            <div class="form-group">
                <label for="nama">Nama Vendor</label>
                <input type="text" name="nama" id="nama" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="kontak">Kontak Vendor</label>
                <input type="text" name="kontak" id="kontak" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="nama_barang">Nama Barang</label>
                <input type="text" name="nama_barang" id="nama_barang" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="nomor_invoice">Nomor Invoice</label>
                <input type="text" name="nomor_invoice" id="nomor_invoice" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Add Vendor</button>
        </form>
    </div>
</body>
</html>
