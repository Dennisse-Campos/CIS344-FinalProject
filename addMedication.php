<?php
// Include the PharmacyDatabase class
require_once 'PharmacyDatabase.php';

// Start a session (if you need to check for pharmacist login)
session_start();

// Check if the user is logged in and is a pharmacist (optional, if you want to restrict access)
if (isset($_SESSION['loggedIn']) && $_SESSION['loggedIn'] === true && $_SESSION['userType'] === 'pharmacist') {
    //  Allow access
} else {
    //  Redirect to login or an error page
    header('Location: login.php'); //  adjust the path if necessary
    exit();
}


// Create an instance of the PharmacyDatabase class
$db = new PharmacyDatabase();

// Initialize variables for form data and error messages
$medicationName = '';
$dosage = '';
$manufacturer = '';
$price = '';
$errors = array();
$successMessage = '';

// Process the form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the form data
    $medicationName = $_POST['medicationName'];
    $dosage = $_POST['dosage'];
    $manufacturer = $_POST['manufacturer'];
    $price = $_POST['price'];

    // Validate the form data
    if (empty($medicationName)) {
        $errors[] = 'Medication Name is required';
    }
    if (empty($dosage)) {
        $errors[] = 'Dosage is required';
    }
    if (empty($manufacturer)) {
        $errors[] = 'Manufacturer is required';
    }
    if (empty($price)) {
        $errors[] = 'Price is required';
    } elseif (!is_numeric($price)) {
        $errors[] = 'Price must be a number';
    }

    // If there are no errors, add the medication to the database
    if (empty($errors)) {
        $result = $db->addMedication($medicationName, $dosage, $manufacturer, $price);
        if ($result) {
            $medicationId = mysqli_insert_id($db->getConnection()); // Get the new medication ID
            $successMessage = 'Medication added successfully!  Medication ID: ' . $medicationId;  //for testing
            // Clear the form
            $medicationName = '';
            $dosage = '';
            $manufacturer = '';
            $price = '';
             $_SESSION['new_medication_id'] = $medicationId; //store in session
        } else {
            $errors[] = 'Failed to add medication.  Please check the data and try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Medication</title>
    <link rel="stylesheet" href="style.css"> </head>
<body>
    <div class="container">
        <h2>Add Medication</h2>

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
                <label for="medicationName">Medication Name:</label>
                <input type="text" id="medicationName" name="medicationName" value="<?php echo $medicationName; ?>" required>
            </div>
            <div class="form-group">
                <label for="dosage">Dosage (include mg):</label>
                <input type="text" id="dosage" name="dosage" value="<?php echo $dosage; ?>" required>
            </div>
            <div class="form-group">
                <label for="manufacturer">Manufacturer:</label>
                <input type="text" id="manufacturer" name="manufacturer" value="<?php echo $manufacturer; ?>" required>
            </div>
            <div class="form-group">
                <label for="price">Price:</label>
                <input type="text" id="price" name="price" value="<?php echo $price; ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Add Medication</button>
            <a href="home.php">Cancel</a>  
        </form>
    </div>
</body>
</html>
