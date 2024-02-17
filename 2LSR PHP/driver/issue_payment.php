<?php
// Ensure the driver ID is provided in the URL
if (isset($_GET['driv_id'])) {
    $drivID = $_GET['driv_id'];

    // Function to retrieve DELIVERING orders assigned to the driver with the given DriverID
    function getDeliveringOrders($driverID)
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

        $sql = "SELECT o.OrdID, c.CusName, o.OrdTotal
                FROM ORDERS o, CUSTOMERS c
                WHERE o.DriverID = $driverID
                AND o.CusID = c.CusID
                AND o.OrdStatus = 'DELIVERING'";
        $result = $conn->query($sql);

        $orders = array();
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $orders[] = array(
                    'OrdID' => $row['OrdID'],
                    'CusName' => $row['CusName'],
                    'OrdTotal' => $row['OrdTotal']
                );
            }
        }

        $conn->close();

        return $orders;
    }

    $deliveringOrders = getDeliveringOrders($drivID);
} else {
    // If no driver ID is provided in the URL, redirect back to the login page
    header("Location: index.php");
    exit;
}

// Handle form submission for completing or canceling an order
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['complete_order']) || isset($_POST['cancel_order'])) {
        $selectedOrderID = $_POST['selected_order'];

        $servername = "localhost";
        $username = "id21105111_2lsr_manager";
        $password = "2lsr-for-DATABASE";
        $dbname = "id21105111_2lsr_test_db";


        $conn = new mysqli($servername, $username, $password, $dbname);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $selectedOrderID = intval($selectedOrderID); // Sanitize input as an integer

        if (isset($_POST['complete_order'])) {
            $sql = "UPDATE ORDERS SET OrdStatus = 'COMPLETED' WHERE OrdID = $selectedOrderID";
        } elseif (isset($_POST['cancel_order'])) {
            $sql = "UPDATE ORDERS SET OrdStatus = 'CANCELLED' WHERE OrdID = $selectedOrderID";
        }

        if ($conn->query($sql) === TRUE) {
            // Reload the page to show updated order status
            header("Location: issue_payment.php?driv_id=$drivID");
            exit;
        } else {
            echo "Error updating order status: " . $conn->error;
        }

        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Issue Payment</title>
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
    <h1>Issue Payment</h1>
    <h2>Hello Driver <?php echo $drivID; ?></h2>

    <h2>Delivering Orders</h2>
    <form method="post" action="">
        <table>
            <tr>
                <th>Order ID</th>
                <th>Customer Name</th>
                <th>Total</th>
                <th>Select</th>
            </tr>
            <?php foreach ($deliveringOrders as $order): ?>
                <tr>
                    <td><?php echo $order['OrdID']; ?></td>
                    <td><?php echo $order['CusName']; ?></td>
                    <td><?php echo $order['OrdTotal']; ?></td>
                    <td><input type="radio" name="selected_order" value="<?php echo $order['OrdID']; ?>"></td>
                </tr>
            <?php endforeach; ?>
        </table>
        <input type="submit" name="complete_order" value="Complete Order">
        <input type="submit" name="cancel_order" value="Cancel Order">
    </form>

    <br>
    <form method="post" action="drivers_dashboard.php">
        <input type="hidden" name="driv_id" value="<?php echo $drivID; ?>">
        <input type="submit" name="back_dashboard" value="Back to Dashboard">
    </form>
</body>
</html>
