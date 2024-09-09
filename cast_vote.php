<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['voter_id'])) {
    header("Location: ../voter/voter_login.html");
    exit();
}

$voter_id = $_SESSION['voter_id'];
$president_id = $_POST['president_id'];
$vice_president_id = $_POST['vice_president_id'];

// Get current voting session
$session_query = "SELECT id, end_time FROM voting_sessions ORDER BY id DESC LIMIT 1";
$session_result = $conn->query($session_query);
$current_session = $session_result->fetch_assoc();

if (!$current_session) {
    die("Voting session not found.");
}

$voting_end_time = strtotime($current_session['end_time']);
$current_time = time();

if ($current_time > $voting_end_time) {
    die("Voting period has ended.");
}

// Check if candidates exist
$candidate_query = "SELECT id FROM cont_register_tb WHERE id = ?";
$stmt = $conn->prepare($candidate_query);

$stmt->bind_param("i", $president_id);
$stmt->execute();
$president_result = $stmt->get_result();

$stmt->bind_param("i", $vice_president_id);
$stmt->execute();
$vice_president_result = $stmt->get_result();

if ($president_result->num_rows == 0 || $vice_president_result->num_rows == 0) {
    die("Invalid candidate ID.");
}

// Check if voter has already voted in this session
$vote_check_query = "SELECT * FROM votes WHERE voter_id = ? AND session_id = ?";
$stmt = $conn->prepare($vote_check_query);
$stmt->bind_param("ii", $voter_id, $current_session['id']);
$stmt->execute();
$vote_check_result = $stmt->get_result();

if ($vote_check_result->num_rows > 0) {
    echo '<script>alert("You have already voted."); window.location="../vote_casting/voting_page.php";</script>';
    exit();
}

// Cast the votes
$vote_query = "INSERT INTO votes (voter_id, candidate_id, session_id) VALUES (?, ?, ?), (?, ?, ?)";
$stmt = $conn->prepare($vote_query);
$stmt->bind_param("iiiiii", $voter_id, $president_id, $current_session['id'], $voter_id, $vice_president_id, $current_session['id']);

if ($stmt->execute()) {
    echo '<script>alert("Vote casting successful."); window.location="../vote_casting/voting_page.php";</script>';
} else {
    echo "Error casting votes: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
