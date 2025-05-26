<?php
session_start();
include_once("config.php");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch all orders for the logged-in user
$stmt = $mysqli->prepare("SELECT id, total_price, order_status, created_at FROM orders WHERE buyer_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$orders = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>My Orders</title>
    <link href="style/style.css" rel="stylesheet" type="text/css">
</head>
<body>

<h1 style="text-align:center;">My Orders</h1>

<?php if (count($orders) === 0): ?>
    <p style="text-align:center;">You have no orders yet.</p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Date</th>
                <th>Status</th>
                <th>Total Price</th>
                <th>Details</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?php echo htmlspecialchars($order['id']); ?></td>
                    <td><?php echo date("Y-m-d", strtotime($order['created_at'])); ?></td>
                    <td><?php echo htmlspecialchars($order['order_status']); ?></td>
                    <td><?php echo '$'.number_format($order['total_price'], 2); ?></td>
                    <td><a class="order-link" href="invoice.php?order_id=<?php echo $order['id']; ?>">View Invoice</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<p style="text-align:center;"><a href="index.php">Back to Shop</a></p>

</body>
</html>