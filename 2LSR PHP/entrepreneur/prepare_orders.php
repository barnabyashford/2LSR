<!DOCTYPE html>
<html>
<head>
    <title>Prepare Orders</title>
</head>
<body>
    <h1>Prepare Orders</h1>

    <?php
    // Function to retrieve the list of ongoing orders with customer details and product lists
    function getOngoingOrders()
    {
        $servername = "localhost";
        $username = "id21105111_2lsr_manager";
        $password = "2lsr-for-DATABASE";
        $dbname = "id21105111_2lsr_test_db";

        $conn = new mysqli($servername, $username, $password, $dbname);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $sql = "SELECT o.OrdID, c.CusName, c.CusPhone, o.ExpectedDeliveryTime, o.OrdStatus, p.ProdID, p.ProdName, opl.ProdQuan
                FROM ORDERS o
                JOIN CUSTOMERS c ON o.CusID = c.CusID
                JOIN ORDER_PRODUCT_LISTS opl ON o.OrdID = opl.OrdID
                JOIN PRODUCTS p ON opl.ProdID = p.ProdID
                WHERE o.OrdStatus = 'ONGOING'";
        $result = $conn->query($sql);

        $orders = array();
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $ordID = $row['OrdID'];
                if (!isset($orders[$ordID])) {
                    $orders[$ordID] = array(
                        'CusName' => $row['CusName'],
                        'CusPhone' => $row['CusPhone'],
                        'ExpectedDeliveryTime' => $row['ExpectedDeliveryTime'],
                        'OrdStatus' => $row['OrdStatus'],
                        'Products' => array()
                    );
                }
                $orders[$ordID]['Products'][] = array(
                    'ProdID' => $row['ProdID'],
                    'ProdName' => $row['ProdName'],
                    'ProdQuan' => $row['ProdQuan']
                );
            }
        }

        $conn->close();

        return $orders;
    }

    // Function to retrieve the list of all products with their price and remaining quantity
    function getAllProducts()
    {
        $servername = "localhost";
        $username = "id21105111_2lsr_manager";
        $password = "2lsr-for-DATABASE";
        $dbname = "id21105111_2lsr_test_db";

        $conn = new mysqli($servername, $username, $password, $dbname);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $sql = "SELECT ProdID, ProdName, ProdPrice, ProdRemain FROM PRODUCTS";
        $result = $conn->query($sql);

        $products = array();
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $products[$row['ProdID']] = array(
                    'ProdName' => $row['ProdName'],
                    'ProdPrice' => $row['ProdPrice'],
                    'ProdRemain' => $row['ProdRemain']
                );
            }
        }

        $conn->close();

        return $products;
    }

    // Function to execute the given SQL query
    function executeSQL($sql)
    {
        $servername = "localhost";
        $username = "id21105111_2lsr_manager";
        $password = "2lsr-for-DATABASE";
        $dbname = "id21105111_2lsr_test_db";

        $conn = new mysqli($servername, $username, $password, $dbname);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        if ($conn->query($sql) === TRUE) {
            $conn->close();
            return true;
        } else {
            $conn->close();
            return false;
        }
    }

    // Function to confirm the order and update product quantities
    function confirmOrder($orderID)
    {
        $servername = "localhost";
        $username = "id21105111_2lsr_manager";
        $password = "2lsr-for-DATABASE";
        $dbname = "id21105111_2lsr_test_db";

        $conn = new mysqli($servername, $username, $password, $dbname);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $orderSql = "SELECT ProdID, ProdQuan FROM ORDER_PRODUCT_LISTS WHERE OrdID = '$orderID'";
        $result = $conn->query($orderSql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $prodID = $row['ProdID'];
                $prodQuan = $row['ProdQuan'];

                // Update the remaining quantity of the product in the PRODUCTS table
                $updateSql = "UPDATE PRODUCTS SET ProdRemain = ProdRemain - $prodQuan WHERE ProdID = '$prodID'";
                $conn->query($updateSql);
            }
        }

        // Close the connection
        $conn->close();
    }

    $ongoingOrders = getOngoingOrders();
    $allProducts = getAllProducts();

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        if (isset($_POST['submit'])) {
            $orderID = $_POST['order_id'];
            $productID = $_POST['prod_id'];
            $productQuantity = $_POST['prod_quan'];

            if ($_POST['action'] === "add") {
                // Check if the product exists in the store
                if (isset($allProducts[$productID])) {
                    // Execute SQL to add the product to the order_product_lists table
                    $addSql = "INSERT INTO ORDER_PRODUCT_LISTS (OrdID, ProdID, ProdQuan) VALUES ('$orderID', '$productID', '$productQuantity')";
                    $result = executeSQL($addSql);

                    if ($result) {
                        echo "<p>Product successfully added to the order.</p>";
                    } else {
                        echo "<p>Error adding product to the order.</p>";
                    }
                } else {
                    echo "<p>Invalid Product ID. Product does not exist in the store.</p>";
                }
            } elseif ($_POST['action'] === "change") {
                // Execute SQL to change the product quantity in the order_product_lists table
                $changeSql = "UPDATE ORDER_PRODUCT_LISTS SET ProdQuan = '$productQuantity' WHERE OrdID = '$orderID' AND ProdID = '$productID'";
                $result = executeSQL($changeSql);

                if ($result) {
                    echo "<p>Product quantity successfully changed.</p>";
                } else {
                    echo "<p>Error changing product quantity.</p>";
                }
            } elseif ($_POST['action'] === "remove") {
                // Execute SQL to remove the product from the order_product_lists table
                $removeSql = "CALL DeleteData('ORDER_PRODUCT_LISTS', '$orderID', '$productID')";
                $result = executeSQL($removeSql);

                if ($result) {
                    echo "<p>Product successfully removed from the order.</p>";
                } else {
                    echo "<p>Error removing product from the order.</p>";
                }
            } elseif ($_POST['action'] === "cancel") {
                // Execute SQL to cancel the order (change the OrdStatus to "CANCELLED" and ProdID and ProdQuan to NULL)
                $cancelSql = "UPDATE ORDERS SET OrdStatus = 'CANCELLED' WHERE OrdID = '$orderID'";
                $result = executeSQL($cancelSql);

                if ($result) {
                    echo "<p>Order successfully cancelled.</p>";
                } else {
                    echo "<p>Error cancelling the order.</p>";
                }
            } elseif ($_POST['action'] === "confirm") {
                // Confirm the order and update product quantities
                confirmOrder($orderID);
                // Update the order status to "CONFIRMED"
                $confirmSql = "UPDATE ORDERS SET OrdStatus = 'CONFIRMED' WHERE OrdID = '$orderID'";
                $result = executeSQL($confirmSql);

                if ($result) {
                    echo "<p>Order successfully confirmed.</p>";
                } else {
                    echo "<p>Error confirming the order.</p>";
                }
            }
        }
    }
    ?>

    <form method="post" action="">
        <h2>Ongoing Orders</h2>
        <table>
            <tr>
                <th>Order ID</th>
                <th>Customer Name</th>
                <th>Customer Phone</th>
                <th>Expected Delivery Time</th>
                <th>Order Status</th>
                <th>Products</th>
            </tr>
            <?php foreach ($ongoingOrders as $ordID => $order): ?>
                <tr>
                    <td><?php echo $ordID; ?></td>
                    <td><?php echo $order['CusName']; ?></td>
                    <td><?php echo $order['CusPhone']; ?></td>
                    <td><?php echo $order['ExpectedDeliveryTime']; ?></td>
                    <td><?php echo $order['OrdStatus']; ?></td>
                    <td>
                        <ul>
                            <?php foreach ($order['Products'] as $product): ?>
                                <li><?php echo $product['ProdName'] . " (Quantity: " . $product['ProdQuan'] . ")"; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>

        <h2>All Products</h2>
        <table>
            <tr>
                <th>Product ID</th>
                <th>Product Name</th>
                <th>Price</th>
                <th>Remaining Quantity</th>
            </tr>
            <?php foreach ($allProducts as $prodID => $product): ?>
                <tr>
                    <td><?php echo $prodID; ?></td>
                    <td><?php echo $product['ProdName']; ?></td>
                    <td><?php echo $product['ProdPrice']; ?></td>
                    <td><?php echo $product['ProdRemain']; ?></td>
                </tr>
            <?php endforeach; ?>
        </table>

        <h2>Take Action on Order</h2>
        <label for="order_id">Order ID:</label>
        <input type="text" name="order_id" id="order_id" required><br>
        <label for="prod_id">Product ID:</label>
        <input type="text" name="prod_id" id="prod_id"><br>
        <label for="prod_quan">Product Quantity (min = 1):</label>
        <input type="number" name="prod_quan" id="prod_quan" min="1"><br>
        <input type="radio" name="action" value="add"> Add Product to Order<br>
        <input type="radio" name="action" value="change"> Change Product Quantity<br>
        <input type="radio" name="action" value="remove"> Remove Product from Order<br>
        <input type="radio" name="action" value="cancel"> Cancel Order<br>
        <input type="radio" name="action" value="confirm"> Confirm Order<br>
        <input type="submit" name="submit" value="Take Action">
    </form>
    <br>
    <form method="post" action="index.php">
        <input type="submit" name="dashboard" value="Go Back to Dashboard">
    </form>
</body>
</html>
