<!DOCTYPE html>
<html>
<head>
    <title>Entrepreneur Dashboard</title>
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
    <h1>Welcome, Entrepreneur!</h1>

    <!-- Add buttons for Prepare Orders and Assign Drivers -->
    <br>
    <a href="../index.php"><button>Go Back</button></a><p>
    <a href="data_analytics.php"><button>Data Analytics</button></a><p>
    <a href="prepare_orders.php"><button>Prepare Orders</button></a>
    <a href="employ_drivers.php"><button>Employ Drivers</button></a>
    <a href="assign_drivers.php"><button>Assign Drivers</button></a>
    <a href="change_records.php"><button>Change Records</button></a>
    <a href="add_new_products.php"><button>Add new products</button></a>
    

    <h2>Order Overview</h2>
    <table>
        <tr>
            <th>Order ID</th>
            <th>Customer Name</th>
            <th>Driver ID</th>
            <th>Products</th>
            <th>Total</th>
            <th>Status</th>
            <!-- Remove the "Action" column -->
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

        $sql = "SELECT o.OrdID, c.CusName, o.DriverID, GROUP_CONCAT(p.ProdName,' : ',opl.ProdQuan SEPARATOR '<p>') AS ProdList, o.OrdTotal, o.OrdStatus
                FROM ORDERS o, CUSTOMERS c ,ORDER_PRODUCT_LISTS opl, PRODUCTS p
                WHERE o.CusID = c.CusID
                AND opl.OrdID = o.OrdID
                AND opl.ProdID = p.ProdID
                GROUP BY o.OrdID";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['OrdID'] . "</td>";
                echo "<td>" . $row['CusName'] . "</td>";
                echo "<td>" . $row['DriverID'] . "</td>";
                echo "<td>" . $row['ProdList'] . "</td>";
                echo "<td>" . $row['OrdTotal'] . "</td>";
                echo "<td>" . $row['OrdStatus'] . "</td>";
                // Remove the "Action" column
                // echo "<td><a href='order_details.php?ord_id=" . $row['OrdID'] . "'>View Details</a></td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='5'>No orders found.</td></tr>";
        }

        $conn->close();
        ?>
    </table>

    <h2>Driver Overview</h2>
    <table>
        <tr>
            <th>Driver ID</th>
            <th>Driver Name</th>
            <th>Total Orders</th>
            <th>Total Revenue</th>
        </tr>
        <!-- PHP code to fetch driver data from the database and populate the table -->
        <?php
        $conn = new mysqli($servername, $username, $password, $dbname);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $sql = "SELECT d.DriverID, d.DriverName, COUNT(DISTINCT o.OrdID) AS TotalOrders, SUM(o.OrdTotal) AS TotalRevenue
                FROM DRIVERS d
                LEFT JOIN ORDERS o ON d.DriverID = o.DriverID AND o.OrdStatus = 'COMPLETED'
                GROUP BY d.DriverID, d.DriverName";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['DriverID'] . "</td>";
                echo "<td>" . $row['DriverName'] . "</td>";
                echo "<td>" . $row['TotalOrders'] . "</td>";
                echo "<td>" . $row['TotalRevenue'] . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='4'>No drivers found.</td></tr>";
        }

        $conn->close();
        ?>
    </table>

    <h2>Customer Overview</h2>
    <table>
        <tr>
            <th>Customer ID</th>
            <th>Customer Name</th>
            <th>Phone</th>
            <th>LINE ID</th>
            <th>Note to Driver</th>
        </tr>
        <!-- PHP code to fetch customer data from the database and populate the table -->
        <?php
        $conn = new mysqli($servername, $username, $password, $dbname);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $sql = "SELECT * FROM CUSTOMERS";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['CusID'] . "</td>";
                echo "<td>" . $row['CusName'] . "</td>";
                echo "<td>" . $row['CusPhone'] . "</td>";
                echo "<td>" . $row['CusLineID'] . "</td>";
                echo "<td>" . $row['NoteToDriver'] . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='5'>No customers found.</td></tr>";
        }

        $conn->close();
        ?>
    </table>

    <h2>Product Overview</h2>
    <table>
        <tr>
            <th>Product ID</th>
            <th>Product Name</th>
            <th>Price</th>
        </tr>
        <!-- PHP code to fetch product data from the database and populate the table -->
        <?php
        $conn = new mysqli($servername, $username, $password, $dbname);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $sql = "SELECT * FROM PRODUCTS";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['ProdID'] . "</td>";
                echo "<td>" . $row['ProdName'] . "</td>";
                echo "<td>" . $row['ProdPrice'] . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='3'>No products found.</td></tr>";
        }

        $conn->close();
        ?>
    </table>
</body>
</html>
