<!DOCTYPE html>
<html>
<head>
    <title>Add New Products</title>
    <style>
        label {
            display: inline-block;
            width: 150px;
        }
    </style>
</head>
<body>
    <h1>Add New Products</h1>
    <form method="post" action="">
        <label for="prodName">Product Name:</label>
        <input type="text" name="prodName" id="prodName" required>
        <br>
        <label for="prodPrice">Product Price:</label>
        <input type="number" name="prodPrice" id="prodPrice" step="0.01" required>
        <br>
        <label for="prodRemain">Product Remain:</label>
        <input type="number" name="prodRemain" id="prodRemain" required>
        <br>
        <input type="submit" name="addProduct" value="Add Product">
    </form>

    <?php
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['addProduct'])) {
        $servername = "localhost";
        $username = "id21105111_2lsr_manager";
        $password = "2lsr-for-DATABASE";
        $dbname = "id21105111_2lsr_test_db";
        $conn = new mysqli($servername, $username, $password, $dbname);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Get input data
        $prodName = $_POST['prodName'];
        $prodPrice = $_POST['prodPrice'];
        $prodRemain = $_POST['prodRemain'];

        // Prepare the SQL statement with placeholders
        $sql = "INSERT INTO PRODUCTS (ProdName, ProdPrice, ProdRemain) VALUES (?, ?, ?)";

        // Use prepared statement to avoid SQL injection
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sdi", $prodName, $prodPrice, $prodRemain);

        // Execute the prepared statement
        if ($stmt->execute()) {
            echo "<p>New product added successfully.</p>";
        } else {
            echo "<p>Error adding product: " . $conn->error . "</p>";
        }

        $stmt->close();
        $conn->close();
    }
    ?>
</body>
</html>
