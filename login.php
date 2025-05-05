<!-- CREATE LOGIN PHP -->

<?php
    // **IMPORTANT: session_start() MUST be the very first thing!**
        session_start();

        // Include the PharmacyDatabase class
        require_once 'PharmacyDatabase.php';

        // Create an instance of the PharmacyDatabase class
        $db = new PharmacyDatabase();

        // Function for handling login
        function handleLogin($db) {
            if (isset($_POST['username']) && isset($_POST['password']) && isset($_POST['userType'])) {
                $username = $_POST['username'];
                $password = $_POST['password'];
                $userType = $_POST['userType'];

                // Verify the user and get the hashed password and other user details
                $user = $db->verifyUserLogin($username, $userType);

                if ($user) {
                    $storedHashedPassword = $user['password'];
                    // Verify the password
                    if (password_verify($password, $storedHashedPassword)) {
                        // Password is correct.  Set session variables.
                        $_SESSION['loggedIn'] = true;
                        $_SESSION['username'] = $username;
                        $_SESSION['userType'] = $userType;
                        $_SESSION['userId'] = $user['userId']; // Store the user ID for later use
                        $_SESSION['contactInfo'] = $user['contactInfo'];
                        // Redirect based on user type
                        if ($userType == 'pharmacist') {
                            header('Location: home.php'); // Create this file
                            exit();
                        } else {
                            header('Location: patient_home.php');  //  Create this file
                            exit();
                        }
                    } else {
                        $loginError = "Invalid password.";
                    }
                } else {
                    $loginError = "User not found.  Please register.";
                }
            }
            return $loginError;
        }

        // Handle login
        $loginError = "";
        if (isset($_POST['login'])) {
            $loginError = handleLogin($db);
        }

        // Check if the user is already logged in
        if (isset($_SESSION['loggedIn']) && $_SESSION['loggedIn'] === true) {
            // Redirect to appropriate dashboard based on user type
            if ($_SESSION['userType'] == 'pharmacist') {
                header('Location: home.php');
                exit();
            } else {
                header('Location: patient_home.php');
                exit();
            }
        }
        ?>

        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Pharmacy Login</title>
            <link rel="stylesheet" href="style.css">
        </head>
        <body>
            <div class="container">
                <h2>Login</h2>
                <?php if (isset($loginError)) { ?>
                    <p style="color: red;"><?php echo $loginError; ?></p>
                <?php } ?>
                <form method="POST" action="">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required><br><br>
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required><br><br>
                    <label for="userType">User Type:</label>
                    <select id="userType" name="userType">
                        <option value="patient">Patient</option>
                        <option value="pharmacist">Pharmacist</option>
                    </select><br><br>
                    <input type="submit" name="login" value="Login">
                    <p>Need an account? <a href="register.php">Register here</a>.</p>
                </form>
            </div>
        </body>
        </html>
        