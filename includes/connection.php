<?php
// The main file contains the database connection, session initializing, and template function, other PHP files will depend on this file.
// Include the configuration file
include_once __DIR__ . '/config.php';
// We need to use sessions, so you should always start sessions using the below code.
session_start();
// Connect to the MySQL database using the PDO interface
try {
    $pdo = new PDO('mysql:host=' . db_host . ';dbname=' . db_name . ';charset=' . db_charset, db_user, db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $exception) {
    // If there is an error with the connection, stop the script and display the error.
    exit('Failed to connect to database!');
}
