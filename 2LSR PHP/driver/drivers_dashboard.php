<?php
// Ensure the driver ID is provided in the URL
if (isset($_GET['driv_id'])) {
    $drivID = $_GET['driv_id'];

    // Function to retrieve orders assigned to the driver with the given DriverID
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

        $sql = "SELECT o.OrdID, c.CusName, o.TimeOfOrdering, o.ExpectedDeliveryTime, o.OrdTotal, o.OrdStatus
                FROM ORDERS o, CUSTOMERS c
                WHERE o.DriverID = $driverID
                AND o.CusID = c.CusID
                AND (o.OrdStatus = 'CONFIRMED'
                OR o.OrdStatus = 'DELIVERING')";
        $result = $conn->query($sql);

        $orders = array();
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $orders[] = array(
                    'OrdID' => $row['OrdID'],
                    'CusName' => $row['CusName'],
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
?>

<!DOCTYPE html>
<html>
<head>
    <title>Driver's Dashboard</title>
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
    <h1>Driver's Dashboard</h1>
    <h2>Hello Driver <?php echo $drivID; ?></h2>

    <h2>Assigned Orders</h2>
    <?php if (!empty($assignedOrders)): ?>
        <table>
            <tr>
                <th>Order ID</th>
                <th>Customer Name</th>
                <th>Ordered at</th>
                <th>Expected Delivery Time</th>
                <th>Total</th>
                <th>Order Status</th>
            </tr>
            <?php foreach ($assignedOrders as $order): ?>
                <tr>
                    <td><?php echo $order['OrdID']; ?></td>
                    <td><?php echo $order['CusName']; ?></td>
                    <td><?php echo $order['TimeOfOrdering']; ?></td>
                    <td><?php echo $order['ExpectedDeliveryTime']; ?></td>
                    <td><?php echo $order['OrdTotal']; ?></td>
                    <td><?php echo $order['OrdStatus']; ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>No orders assigned to you at the moment.</p>
    <?php endif; ?>
    
    <br>
    <form method="post" action="deliver_orders.php?driv_id=<?php echo $drivID; ?>">
        <input type="submit" name="deliver_orders" value="Deliver Orders">
    </form>
    <p>
    <form method="post" action="issue_payment.php?driv_id=<?php echo $drivID; ?>">
        <input type="submit" name="issue_payment" value="Issue Payment and Update Order Status">
    </form>
    </form>
    <br>
    <form method="post" action="index.php">
        <input type="submit" name="dashboard" value="Log Out">
    </form>
</body>
</html>
