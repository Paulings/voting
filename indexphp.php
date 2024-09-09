
<?php
session_start();
include 'voter/db_connection.php';

if (!isset($_SESSION['attempts'])) {
    $_SESSION['attempts'] = 0;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_SESSION['attempts'] < 3) {
    $reg_no = $_POST['reg_no'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM voter_register_tb WHERE reg_no = ?");
    $stmt->bind_param("s", $reg_no);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $voter = $result->fetch_assoc();
        if (password_verify($password, $voter['password'])) {
            $_SESSION['voter_id'] = $voter['id'];
            $_SESSION['attempts'] = 0;  // Reset attempts on successful login
            header("Location: vote_casting/voting_page.php");
            exit();
        } else {
            $_SESSION['attempts']++;
            $error_message = "Invalid credentials";
        }
    } else {
        $_SESSION['attempts']++;
        $error_message = "Voter not found";
    }
    $stmt->close();
}

if ($_SESSION['attempts'] >= 3) {
    $lockout_time = 30;  // Lockout time in seconds
    if (!isset($_SESSION['lockout_time'])) {
        $_SESSION['lockout_time'] = time() + $lockout_time;
    }
    $remaining_time = $_SESSION['lockout_time'] - time();
    if ($remaining_time <= 0) {
        $_SESSION['attempts'] = 0;
        unset($_SESSION['lockout_time']);
        $remaining_time = 0;
    }
}
?>
