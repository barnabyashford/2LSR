<?php
function driverExists($drivID) {
    $servername = "localhost";
    $username = "id21105111_2lsr_manager";
    $password = "2lsr-for-DATABASE";
    $dbname = "id21105111_2lsr_test_db";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $drivID = intval($drivID); // Sanitize input as an integer

    $sql = "SELECT DriverID FROM DRIVERS WHERE DriverID = $drivID";
    $result = $conn->query($sql);

    $conn->close();

    return ($result->num_rows > 0);
}

if (isset($_POST['submit'])) {
    // Check if the CusID exists in the CUSTOMERS table
    if (!empty($_POST['driv_id'])) {
        $drivID = $_POST['driv_id'];
        if (driverExists($drivID)) {
            // Redirect to the order_products.php page with the cus_id parameter
            header("Location: drivers_dashboard.php?driv_id=$drivID");
            exit;
        } else {
            // Display error message or handle accordingly
            echo "Your ID: $drivID does not exist in our database. Please contact your supervisor.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>2LSR Driver Form</title>
</head>
<body>
    <h1>2LSR Driver Form</h1>
    <p>Please input your ID.</p>
    <form method="post" action="">
        <label for="driv_id">Driver ID:</label>
        <input type="text" name="driv_id" id="driv_id">
        <input type="submit" name="submit" value="Submit">
    </form>
</body>
</html>
