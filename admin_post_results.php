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

// Get voting session details
$votingSession = getVotingSession($conn);

// Determine current time and voting status
$currentTimestamp = time();
$votingStartTime = $votingSession ? strtotime($votingSession['start_time']) : null;
$votingEndTime = $votingSession ? strtotime($votingSession['end_time']) : null;
$votingOngoing = $votingStartTime && $votingEndTime && ($currentTimestamp >= $votingStartTime && $currentTimestamp <= $votingEndTime);

// Debugging output
error_log("Current Time: " . date('Y-m-d H:i:s', $currentTimestamp));
error_log("Voting Start Time: " . ($votingStartTime ? date('Y-m-d H:i:s', $votingStartTime) : 'N/A'));
error_log("Voting End Time: " . ($votingEndTime ? date('Y-m-d H:i:s', $votingEndTime) : 'N/A'));
error_log("Voting Ongoing: " . ($votingOngoing ? 'true' : 'false'));

// Get candidates and their votes
$candidates = getCandidatesWithVotes($conn);

// Handle visibility status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    if ($action == 'post') {
        $updateQuery = "UPDATE settings SET value='1' WHERE name='results_visibility'";
    } elseif ($action == 'close') {
        $updateQuery = "UPDATE settings SET value='0' WHERE name='results_visibility'";
    }
    $conn->query($updateQuery);
}

// Check current visibility status
$visibilityQuery = "SELECT value FROM settings WHERE name='results_visibility'";
$visibilityResult = $conn->query($visibilityQuery);
$visibilityRow = $visibilityResult->fetch_assoc();
$resultsVisible = $visibilityRow['value'] == '1';

// HTML begins here
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Control Panel</title>
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
        .control-buttons {
            margin-top: 20px;
        }
        .button {
            background-color: blue;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
            width : 200px;
            padding : 15px;
        }
    </style>
</head>
<body>
<div class="menu-item">
<a href="../dashboard/dashboard.php" style="font-size:20px">Home </a>/ AdminDashboard
    </div>

<div class="container">
    <h1>Admin Control Panel</h1>


    <div class="control-buttons">
        <form action="../vote_casting/admin_post_results.php" method="post">
            <?php if ($resultsVisible): ?>
                <button type="submit" class="button" name="action" value="close">Close Results</button>
            <?php else: ?>
                <button type="submit"  class="button" name="action" value="post">Post Results</button>
            <?php endif; ?>
        </form>
    </div>

    <?php
    if ($votingOngoing) {
        // Display candidates and their votes
        echo "<h1>Election is ongoing</h1>";
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
    } else {
        // Voting is closed, calculate and display winners
        echo "<h1>Voting has ended</h1>";

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
    }
    ?>
</div>

</body>
</html>

<?php $conn->close(); ?>
