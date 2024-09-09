<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Page</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 800px;
            margin: auto;
            background: white;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        p {
            margin-bottom: 10px;
        }
        button {
            background-color: #ff0000;
            color: #fff;
            border: none;
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #cc0000;
        }
        header {
            background-color: #333;
            color: #fff;
            padding: 20px;
            text-align: center;
        }

        header h1 {
            margin: 0;
            font-size : 20px;
            color : white;
            font-size : 35px;
        }
        
        nav {
            margin-top: 10px;
            margin-left: 75%;
        }

        nav a {
            text-decoration: none;
            color: #fff;
            background-color: blue;
            padding: 8px 16px;
            margin: 0 10px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        nav a:hover {
            background-color: green;
        }
    </style>
</head>
<body>
<header>
        <h1>e-Voting system</h1>
        <nav>
            <a href="../index.html">Back</a>
        </nav>
    </header>
<?php
include('db_connection.php');

// Check if delete button is clicked
if(isset($_POST['delete_button'])) {
    $id_to_delete = $_POST['delete_button'];

    // Prepare and execute the SQL query to delete the selected election detail
    $sql_delete = "DELETE FROM election_detail_tb WHERE id = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    
    // Check if the statement was prepared successfully
    if ($stmt_delete) {
        $stmt_delete->bind_param("i", $id_to_delete);
        if ($stmt_delete->execute()) {
            // Redirect to the same page to refresh the election details after deletion
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit();
        } else {
            echo '<script>alert("Error occurred while deleting election detail")</script>';
        }
        $stmt_delete->close();
    } else {
        echo '<script>alert("Error occurred while preparing delete statement")</script>';
    }
}

// Fetch election details from the database
$sql = "SELECT * FROM election_detail_tb";
$result = $conn->query($sql);

// Check if there are rows in the result
if ($result->num_rows > 0) {
    // Output data of each row
    while($row = $result->fetch_assoc()) {
        echo "<div class='container'>";
        echo "<h1>Election detail</h1>";
        echo "<p>" . $row['Election_detail'] . "</p>";
        // Add delete button with form for each election detail
        echo "<form action='" . $_SERVER['PHP_SELF'] . "' method='POST'>";
        echo "<input type='hidden' name='delete_button' value='" . $row['id'] . "'>";
    
        echo "</form>";
        echo "</div>";
    }
} else {
    echo "<div class='container'>";
    echo "<h1>No Election Detail Found</h1>";
    echo "</div>";
}

$conn->close();
?>
</body>
</html>
