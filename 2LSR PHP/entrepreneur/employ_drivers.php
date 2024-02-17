<?php
if (isset($_POST['submit'])) {
    // Process the registration form and insert data into the DRIVERS table
    $servername = "localhost";
        $username = "id21105111_2lsr_manager";
        $password = "2lsr-for-DATABASE";
        $dbname = "id21105111_2lsr_test_db";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Sanitize and validate inputs
    $driverName = mysqli_real_escape_string($conn, $_POST['driver_name']);
    $driverPhone = isset($_POST['driver_phone']) ? $_POST['driver_phone'] : null;

    // Use prepared statement for all values
    $sql = "INSERT INTO DRIVERS (DriverName, DriverPhone)
            VALUES (?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $driverName, $driverPhone);

    if ($stmt->execute()) {
        // Get the newly generated DriverID
        $driverID = $stmt->insert_id;
        $stmt->close();
        $conn->close();

        // Redirect to the driver's dashboard page with the newly generated DriverID
        header("Location: index.php");
        exit;
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Employ Driver</title>
</head>
<body>
    <h1>Employ Driver</h1>
    <form method="post">
        <label for="driver_name">Name:</label>
        <input type="text" name="driver_name" id="driver_name" required><br>
        <label for="driver_phone">Phone (XX-XXXX-XXXX):</label>
        <input type="text" name="driver_phone" id="driver_phone"><br>
        <input type="submit" name="submit" value="Register">
    </form>
</body>
</html>
