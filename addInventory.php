<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

// Include the PharmacyDatabase class
require_once 'PharmacyDatabase.php';

// Start a session (if you need to check for pharmacist login)
session_start();

// Check if the user is logged in and is a pharmacist
if (isset($_SESSION['loggedIn']) && $_SESSION['loggedIn'] === true && $_SESSION['userType'] === 'pharmacist') {
    // Allow access
} else {
    // Redirect to login or an error page
    header('Location: login.php');
    exit();
}

// Create an instance of the PharmacyDatabase class
$db = new PharmacyDatabase();

// Initialize variables for form data and error messages
$medicationId = '';
$quantity = '';
$errors = array();
$successMessage = '';

// Get the list of medications for the dropdown
$medications = $db->getAllMedications();

// Process the form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the form data
    $medicationId = $_POST['medicationId'];
    $quantity = $_POST['quantity'];

    // Validate the form data
    if (empty($medicationId)) {
        $errors[] = 'Medication is required';
    }
    if (empty($quantity)) {
        $errors[] = 'Quantity is required';
    } elseif (!is_numeric($quantity) || $quantity <= 0) {
        $errors[] = 'Quantity must be a positive number';
    }

    // If there are no errors, add the medication to the inventory
    if (empty($errors)) {
        $result = $db->addToInventory($medicationId, $quantity);
        if ($result) {
            $successMessage = 'Medication added to inventory successfully!';
            $quantity = '';
        } else {
            $errors[] = 'Failed to add medication to inventory. Please check the data and try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add to Inventory</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Add to Inventory</h2>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo $error; ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($successMessage)): ?>
            <div class="alert alert-success">
                <p><?php echo $successMessage; ?></p>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="medicationId">Medication:</label>
                <select id="medicationId" name="medicationId" required>
                    <option value="">Select Medication</option>
                    <?php foreach ($medications as $medication): ?>
                        <option value="<?php echo $medication['medicationId']; ?>">
                            <?php echo $medication['medicationName']; ?> - <?php echo $medication['dosage']; ?> - <?php echo $medication['manufacturer']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="quantity">Quantity:</label>
                <input type="number" id="quantity" name="quantity" value="<?php echo $quantity; ?>" min="0" required>
            </div>
            <button type="submit" class="btn btn-primary">Add to Inventory</button>
            <a href="home.php">Cancel</a>
        </form>
    </div>
</body>
</html>
