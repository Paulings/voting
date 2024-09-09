<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';  // Correct the path to the autoload file
include 'db_connection.php';

use Twilio\Rest\Client;

// Twilio credentials from your account
$sid = 'ACc3515f474e88f884b3be847892824c0c'; // Replace with your actual Twilio Account SID
$token = 'cd9e4a8d4e6b2541872450baf4dde331'; // Replace with your actual Twilio Auth Token
$from = '+1 762 550 2554'; // Replace with your Twilio phone number

function formatPhoneNumber($phoneNumber) {
    // Check if the phone number starts with '+', if not, prepend the country code
    if (strpos($phoneNumber, '+') === 0) {
        return $phoneNumber;
    }
    // Add your country code here (e.g., +255 for Tanzania)
    $countryCode = '+255'; 
    // Ensure phone number has no leading zeros if country code is added
    $phoneNumber = ltrim($phoneNumber, '0');
    return $countryCode . $phoneNumber;
}

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Escape input to prevent SQL injection
    $username = $conn->real_escape_string($username);
    $password = $conn->real_escape_string($password);

    $query = "SELECT * FROM users WHERE username='$username'";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        if (password_verify($password, $admin['password'])) {
            // Generate OTP (example)
            $otp = mt_rand(100000, 999999);

            // Format the phone number
            $to = formatPhoneNumber($admin['phone_number']);

            // Create Twilio client
            $client = new Client($sid, $token);

            // Send SMS
            try {
                $message = $client->messages->create(
                    $to, // Admin's phone number from database
                    [
                        'from' => $from,
                        'body' => 'Your OTP for login: ' . $otp
                    ]
                );

                // Store OTP in session for verification
                $_SESSION['verify_otp.php'] = $otp;
                $_SESSION['admin_id'] = $admin['id'];

                // Redirect to OTP verification page
                header("Location: verify_otp.php");
                exit;
            } catch (Exception $e) {
                echo 'Error: ' . $e->getMessage();
            }
        } else {
            echo '<script>
            alert("Invalid credential");
            window.location="../admin/admin_login.html";
            </script>';
        }
    }else {
        echo '<script>
        alert("Invalid credential");
        window.location="../admin/admin_login.html";
        </script>';    
}
}
?>
