<!DOCTYPE html>
<html>
<head>
    <title>Assign Drivers</title>
</head>
<body>
    
    <h1>Assign Drivers</h1>
    <form method="post" action="">
        <label for="order_id">Order ID:</label>
        <input type="text" name="order_id" id="order_id" required>
        <br>
        <label for="driver_id">Select Driver:</label>
        <select name="driver_id" id="driver_id" required>
            <option value="">-- Select a driver --</option>
            <!-- PHP code to fetch driver data from the database and populate the drop-down list -->
            <?php
            $servername = "localhost";
            $username = "id21105111_2lsr_manager";
            $password = "2lsr-for-DATABASE";
            $dbname = "id21105111_2lsr_test_db";

            $conn = new mysqli($servername, $username, $password, $dbname);

            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            $sql = "SELECT DriverID, DriverName FROM DRIVERS";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='" . $row['DriverID'] . "'>" . $row['DriverName'] . "</option>";
                }
            }

            $conn->close();
            ?>
        </select>
        <br>
        <input type="submit" name="assign" value="Assign Driver">
    </form>

    <?php
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        if (isset($_POST['assign'])) {
            $servername = "localhost";
                $username = "id21105111_2lsr_manager";
                $password = "2lsr-for-DATABASE";
                $dbname = "id21105111_2lsr_test_db";

            $conn = new mysqli($servername, $username, $password, $dbname);

            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            $orderID = $_POST['order_id'];
            $driverID = $_POST['driver_id'];

            // Check if the order and driver exist in the database
            $checkSql = "SELECT COUNT(ORDERS.OrdID) AS OrderExistence, COUNT(DRIVERS.DriverID) AS DriverExistence FROM ORDERS, DRIVERS WHERE ORDERS.OrdID = $orderID AND DRIVERS.DriverID = $driverID";
            $checkResult = $conn->query($checkSql);
            $checkRow = $checkResult->fetch_assoc();

            if ($checkRow['OrderExistence'] > 0 && $checkRow['DriverExistence'] > 0) {
                // Call the AssignOrder procedure to assign the driver to the order
                $assignSql = "CALL AssignOrder($driverID, $orderID)";
                if ($conn->query($assignSql) === TRUE) {
                    echo "<p>Order successfully assigned to the driver.</p>";
                } else {
                    echo "<p>Error assigning driver to the order: " . $conn->error . "</p>";
                }
            } else {
                echo "<p>Invalid Order ID or Driver ID.</p>";
            }

            $conn->close();
        }
    }
    ?>

    <h2>Order Overview</h2>
    <table>
        <tr>
            <th>Order ID</th>
            <th>Customer Name</th>
            <th>Driver ID</th>
            <th>Time of Ordering</th>
            <th>Expected Delivery Time</th>
            <th>Order Status</th>
        </tr>
        <!-- PHP code to fetch order data from the database and populate the table -->
        <?php
        $servername = "localhost";
        $username = "id21105111_2lsr_manager";
        $password = "2lsr-for-DATABASE";
        $dbname = "id21105111_2lsr_test_db";

        $conn = new mysqli($servername, $username, $password, $dbname);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $sql = "SELECT o.OrdID, c.CusName, o.DriverID, o.TimeOfOrdering, o.ExpectedDeliveryTime, o.OrdStatus
            FROM ORDERS o
            JOIN CUSTOMERS c ON o.CusID = c.CusID
            WHERE o.OrdStatus = 'CONFIRMED'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['OrdID'] . "</td>";
                echo "<td>" . $row['CusName'] . "</td>";
                echo "<td>" . $row['DriverID'] . "</td>";
                echo "<td>" . $row['TimeOfOrdering'] . "</td>";
                echo "<td>" . $row['ExpectedDeliveryTime'] . "</td>";
                echo "<td>" . $row['OrdStatus'] . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='6'>No confirmed orders found.</td></tr>";
        }

        $conn->close();
        ?>
    </table>

    <form method="post" action="index.php">
        <input type="submit" name="dashboard" value="Go Back to Dashboard">
    </form>
</body>
</html>