<?php

if (isset($_POST['submit'])) {
    // Process the registration form and insert data into the CUSTOMERS table
    $servername = "localhost";
    $username = "id21105111_2lsr_manager";
    $password = "2lsr-for-DATABASE";
    $dbname = "id21105111_2lsr_test_db";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $cusName = $_POST['cus_name'];
    $cusAddressLat = $_POST['cus_address_latitude'];
    $cusAddressLong = $_POST['cus_address_longitude'];
    $cusPhone = !empty($_POST['cus_phone']) ? $_POST['cus_phone'] : null;
    $cusLineID = !empty($_POST['cus_line_id']) ? $_POST['cus_line_id'] : null;
    $noteToDriver = !empty($_POST['note_to_driver']) ? $_POST['note_to_driver'] : null;

    $sql = "INSERT INTO CUSTOMERS (CusName, CusAddressLatitude, CusAddressLongtitude, CusPhone, CusLineID, NoteToDriver)
            VALUES ('$cusName', $cusAddressLat, $cusAddressLong, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $cusPhone, $cusLineID, $noteToDriver);
    
    if ($stmt->execute()) {
        // Get the newly generated CusID
        $cusID = $stmt->insert_id;
        $stmt->close();
        $conn->close();

        // Redirect to the order products page with the newly generated CusID
        header("Location: order_products.php?cus_id=$cusID");
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
    <title>Customer Registration</title>
</head>
<body>
    <h1>Customer Registration</h1>
    <p>Get latitude and longtitude for your address <a href="https://www.latlong.net/">here</a>.</p>
    <form method="post">
        <label for="cus_name">Name:</label>
        <input type="text" name="cus_name" id="cus_name" required><br>
        <label for="cus_address_latitude">Address Latitude:</label>
        <input type="text" name="cus_address_latitude" id="cus_address_latitude" required><br>
        <label for="cus_address_longitude">Address Longitude:</label>
        <input type="text" name="cus_address_longitude" id="cus_address_longitude" required><br>
        <label for="cus_phone">Phone(XX-XXXX-XXXX):</label>
        <input type="text" name="cus_phone" id="cus_phone"><br>
        <label for="cus_line_id">LINE ID:</label>
        <input type="text" name="cus_line_id" id="cus_line_id"><br>
        <label for="note_to_driver">Note to Driver:</label><br>
        <textarea name="note_to_driver" id="note_to_driver" rows="4" cols="50"></textarea><br>
        <input type="submit" name="submit" value="Register">
    </form>
</body>
</html>
