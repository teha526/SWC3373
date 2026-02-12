<?php
include 'index.php';
include 'menu.php';

// Total revenue from completed orders
$result = $conn->query("SELECT SUM(total_amount) AS total_revenue FROM Orders WHERE order_status='Completed'");
$row = $result->fetch_assoc();
$total_revenue = $row['total_revenue'] ?? 0;

// Total orders
$result = $conn->query("SELECT COUNT(*) AS total_orders FROM Orders");
$row = $result->fetch_assoc();
$total_orders = $row['total_orders'];

// Best-selling cookies
$result = $conn->query("
    SELECT p.product_name, SUM(oi.quantity) AS total_sold
    FROM Order_Items oi
    JOIN Products p ON oi.product_id = p.product_id
    GROUP BY oi.product_id
    ORDER BY total_sold DESC
    LIMIT 1
");
$row = $result->fetch_assoc();
$best_selling = $row['product_name'] ?? 'N/A';

echo "<h2>Sales Report</h2>";
echo "Total Revenue: RM $total_revenue <br>";
echo "Total Orders: $total_orders <br>";
echo "Best-selling Product: $best_selling <br>";

$conn->close();
?>
