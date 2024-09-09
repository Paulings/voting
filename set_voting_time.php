<?php
session_start();
include 'db_connection.php';

// Check if form is submitted to set voting time
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['start_time']) && isset($_POST['end_time'])) {
    // Retrieve start time and end time from form submission
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];

    // Insert or update the voting session in the database
    $insert_query = "INSERT INTO voting_sessions (start_time, end_time) VALUES ('$start_time', '$end_time') ON DUPLICATE KEY UPDATE start_time='$start_time', end_time='$end_time'";
    if ($conn->query($insert_query) === TRUE) {
        echo "Voting time set successfully!";
    } else {
        echo "Error: " . $conn->error;
    }
}

// Check if the "End Voting" button is clicked
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['end_voting'])) {
    // Delete the voting session from the database
    $delete_query = "DELETE FROM voting_sessions";
    if ($conn->query($delete_query) === TRUE) {
        echo "Voting ended successfully!";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Voting Time</title>
    <link rel="stylesheet" href="../include/css/set_voting_time.css">
</head>
<body>
    <div class="container">
        <h1>Set Voting Time</h1>
        <form method="POST" action="../vote_casting/set_voting_time.php">
            <label for="start_time">Start Time:</label>
            <input type="datetime-local" id="start_time" name="start_time" required>
            <label for="end_time">End Time:</label>
            <input type="datetime-local" id="end_time" name="end_time" required>
            <button type="submit">Set Voting Time</button>
        </form>
        <form method="POST" action="">
            <button type="submit" name="end_voting">End Voting</button>
        </form>
        <div id="countdown"></div>
    </div>

    <script>
        // Function to update countdown timer
        function updateCountdown(endTime) {
            // Get the current date and time
            var now = new Date().getTime();

            // Calculate the remaining time
            var distance = new Date(endTime) - now;

            // Calculate days, hours, minutes, and seconds
            var days = Math.floor(distance / (1000 * 60 * 60 * 24));
            var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            var seconds = Math.floor((distance % (1000 * 60)) / 1000);

            // Display the countdown
            document.getElementById("countdown").innerHTML = "Countdown: " + days + "d " + hours + "h "
                + minutes + "m " + seconds + "s ";

            // Update the countdown every 1 second
            setTimeout(function() {
                updateCountdown(endTime);
            }, 1000);
        }

        // Get the end time from the server and start the countdown
        var endTime = "<?php echo $end_time; ?>";
        updateCountdown(endTime);
    </script>
</body>
</html>
