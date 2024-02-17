<?php
// Function to retrieve the list of products from the database
function getProductsList() {
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
            $products[] = $row;
        }
    }

    $conn->close();

    return $products;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['submit_order'])) {
        $cusID = $_POST['cus_id'];

        // Retrieve the list of products
        $productsList = getProductsList();

        // Process the order and insert data into the ORDERS and ORDER_PRODUCT_LISTS tables
        $servername = "localhost";
        $username = "id21105111_2lsr_manager";
        $password = "2lsr-for-DATABASE";
        $dbname = "id21105111_2lsr_test_db";

        $conn = new mysqli($servername, $username, $password, $dbname);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Insert data into ORDERS table
        $expectedDeliveryTime = !empty($_POST['expected_delivery_time']) ? $_POST['expected_delivery_time'] : null;
        if (empty($expectedDeliveryTime)) {
            $expectedDeliveryTime = null;
        }

        $sql = "INSERT INTO ORDERS (CusID, ExpectedDeliveryTime) VALUES ('$cusID', ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $expectedDeliveryTime);
        
        if ($stmt->execute()) {
            $orderID = $stmt->insert_id;

            // Insert data into ORDER_PRODUCT_LISTS table for each product
            foreach ($productsList as $product) {
                $prodID = $product['ProdID'];
                $quantity = $_POST['product_' . $prodID];

                if (!empty($quantity)) {
                    $sql = "INSERT INTO ORDER_PRODUCT_LISTS (OrdID, ProdID, ProdQuan) VALUES ('$orderID', '$prodID', '$quantity')";
                    $conn->query($sql);
                }
            }

            // Close the connection and redirect to order_success.php or a success message
            $stmt->close();
            $conn->close();
            header("Location: order_success.php?order_id=$orderID");
            exit;
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }

        $stmt->close();
        $conn->close();
    }
}

// Retrieve the list of products from the database
$productsList = getProductsList();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Order Products</title>
</head>
<body>
    <h1>Order Products</h1>
    <form method="post" action="">
        <?php foreach ($productsList as $product): ?>
            <label for="product_<?php echo $product['ProdID']; ?>">
                <?php echo $product['ProdName']; ?> (Price: $<?php echo $product['ProdPrice']; ?>):
            </label>
            <input type="number" name="product_<?php echo $product['ProdID']; ?>" id="product_<?php echo $product['ProdID']; ?>" min="0"><br>
        <?php endforeach; ?>

        <input type="hidden" name="cus_id" value="<?php echo $_GET['cus_id']; ?>">

        <?php if (!empty($_GET['cus_id'])): ?>
            <label for="expected_delivery_time">Expected Delivery Time (optional):</label>
            <input type="datetime-local" name="expected_delivery_time" id="expected_delivery_time"><br>
        <?php endif; ?>

        <input type="submit" name="submit_order" value="Place Order">
    </form>
</body>
</html>

