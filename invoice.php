<?php
session_start();
include_once("config.php");

if (!isset($_GET['order_id']) || !is_numeric($_GET['order_id'])) {
    die("Invalid order ID.");
}

$order_id = intval($_GET['order_id']);

// Fetch order info
$stmt = $mysqli->prepare("SELECT o.id, o.buyer_id, o.total_price, o.shipping_address, o.order_status, o.created_at, u.username 
                          FROM orders o
                          JOIN users u ON o.buyer_id = u.id
                          WHERE o.id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();
$stmt->close();

if (!$order) {
    die("Order not found.");
}

// Fetch order items
$stmt = $mysqli->prepare("SELECT oi.product_id, oi.quantity, oi.unit_price, p.product_name 
                          FROM order_items oi
                          JOIN products p ON oi.product_id = p.id
                          WHERE oi.order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order_items = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Invoice #<?php echo htmlspecialchars($order['id']); ?></title>
    <link href="style/style.css" rel="stylesheet" type="text/css">
</head>

<body>
  <div class="invoice-container">
    <h1>Your Invoice</h1>
    <p><strong>Name:</strong> <?php echo htmlspecialchars($order['username']); ?></p>
    <p><strong>Order Number:</strong> <?php echo htmlspecialchars($order['id']); ?></p>
    <p><strong>Order Date:</strong> <?php echo date("Y-m-d", strtotime($order['created_at'])); ?></p>
    <p><strong>Shipping Address:</strong> <?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
    <p><strong>Order Status:</strong> <?php echo htmlspecialchars($order['order_status']); ?></p>

    <h2>Order Items</h2>
    <table>
      <thead>
        <tr>
          <th>Product</th>
          <th>Qty</th>
          <th>Unit Price</th>
          <th>Subtotal</th>
        </tr>
      </thead>
      <tbody>
        <?php 
        $total = 0;
        foreach ($order_items as $item):
          $subtotal = $item['quantity'] * $item['unit_price'];
          $total += $subtotal;
        ?>
        <tr>
          <td><?php echo htmlspecialchars($item['product_name']); ?></td>
          <td><?php echo (int)$item['quantity']; ?></td>
          <td><?php echo '$'.number_format($item['unit_price'], 2); ?></td>
          <td><?php echo '$'.number_format($subtotal, 2); ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr>
          <td colspan="3" style="text-align:right;"><strong>Total:</strong></td>
          <td><?php echo '$'.number_format($total, 2); ?></td>
        </tr>
      </tfoot>
    </table>

    <p><a href="index.php">Back to Shop</a></p>
  </div>
</body>
</html>
