<!DOCTYPE html>
<html>
<head>
    <title>Data Analytics</title>
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
    <h1>Data Analytics</h1>
    <form method="post" action="">
        <label for="option">Choose an option:</label>
        <select name="option" id="option" required>
            <option value="">-- Select an option --</option>
            <option value="AllOrderInTime">Order in specific time</option>
            <option value="SummarisedTotal">Total revenue in specific time</option>
            <option value="SummaryByCustomer">Summary by customer</option>
            <option value="DriverStatistics">Driver performance</option>
        </select>
        <br>
        <label for="year">Year:</label>
        <input type="number" name="year" id="year" required>
        <br>
        <label for="month">Month:</label>
        <input type="number" name="month" id="month" min="1" max="12">
        <br>
        <label for="day">Day:</label>
        <input type="number" name="day" id="day" min="1" max="31">
        <br>
        <label for="optional_value">Optional Value:</label>
        <input type="text" name="optional_value" id="optional_value">
        <br>
        <input type="submit" name="visualise" value="Visualise">
    </form>

    <br>
    <?php
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['visualise'])) {
        $option = $_POST['option'];
        $year = $_POST['year'];
        $month = !empty($_POST['month']) ? $_POST['month'] : null;
        $day = !empty($_POST['day']) ? $_POST['day'] : null;
        $optionalValue = !empty($_POST['optional_value']) ? $_POST['optional_value'] : null;

        // Add your code to construct the SQL query and display the results here
        $servername = "localhost";
        $username = "id21105111_2lsr_manager";
        $password = "2lsr-for-DATABASE";
        $dbname = "id21105111_2lsr_test_db";

        $conn = new mysqli($servername, $username, $password, $dbname);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $sql = "";
        $condition = "";

        switch ($option) {
            case "AllOrderInTime":
                $condition = "WHERE YEAR(TimeOfOrdering) = $year";
                if (!empty($month)) {
                    $condition .= " AND MONTH(TimeOfOrdering) = $month";
                }
                if (!empty($day)) {
                    $condition .= " AND DAY(TimeOfOrdering) = $day";
                }
                if (!empty($optionalValue)) {
                    $condition .= " AND OrdStatus = '$optionalValue'";
                }
                $sql = "SELECT * FROM ORDERS $condition";
                break;
            case "SummarisedTotal":
                $condition = "WHERE YEAR(TimeOfOrdering) = $year";
                if (!empty($month)) {
                    $condition .= " AND MONTH(TimeOfOrdering) = $month";
                }
                if (!empty($day)) {
                    $condition .= " AND DAY(TimeOfOrdering) = $day";
                }
                $sql = "SELECT COUNT(OrdID) AS OrderCount, SUM(OrdTotal) as TotalRevenue FROM ORDERS $condition";
                break;
            case "SummaryByCustomer":
                $condition = "WHERE YEAR(o.TimeOfOrdering) = $year";
                if (!empty($month)) {
                    $condition .= " AND MONTH(o.TimeOfOrdering) = $month";
                }
                if (!empty($day)) {
                    $condition .= " AND DAY(o.TimeOfOrdering) = $day";
                }
                if (!empty($optionalValue)) {
                    $condition .= " AND o.CusID = '$optionalValue'";
                }
                $sql = "SELECT o.CusID AS CusID, c.CusName AS Name, SUM(o.OrdTotal) as TotalRevenue FROM ORDERS o, CUSTOMERS c $condition  AND c.CusID = o.CusID GROUP BY o.CusID";
                break;
            case "DriverStatistics":
                $condition = "WHERE YEAR(o.TimeOfOrdering) = $year";
                if (!empty($month)) {
                    $condition .= " AND DAY(o.TimeOfOrdering) = $month";
                }
                if (!empty($day)) {
                    $condition .= " AND DAY(o.TimeOfOrdering) = $day";
                }
                if (!empty($optionalValue)) {
                    $condition .= " AND o.DriverID = '$optionalValue'";
                }
                $sql = "SELECT d.DriverID AS DriverID, d.DriverName AS DriverName, COUNT(DISTINCT o.OrdID) AS OrderCount, SUM(o.OrdTotal) AS revenue FROM ORDERS o, DRIVERS d $condition AND d.DriverID = o.DriverID AND o.OrdStatus = 'COMPLETED' GROUP BY o.DriverID";
                break;
        }

        if (!empty($sql)) {
            $result = $conn->query($sql);

            if ($result) {
                if ($result->num_rows > 0) {
                    echo "<h2>Query Result:</h2>";
                    echo "<table>";
                    // Display column names
                    echo "<tr>";
                    while ($field = $result->fetch_field()) {
                        echo "<th>" . $field->name . "</th>";
                    }
                    echo "</tr>";

                    // Display data
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        foreach ($row as $value) {
                            echo "<td>" . $value . "</td>";
                        }
                        echo "</tr>";
                    }
                    echo "</table>";
                } else {
                    echo "<p>No data found for the selected parameters.</p>";
                }
            } else {
                echo "<p>Error executing the query: " . $conn->error . "</p>";
            }
        } else {
            echo "<p>Please select an option and provide the required information.</p>";
        }

        $conn->close();
    }
    ?>
</body>
</html>