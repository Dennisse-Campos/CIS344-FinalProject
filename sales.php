<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include the PharmacyDatabase class
require_once 'PharmacyDatabase.php';

// Start a session
session_start();

// Check if the user is logged in and is a patient
if (!isset($_SESSION['loggedIn']) || $_SESSION['loggedIn'] !== true || $_SESSION['userType'] !== 'patient') {
    // Redirect to the login page if not logged in or not a patient
    header('Location: login.php');
    exit();
}

// Create an instance of the PharmacyDatabase class
$db = new PharmacyDatabase();

// Get prescription ID from the URL
if (isset($_GET['prescriptionId'])) {
    $prescriptionId = $_GET['prescriptionId'];

    // Fetch prescription details
    $query = "SELECT p.prescriptionId, m.medicationName, p.dosageInstructions, p.quantity AS pillsPerBottle, p.refillCount AS refills, m.price AS pricePerPill
                FROM Prescriptions p
                JOIN Medications m ON p.medicationId = m.medicationId
                WHERE p.prescriptionId = ?";
    $conn = $db->getConnection();
    $stmt = $conn->prepare($query);

    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }

    $stmt->bind_param("i", $prescriptionId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        die("Prescription not found.");
    }
    $prescription = $result->fetch_assoc();

} else {
    die("Prescription ID is missing.");
}

// Handle the form submission to process the sale
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $quantityToBuy = $_POST['quantityToBuy'];

    // Validate if the quantity to buy is a positive integer
    if (!is_numeric($quantityToBuy) || $quantityToBuy <= 0 || floor($quantityToBuy) != floor($quantityToBuy)) {
        $error = "Please enter a valid number of bottles.";
    } else {
        // Calculate the total number of pills being purchased
        $totalPills = $quantityToBuy * $prescription['pillsPerBottle'];

        // Determine the maximum number of bottles that can be purchased based on refills
        $availableRefills = $prescription['refills'];
        $maxPurchaseQuantity = $availableRefills + 1; // Can purchase for the current fill + refills

        if ($quantityToBuy > $maxPurchaseQuantity) {
            $error = "You can only purchase a maximum of " . $maxPurchaseQuantity . " bottles with the available refills.";
        } else {
            // Calculate the total price
            $totalPrice = $totalPills * $prescription['pricePerPill'];

            // Call the processSale method in the PharmacyDatabase class
            $saleResult = $db->processSale($prescriptionId, $quantityToBuy);

            if ($saleResult) {
                // Reduce the refill count
                $newRefillCount = $availableRefills - $quantityToBuy;
                $conn = $db->getConnection();
                $updateRefillQuery = "UPDATE Prescriptions SET refillCount = ? WHERE prescriptionId = ?";
                $updateRefillStmt = $conn->prepare($updateRefillQuery);
                if ($updateRefillStmt) {
                    $updateRefillStmt->bind_param("ii", $newRefillCount, $prescriptionId);
                    $updateRefillStmt->execute();
                    $updateRefillStmt->close();
                    $successMessage = "Sale processed successfully! Total Price: $" . number_format($totalPrice, 2) . ". Refills remaining: " . $newRefillCount . ".";
                } else {
                    $errorMessage = "Error updating refill count.";
                }
            } else {
                echo "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative' role='alert'>
                <strong class='font-bold'>Error!</strong>
                <span class='block sm:inline'>Sale was not processed due to a database error. Check server error logs for details.</span>
                <a href='patient_home.php' class='absolute top-3 right-3 text-blue-500 underline'>Back to Dashboard</a>
                </div>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Process Sale</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@tailwindcss/browser@latest"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
        <h2 class="text-2xl font-semibold mb-6 text-center text-blue-600">Process Sale</h2>
        <?php if (isset($successMessage)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Success!</strong>
                <span class="block sm:inline"><?php echo $successMessage; ?></span>
            </div>
        <?php endif; ?>
        <?php if (isset($errorMessage)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Error!</strong>
                <span class='block sm:inline'><?php echo $errorMessage; ?></span>
                <a href='patient_home.php' class='absolute top-3 right-3 text-blue-500 underline'>Back to Dashboard</a>
            </div>
        <?php endif; ?>
        <div class="mb-4">
            <p class="text-gray-700">Medication: <span class="font-semibold text-green-600"><?php echo $prescription['medicationName']; ?></span></p>
            <p class="text-gray-700">Dosage Instructions: <span class="font-semibold text-indigo-600"><?php echo $prescription['dosageInstructions']; ?></span></p>
            <p class="text-gray-700">Quantity in Bottle: <span class="font-semibold text-purple-600"><?php echo $prescription['pillsPerBottle']; ?></span></p>
            <p class="text-gray-700">Refills: <span class="font-semibold text-orange-600"><?php echo $prescription['refills']; ?></span></p>
            <p class="text-gray-700">Price per Bottle: <span class="font-semibold text-red-600">$<?php echo number_format($prescription['pillsPerBottle'] * $prescription['pricePerPill'], 2); ?></span></p>
        </div>
        <form method="post" action="" class="space-y-4">
            <div>
                <label for="quantityToBuy" class="block text-gray-700 text-sm font-bold mb-2">Number of Bottles to Buy:</label>
                <input type="number" id="quantityToBuy" name="quantityToBuy" min="1" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                <?php if (isset($error)): ?>
                    <p class="text-red-500 text-xs italic"><?php echo $error; ?></p>
                <?php endif; ?>
            </div>
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full">Process Sale</button>
        </form>
        <div class="mt-6 text-center">
            <a href="patient_home.php" class="text-blue-500 hover:text-blue-700 font-semibold">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>

<!-- If refills is 0, the user can purchase 1 time.
If refills is greater than 0, the user can purchase up to refills + 1 times. -->