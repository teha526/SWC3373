<?php
include 'index.php';
include 'menu.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // ================= CUSTOMER INPUT =================
    $customer_name = trim($_POST['customer_name']);
    $email   = trim($_POST['email']);
    $phone   = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $payment_method = $_POST['payment_method'] ?? 'Cash';
    $products = $_POST['products'];

    // Escape strings
    $customer_name_safe = $conn->real_escape_string($customer_name);
    $email_safe   = $conn->real_escape_string($email);
    $phone_safe   = $conn->real_escape_string($phone);
    $address_safe = $conn->real_escape_string($address);
    $payment_method_safe = $conn->real_escape_string($payment_method);

    // ================= CHECK CUSTOMER =================
    $check = $conn->query("
        SELECT customer_id FROM Customers
        WHERE customer_name = '$customer_name_safe'
    ");

    if ($check->num_rows > 0) {
        // Existing customer
        $row = $check->fetch_assoc();
        $customer_id = $row['customer_id'];
    } else {
        // New customer
        $conn->query("
            INSERT INTO Customers (customer_name, email, phone, address)
            VALUES ('$customer_name_safe', '$email_safe', '$phone_safe', '$address_safe')
        ");
        $customer_id = $conn->insert_id;
    }

    // ================= CALCULATE TOTAL =================
    $total_amount = 0;

    foreach ($products as $product_id => $quantity) {

        $product_id = (int)$product_id;
        $quantity   = (int)$quantity;

        if ($quantity <= 0) continue;

        $result = $conn->query("
            SELECT price FROM Products WHERE product_id = $product_id
        ");
        $row = $result->fetch_assoc();

        $price = (float)$row['price'];
        $total_amount += $price * $quantity;
    }

    // ================= INSERT ORDER =================
    $conn->query("
        INSERT INTO Orders (customer_id, total_amount, order_status)
        VALUES ($customer_id, $total_amount, 'Pending')
    ");

    $order_id = $conn->insert_id;

    // ================= ORDER ITEMS =================
    foreach ($products as $product_id => $quantity) {

        $product_id = (int)$product_id;
        $quantity   = (int)$quantity;

        if ($quantity <= 0) continue;

        $result = $conn->query("
            SELECT price FROM Products WHERE product_id = $product_id
        ");
        $row = $result->fetch_assoc();

        $price = (float)$row['price'];
        $subtotal = $price * $quantity;

        $conn->query("
            INSERT INTO Order_Items (order_id, product_id, quantity, subtotal)
            VALUES ($order_id, $product_id, $quantity, $subtotal)
        ");

        $conn->query("
            UPDATE Products
            SET stock_quantity = stock_quantity - $quantity
            WHERE product_id = $product_id
        ");
    }

    // ================= INSERT PAYMENT =================
    $conn->query("
        INSERT INTO Payments (order_id, payment_method, amount_paid)
        VALUES ($order_id, '$payment_method_safe', $total_amount)
    ");

    echo "<h3>✅ Order placed successfully!</h3>";
    echo "<p>Customer: <b>$customer_name</b></p>";
    echo "<p>Order ID: <b>$order_id</b></p>";
    echo "<p>Payment Method: <b>$payment_method</b></p>";
    echo "<p>Total Paid: RM <b>$total_amount</b></p>";
}
?>

<hr>

<?php
// ================= DISPLAY FORM =================
$products = $conn->query("
    SELECT product_id, product_name, price, stock_quantity FROM Products
");

echo '<form method="POST">';

echo 'Customer Name: <input type="text" name="customer_name" required><br><br>';
echo 'Email: <input type="email" name="email" required><br><br>';
echo 'Phone: <input type="text" name="phone" required><br><br>';
echo 'Address: <input type="text" name="address" required><br><br>';

// Payment Method dropdown
echo 'Payment Method:
      <select name="payment_method">
        <option value="Cash">Cash</option>
        <option value="Online Banking">Online Banking</option>
        <option value="eWallet">eWallet</option>
      </select><br><br>';

// Product list
while ($p = $products->fetch_assoc()) {

    echo $p['product_name'] .
        ' (RM ' . $p['price'] .
        ', Stock: ' . $p['stock_quantity'] . ') ' .
        'Quantity: <input type="number" name="products[' . $p['product_id'] .
        ']" min="0" max="' . $p['stock_quantity'] .
        '" value="0"><br>';
}

echo '<br><input type="submit" value="Place Order">';
echo '</form>';

$conn->close();
?>
