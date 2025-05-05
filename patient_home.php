<?php
// Include the PharmacyDatabase class
require_once 'PharmacyDatabase.php';

// Start a session at the beginning of the script
session_start();

// Check if the user is logged in and is a patient
if (!isset($_SESSION['loggedIn']) || $_SESSION['loggedIn'] !== true || $_SESSION['userType'] !== 'patient') {
    // Redirect to the login page if not logged in or not a patient
    header('Location: login.php');
    exit();
}

// Create an instance of the PharmacyDatabase class
$db = new PharmacyDatabase();

// Get patient details from session
$username = $_SESSION['username'];
$userId = $_SESSION['userId'];


// Fetch the patient's details, including contact info and prescriptions
$patientDetails = $db->getUserDetails($userId);

if ($patientDetails) {
    $contactInfo = $patientDetails['contactInfo'];
    $prescriptions = $patientDetails['prescriptions'];
} else {
    $contactInfo = "No contact information available."; //set default
    $prescriptions = []; // Ensure $prescriptions is always defined as an array
}



?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <style>
      a[href^="sales.php"] {
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            text-decoration: none;
            position: absolute;
            bottom: 10px;
            right: 10px;
            transition: background-color 0.3s ease;
            white-space: nowrap;
        }

        a[href^="sales.php"]:hover {
            background-color: #0056b3;
        }

        li{
            position: relative;
            padding: 15px;
            margin-bottom: 10px;
            background-color: #f9f9f9;
            border-radius: 5px;
            border: 1px solid #ddd;
        }

        ul{
            margin-bottom: 20px;
        }

        .container{
            margin-top: 20px;
            padding: 20px;
            border-radius: 8px;
            background-color: #fff;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            width: 80%;
            margin-left: auto;
            margin-right: auto;
        }

        h2{
            text-align: center;
            color: #0056b3;
        }

        h3{
            color: #0056b3;
            text-align: center;
            margin-bottom: 20px;
        }

        p{
            margin-bottom: 10px;
            color: #555;
        }

    </style>
</head>
<body>
    <div class="container">
        <h2>Patient Dashboard</h2>
        <p>Welcome, <?php echo $username; ?>!</p>
        <p>Your Contact Information: <?php echo $contactInfo; ?></p>

        <h3>Your Prescriptions:</h3>
        <?php if (empty($prescriptions)) { ?>
            <p>You have no prescriptions.</p>
        <?php } else { ?>
            <ul>
                <?php foreach ($prescriptions as $prescription) { ?>
                    <li>
                        <strong>Medication:</strong> <?php echo $prescription['medicationName']; ?><br>
                        <strong>Dosage:</strong> <?php echo $prescription['dosageInstructions']; ?><br>
                        <strong>Quantity:</strong> <?php echo $prescription['quantity']; ?><br>
                        <strong>Refills:</strong> <?php echo $prescription['refills']; ?><br>
                        <a href="sales.php?prescriptionId=<?php echo $prescription['prescriptionId']; ?>">Buy Now - <?php echo $prescription['prescriptionId']; ?></a>
                         <script>
                            console.log("Prescription ID in patient_home.php: <?php echo $prescription['prescriptionId']; ?>");
                        </script>
                    </li>
                <?php } ?>
            </ul>
        <?php } ?>
        <a href="logout.php" class="logout-button">Logout</a>
    </div>
</body>
</html>
