<?php
include '../auth/auth.php';
checkAuth();
include '../config/db.php';

$message = "";

// Proses hapus inventory dengan validasi
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    // Cek apakah ID inventory ada
    $check_stmt = $conn->prepare("SELECT * FROM inventory WHERE id = :id");
    $check_stmt->execute(['id' => $id]);
    $inventory_exists = $check_stmt->fetch();

    if ($inventory_exists) {
        // Hapus inventory
        $stmt = $conn->prepare("DELETE FROM inventory WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $message = "Inventory deleted successfully!";
    } else {
        $message = "Inventory item not found!";
    }
}

// Search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Modify SQL query to include search functionality
$stmt = $conn->prepare("SELECT inventory.id, inventory.nama_barang, inventory.jenis_barang, inventory.kuantitas_stok,
                        inventory.lokasi_gudang, inventory.barcode, inventory.harga, vendor.nama AS vendor_nama
                        FROM inventory
                        JOIN vendor ON inventory.vendor_id = vendor.id
                        WHERE (inventory.nama_barang LIKE :search
                        OR inventory.barcode LIKE :search
                        OR inventory.jenis_barang LIKE :search
                        OR inventory.lokasi_gudang LIKE :search
                        OR inventory.kuantitas_stok LIKE :search
                        OR vendor.nama LIKE :search
                        )");
$stmt->execute(['search' => '%' . $search . '%']);

$inventories = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <title>Inventory List</title>
</head>
<body>

<?php include '../partials/header.php';?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-3">
            <?php include '../partials/sidebar.php';?>
        </div>
        <div class="col-md-9">
            <h1 class="mt-4">Inventory List</h1>

            <?php if (!empty($message)): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif;?>

            <!-- Search Form -->
            <form method="GET" class="form-inline mb-3">
                <div class="form-group mr-2">
                    <input type="text" name="search" class="form-control" placeholder="Search by Name or Barcode" value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <button type="submit" class="btn btn-primary">Search</button>
            </form>

            <div class="card mt-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Inventory List</h5>
                    <!-- Add Inventory Button -->
                    <a href="add_inventory.php" class="btn btn-success">Add Inventory</a>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nama Barang</th>
                                <th>Jenis Barang</th>
                                <th>Kuantitas Stok</th>
                                <th>Lokasi Gudang</th>
                                <th>Harga</th>
                                <th>Barcode</th>
                                <th>Vendor</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
    <?php if (count($inventories) > 0): ?>
        <?php
// Inisialisasi variabel untuk penomoran
$i = 1;
foreach ($inventories as $inventory):
    // Cek jika kuantitas stok adalah 0
    $is_out_of_stock = ($inventory['kuantitas_stok'] == 0);
    ?>
						            <tr class="<?php echo $is_out_of_stock ? 'table-danger' : ''; ?>"> <!-- Beri class 'table-danger' jika stok habis -->
						                <td><?php echo $i++; ?></td> <!-- Ganti ID dengan penomoran -->
						                <td><?php echo htmlspecialchars($inventory['nama_barang']); ?></td>
						                <td><?php echo htmlspecialchars($inventory['jenis_barang']); ?></td>
						                <td><?php echo htmlspecialchars($inventory['kuantitas_stok']); ?></td>
						                <td><?php echo htmlspecialchars($inventory['lokasi_gudang']); ?></td>
						                <td>Rp. <?php echo number_format($inventory['harga'], 0, ',', '.'); ?></td>
						                <td><?php echo htmlspecialchars($inventory['barcode']); ?></td>
						                <td><?php echo htmlspecialchars($inventory['vendor_nama']); ?></td>
						                <td>
						                    <a href="edit_inventory.php?id=<?php echo htmlspecialchars($inventory['id']); ?>" class="btn btn-warning btn-sm">Edit</a>
						                    <a href="?delete=<?php echo htmlspecialchars($inventory['id']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this item?');">Delete</a>
						                </td>
						            </tr>
						            <!-- Tampilkan alert jika stok habis -->
						            <?php if ($is_out_of_stock): ?>
						                <tr>
						                    <td colspan="9">
						                        <div class="alert alert-danger">
						                            <?php echo "Warning: Stok barang '" . htmlspecialchars($inventory['nama_barang']) . "' habis!"; ?>
						                        </div>
						                    </td>
						                </tr>
						            <?php endif;?>
        <?php endforeach;?>
    <?php else: ?>
        <tr>
            <td colspan="9" class="text-center">No inventory items found.</td>
        </tr>
    <?php endif;?>
</tbody>


                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../partials/footer.php';?>
</body>
</html>
