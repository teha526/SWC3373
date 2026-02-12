<?php
include 'index.php';
include 'menu.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $order_id = (int)$_POST['order_id'];
    $status = $conn->real_escape_string($_POST['status']);

    $conn->query("
        UPDATE Orders
        SET order_status='$status'
        WHERE order_id=$order_id
    ");

    echo "<p>Order #$order_id updated to $status</p>";
}

$result = $conn->query("SELECT order_id, order_status FROM Orders");

echo '<form method="POST">';
echo 'Select Order: <select name="order_id">';

while ($row = $result->fetch_assoc()) {
    echo "<option value='{$row['order_id']}'>
            Order #{$row['order_id']} ({$row['order_status']})
          </option>";
}

echo '</select><br><br>';

echo 'New Status:
      <select name="status">
        <option value="Pending">Pending</option>
        <option value="Processing">Processing</option>
        <option value="Completed">Completed</option>
        <option value="Cancelled">Cancelled</option>
      </select><br><br>';

echo '<input type="submit" value="Update Order">';
echo '</form>';

$conn->close();
?>
