<?php
// Include necessary files (adjust the path if needed)
require_once 'PharmacyDatabase.php';

// Start session (if you're using sessions)
session_start();

// Enable error reporting for debugging
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

// Create a new instance of the PharmacyDatabase class
$db = new PharmacyDatabase();

// Fetch all prescriptions
$prescriptions = $db->getAllPrescriptions();
?> 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Prescriptions</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 20px;
            color: #333;
        }

        h1 {
            color: #0056b3;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #ffffff;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        th, td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #007bff;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        a {
            color: #007bff;
            text-decoration: none;
            transition: color 0.3s;
        }

        a:hover {
            color: #0056b3;
        }

        .no-data {
            text-align: center;
        }
    </style>
</head>
<body>
    <h1>All Prescriptions</h1>
    <table border="1">
        <tr>
            <th>Prescription ID</th>
            <th>User ID</th>
            <th>Medication ID</th>
            <th>Medication Name</th>
            <th>Dosage Instructions</th>
            <th>Quantity</th>
        </tr>
        <?php if (empty($prescriptions)): ?>
            <tr>
                <td colspan="6" class="no-data">No prescriptions found.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($prescriptions as $prescription): ?>
                <tr>
                    <td><?= htmlspecialchars($prescription['prescriptionId']) ?></td>
                    <td><?= htmlspecialchars($prescription['userId']) ?></td>
                    <td><?= htmlspecialchars($prescription['medicationId']) ?></td>
                    <td><?= htmlspecialchars($prescription['medicationName']) ?></td>
                    <td><?= htmlspecialchars($prescription['dosageInstructions']) ?></td>
                    <td><?= htmlspecialchars($prescription['quantity']) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </table>
    <a href="PharmacyServer.php">Back to Home</a>
    <!-- <a href="home.php">Back to Home</a> -->
</body>
</html>

