<?php
// Include the PharmacyDatabase class
require_once 'PharmacyDatabase.php';

// Start a session at the beginning of the script
session_start();

// Create an instance of the PharmacyDatabase class
$db = new PharmacyDatabase();

// Function for handling registration
function handleRegistration($db) {
    if (isset($_POST['new_username']) && isset($_POST['new_password']) && isset($_POST['new_userType']) && isset($_POST['new_contactInfo'])) {
        $new_username = $_POST['new_username'];
        $new_password = $_POST['new_password'];
        $new_userType = $_POST['new_userType'];
        $new_contactInfo = $_POST['new_contactInfo'];

        // Basic validation (you should add more robust validation)
        $registrationErrors = array();
        if (empty($new_username)) {
            $registrationErrors[] = "Username is required.";
        }
        if (empty($new_password)) {
            $registrationErrors[] = "Password is required.";
        }
        if (empty($new_userType)) {
            $registrationErrors[] = "User Type is required.";
        }
        if (empty($new_contactInfo)) {
            $registrationErrors[] = "Contact Info is required.";
        }

        if (count($registrationErrors) == 0) {
            // Use the registerUser method from PharmacyDatabase
            $result = $db->registerUser($new_username, $new_password, $new_userType, $new_contactInfo); // Pass the plain text password
            if ($result) {
                // Redirect based on user type
                $_SESSION['loggedIn'] = true;
                $_SESSION['username'] = $new_username;
                $_SESSION['userType'] = $new_userType;
                if ($new_userType == 'patient') {
                    header('Location: patient_home.php');
                    exit(); // Important: Stop further execution
                } elseif ($new_userType == 'pharmacist') {
                    header('Location: home.php');
                    exit();
                }
                 else {
                     header('Location: login.php');
                     exit();
                 }
            } else {
                $registrationError = "Registration failed.  Username may be taken.";
            }
        } else {
            $registrationError = implode("<br>", $registrationErrors);
        }
        return $registrationError;
    }
}

$registrationError = "";
$registrationSuccess = "";
if (isset($_POST['register'])) {
    $registrationError = handleRegistration($db);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacy Registration</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Register</h2>
        <?php if (isset($registrationSuccess)) { ?>
            <p style="color: green;"><?php echo $registrationSuccess; ?></p>
        <?php } ?>
        <?php if (isset($registrationError)) { ?>
            <p style="color: red;"><?php echo $registrationError; ?></p>
        <?php } ?>
        <form method="POST" action="">
            <label for="new_username">Username:</label>
            <input type="text" id="new_username" name="new_username" required><br><br>
            <label for="new_password">Password:</label>
            <input type="password" id="new_password" name="new_password" required><br><br>
            <label for="new_userType">User Type:</label>
            <select id="new_userType" name="new_userType">
                <option value="patient">Patient</option>
                <option value="pharmacist">Pharmacist</option>
            </select><br><br>
            <label for="new_contactInfo">Contact Info:</label>
            <input type="text" id="new_contactInfo" name="new_contactInfo" required><br><br>
            <input type="submit" name="register" value="Register">
            <p>Already have an account? <a href="login.php">Login here</a>.</p>
        </form>
    </div>
</body>
</html>