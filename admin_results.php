<?php
include 'db_connection.php'; // Include your database connection script

// Function to fetch candidates and their votes
function getCandidatesWithVotes($conn) {
    $query = "SELECT cr.id, cr.first_name, cr.sir_name, cr.candidate, cr.photo, COUNT(v.voter_id) AS vote_count
              FROM cont_register_tb cr
              LEFT JOIN votes v ON cr.id = v.candidate_id
              GROUP BY cr.id, cr.first_name, cr.sir_name, cr.candidate, cr.photo
              ORDER BY cr.candidate"; // Adjust ORDER BY clause as needed

    $result = $conn->query($query);
    $candidates = []; // Initialize $candidates as an empty array

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $candidate = [
                'id' => $row['id'],
                'name' => $row['first_name'] . " " . $row['sir_name'],
                'position' => $row['candidate'],
                'photo' => $row['photo'],
                'vote_count' => $row['vote_count']
            ];
            $candidates[] = $candidate;
        }
    }

    return $candidates;
}

// Get candidates and their votes
$candidates = getCandidatesWithVotes($conn);

// HTML begins here
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candidate Votes</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            padding: 20px;
        }
        .candidate {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        .candidate h2 {
            margin-top: 0;
            margin-bottom: 10px;
            font-size: 20px;
        }
        .candidate-info {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        .candidate-info img {
            width: 100px;
            height: auto;
            border-radius: 6px;
            margin-right: 15px;
        }
        .candidate-details {
            flex: 1;
        }
        .candidate-details p {
            margin: 5px 0;
        }
        .vote-count {
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Candidate Votes</h1>
    <?php
    if (!empty($candidates)) {
        foreach ($candidates as $candidate) {
            ?>
            <div class="candidate">
                <h2>Candidate: <?php echo htmlspecialchars($candidate['name']); ?></h2>
                <div class="candidate-info">
                    <img src="../uploads/<?php echo htmlspecialchars($candidate['photo']); ?>" alt="Candidate Photo">
                    <div class="candidate-details">
                        <p><strong>Candidate ID:</strong> <?php echo htmlspecialchars($candidate['id']); ?></p>
                        <p><strong>Position:</strong> <?php echo htmlspecialchars($candidate['position']); ?></p>
                        <p class="vote-count">Vote Count: <?php echo htmlspecialchars($candidate['vote_count']); ?></p>
                    </div>
                </div>
            </div>
            <?php
        }
    } else {
        echo "<p>No candidates found.</p>";
    }
    ?>
</div>

</body>
</html>

<?php $conn->close(); ?>
