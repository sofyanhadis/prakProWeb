<?php
session_start();
require 'koneksi.php';
/*
// Ambil data menu dari database
$menu = [];
$result = $conn->query("SELECT * FROM menu");

if ($result) {
  while ($row = $result->fetch_assoc()) {
    $menu[] = $row;
  }
} else {
  die("Gagal mengambil data menu: " . $conn->error);
}
*/
// Inisialisasi cart di session

$menu = [
    [
        "nama_menu" => "Paket Nasi",
        "harga" => 10000,
        "img" => "https://images.unsplash.com/photo-1628521061262-19b5cdb7eee5?w=500&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8M3x8cmljZSUyMGJvd2x8ZW58MHx8MHx8fDA%3D"
    ],
    [
        "nama_menu" => "Ayam Goreng",
        "harga" => 7500,
        "img" => "https://images.unsplash.com/photo-1626645738196-c2a7c87a8f58?w=500&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8Mnx8ZnJpZWQlMjBjaGlja2VufGVufDB8fDB8fHww"
    ],
    [
        "nama_menu" => "Sayur Kangkung",
        "harga" => 3000,
        "img" => "https://images.unsplash.com/photo-1680676066605-08566beed799?w=500&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8M3x8a2FuZ2t1bmd8ZW58MHx8MHx8fDA%3D"
    ],
    [
        "nama_menu" => "Kopi Hitam",
        "harga" => 5000,
        "img" => "https://images.unsplash.com/photo-1610632380989-680fe40816c6?w=500&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8Mjh8fGNvZmZlZXxlbnwwfHwwfHx8MA%3D%3D"
    ],
];

if (!isset($_SESSION['cart'])) {
  $_SESSION['cart'] = [];
}

$message = "";
$showInvoice = false;
$invoiceData = null;

// Handle Add to Cart
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  // Tambah ke keranjang
  if (isset($_POST['food_name'])) {
    $foodName = $_POST['food_name'];

    // Cari data makanan berdasarkan nama dari menu
    $item = null;
    foreach ($menu as $food) {
      if ($food['nama_menu'] === $foodName) {
        $item = $food;
        break;
      }
    }

    if ($item) {
      if (isset($_SESSION['cart'][$foodName])) {
        $_SESSION['cart'][$foodName]['qty']++;
      } else {
        $_SESSION['cart'][$foodName] = [
          "name" => $item['nama_menu'],
          "price" => $item['harga'],
          "qty" => 1
        ];
      }
      $message = "✅ \"$foodName\" telah ditambahkan ke keranjang!";
    } else {
      $message = "⚠ Item tidak ditemukan.";
    }
  }

  // Kurangi qty
  if (isset($_POST['reduce_item'])) {
    $foodName = $_POST['reduce_item'];
    if (isset($_SESSION['cart'][$foodName])) {
      $_SESSION['cart'][$foodName]['qty']--;
      if ($_SESSION['cart'][$foodName]['qty'] <= 0) {
        unset($_SESSION['cart'][$foodName]);
      }
      $message = "🔻 \"$foodName\" dikurangi dari keranjang.";
    }
  }

  // Checkout
  if (isset($_POST['checkout'])) {
    if (!empty($_SESSION['cart'])) {
      $uangDibayar = isset($_POST['jumlah_uang']) ? (int)$_POST['jumlah_uang'] : 0;

      $showInvoice = true;
      $invoiceData = $_SESSION['cart'];

      $totalInvoice = 0;
      foreach ($invoiceData as $item) {
        $totalInvoice += $item['price'] * $item['qty'];
      }

      if ($uangDibayar < $totalInvoice) {
        $message = "⚠ Uang yang dibayarkan kurang dari total belanja!";
        $showInvoice = false;
      } else {
        $kembalian = $uangDibayar - $totalInvoice;
        $invoiceDate = date('Y-m-d H:i:s');
        $_SESSION['cart'] = [];
      }
    } else {
      $message = "⚠ Keranjang belanja kosong, tidak bisa checkout.";
    }
  }
}

// Hitung total harga cart
$totalPrice = 0;
foreach ($_SESSION['cart'] as $cartItem) {
  $totalPrice += $cartItem['price'] * $cartItem['qty'];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Warteg FAST</title>
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      padding: 20px;
      background-color: #fff8f0;
      color: #333;
    }

    h1,
    h2 {
      text-align: center;
      color: #d35400;
    }

    .message {
      background: #d4edda;
      color: #155724;
      padding: 10px 15px;
      border-radius: 6px;
      width: fit-content;
      margin: 0 auto 20px auto;
      font-weight: 600;
      box-shadow: 0 0 5px rgba(0, 128, 0, 0.2);
    }

    .gallery {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 25px;
      margin-bottom: 40px;
    }

    .food-item {
      background-color: #fff;
      border-radius: 12px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
      width: 220px;
      text-align: center;
      padding: 15px;
      transition: transform 0.2s ease;
    }

    .food-item:hover {
      transform: scale(1.05);
    }

    .food-item img {
      width: 100%;
      border-radius: 12px;
      height: 150px;
      object-fit: cover;
    }

    .food-name {
      font-weight: 600;
      margin: 12px 0 6px 0;
      font-size: 18px;
      color: #e67e22;
    }

    .food-price {
      color: #888;
      font-size: 14px;
      margin-bottom: 12px;
    }

    .food-item button {
      background-color: #e67e22;
      border: none;
      color: white;
      padding: 10px 18px;
      border-radius: 25px;
      cursor: pointer;
      font-weight: 600;
      font-size: 14px;
      transition: background-color 0.3s ease;
    }

    .food-item button:hover {
      background-color: #cf711b;
    }

    .cart {
      max-width: 700px;
      margin: 0 auto 30px auto;
      background: white;
      border-radius: 12px;
      padding: 20px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .cart .kurangqty {
      background-color: #c0392b;
      color: white;
      border: none;
      padding: 5px 10px;
      font-size: 12px;
      border-radius: 4px;
      margin-left: 6px;
      cursor: pointer;
    }

    .cart table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 15px;
    }

    .cart th,
    .cart td {
      text-align: left;
      padding: 10px;
      border-bottom: 1px solid #ddd;
    }

    .cart th {
      background-color: #f9f9f9;
      color: #d35400;
    }

    .cart-total {
      text-align: right;
      font-weight: 700;
      font-size: 18px;
      color: #e67e22;
    }

    .checkout-btn {
      display: block;
      margin: 0 auto;
      background-color: #27ae60;
      border: none;
      color: white;
      padding: 12px 25px;
      font-size: 16px;
      border-radius: 25px;
      cursor: pointer;
      font-weight: 700;
      transition: background-color 0.3s ease;
    }

    .checkout-btn:hover {
      background-color: #1e8449;
    }

    .invoice {
      max-width: 700px;
      margin: 30px auto;
      background: white;
      border-radius: 12px;
      padding: 25px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
      font-family: 'Courier New', Courier, monospace;
      color: #2c3e50;
    }

    .invoice h2 {
      color: #2980b9;
      margin-bottom: 20px;
      text-align: center;
    }

    .invoice table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 25px;
    }

    .invoice th,
    .invoice td {
      border: 1px solid #ddd;
      padding: 12px 15px;
      text-align: left;
    }

    .invoice th {
      background-color: #3498db;
      color: white;
    }

    .invoice-total {
      text-align: right;
      font-weight: 700;
      font-size: 20px;
      color: #c0392b;
    }

    .invoice-date {
      text-align: right;
      font-size: 14px;
      color: #7f8c8d;
      margin-top: -15px;
      margin-bottom: 15px;
    }
  </style>
</head>

<body>

  <h1>Warteg FAST</h1>

  <?php if ($message): ?>
    <div class="message"><?= htmlspecialchars($message) ?></div>
  <?php endif; ?>

  <?php if ($showInvoice): ?>
    <div class="invoice">
      <h2>Invoice Pesanan</h2>
      <div class="invoice-date">Tanggal: <?= htmlspecialchars($invoiceDate) ?></div>
      <table>
        <thead>
          <tr>
            <th>Nama Makanan</th>
            <th>Harga</th>
            <th>Qty</th>
            <th>Subtotal</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $totalInvoice = 0;
          foreach ($invoiceData as $item):
            $subtotal = $item['price'] * $item['qty'];
            $totalInvoice += $subtotal;
          ?>
            <tr>
              <td><?= htmlspecialchars($item['name']) ?></td>
              <td>IDR <?= number_format($item['price'], 2) ?></td>
              <td><?= $item['qty'] ?></td>
              <td>IDR <?= number_format($subtotal, 2) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <div class="invoice-total">Total Pembayaran: IDR <?= number_format($totalInvoice, 2) ?></div>
      <div class="invoice-total">Uang Dibayarkan: IDR <?= number_format($uangDibayar, 2) ?></div>
      <div class="invoice-total">Kembalian: IDR <?= number_format($kembalian, 2) ?></div>
      <button onclick="window.print()" style="margin-top: 20px; padding: 10px 20px; background-color: #2980b9; color: white; border: none; border-radius: 8px; cursor: pointer;">🖨 Cetak Invoice</button>


    </div>
  <?php else: ?>

    <div class="gallery">
      <?php foreach ($menu as $food): ?>
        <div class="food-item">
          <img src="<?= $food['img'] ?>" alt="<?= htmlspecialchars($food['nama_menu']) ?>" />
          <div class="food-name"><?= htmlspecialchars($food['nama_menu']) ?></div>
          <div class="food-price">IDR <?= number_format($food['harga'], 2) ?></div>
          <form method="POST">
            <input type="hidden" name="food_name" value="<?= htmlspecialchars($food['nama_menu']) ?>" />
            <button type="submit">Tambah ke Keranjang</button>
          </form>
        </div>
      <?php endforeach; ?>
    </div>

    <h2>Keranjang Belanja</h2>
    <div class="cart">
      <?php if (empty($_SESSION['cart'])): ?>
        <p>Keranjang belanja masih kosong.</p>
      <?php else: ?>
        <table>
          <thead>
            <tr>
              <th>Nama Makanan</th>
              <th>Harga</th>
              <th>Qty</th>
              <th>Subtotal</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($_SESSION['cart'] as $cartItem): ?>
              <tr>
                <td><?= htmlspecialchars($cartItem['name']) ?></td>
                <td>IDR <?= number_format($cartItem['price'], 2) ?></td>
                <td>
                  <?= $cartItem['qty'] ?>

                <td>IDR <?= number_format($cartItem['price'] * $cartItem['qty'], 2) ?></td>
                <td>
                  <form method="POST" style="display:inline;">
                    <input type="hidden" name="reduce_item" value="<?= htmlspecialchars($cartItem['name']) ?>" />
                    <button class="kurangqty" type="submit">-</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <div class="cart-total">Total: IDR <?= number_format($totalPrice, 2) ?></div>

        <form method="POST" style="text-align:center;">
          <div style="text-align: right; margin-top: 10px; margin-bottom: 20px;">
            <label for="jumlah_uang" style="font-weight: 600; font-size: 14px; color: #333;">Uang Dibayarkan:</label><br>
            <input
              type="number"
              name="jumlah_uang"
              id="jumlah_uang"
              required
              min="<?= $totalPrice ?>"
              placeholder="Masukkan nominal pembayaran"
              style="
      margin-top: 6px;
      padding: 8px 10px;
      font-size: 14px;
      border-radius: 6px;
      border: 1px solid #ccc;
      width: 200px;
      box-sizing: border-box;
    " />
          </div>

          <button class="checkout-btn" type="submit" name="checkout" value="1">Checkout</button>
        </form>

      <?php endif; ?>
    </div>
  <?php endif; ?>
</body>

</html>