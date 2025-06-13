<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $name = isset($_POST['name']) ? $_POST['name'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $notes = isset($_POST['notes']) ? $_POST['notes'] : '';

    // Retrieve cart items from localStorage (assumed to be passed from JavaScript)
    $cartItems = isset($_POST['cartItems']) ? json_decode($_POST['cartItems'], true) : array();

    // Database connection parameters
    $servername = "localhost"; // Replace with your server name
    $username = "root";        // Replace with your database username
    $password = "";            // Replace with your database password
    $dbname = "cupcake_shop";  // Replace with your database name

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Insert order into orders table
    $stmt = $conn->prepare("INSERT INTO orders (name, email, notes) VALUES (?, ?, ?)");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("sss", $name, $email, $notes);

    if ($stmt->execute()) {
        $orderId = $stmt->insert_id;  // Get the inserted order ID

        // Insert cart items into order_items table
        foreach ($cartItems as $item) {
            $itemName = isset($item['name']) ? $item['name'] : '';
            $quantity = isset($item['quantity']) ? $item['quantity'] : 0;
            $price = isset($item['price']) ? $item['price'] : 0.0;
            $flavours = isset($item['flavours']) ? implode(",", $item['flavours']) : '';
            $description = isset($item['description']) ? $item['description'] : '';

            $stmtItem = $conn->prepare("INSERT INTO order_items (order_id, item_name, quantity, price, flavours, description) VALUES (?, ?, ?, ?, ?, ?)");
            if ($stmtItem) {
                $stmtItem->bind_param("isidss", $orderId, $itemName, $quantity, $price, $flavours, $description);
                $stmtItem->execute();
                $stmtItem->close();
            }
        }

        echo "Thank you for your order!";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
