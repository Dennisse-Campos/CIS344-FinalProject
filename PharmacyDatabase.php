<?php
class PharmacyDatabase {
    private $host = "localhost";
    private $port = "3306";
    private $database = "pharmacy_portal_db";
    private $user = "root";
    private $db_password = "";
    private $connection;

    public function __construct() {
        $this->connect();
    }

    private function connect() {
        $this->connection = new mysqli($this->host, $this->user, $this->db_password, $this->database, $this->port);
        if ($this->connection->connect_error) {
            die("Connection failed: " . $this->connection->connect_error);
        }
        echo "Successfully connected to the database";
    }

    public function getConnection() {
        return $this->connection;
    }

    public function addPrescription($patientUserName, $medicationId, $dosageInstructions, $quantity, $refillCount)  {
        $stmt = $this->connection->prepare(
            "SELECT userId FROM Users WHERE userName = ? AND userType = 'patient'"
        );
        $stmt->bind_param("s", $patientUserName);
        $stmt->execute();
        $stmt->bind_result($patientId);
        $stmt->fetch();
        $stmt->close();
        
        if ($patientId){
            $stmt = $this->connection->prepare(
                "INSERT INTO prescriptions (userId, medicationId, prescribedDate, dosageInstructions, quantity, refillCount) VALUES (?, ?, NOW(), ?, ?, ?)"
            );
            $stmt->bind_param("iisii", $patientId, $medicationId, $dosageInstructions, $quantity, $refillCount);
            $success = $stmt->execute();
            $stmt->close();
            return $success;
        } else {
            return false;
        }
    }

    // Had to change this function
    public function getAllPrescriptions() {
        $sql = "SELECT 
                p.prescriptionId,
                p.userId,
                p.medicationId,
                m.medicationName,  
                p.dosageInstructions,
                p.quantity
            FROM prescriptions p
            JOIN medications m ON p.medicationId = m.medicationId";

        $result = $this->connection->query($sql);

        if ($result === false) {
            error_log("Error in getAllPrescriptions: " . $this->connection->error);
            return [];
        }

        return $result->fetch_all(MYSQLI_ASSOC);
    }
    


// my code from here below

    public function MedicationInventory() {
        /*
        Complete this function to test the functionality of
        MedicationInventoryView and implement it in the server
        */
        //Wrire code here

        $sql = "SELECT medicationName, dosage, manufacturer, quantityAvailable FROM MedicationInventoryView";
        $result = $this->connection->query($sql);

        if ($result === false) {
            // Log the error (do NOT display the actual error to the user in production)
            error_log("Error querying MedicationInventoryView: " . $this->connection->error);
            return []; // Return an empty array on failure
        }

        $inventoryData = array(); // Initialize an empty array to store the results.

        if ($result->num_rows > 0) {
            // Fetch each row as an associative array
            while ($row = $result->fetch_assoc()) {
                $inventoryData[] = $row;
            }
        }

        return $inventoryData;
    }

    public function addUser($userName, $password, $userType, $contactInfo) { 
        // Write your code here

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT); // Hash the password here
        $query = "INSERT INTO Users (userName, password, userType, contactInfo) VALUES (?, ?, ?, ?)";
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("ssss", $userName, $hashedPassword, $userType, $contactInfo);
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    // Verify User Existence for Login
    public function verifyUserLogin($username, $userType) {
        $stmt = $this->connection->prepare("SELECT userId, userName, userType, password FROM Users WHERE userName = ? AND userType = ?");
        if (!$stmt) {
            die("Error preparing statement (verifyUserLogin): " . $this->connection->error);
        }
        $stmt->bind_param("ss", $username, $userType);
        $stmt->execute();
        $result = $stmt->get_result(); //get the result
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            $stmt->close();
            return $user; // Return the whole user row, including hashed password
        } else {
            $stmt->close();
            return null; // User not found
        }
    }



    //Add Other needed functions here

    public function addMedication($medicationName, $dosage, $manufacturer, $price) {
        // Validate data types and values
        if (!is_string($medicationName) || empty($medicationName)) {
            error_log("Invalid medicationName: " . $medicationName);
            return false;
        }
        if (!is_string($dosage) || empty($dosage)) {
            error_log("Invalid dosage: " . $dosage);
            return false;
        }
        if (!is_string($manufacturer) || empty($manufacturer)) {
           error_log("Invalid manufacturer: " . $manufacturer);
            return false;
        }
        if (!is_numeric($price) || $price < 0) {
            error_log("Invalid price: " . $price);
            return false;
        }

        $medicationName = trim($medicationName);
        $dosage = trim($dosage);
        $manufacturer = trim($manufacturer);

        // Use a prepared statement to prevent SQL injection
        $stmt = $this->connection->prepare("INSERT INTO Medications (medicationName, dosage, manufacturer, price) VALUES (?, ?, ?, ?)");
        if ($stmt === false) {
            die("Error preparing statement: " . $this->connection->error);
        }

        // Bind the parameters
        $stmt->bind_param("sssd", $medicationName, $dosage, $manufacturer, $price);

        // Execute the query
        if ($stmt->execute()) {
            $stmt->close();
            return true; // Indicate success
        } else {
            error_log("Error executing query: " . $this->connection->error);
            $stmt->close();
            return false; // Indicate failure
        }
    }



    public function getUserDetails($userId) {
        // Use prepared statements to prevent SQL injection
        $stmt0 = $this->connection->prepare("SELECT userID, userName, contactInfo, userType FROM Users WHERE userID = ?");
        if ($stmt0 === false) {
            die("Error preparing statement: " . $this->connection->error);
        }
        $stmt0->bind_param("i", $userId);
        $stmt0->execute();
        $userResult = $stmt0->get_result();

        if ($userResult->num_rows == 0) {
            $stmt0->close();
            return null;
        }
        $userDetails = $userResult->fetch_assoc();
        $stmt0->close();

        // Get user's prescriptions, including refills
        $query = "SELECT p.prescriptionId, p.dosageInstructions, p.quantity, m.medicationName, p.refillCount AS refills
                    FROM Prescriptions p
                    JOIN Medications m ON p.medicationId = m.medicationId
                    WHERE p.userId = ?"; //changed p.userID to p.userId
        $stmt = $this->connection->prepare($query);
        if ($stmt === false) {
            die("Error preparing statement: " . $this->connection->error);
        }
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $prescriptionResult = $stmt->get_result();
        if(!$prescriptionResult){
            die("Error with prescription query: ". $this->connection->error);
        }
        // echo "Number of rows: " . $prescriptionResult->num_rows . "<br>"; // Add this line
        // echo "Error: " . $this->connection->error . "<br>";
        $userDetails['prescriptions'] = $prescriptionResult->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $userDetails;
    }


    public function getAllMedications() {
        $query = "SELECT medicationId, medicationName, dosage, manufacturer FROM Medications"; //get the data
        $result = $this->connection->query($query);
        if (!$result) {
            die("Error querying medications: " . $this->connection->error); //error check
        }
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function addToInventory($medicationId, $quantity) {
        //  Check if medication exists
        $checkMedicationQuery = "SELECT medicationId FROM Medications WHERE medicationId = ?";
        $checkMedicationStmt = $this->connection->prepare($checkMedicationQuery);
    
        if(!$checkMedicationStmt){
             die("Error preparing statement (checkMedication): " . $this->connection->error);
        }
    
        $checkMedicationStmt->bind_param("i", $medicationId);
        $checkMedicationStmt->execute();
        $checkMedicationResult = $checkMedicationStmt->get_result();
    
        if ($checkMedicationResult->num_rows == 0) {
             die("Medication ID does not exist.");
        }
        $checkMedicationStmt->close();
    
        // First, check if the medicationId exists in the Inventory table
        $checkQuery = "SELECT quantityAvailable FROM Inventory WHERE medicationId = ?";
        $checkStmt = $this->connection->prepare($checkQuery);
        if (!$checkStmt) {
            die("Error preparing statement (check): " . $this->connection->error);
        }
        $checkStmt->bind_param("i", $medicationId);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
    
    
    
        if ($checkResult->num_rows > 0) {
            // Medication exists, update the quantity
            $updateQuery = "UPDATE Inventory SET quantityAvailable = quantityAvailable + ?, lastUpdated = NOW() WHERE medicationId = ?";
            $updateStmt = $this->connection->prepare($updateQuery);
            if (!$updateStmt) {
                die("Error preparing statement (update): " . $this->connection->error);
            }
            $updateStmt->bind_param("ii", $quantity, $medicationId);
            $updateResult = $updateStmt->execute();
            if(!$updateResult){
                 die("Error updating inventory: " . $this->connection->error);
            }
            $updateStmt->close();
            return $updateResult; // Return the result of the update
        } else {
            // Medication doesn't exist, insert a new row
            $insertQuery = "INSERT INTO Inventory (medicationId, quantityAvailable, lastUpdated) VALUES (?, ?, NOW())";
            $insertStmt = $this->connection->prepare($insertQuery);
            if (!$insertStmt) {
                die("Error preparing statement (insert): " . $this->connection->error);
            }
            $insertStmt->bind_param("ii", $medicationId, $quantity);
            $insertResult = $insertStmt->execute();
             if(!$insertResult){
                 die("Error inserting into inventory: " . $this->connection->error);
            }
            $insertStmt->close();
            return $insertResult;
        }
    }
    

    private function getPricePerBottle($prescriptionId) {
        $conn = $this->getConnection();
        $sql = "SELECT m.price * p.quantity AS pricePerBottle
                FROM Prescriptions p
                JOIN Medications m ON p.medicationId = m.medicationId
                WHERE p.prescriptionId = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $prescriptionId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['pricePerBottle'];
        }
        return null;
    }

    public function processSale($prescriptionId, $quantity) {
        
        $conn = $this->getConnection();

        if ($conn === null) {
            error_log("No database connection in processSale.");
            return false;
        }

        $conn->autocommit(false); // Start transaction

        try {
            // Fetch the price per bottle
            $pricePerBottle = $this->getPricePerBottle($prescriptionId);

            if ($pricePerBottle === null) {
                $conn->rollback();
                error_log("Error: Could not retrieve price per bottle for prescription ID: " . $prescriptionId);
                return false;
            }

            $saleAmount = $quantity * $pricePerBottle;

            // Insert into sales table INCLUDING saleAmount
            $sqlSales = "INSERT INTO sales (prescriptionId, quantitySold, saleDate, saleAmount) VALUES (?, ?, NOW(), ?)";
            $stmtSales = $conn->prepare($sqlSales);
            $stmtSales->bind_param('iis', $prescriptionId, $quantity, $saleAmount); // 's' for string/decimal
            $stmtSales->execute();

            if ($stmtSales->affected_rows > 0) {
                // Update refill count in prescriptions table
                $sqlRefill = "UPDATE Prescriptions SET refillCount = refillCount - ? WHERE prescriptionId = ?";
                $stmtRefill = $conn->prepare($sqlRefill);
                $stmtRefill->bind_param('ii', $quantity, $prescriptionId);
                $stmtRefill->execute();

                if ($stmtRefill->affected_rows >= 0) {
                    $conn->commit();
                    $stmtSales->close();
                    $stmtRefill->close();
                    return true;
                } else {
                    $conn->rollback();
                    $stmtSales->close();
                    $stmtRefill->close();
                    error_log("Error updating refill count. Rolling back sale.");
                    return false;
                }
            } else {
                $conn->rollback();
                $stmtSales->close();
                error_log("Error inserting into sales table. Rolling back.");
                return false;
            }

        } catch (Exception $e) {
            $conn->rollback();
            if (isset($stmtSales)) $stmtSales->close();
            if (isset($stmtRefill)) $stmtRefill->close();
            error_log("Exception during sale process: " . $e->getMessage());
            return false;
        }
    }
    


}
?>

