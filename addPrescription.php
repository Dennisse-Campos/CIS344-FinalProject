<?php
require_once 'PharmacyDatabase.php';

session_start();

$db = new PharmacyDatabase();

if (isset($_POST["patient_username"]) && isset($_POST["medication_id"]) && isset($_POST["dosage_instructions"]) && isset($_POST["quantity"]) && isset($_POST["refill_count"])) {
    // 1. Retrieve Form Data
    $patient_username = $_POST["patient_username"];
    $medication_id = $_POST["medication_id"];
    $dosage_instructions = $_POST["dosage_instructions"];
    $quantity = $_POST["quantity"];
    $refill_count = $_POST["refill_count"]; 

    // 2.  Validate data
    $errors = array();

    if (empty($patient_username)) {  // Corrected variable name
        $errors[] = "Patient Username is required.";
    }

    if (!is_numeric($medication_id) || $medication_id <= 0) {
        $errors[] = "Medication ID must be a positive number.";
    }

    if (empty($dosage_instructions)) {
        $errors[] = "Dosage Instructions are required.";
    }
    if (!is_numeric($quantity) || $quantity <= 0) {
        $errors[] = "Quantity must be a positive number.";
    }
    if (!is_numeric($refill_count) || $refill_count < 0) {
        $errors[] = "Refill Count must be a non-negative number.";
    }

    // Print the data being passed to addPrescription
    echo "<br>Data being passed to addPrescription:<br>";
    echo "Patient Username: " . $patient_username . "<br>";
    echo "Medication ID: " . $medication_id . "<br>";
    echo "Dosage Instructions: " . $dosage_instructions . "<br>";
    echo "Quantity: " . $quantity . "<br>";
    echo "Refill Count: " . $refill_count . "<br>";

    $refill_count = intval($refill_count);
    if (empty($errors)) {
        
        // 3. Call the addPrescription() method
        $result = $db->addPrescription($patient_username, $medication_id, $dosage_instructions, $quantity, $refill_count);

        // 4. Handle the result
        if ($result) {
            echo "Prescription added successfully!";
        } else {
            echo "Failed to add prescription1.";
        }
    } else {
        // Display the errors to the user
        echo "<b>The following errors occurred:</b><br>";
        foreach ($errors as $error) {
            echo $error . "<br>";
        }
    }
}
?>



<!DOCTYPE html>
<html>
<head><title>Add Prescription</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Add Reservation</h1>
    <form method="POST" action="">
        Patient Username: <input type="text" name="patient_username" /><br>
        Medication ID : <input type="number" name="medication_id" min="1"/><br>
        Dosage Instructions: <textarea name="dosage_instructions"></textarea><br>
        Quantity: <input type="number" name="quantity" min="1" /><br>

        Refill Count: <input type="number" name="refill_count" min="0" /><br>
        <button type="submit">Save</button>
    </form>
    <a href="PharmacyServer.php">Back to Home</a>
    </body>
</html>

