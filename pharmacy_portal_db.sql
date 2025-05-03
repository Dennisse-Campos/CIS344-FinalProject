-- MySQL Tables and Relationships --

-- Create the Database --
CREATE DATABASE pharmacy_portal_db;

-- Call the database --
USE pharmacy_portal_db;

-- 1. Users Table --
CREATE TABLE IF NOT EXISTS Users (
	userID INT NOT NULL UNIQUE AUTO_INCREMENT PRIMARY KEY,
    userName VARCHAR(45) NOT NULL UNIQUE,
    contactInfo VARCHAR(200),
    userType ENUM('pharmacist','patient') NOT NULL,
	password VARCHAR(255) NOT NULL);

-- 2.Medications Table --
CREATE TABLE IF NOT EXISTS Medications(
	medicationId INT NOT NULL UNIQUE AUTO_INCREMENT PRIMARY KEY,
    medicationName VARCHAR(45) NOT NULL,
    dosage VARCHAR(45) NOT NULL,
    manufacturer VARCHAR(100)
	price DECIMAL(10,2) NOT NULL);

-- 3.Prescriptions Table --
CREATE TABLE IF NOT EXISTS Prescriptions (
	prescriptionID INT NOT NULL UNIQUE AUTO_INCREMENT PRIMARY KEY,
    userID INT NOT NULL,
    medicationId INT NOT NULL,
    prescribedDate DATETIME NOT NULL,
    dosageInstructions VARCHAR(200),
    quantity INT NOT NULL,
    refillCount INT DEFAULT 0,
    FOREIGN KEY (userID) REFERENCES Users(userID),
    FOREIGN KEY (medicationId) REFERENCES Medications(medicationId));

-- 4. Inventory Table --    
CREATE TABLE IF NOT EXISTS Inventory(
	inventoryID INT NOT NULL UNIQUE AUTO_INCREMENT PRIMARY KEY,
    medicationID INT NOT NULL,
    quantityAvailable INT NOT NULL,
    lastUpdated DATETIME NOT NULL,
    FOREIGN KEY (medicationID) REFERENCES Medications(medicationId));

-- 5. Sales Table --    
CREATE TABLE IF NOT EXISTS Sales(
	saleID INT NOT NULL UNIQUE AUTO_INCREMENT PRIMARY KEY,
    prescriptionID INT NOT NULL,
    saleDate DATETIME NOT NULL,
    quantitySold INT NOT NULL,
    saleAmount DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (prescriptionID) REFERENCES Prescriptions(prescriptionID));
    
-- Stored Procedures Views and Triggers --

-- 1. AddorUpdate Stored Procedure --
DELIMITER //
CREATE PROCEDURE AddOrUpdateUser(
	IN p_userID INT,
	IN p_userName VARCHAR(45),
	IN p_contactInfo VARCHAR(200),
	IN p_userType VARCHAR(200)
)
BEGIN
	IF p_userID IS NOT NULL THEN
		UPDATE Users
        SET userName = p_userName,
			contactInfo = p_contactInfo,
            userType = p_userType
		WHERE userID = p_userID;
	ELSE
		INSERT INTO Users(userName, contactInfo, userType)
        VALUES (p_userName, p_contactInfo, p_userType);
	END IF;
END //
DELIMITER ;

-- 2. ProcessSale Stored Procedures --
DELIMITER //
CREATE PROCEDURE ProcessSale(
	IN p_prescriptionID INT,
    IN p_quantitySold INT
)
BEGIN
	DECLARE v_medicationId INT;
    DECLARE v_quantityAvailable INT;
	DECLARE v_prescriptionQuantity INT;
     
     -- Get medicationID and prescription quantity -- 
     SELECT medicationId, quantity INTO v_medicationId,v_prescriptionQuantity
     FROM Prescriptions
     WHERE prescriptionID = p_prescriptionID ;
     
     -- Get current Inventory --
     SELECT quantityAvailable INTO v_quantityAvailable
     FROM Inventory
     WHERE medicationId = v_medicationId;
     
     -- Check if enough stock is available --
     IF v_quantityAvailable >= p_quantitySold THEN
     
		-- Inventory Update --
		UPDATE Inventory
		SET quantityAvailable = quantityAvailable - p_quantitySold,
			lastUpdated = NOW()
		WHERE medicationId = v_medicationId;
    
		-- Insert sale record --
		INSERT INTO Sales (prescriptionID, saleDate, quantitySold, saleAmount)
		VALUES (p_prescriptionID, NOW(),p_quantitySold, 0.00);  -- Placeholder for saleAmount --
    
		SELECT 'Sale processed sucessfully.' AS message;
	ELSE
		SELECT 'Insufficient stock.' AS message;
	END IF;
END //
DELIMITER ;

-- View: MedicationInventoryView --
CREATE VIEW MedicationInventoryView AS
SELECT
	m.medicationName,
    m.dosage,
    m.manufacturer,
    i.quantityAvailable
    
FROM Medications m
JOIN Inventory i ON m.medicationId = i.medicationId;

-- Trigger: AfterPrescriptionInsert --
DELIMITER //
CREATE TRIGGER AfterPrescriptionInsert
AFTER INSERT ON Prescriptions
FOR EACH ROW
BEGIN
	UPDATE Inventory
	SET quantityAvailable = quantityAvailable - NEW.quantity, lastUpdated = NOW()
    WHERE medicationId = NEW.medicationId;
    
    -- Notify if stock is low
    IF (SELECT quantityAvailable FROM Inventory WHERE medicationId = NEW.medicationId) < 10 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Low stock for medication ID: ';
    END IF;
END //
DELIMITER ;

-- Data Population --
INSERT INTO Users (userName, contactInfo, userType, password)
	VALUES 	('jane_doe', 'jane@example.com', 'patient', '123'),
			('john_smith', 'john@example.com', 'pharmacist', '123'),
            ('alice_brown', 'alice@example.com', 'patient', '123');
            
INSERT INTO Medications (medicationName, dosage, manufacturer, price)
	VALUES	('Amoxicillin', '500mg', 'Pfizer', 0.61),
			('Ibuprofen', '200mg', 'Johnson & Johnson', 0.20),
            ('Paracetamol', '500mg', 'Bayer', 0.14);
            
INSERT INTO Prescriptions (userID, medicationId, prescribedDate, dosageInstructions, quantity, refillCount)
	VALUES	(1, 1, NOW(), 'Take 1 capsule every 8 hours', 10, 1),
			(3, 2, NOW(), 'Take 1 tablet every 6 hours', 15, 0),
			(1, 3, NOW(), 'Take 2 tablets twice a day', 20, 2);
            
INSERT INTO Inventory (medicationID, quantityAvailable, lastUpdated)
	VALUES	(1, 150, NOW()),
			(2, 200, NOW()),
            (3, 250, NOW());
            
INSERT INTO Sales (prescriptionID, saleDate, quantitySold, saleAmount)
	VALUES	(1, NOW(), 21, 12.81),
			(2, NOW(), 15, 3.00),
            (3, NOW(), 20, 2.80);
	
