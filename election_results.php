<?php
include 'db_connection.php';

// Function to get voting session details from the database
function getVotingSession($conn) {
    $query = "SELECT * FROM voting_sessions ORDER BY id DESC LIMIT 1";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }

    return null;
}

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

// Check current visibility status
$visibilityQuery = "SELECT value FROM settings WHERE name='results_visibility'";
$visibilityResult = $conn->query($visibilityQuery);
$visibilityRow = $visibilityResult->fetch_assoc();
$resultsVisible = $visibilityRow['value'] == '1';

// Get candidates and their votes
$candidates = getCandidatesWithVotes($conn);

// HTML begins here
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voting Results</title>
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
        .winner {
            background-color: #baf5d5; /* Light green */
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .winners-container {
            margin-top: 30px;
        }
        button {
            background-color: blue;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
            width : 200px;
            padding : 15px;
        }
        button:hover {
            background-color: #cc0000;
        }
        header {
          background-color: #007bff;
            color: #fff;
            padding: 20px;
            text-align: center;
            
            width : 100%;
            margin-bottom : 0%;
            
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
        .button {
            margin-left : 80%;
        }
        
    </style>
</head>
<body>
    <header>
        <h1> Election results page</h1>
<a href="../vote_casting/voting_page.php"><button class="button">back</button>
        </a>
    </header>
<div class="container">
    <?php
    if ($resultsVisible) {
        echo "<h1>Election Results</h1>";

        if (!empty($candidates)) {
            $positions = array_unique(array_column($candidates, 'position'));

            foreach ($positions as $position) {
                $highestVoteCount = 0;
                $winner = null;

                foreach ($candidates as $candidate) {
                    if ($candidate['position'] == $position && $candidate['vote_count'] > $highestVoteCount) {
                        $highestVoteCount = $candidate['vote_count'];
                        $winner = $candidate;
                    }
                }

                if ($winner !== null) {
                    ?>
                    <div class="winner">
                        <h2>Winner for <?php echo htmlspecialchars($position); ?>:</h2>
                        <div class="candidate-info">
                            <img src="../uploads/<?php echo htmlspecialchars($winner['photo']); ?>" alt="Winner Photo">
                            <div class="candidate-details">
                                <p><strong>Candidate ID:</strong> <?php echo htmlspecialchars($winner['id']); ?></p>
                                <p><strong>Candidate Name:</strong> <?php echo htmlspecialchars($winner['name']); ?></p>
                                <p><strong>Position:</strong> <?php echo htmlspecialchars($winner['position']); ?></p>
                                <p class="vote-count">Vote Count: <?php echo htmlspecialchars($winner['vote_count']); ?></p>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            }
        } else {
            echo "<p>No candidates found.</p>";
        }
    } else {
        echo "<h1>Results are not available yet.</h1>";
    }
    ?>
</div>

</body>
</html>

<?php $conn->close(); ?>
