<?php
session_start();

include_once("config.php");
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>View shopping cart</title>
<link href="style/style.css" rel="stylesheet" type="text/css"></head>
<body>
<h1 align="center">View Cart</h1>
<div class="cart-view-table-back">
<form method="post" action="cart_update.php">
<table width="100%"  cellpadding="6" cellspacing="0"><thead><tr><th>Quantity</th><th>Name</th><th>Price</th><th>Total</th><th>Remove</th></tr></thead>
  <tbody>
 	<?php
	if(isset($_SESSION["cart_products"])) //check session var
    {
		$total = 0; //set initial total value
		$b = 0; //var for zebra stripe table 
		foreach ($_SESSION["cart_products"] as $cart_itm)
        {
			//set variables to use in content below
			$product_name = $cart_itm["product_name"];
			$product_qty = $cart_itm["product_qty"];
			$product_price = $cart_itm["product_price"];
			$product_code = $cart_itm["product_code"];
			$product_color = $cart_itm["product_color"];
			$subtotal = ($product_price * $product_qty); //calculate Price x Qty
			
		   	$bg_color = ($b++%2==1) ? 'odd' : 'even'; //class for zebra stripe 
		    echo '<tr class="'.$bg_color.'">';
			echo '<td><input type="text" size="2" maxlength="2" name="product_qty['.$product_code.']" value="'.$product_qty.'" /></td>';
			echo '<td>'.$product_name.'</td>';
			echo '<td>'.$currency.$product_price.'</td>';
			echo '<td>'.$currency.$subtotal.'</td>';
			echo '<td><input type="checkbox" name="remove_code[]" value="'.$product_code.'" /></td>';
            echo '</tr>';
			$total = ($total + $subtotal); //add subtotal to total var
        }
		
		$grand_total = $total + $shipping_cost; //grand total including shipping cost
		foreach($taxes as $key => $value){ //list and calculate all taxes in array
				$tax_amount     = round($total * ($value / 100));
				$tax_item[$key] = $tax_amount;
				$grand_total    = $grand_total + $tax_amount;  //add tax val to grand total
		}
		
		$list_tax       = '';
		foreach($tax_item as $key => $value){ //List all taxes
			$list_tax .= $key. ' : '. $currency. sprintf("%01.2f", $value).'<br />';
		}
		$shipping_cost = ($shipping_cost)?'Shipping Cost : '.$currency. sprintf("%01.2f", $shipping_cost).'<br />':'';
	}
    ?>
    <tr><td colspan="5"><span style="float:left;text-align: left;"><?php echo $shipping_cost. $list_tax; ?>Amount Payable : <?php echo sprintf("%01.2f", $grand_total);?></span></td></tr>
    <tr>
	<td colspan="5">
		<div style="float: left;">
		<a href="index.php" class="button">Add More Items</a>
		<button type="submit">Update</button>
		</div>
	</td>
	</tr>
  </tbody>
</table>

<input type="hidden" name="return_url" value="<?php 
$current_url = urlencode($url="http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
echo $current_url; ?>" />
</form>

<?php if (isset($_SESSION["cart_products"]) && count($_SESSION["cart_products"]) > 0): ?>
    <div style="margin-top: 20px;">
        <form method="post" action="confirm_order.php" onsubmit="return validateAndConfirm();">
            <h2>Shipping Address</h2>
			<table>
				<tr>
					<td><label for="street_name">Street name:</label></td>
					<td><input type="text" id="street_name" name="street_name" required></td>
				</tr>
				<tr>
					<td><label for="street_number">Street number:</label></td>
					<td><input type="text" id="street_number" name="street_number" required></td>
				</tr>
				<tr>
					<td><label for="postal_code">Postal code:</label></td>
					<td><input type="text" id="postal_code" name="postal_code" required></td>
				</tr>
				<tr>
					<td><label for="city">City:</label></td>
					<td><input type="text" id="city" name="city" required></td>
				</tr>
			</table>

            <input type="submit" name="confirm_order" value="Confirm Order" />
        </form>
    </div>

    <script>
        function validateAndConfirm() {
            const fields = ['street_name', 'street_number', 'postal_code', 'city'];
            for (let id of fields) {
                let val = document.getElementById(id).value.trim();
                if (val === '') {
                    alert("Please fill out all address fields.");
                    return false;
                }
            }
            return confirm("Are you sure you want to place this order?");
        }
    </script>
<?php endif; ?>

</div>

</body>
</html>
