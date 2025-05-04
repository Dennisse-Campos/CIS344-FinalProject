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

    public function addUser($userName, $contactInfo, $userType, $password) {
           //Write Code here
        
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Use a prepared statement to prevent SQL injection
        $stmt = $this->connection->prepare("INSERT INTO Users (userName, contactInfo, userType, password) VALUES (?, ?, ?, ?)");
        if ($stmt === false) {
           die("Error preparing statement: " . $this->connection->error); //  Important
        }
   
        $stmt->bind_param("ssss", $userName, $contactInfo, $userType, $hashedPassword);
   
        if ($stmt->execute()) {
           $stmt->close();
           return true; // Indicate success
       } else {
           error_log("Error executing query: " . $this->connection->error); // Log the error
           $stmt->close();
           return false; // Indicate failure
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
        $stmt = $this->connection->prepare("SELECT userId, userName, contactInfo, userType FROM Users WHERE userId = ?");
        if ($stmt === false) {
            die("Error preparing statement: " . $this->connection->error);
        }
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $userResult = $stmt->get_result();

        if ($userResult->num_rows == 0) {
            $stmt->close();
            return null; // Or throw an exception: throw new Exception("User not found");
        }
        $userDetails = $userResult->fetch_assoc();
        $stmt->close();

        // Get user's prescriptions
        $stmt = $this->connection->prepare("SELECT p.prescriptionId, p.dosageInstructions, p.quantity, m.medicationName 
                                            FROM Prescriptions p 
                                            JOIN Medications m ON p.medicationId = m.medicationId
                                            WHERE p.userId = ?");
        if ($stmt === false) {
            die("Error preparing statement: " . $this->connection->error);
        }
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $prescriptionResult = $stmt->get_result();
        $userDetails['prescriptions'] = $prescriptionResult->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $userDetails;
    }

    public function getUserIdFromUsername($username) {
        $stmt = $this->connection->prepare(
            "SELECT userId FROM Users WHERE userName = ? AND userType = 'patient'"
        );
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->bind_result($userId);
        $stmt->fetch();
        $stmt->close();
        return $userId; // Return the userId (which might be null if not found)
    }


}
?>
