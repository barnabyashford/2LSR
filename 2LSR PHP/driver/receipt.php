<?php
// Ensure the selected order ID is provided in the URL
if (isset($_GET['order_id'])) {
    $orderID = $_GET['order_id'];

    // Function to retrieve order details for the given order ID
    function getOrderDetails($orderID)
    {
        $servername = "localhost";
        $username = "id21105111_2lsr_manager";
        $password = "2lsr-for-DATABASE";
        $dbname = "id21105111_2lsr_test_db";


        $conn = new mysqli($servername, $username, $password, $dbname);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $orderID = intval($orderID); // Sanitize input as an integer

        $sql = "SELECT o.OrdID, c.CusName, o.OrdTotal, o.TimeOfOrdering, o.ExpectedDeliveryTime
                FROM ORDERS o, CUSTOMERS c
                WHERE o.OrdID = $orderID
                AND o.CusID = c.CusID
                AND o.OrdStatus = 'DELIVERING'";
        $result = $conn->query($sql);

        $orderDetails = array();
        if ($result->num_rows > 0) {
            $orderDetails = $result->fetch_assoc();
        }

        $conn->close();

        return $orderDetails;
    }

    $orderDetails = getOrderDetails($orderID);
} else {
    // If no order ID is provided in the URL, redirect back to the driver's dashboard
    header("Location: drivers_dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Receipt</title>
    <style>
        .receipt {
            border: 1px solid black;
            padding: 20px;
            width: 300px;
            margin: 20px auto;
        }

        .receipt h2 {
            text-align: center;
        }

        .receipt p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="receipt">
        <h2>Receipt</h2>
        <p><strong>Order ID:</strong> <?php echo $orderDetails['OrdID']; ?></p>
        <p><strong>Customer Name:</strong> <?php echo $orderDetails['CusName']; ?></p>
        <p><strong>Total:</strong> <?php echo $orderDetails['OrdTotal']; ?></p>
        <p><strong>Ordered at:</strong> <?php echo $orderDetails['TimeOfOrdering']; ?></p>
        <p><strong>Expected Delivery Time:</strong> <?php echo $orderDetails['ExpectedDeliveryTime']; ?></p>
    </div>

    <br>
    <form method="post" action="issue_payment.php">
        <input type="hidden" name="driv_id" value="<?php echo $_GET['driv_id']; ?>">
        <input type="submit" name="complete_order" value="Complete Order">
    </form>

    <br>
    <form method="post" action="issue_payment.php">
        <input type="hidden" name="driv_id" value="<?php echo $_GET['driv_id']; ?>">
        <input type="submit" name="cancel_order" value="Cancel Order">
    </form>

    <br>
    <form method="post" action="drivers_dashboard.php">
        <input type="hidden" name="driv_id" value="<?php echo $_GET['driv_id']; ?>">
        <input type="submit" name="back_dashboard" value="Back to Dashboard">
    </form>
</body>
</html>
