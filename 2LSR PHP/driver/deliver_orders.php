<?php
if (isset($_GET['driv_id'])) {
    $drivID = $_GET['driv_id'];

    // Function to retrieve orders with status 'CONFIRMED' or 'DELIVERING' assigned to the driver with the given DriverID
    function getAssignedOrders($driverID)
    {
        $servername = "localhost";
        $username = "id21105111_2lsr_manager";
        $password = "2lsr-for-DATABASE";
        $dbname = "id21105111_2lsr_test_db";

        $conn = new mysqli($servername, $username, $password, $dbname);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $driverID = intval($driverID); // Sanitize input as an integer

        $sql = "SELECT * FROM ORDERS WHERE DriverID = $driverID AND (OrdStatus = 'CONFIRMED' OR OrdStatus = 'DELIVERING')";
        $result = $conn->query($sql);

        $orders = array();
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $orders[] = array(
                    'OrdID' => $row['OrdID'],
                    'CusID' => $row['CusID'],
                    'DriverID' => $row['DriverID'],
                    'TimeOfOrdering' => $row['TimeOfOrdering'],
                    'ExpectedDeliveryTime' => $row['ExpectedDeliveryTime'],
                    'OrdTotal' => $row['OrdTotal'],
                    'OrdStatus' => $row['OrdStatus']
                );
            }
        }

        $conn->close();

        return $orders;
    }

    $assignedOrders = getAssignedOrders($drivID);
} else {
    // If no driver ID is provided in the URL, redirect back to the login page
    header("Location: index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['take_orders'])) {
        $ordersToUpdate = $_POST['orders'];
        if (!empty($ordersToUpdate)) {
            $servername = "localhost";
            $username = "id21105111_2lsr_manager";
            $password = "2lsr-for-DATABASE";
            $dbname = "id21105111_2lsr_test_db";

            $conn = new mysqli($servername, $username, $password, $dbname);

            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            foreach ($ordersToUpdate as $orderID) {
                // Update the order status to 'DELIVERING'
                $orderID = intval($orderID);
                $updateSql = "UPDATE ORDERS SET OrdStatus = 'DELIVERING' WHERE DriverID = $drivID";
                $conn->query($updateSql);
            }

            $conn->close();

            // Redirect back to the driver's dashboard after updating the orders
            header("Location: drivers_dashboard.php?driv_id=$drivID");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Deliver Orders</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }

        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: center;
        }

        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h1>Deliver Orders</h1>
    <h2>Driver ID: <?php echo $drivID; ?></h2>

    <h2>Orders to Deliver</h2>
    <form method="post" action="">
        <?php if (!empty($assignedOrders)): ?>
            <table>
                <tr>
                    <th>Select</th>
                    <th>Order ID</th>
                    <th>Customer ID</th>
                    <th>Driver ID</th>
                    <th>Ordered at</th>
                    <th>Expected Delivery Time</th>
                    <th>Total</th>
                    <th>Order Status</th>
                </tr>
                <?php foreach ($assignedOrders as $order): ?>
                    <tr>
                        <td><input type="checkbox" name="orders[]" value="<?php echo $order['OrdID']; ?>"></td>
                        <td><?php echo $order['OrdID']; ?></td>
                        <td><?php echo $order['CusID']; ?></td>
                        <td><?php echo $order['DriverID']; ?></td>
                        <td><?php echo $order['TimeOfOrdering']; ?></td>
                        <td><?php echo $order['ExpectedDeliveryTime']; ?></td>
                        <td><?php echo $order['OrdTotal']; ?></td>
                        <td><?php echo $order['OrdStatus']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p>No orders to deliver at the moment.</p>
        <?php endif; ?>
        <br>
        <input type="submit" name="take_orders" value="Take Orders to Delivery">
    </form>

    <br>
    <form method="post" action="drivers_dashboard.php?driv_id=<?php echo $drivID;?>">
        <input type="submit" name="dashboard" value="Go Back">
    </form>
</body>
</html>