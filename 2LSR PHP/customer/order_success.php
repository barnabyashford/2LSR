<!DOCTYPE html>
<html>
<head>
    <title>Order Success</title>
</head>
<body>
    <h1>Order Successfully Placed</h1>
    <?php
    // Check if the order_id is passed via GET parameter
    if (isset($_GET['order_id'])) {
        $servername = "localhost";
        $username = "id21105111_2lsr_manager";
        $password = "2lsr-for-DATABASE";
        $dbname = "id21105111_2lsr_test_db";

        $conn = new mysqli($servername, $username, $password, $dbname);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Retrieve order details from the ORDERS table
        $orderID = $_GET['order_id'];
        $sql = "SELECT * FROM ORDERS WHERE OrdID = $orderID";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $order = $result->fetch_assoc();
            echo "<h3>Order Details:</h3>";
            echo "Order ID: " . $order['OrdID'] . "<br>";
            echo "Customer ID: " . $order['CusID'] . "<br>";
            
            // Check if the expected delivery time is NULL or '0000-00-00 00:00:00'
            $expectedDeliveryTime = ($order['ExpectedDeliveryTime'] && $order['ExpectedDeliveryTime'] !== '0000-00-00 00:00:00') ? $order['ExpectedDeliveryTime'] : 'None';
            echo "Expected Delivery Time: " . $expectedDeliveryTime . "<br>";
        }

        // Retrieve product details from the ORDER_PRODUCT_LISTS table
        $sql = "SELECT * FROM ORDER_PRODUCT_LISTS WHERE OrdID = $orderID";
        $result = $conn->query($sql);

        $orderTotal = 0; // Variable to calculate the order total

        if ($result->num_rows > 0) {
            echo "<h3>Ordered Products:</h3>";
            while ($row = $result->fetch_assoc()) {
                $prodID = $row['ProdID'];
                $quantity = $row['ProdQuan'];

                // Retrieve product details from the PRODUCTS table
                $sql = "SELECT * FROM PRODUCTS WHERE ProdID = $prodID";
                $prodResult = $conn->query($sql);
                $product = $prodResult->fetch_assoc();

                echo "Product ID: " . $prodID . "<br>";
                echo "Product Name: " . $product['ProdName'] . "<br>";
                echo "Quantity: " . $quantity . "<br>";
                $totalPrice = $product['ProdPrice'] * $quantity;
                echo "Total Price: ฿" . $totalPrice . "<br><br>";
                
                $orderTotal += $totalPrice; // Add the product total to the order total
            }
        }

        // Display the order total in Thai Baht
        echo "<h3>Order Total: ฿" . $orderTotal . "</h3>";

        $conn->close();
        
    }
    ?>
    <form method="post" action="index.php">
        <input type="submit" name="register" value="Go Back">
    </form>
</body>
</html>
