<?php
session_start();
include 'db_connection.php'; // Ensure this path is correct based on your file structure

// Check if voter is logged in
if (!isset($_SESSION['voter_id'])) {
    header("Location: ../index.php");
    exit();
}

// Retrieve voter information from session if needed
$first_name = $_SESSION['first_name'] ?? '';
$sir_name = $_SESSION['sir_name'] ?? '';
$reg_no = $_SESSION['reg_no'] ?? '';

// Example query execution using $conn
$session_query = $conn->prepare("SELECT id, start_time, end_time FROM voting_sessions ORDER BY id DESC LIMIT 1");
if (!$session_query) {
    die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
}

$session_query->execute();
$session_result = $session_query->get_result();

if (!$session_result) {
    die("Error retrieving voting session: " . $conn->error);
}

$current_session = $session_result->fetch_assoc();

if (!$current_session) {
    echo "No active voting session.";
    exit();
}

// Further code for displaying candidates or other voting process

// Get current voting session
$session_query = $conn->prepare("SELECT id, start_time, end_time FROM voting_sessions ORDER BY id DESC LIMIT 1");
$session_query->execute();
$session_result = $session_query->get_result();

if (!$session_result) {
    die("Error retrieving voting session: " . $conn->error);
}

$current_session = $session_result->fetch_assoc();

if (!$current_session) {
    echo "No active voting session.";
    exit();
}

$voting_start_time = strtotime($current_session['start_time']);
$voting_end_time = strtotime($current_session['end_time']);
$current_time = time();
$remaining_time = $voting_end_time - $current_time;

// Check if voter has already voted in this session
$vote_check_query = $conn->prepare("SELECT * FROM votes WHERE voter_id = ? AND session_id = ?");
$vote_check_query->bind_param("si", $voter_id, $current_session['id']);
$vote_check_query->execute();
$vote_check_result = $vote_check_query->get_result();
$has_voted = $vote_check_result->num_rows > 0;

// Retrieve candidates for President
$president_query = $conn->prepare("SELECT id, first_name, sir_name, candidate, photo FROM cont_register_tb WHERE candidate = 'President'");
$president_query->execute();
$president_result = $president_query->get_result();

// Retrieve candidates for Vice President
$vice_president_query = $conn->prepare("SELECT id, first_name, sir_name, candidate, photo FROM cont_register_tb WHERE candidate = 'Vice President'");
$vice_president_query->execute();
$vice_president_result = $vice_president_query->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voter Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            flex-direction: column;
        }
        .header {
            position : fixed;
            width: 100%;
            background-color: #007bff;
            color: #fff;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-bottom : 46%;
        }
        .header h1 {
            margin: 0;
        }
        .logout-button {
            padding: 10px 20px;
            font-size: 16px;
            background: #ff4b5c;
            color: #fff;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }
        .container {
            width: 100%;
            max-width: 93%;
            background-color: #fff;
            padding: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            margin-top: 7%;
        }
        .row {
            display: flex;
            justify-content: space-between;
            flex-wrap: nowrap;
        }
        .main_president, .main_vice {
            width: 48%;
            background-color: #f9f9f9;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 5px;
            margin-top: 20px;
        }
        .candidate-section {
            margin-bottom: 30px;
        }
        .candidate {
            padding: 10px;
            border: 1px solid #ddd;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            border-radius: 5px;
            background-color: #fff;
        }
        .candidate img {
            width: 100px;
            height: auto;
            margin-right: 25px;
            border-radius: 6px;
            max-height: 150px;
        }
        #countdown {
            font-size: 20px;
            font-weight: bold;
        }
        .select-button {
            padding: 10px 20px;
            font-size: 16px;
            background: #007bff;
            color: #fff;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }
        .select-button.selected {
            background: green;
        }
        .select-button.cancel {
            background: #ff4b5c;
        }
        .select-button:disabled {
            background: red;
            cursor: not-allowed;
        }
        #vote-button {
            padding: 10px 20px;
            font-size: 16px;
            background: #007bff;
            color: #fff;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            width: 250px;
            margin-left: 40%;
            margin-top: 20px;
        }
        #vote-button:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
       .button {
        margin-left : 100%;
        width : 180px;
        font-size : 20px;
        padding: 5px 20px;
        border: none;
            cursor: pointer;
            border-radius: 5px;

       }
    </style>
</head>
<body>
    <div class="header"> 
    <h2>Welcome, <?php echo htmlspecialchars($first_name . ' ' . $sir_name . ' ' . $reg_no . ''); ?></h2>
        <h1>Voter Dashboard</h1>
        <a href="../vote_casting/election_results.php">
            <button class="button">Election results<button>
    </a>
        <form action="../voter/logout.php" method="POST" style="margin: 0;">
            <button type="submit" class="logout-button">Logout</button>
        </form>
    </div>
    <div class="container">

        <h2>Voting Time Remaining: <span id="countdown"></span></h2>
        <input type="hidden" id="voting_end_time" value="<?php echo date("Y-m-d H:i:s", $voting_end_time); ?>">

        <form id="voteForm" method="POST" action="../vote_casting/cast_vote.php" onsubmit="return validateForm()">
            <h2>Cast Your votes below:</h2>
            <input type="hidden" id="voter_id" name="voter_id" value="<?php echo htmlspecialchars($voter_id); ?>" required>
            
            <input type="hidden" id="president_id" name="president_id" required>
            <input type="hidden" id="vice_president_id" name="vice_president_id" required>

            <div class="row">
                <div class="main_president">
                    <div class="candidate-section">
                        <h3>President Candidates:</h3>
                        <?php if ($president_result->num_rows > 0) {
                            while ($candidate = $president_result->fetch_assoc()) { ?>
                                <div class="candidate">
                                    <img src="../uploads/<?php echo htmlspecialchars($candidate['photo']); ?>" alt="Candidate Photo">
                                    <div>
                                        <p><strong>First Name:</strong> <?php echo htmlspecialchars($candidate['first_name']); ?></p>
                                        <p><strong>Sir Name:</strong> <?php echo htmlspecialchars($candidate['sir_name']); ?></p>
                                        <p><strong>Position:</strong> <?php echo htmlspecialchars($candidate['candidate']); ?></p>
                                        <button type="button" class="select-button" onclick="selectCandidate('president', <?php echo htmlspecialchars($candidate['id']); ?>, this)" <?php echo $has_voted ? 'disabled' : ''; ?>>Vote</button>
                                        <button type="button" class="select-button cancel" onclick="cancelSelection('president')">Cancel</button>
                                    </div>
                                </div>
                            <?php }
                        } else { ?>
                            <p>No candidates available for President.</p>
                        <?php } ?>
                    </div>
                </div>

                <div class="main_vice">
                    <div class="candidate-section">
                        <h3>Vice President Candidates:</h3>
                        <?php if ($vice_president_result->num_rows > 0) {
                            while ($candidate = $vice_president_result->fetch_assoc()) { ?>
                                <div class="candidate">
                                    <img src="../uploads/<?php echo htmlspecialchars($candidate['photo']); ?>" alt="Candidate Photo">
                                    <div>
                                        <p><strong>First Name:</strong> <?php echo htmlspecialchars($candidate['first_name']); ?></p>
                                        <p><strong>Sir Name:</strong> <?php echo htmlspecialchars($candidate['sir_name']); ?></p>
                                        <p><strong>Position:</strong> <?php echo htmlspecialchars($candidate['candidate']); ?></p>
                                        <button type="button" class="select-button" onclick="selectCandidate('vice_president', <?php echo htmlspecialchars($candidate['id']); ?>, this)" <?php echo $has_voted ? 'disabled' : ''; ?>>Vote</button>
                                        <button type="button" class="select-button cancel" onclick="cancelSelection('vice_president')">Cancel</button>
                                    </div>
                                </div>
                            <?php }
                        } else { ?>
                            <p>No candidates available for Vice President.</p>
                        <?php } ?>
                    </div>
                </div>
            </div>

            <button id="vote-button" type="submit" <?php echo $remaining_time <= 0 || $has_voted ? 'disabled' : ''; ?>>Submit</button>
        </form>
    </div>

    <script src="../include/js/select_candidate.js"></script>
    <script src="../include/js/countdown_timer.js"></script>
    <script>
        function selectCandidate(position, id, button) {
            const selectedButton = document.querySelector(`.main_${position} .select-button.selected`);
            if (selectedButton) {
                selectedButton.classList.remove('selected');
            }
            button.classList.add('selected');
            document.getElementById(`${position}_id`).value = id;
        }

        function cancelSelection(position) {
            const selectedButton = document.querySelector(`.main_${position} .select-button.selected`);
            if (selectedButton) {
                selectedButton.classList.remove('selected');
                document.getElementById(`${position}_id`).value = '';
            }
        }

        function validateForm() {
            const presidentId = document.getElementById('president_id').value;
            const vicePresidentId = document.getElementById('vice_president_id').value;

            if (!presidentId || !vicePresidentId) {
                alert("Please select a candidate for both President and Vice President positions.");
                return false;
            }
            if (presidentId === vicePresidentId) {
                alert("You cannot select the same candidate for both President and Vice President.");
                return false;
            }
            return true;
        }
    </script>
</body>
</html>
