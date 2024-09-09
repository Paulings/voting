<?php
// Include your database connection file
include 'db_connection.php';

// Query to fetch candidates and their vote counts
$query = "SELECT cr.id, cr.first_name, cr.sir_name, cr.candidate, cr.photo, COUNT(v.voter_id) AS vote_count
          FROM cont_register_tb cr
          LEFT JOIN votes v ON cr.id = v.candidate_id
          GROUP BY cr.id, cr.first_name, cr.sir_name, cr.candidate, cr.photo
          ORDER BY cr.candidate";

$result = $conn->query($query);

if ($result->num_rows > 0) {
    // Initialize an empty array to store candidates
    $candidates = [];

    // Fetch candidates into the $candidates array
    while ($row = $result->fetch_assoc()) {
        $candidates[] = $row;
    }
} else {
    $candidates = []; // Initialize $candidates as an empty array if no candidates found
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Election Results</title>
    <style>

body {
    font-family: Arial, sans-serif;
    background-color: #f0f0f0;
    margin: 0;
    padding: 20px;
}

.container {
    max-width: 75%;
    margin: 0 auto;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
    padding: 20px;
}

h1 {
    font-size: 2em;
    color: #333;
    text-align: center;
    margin-bottom: 20px;
}

.candidate {
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    padding: 15px;
    margin-bottom: 10px;
    overflow: hidden;
}

.candidate h2 {
    font-size: 1.5em;
    color: #333;
    margin-top: 0;
}

.candidate p {
    margin: 5px 0;
    color: #555;
}

.candidate img {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    object-fit: cover;
    float: right;
    margin-left: 15px;
}

hr {
    border: 0;
    height: 1px;
    background: #ccc;
    margin: 20px 0;
}

@media (max-width: 600px) {
    .candidate img {
        float: none;
        display: block;
        margin: 0 auto 10px;
    }
}

        </style>
</head>
<body>
    <div class="container">
        <h1>Election Results</h1>
        
        <?php if (!empty($candidates)): ?>
            <!-- Display candidates and their vote counts -->
            <?php foreach ($candidates as $candidate): ?>
                <div class="candidate">
                    <h2><?php echo htmlspecialchars($candidate['first_name'] . ' ' . $candidate['sir_name']); ?></h2>
                    <p><strong>Candidate ID:</strong> <?php echo htmlspecialchars($candidate['id']); ?></p>
                    <p><strong>Position:</strong> <?php echo htmlspecialchars($candidate['candidate']); ?></p>
                    <p><strong>Vote Count:</strong> <?php echo htmlspecialchars($candidate['vote_count']); ?></p>
                    <img src="../uploads/<?php echo htmlspecialchars($candidate['photo']); ?>" alt="Candidate Photo">
                </div>
                <hr>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No candidates found.</p>
        <?php endif; ?>
    </div>
</body>
</html>
