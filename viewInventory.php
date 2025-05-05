<?php
require_once 'PharmacyDatabase.php';
session_start();

// Only allow pharmacists to view inventory
if (!isset($_SESSION['loggedIn']) || $_SESSION['loggedIn'] !== true || $_SESSION['userType'] !== 'pharmacist') {
    header('Location: login.php');
    exit();
}

$db = new PharmacyDatabase();
$inventory = $db->MedicationInventory();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Medication Inventory</title>
    <link rel="stylesheet" href="style.css"> 
</head>
<body>
    <h1>Medication Inventory</h1>

    <table border="1" cellpadding="8">
        <thead>
            <tr>
                <th>Medication Name</th>
                <th>Dosage</th>
                <th>Manufacturer</th>
                <th>Quantity Available</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($inventory)): ?>
                <tr><td colspan="4">No inventory data found.</td></tr>
            <?php else: ?>
                <?php foreach ($inventory as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['medicationName']) ?></td>
                        <td><?= htmlspecialchars($item['dosage']) ?></td>
                        <td><?= htmlspecialchars($item['manufacturer']) ?></td>
                        <td><?= htmlspecialchars($item['quantityAvailable']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <br>
    <a href="home.php">Back to Dashboard</a>
</body>
</html>
