<?php
session_start();
include_once("config.php");

if (!isset($_SESSION['user_id']) || !isset($_POST['street_name'], $_POST['street_number'], $_POST['postal_code'], $_POST['city'])) {
    header("Location: index.php");
    exit;
}

if (!isset($_SESSION["cart_products"]) || empty($_SESSION["cart_products"])) {
    die("Cart is empty.");
}

$user_id = $_SESSION['user_id'];
$shipping_address = trim($_POST['street_name']) . ' ' .
                    trim($_POST['street_number']) . ', ' .
                    trim($_POST['postal_code']) . ' ' .
                    trim($_POST['city']);
$total_price = 0;
$order_status = 'pending';

// Calculate total price
foreach ($_SESSION["cart_products"] as $item) {
    $total_price += $item["product_price"] * $item["product_qty"];
}

// Insert order
$stmt = $mysqli->prepare("INSERT INTO orders (buyer_id, total_price, shipping_address, order_status) VALUES (?, ?, ?, ?)");
if (!$stmt) {
    die("Prepare failed: " . $mysqli->error);
}
$stmt->bind_param("idss", $user_id, $total_price, $shipping_address, $order_status);
if (!$stmt->execute()) {
    die("Execute failed: " . $stmt->error);
}
$order_id = $stmt->insert_id;
$stmt->close();

// Prepare statement to insert order items
$stmt = $mysqli->prepare("INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)");
if (!$stmt) {
    die("Prepare failed (order_items): " . $mysqli->error);
}

// Insert each product in cart
foreach ($_SESSION["cart_products"] as $item) {
    $product_code = $item["product_code"];

    // Lookup product_id
    $stmt_lookup = $mysqli->prepare("SELECT id FROM products WHERE product_code = ?");
    if (!$stmt_lookup) {
        die("Prepare failed (lookup): " . $mysqli->error);
    }
    $stmt_lookup->bind_param("s", $product_code);
    $stmt_lookup->execute();
    $stmt_lookup->bind_result($product_id);
    $stmt_lookup->fetch();
    $stmt_lookup->close();

    $qty = $item["product_qty"];
    $price = $item["product_price"];

    $stmt->bind_param("iiid", $order_id, $product_id, $qty, $price);
    if (!$stmt->execute()) {
        die("Execute failed (insert order_items): " . $stmt->error);
    }
}
$stmt->close();

// Clear cart and redirect
unset($_SESSION["cart_products"]);
header("Location: invoice.php?order_id=$order_id");
exit;
