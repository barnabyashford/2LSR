<!DOCTYPE html>
<html>
<head>
    <title>Change Records</title>
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
    <h1>Change Records</h1>
    <form method="post" action="index.php">
        <input type="submit" name="dashboard" value="Go Back to Dashboard">
    </form>
    <br>
    <?php
    // Function to retrieve all table names from the database
    function getTableNames()
    {
        $servername = "localhost";
        $username = "id21105111_2lsr_manager";
        $password = "2lsr-for-DATABASE";
        $dbname = "id21105111_2lsr_test_db";

        $conn = new mysqli($servername, $username, $password, $dbname);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $sql = "SHOW TABLES";
        $result = $conn->query($sql);

        $tableNames = array();
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_row()) {
                $tableNames[] = $row[0];
            }
        }

        $conn->close();

        return $tableNames;
    }

    $tableNames = getTableNames();
    ?>
    
    <form method="post" action="">
        <label for="table_name">Select Table:</label>
        <select name="table_name" id="table_name" required>
            <option value="">-- Select a table --</option>
            <?php foreach ($tableNames as $tableName): ?>
                <option value="<?php echo $tableName; ?>"><?php echo $tableName; ?></option>
            <?php endforeach; ?>
        </select>
        <input type="submit" name="showtable" value="show table">
        <br>
        <label for="id">ID:</label>
        <input type="text" name="id" id="id">
        <br>
        <label for="second_id">Second ID (For ProdID to update in order's product lists table):</label>
        <input type="text" name="second_id" id="second_id">
        <br>
        <label for="column">Column to Update:</label>
        <input type="text" name="column" id="column">
        <br>
        <label for="value">New Value (optional for updates):</label>
        <input type="text" name="value" id="value">
        <br>
        <input type="submit" name="change_records" value="Change Records">
        <input type="submit" name="delete_records" value="Delete Records">
    </form>

    <br>
    <h2>Tables Overview</h2>
    <!-- Display the selected table as a table -->
    <?php
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $tableName = $_POST['table_name'];
        $id = $_POST['id'];
        $secondId = $_POST['second_id'];
        $column = $_POST['column'];
        $value = $_POST['value'];

        // Validate input data
        if (!empty($_POST['showtable'])) {
            $servername = "localhost";
            $username = "id21105111_2lsr_manager";
            $password = "2lsr-for-DATABASE";
            $dbname = "id21105111_2lsr_test_db";

            $conn = new mysqli($servername, $username, $password, $dbname);

            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            $sql = "SELECT * FROM $tableName";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                echo "<table>";
                echo "<tr>";
                while ($field = $result->fetch_field()) {
                    echo "<th>" . $field->name . "</th>";
                }
                echo "</tr>";

                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    foreach ($row as $key => $value) {
                        echo "<td>" . $value . "</td>";
                    }
                    echo "</tr>";
                }

                echo "</table>";
            } else {
                echo "<p>No records found in the selected table.</p>";
            }

            $conn->close();
        }

        // Handling "Change Records" button
        if (isset($_POST['change_records'])) {
            if (!empty($tableName) && !empty($id) && !empty($column)) {
                // Add your code to update the record here
                $servername = "localhost";
                $username = "id21105111_2lsr_manager";
                $password = "2lsr-for-DATABASE";
                $dbname = "id21105111_2lsr_test_db";

                $conn = new mysqli($servername, $username, $password, $dbname);

                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }
                
                if ($tableName == "PRODUCTS") {
                    $sql = "UPDATE $tableName SET $column = '$value' WHERE ProdID = '$id'";
                } else {
                    if ($tableName == "DRIVERS") {
                        $sql = "UPDATE $tableName SET $column = '$value' WHERE DriverID = '$id'";
                    } else {
                        if ($tableName == "CUSTOMERS") {
                            $sql = "UPDATE $tableName SET $column = '$value' WHERE CusID = '$id'";
                        } else {
                            if ($tableName == "ORDERS") {
                                $sql = "UPDATE $tableName SET $column = '$value' WHERE OrdID = '$id'";
                            } else {
                                $sql = "UPDATE $tableName SET $column = '$value' WHERE OrdID = '$id' AND ProdID = '$secondId'";
                            }
                        }
                    }
                }


                if ($conn->query($sql) === TRUE) {
                    echo "<p>Record updated successfully.</p>";
                } else {
                    echo "<p>Error updating record: " . $conn->error . "</p>";
                }

                $conn->close();
            } else {
                echo "<p>Please provide the required information.</p>";
            }
        }

        // Handling "Delete Records" button
        if (isset($_POST['delete_records'])) {
            if (!empty($tableName) && !empty($id)) {
                // Add your code to delete the record here
                $servername = "localhost";
                $username = "id21105111_2lsr_manager";
                $password = "2lsr-for-DATABASE";
                $dbname = "id21105111_2lsr_test_db";

                $conn = new mysqli($servername, $username, $password, $dbname);

                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }


                // Check if the secondId is provided (applicable for ORDER_PRODUCT_LISTS)
                if (!empty($secondId)) {
                    $sql = "CALL DeleteData('$tableName', '$id', '$secondId')";
                } else {
                    $sql = "CALL DeleteData('$tableName', '$id', NULL)";
                }

                if ($conn->query($sql) === TRUE) {
                    echo "<p>Record deleted successfully.</p>";
                } else {
                    echo "<p>Error deleting record: " . $conn->error . "</p>";
                }

                $conn->close();
            } else {
                echo "<p>Please provide the required information.</p>";
            }
        }
    }
    ?>
</body>
</html>
