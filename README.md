# CIS344-FinalProject
  This repository contains the code for a Pharmacy Set-up.

## Table of Contents
* Installation
* Database Setup
* Running the application
* Testing the Application


## Installation
Clone this repository in your local machine:
* .htaccess
* PharmacyDatabase.php
* PharmacyServer.php
* addInventory.php
* addMedication.php
* addPrescription.php
* home.php
* login.php
* logout.php
* patient_home.php
* pharmacy_portal_db.sql
* register.php
* sales.php
* style.css
* viewInventory.php
* viewPrescription.php

## Database Setup
1.  Start the Apache and MySQL services in your XAMPP Control Panel.
2.  Open your web browser and navigate to http://localhost/phpmyadmin/
3.  Create a new database named pharmacy_portal_db
4.  Import the pharmacy_portal_db.sql file located in the project root into the database you just created. This will create the necessary tables and schema.

## Running the application
1.  Open your Web browser
2.  Navigate to the application's homepage. The URL will typically be:
    * http://localhost/index.html (if you placed the folder directly in htdocs)
    * if placed in a folder type: http://localhost/your-folder-name/login.php

      Adjust the URL based on your project's location within the web server's document root.

## Testing the Application  
1.  **Browse the Website:** Navigate through the different pages (login, patient_home, sales, pharmacy portal, etc.) to ensure the basic layout is working.

### Patient's View
1.  ** Buy Prescription:**
    * Fill out the login form or register as a new user.
    * If you login as an already created user, click the **"Process Sale"** button and choose the amount of bottle you want to buy.
    * After purchase, a green box must pop-up saying **"Sucess! Sale Processed"**. Then click the **"Back to Dashboard"** button to get back to the patient home page.
    * Check your database (using phpMyAdmin) to confirm that the sale has been added to the appropriate table (Sales).
  
### Pharmacist's View
1.  **Pharmacist Dashboard:**
     * You can go through any of the available websites that are displayed.
2. **Add Prescription:**
     * Make sure the information you're adding for Patient Username, and Medication ID are in the database.
     * Fill out the form, then click save.
     * It should display the form again and on top of the page should display the information you just added.
     * Using phpmyadmin, check if it was added in the database in the Prescriptions table.
3. **View Prescriptions:**
     * When you enter this page, a table with all of the prescriptions will be displayed. The patients names won't be exposed, their assigned user ID will be the only thing showing.
     * It will display the:
       * Prescription ID
       * User ID
       * Medication ID
       * Medication Name
       * Dosage Instrucations
       * Quantity in each bottle
4. **Add Medication:**
    * This page contains a form you should fill out if you need to add a Medication into the system.
    * It asks for:
       * Medication Name
       * Dosage
       * Manufacturer
       * Price per pill
     * When done correct, the form will clear out and at the top of the page, it should say *"Medication added Successfully! Medication ID: "*
     * Using phpmyadmin, check if it was added in the database in the Medication table.
5.  **Add to Inventory:**
      * This page should display a dropdown menu with all of the medication names that are in the database, as well as an input box asking how much you want to add to the inventory.
      * When all of the information was input correctly then on top of the newly empty form it should say *"Medication added to inventory sucessfully!"*
      * This is required when you added a new medication into the medication table, or when you've ran out of inventory.
      * Using phpmyadmin, check if it was added in the database in the Inventory table.
6. **View Inventory:**
   * This page will show a table displaying all of the Medication that is in the database, as well as the quantity available.
   * It will display:
       * Medication Name
       * Dosage
       * Manufacturer
       * Quantity Available

7. **Log out:**
    * When you log out from either (Patient, Pharmacist) dashboard, you will be taken back to the login page.
