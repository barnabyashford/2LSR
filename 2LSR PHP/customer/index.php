<?php
function customerExists($cusID) {
    $servername = "localhost";
    $username = "id21105111_2lsr_manager";
    $password = "2lsr-for-DATABASE";
    $dbname = "id21105111_2lsr_test_db";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $cusID = intval($cusID); // Sanitize input as an integer

    $sql = "SELECT CusID FROM CUSTOMERS WHERE CusID = $cusID";
    $result = $conn->query($sql);

    $conn->close();

    return ($result->num_rows > 0);
}

if (isset($_POST['submit'])) {
    // Check if the CusID exists in the CUSTOMERS table
    if (!empty($_POST['cus_id'])) {
        $cusID = $_POST['cus_id'];
        if (customerExists($cusID)) {
            // Redirect to the order_products.php page with the cus_id parameter
            header("Location: order_products.php?cus_id=$cusID");
            exit;
        } else {
            // Display error message or handle accordingly
            echo "Customer with CusID $cusID does not exist.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Customer Order Form</title>
</head>
<body>
    <h1>Customer Order Form</h1>
    <p>PLease input your ID</p>
    <form method="post" action="">
        <label for="cus_id">Customer ID:</label>
        <input type="text" name="cus_id" id="cus_id">
        <input type="submit" name="submit" value="Submit">
    </form>
    <br>
    <p>Your first time here? Register today!</p>
    <form method="post" action="customer_register.php">
        <input type="submit" name="register" value="Register">
    </form>
</body>
</html>
