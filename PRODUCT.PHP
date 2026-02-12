<?php
include 'index.php';
include 'menu.php';

echo '<h2>Product List</h2>';

$sql = "SELECT product_id, product_name, category, price, stock_quantity FROM Products";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<table border='1' cellpadding='10'>";
    echo "<tr>
            <th>ID</th>
            <th>Name</th>
            <th>Category</th>
            <th>Price (RM)</th>
            <th>Stock</th>
          </tr>";

    while($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>{$row['product_id']}</td>
                <td>{$row['product_name']}</td>
                <td>{$row['category']}</td>
                <td>{$row['price']}</td>
                <td>{$row['stock_quantity']}</td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "No products found.";
}

$conn->close();
?>
