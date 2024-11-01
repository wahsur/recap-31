<?php
// Koneksi ke database
include '../config.php';

// Inisialisasi variabel pencarian
$search_query = '';

// Cek apakah ada pencarian yang dilakukan
if (isset($_GET['search'])) {
    $search_query = $_GET['search'];
    // Modifikasi query untuk mencari produk berdasarkan kode_barang
    $sql = "SELECT * FROM products WHERE kode_barang LIKE '%$search_query%'";
} else {
    // Jika tidak ada pencarian, tampilkan semua produk
    $sql = "SELECT * FROM products";
}

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Produk</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            text-align: center;
        }

        h1 {
            color: #333;
            margin-top: 20px;
        }

        .search-container {
            margin: 20px 0;
        }

        .search-container input[type="text"] {
            padding: 10px;
            width: 250px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }

        .search-container button {
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .search-container button:hover {
            background-color: #45a049;
        }

        .product-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            margin-top: 20px;
        }

        .product-card {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 10px;
            width: 300px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
            padding: 20px;
            position: relative;
        }

        .product-card:hover {
            transform: translateY(-10px);
        }

        .product-card img {
            width: 100%;
            height: auto;
            border-radius: 10px;
        }

        .product-details {
            text-align: left;
            margin-top: 10px;
        }

        .product-details h3 {
            margin: 10px 0;
            font-size: 18px;
            color: #333;
        }

        .product-details p {
            margin: 5px 0;
            color: #666;
        }

        .product-card form {
            margin-top: 10px;
            position: relative;
        }

        .product-card input[type="number"] {
            padding: 10px;
            width: 60px;
            border-radius: 5px;
            border: 1px solid #ddd;
            margin-right: 10px;
        }

        .product-card button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .product-card button:hover {
            background-color: #45a049;
        }

        .tooltip {
            position: absolute;
            background-color: #f44336;
            color: #fff;
            border-radius: 5px;
            padding: 5px;
            display: none;
            font-size: 14px;
            white-space: nowrap;
        }

        .popup {
            display: none;
            position: fixed;
            z-index: 9;
            padding: 20px;
            background-color: #fff;
            border: 1px solid #888;
            width: 300px;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .popup button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 5px;
        }
    </style>
</head>
<body>

<h1>Daftar Produk</h1>

<!-- Form pencarian produk -->
<div class="search-container">
    <form action="" method="GET">
        <input type="text" name="search" placeholder="Cari produk berdasarkan kode..." value="<?php echo $search_query; ?>">
        <button type="submit">Cari</button>
    </form>
</div>

<div class="product-container">
    <?php
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            echo '
            <div class="product-card">
                <img src="../uploads/' . $row['gambar'] . '" alt="Gambar Produk">
                <div class="product-details">
                    <h3>' . $row['nama_barang'] . '</h3>
                    <p>Kode Barang: ' . $row['kode_barang'] . '</p>
                    <p>Harga: Rp ' . number_format($row['harga'], 0, ',', '.') . '</p>
                    <p>Stok: ' . $row['stok'] . '</p>
                    <form onsubmit="return orderProduct(\'' . $row['kode_barang'] . '\', ' . $row['stok'] . ', this)" novalidate>
                        <input type="number" name="quantity" min="1" max="' . $row['stok'] . '" value="1" required>
                        <button type="submit">Pesan</button>
                        <div class="tooltip"></div>
                    </form>
                </div>
            </div>';
        }
    } else {
        echo "<p>Produk tidak ditemukan.</p>";
    }
    ?>
</div>

<!-- Pop-up konfirmasi pemesanan -->
<div id="orderPopup" class="popup">
    <p id="popupMessage">Produk berhasil dipesan!</p>
    <button onclick="closePopup()">Tutup</button>
</div>

<script>
    // Fungsi untuk memesan produk
    function orderProduct(kode_barang, stok, form) {
        // Ambil jumlah pesanan dari form yang dikirimkan
        const quantity = form.quantity.value;
        const tooltip = form.querySelector('.tooltip');

        // Validasi jumlah pesanan
        if (quantity > stok) {
            tooltip.textContent = 'Stok tidak mencukupi!';
            tooltip.style.display = 'block';
            return false;
        }

        // Kirim request AJAX untuk memproses pemesanan tanpa reload halaman
        fetch('order.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                kode_barang: kode_barang,
                quantity: quantity
            })
        })
        .then(response => response.text())
        .then(data => {
            // Tampilkan pop-up pemesanan berhasil
            document.getElementById('popupMessage').textContent = data;
            document.getElementById('orderPopup').style.display = 'block';
        })
        .catch(error => console.error('Error:', error));

        // Mencegah form submit dan reload halaman
        return false;
    }

    // Fungsi untuk menutup pop-up
    function closePopup() {
        document.getElementById('orderPopup').style.display = 'none';
    }
</script>

</body>
</html>
